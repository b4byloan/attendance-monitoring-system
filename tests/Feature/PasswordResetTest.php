<?php

use App\Models\Setting;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

it('shows the forgot password link and pages', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Forgot password?')
        ->assertSee('border-r border-slate-200', false);

    $this->get(route('password.request'))
        ->assertOk()
        ->assertSee('Email password reset link');

    $this->get(route('password.reset', ['token' => 'sample-token', 'email' => 'user@example.com']))
        ->assertOk()
        ->assertSee('Set a new password')
        ->assertSee('sample-token')
        ->assertSee('user@example.com');
});

it('sends a password reset link to a registered account', function () {
    Notification::fake();

    $user = User::factory()->create([
        'role' => 'student',
        'is_active' => true,
        'email' => 'reset@example.com',
    ]);

    $this->post(route('password.email'), ['email' => $user->email])
        ->assertRedirect()
        ->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

it('brands the reset email with the school name', function () {
    Notification::fake();

    Setting::current()->update(['school_name' => 'Eugenio Cabezas National Highschool']);

    $user = User::factory()->create([
        'role' => 'student',
        'is_active' => true,
        'email' => 'branded@example.com',
    ]);

    $this->post(route('password.email'), ['email' => $user->email])
        ->assertSessionHas('status');

    Notification::assertSentTo(
        $user,
        ResetPasswordNotification::class,
        function (ResetPasswordNotification $notification) use ($user): bool {
            $schoolName = 'Eugenio Cabezas National Highschool';
            $mail = $notification->toMail($user);
            $html = $mail->render();

            return $mail->subject === "Reset your {$schoolName} password"
                && $mail->from === [config('mail.from.address'), $schoolName]
                && $mail->view === 'emails.auth.reset-password'
                && $mail->viewData['schoolName'] === $schoolName
                && str_contains($html, $schoolName)
                && ! str_contains($html, 'Laravel');
        }
    );
});

it('rejects password reset requests for unknown emails', function () {
    Notification::fake();

    $this->post(route('password.email'), ['email' => 'missing@example.com'])
        ->assertSessionHasErrors('email');

    Notification::assertNothingSent();
});

it('resets the password with a valid token', function () {
    $user = User::factory()->create([
        'role' => 'student',
        'is_active' => true,
        'email' => 'reset2@example.com',
        'password' => 'oldpassword',
    ]);

    $token = Password::createToken($user);

    $this->post(route('password.store'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ])
        ->assertRedirect(route('login'))
        ->assertSessionHas('status');

    expect(Hash::check('newpassword123', $user->fresh()->password))->toBeTrue();

    $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
});

it('refuses to reset the password with an invalid token', function () {
    $user = User::factory()->create([
        'role' => 'student',
        'is_active' => true,
        'email' => 'reset3@example.com',
        'password' => 'oldpassword',
    ]);

    $this->post(route('password.store'), [
        'token' => 'not-a-real-token',
        'email' => $user->email,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ])->assertSessionHasErrors('email');

    expect(Hash::check('oldpassword', $user->fresh()->password))->toBeTrue();
});
