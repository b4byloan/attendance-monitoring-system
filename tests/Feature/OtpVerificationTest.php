<?php

use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Notifications\OtpCodeNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

function otpUser(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'role' => 'student',
        'is_active' => true,
        'email_verified_at' => null,
        'otp_code' => Hash::make('123456'),
        'otp_expires_at' => now()->addMinutes(10),
        'otp_attempts' => 0,
    ], $attributes));
}

function otpSection(): Section
{
    return Section::create([
        'name' => 'Grade 10 - Rizal',
        'grade_level' => 'Grade 10',
        'school_year' => '2026-2027',
        'max_capacity' => 50,
    ]);
}

it('redirects unverified accounts to the otp screen', function () {
    $section = otpSection();
    $user = otpUser();
    Student::create([
        'lrn' => '123456789701',
        'first_name' => 'Otp',
        'last_name' => 'Student',
        'section_id' => $section->id,
        'status' => 'active',
        'qr_code' => 'otp-student-qr',
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('student.dashboard'))
        ->assertRedirect(route('otp.show'));

    $this->actingAs($user)
        ->get(route('otp.show'))
        ->assertOk()
        ->assertSee('Enter your one-time password')
        ->assertSee($user->email)
        ->assertSee('Resend code');
});

it('rejects a wrong otp code and accepts the correct one', function () {
    $user = otpUser(['role' => 'guard']);

    $this->actingAs($user->fresh())
        ->post(route('otp.verify'), ['code' => '000000'])
        ->assertSessionHasErrors('code');

    expect($user->fresh()->email_verified_at)->toBeNull()
        ->and($user->fresh()->otp_attempts)->toBe(1);

    $this->actingAs($user->fresh())
        ->post(route('otp.verify'), ['code' => '123456'])
        ->assertRedirect(route('guard.dashboard'));

    $user->refresh();

    expect($user->email_verified_at)->not->toBeNull()
        ->and($user->otp_code)->toBeNull()
        ->and($user->otp_attempts)->toBe(0);

    $this->actingAs($user)
        ->get(route('guard.dashboard'))
        ->assertOk();
});

it('rejects an expired otp code', function () {
    $user = otpUser([
        'role' => 'guard',
        'otp_expires_at' => now()->subMinute(),
    ]);

    $this->actingAs($user)
        ->post(route('otp.verify'), ['code' => '123456'])
        ->assertSessionHasErrors('code');

    expect($user->fresh()->email_verified_at)->toBeNull();
});

it('resends a fresh otp code', function () {
    Notification::fake();

    $user = otpUser(['role' => 'guard']);
    $previous = $user->otp_code;

    $this->actingAs($user)
        ->post(route('otp.resend'))
        ->assertSessionHas('success');

    expect($user->fresh()->otp_code)->not->toBe($previous);

    Notification::assertSentTo($user, OtpCodeNotification::class);
});

it('sends an otp when an unverified account signs in', function () {
    Notification::fake();

    $user = User::factory()->create([
        'role' => 'guard',
        'is_active' => true,
        'email_verified_at' => null,
        'password' => 'password123',
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password123',
    ])->assertRedirect(route('otp.show'));

    $this->assertAuthenticatedAs($user);

    Notification::assertSentTo($user, OtpCodeNotification::class);
});

it('creates admin accounts unverified and emails an otp', function () {
    Notification::fake();

    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->post(route('admin.users.store-account'), [
            'account_type' => 'teacher',
            'first_name' => 'Otp',
            'last_name' => 'Teacher',
            'email' => 'otp.teacher@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])
        ->assertRedirect(route('admin.users.index', ['tab' => 'teachers']))
        ->assertSessionHas('success');

    $teacher = User::where('email', 'otp.teacher@example.com')->firstOrFail();

    expect($teacher->email_verified_at)->toBeNull()
        ->and($teacher->otp_code)->not->toBeNull();

    Notification::assertSentTo($teacher, OtpCodeNotification::class);
});

it('shows the otp confirmation modal on the admin add-user form', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'teachers']))
        ->assertOk()
        ->assertSee('add-user-otp-modal', false)
        ->assertSee('Verification code required')
        ->assertSee('Yes, create and send OTP')
        ->assertSee('one-time password (OTP)', false);
});
