<?php

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

it('emails a password reset link instead of generating one', function () {
    Notification::fake();

    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $target = User::factory()->create(['role' => 'guard', 'email' => 'guard@example.com']);

    $this->actingAs($admin)
        ->from(route('admin.users.edit', $target))
        ->patch(route('admin.users.reset-password', $target))
        ->assertRedirect(route('admin.users.edit', $target))
        ->assertSessionHas('success')
        ->assertSessionMissing('generated_password');

    expect(Hash::check('password', $target->fresh()->password))->toBeTrue();

    Notification::assertSentTo($target, ResetPasswordNotification::class);
});

it('offers a reset action in the accounts table instead of the edit page', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $teacher = User::factory()->create(['name' => 'Reset Teacher', 'role' => 'teacher', 'email' => 'reset.teacher@example.com']);
    $otherAdmin = User::factory()->create(['name' => 'Reset Admin', 'role' => 'admin', 'email' => 'reset.admin@example.com']);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'teachers']))
        ->assertOk()
        ->assertSee('Reset password')
        ->assertSee('account-reset-modal-'.$teacher->id, false)
        ->assertSee('Send a password reset email to Reset Teacher?')
        ->assertSee(route('admin.users.reset-password', $teacher), false);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'admin']))
        ->assertOk()
        ->assertSee('account-reset-modal-'.$otherAdmin->id, false)
        ->assertSee('Set a new password for Reset Admin?')
        ->assertSee(route('admin.users.set-password', $otherAdmin), false)
        ->assertSee('name="password_confirmation"', false);

    $this->actingAs($admin)
        ->get(route('admin.users.edit', $teacher))
        ->assertOk()
        ->assertDontSee('Send Reset Email')
        ->assertDontSee('reset-password-modal', false);
});

it('sets a new password directly for admin accounts only', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $target = User::factory()->create(['name' => 'Target Admin', 'role' => 'admin', 'email' => 'target.admin@example.com']);
    $teacher = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($admin)
        ->from(route('admin.users.index', ['tab' => 'admin']))
        ->patch(route('admin.users.set-password', $target), [
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ])
        ->assertRedirect(route('admin.users.index', ['tab' => 'admin']))
        ->assertSessionHas('success');

    expect(Hash::check('brand-new-password', $target->fresh()->password))->toBeTrue();

    $this->actingAs($admin)
        ->from(route('admin.users.index', ['tab' => 'teachers']))
        ->patch(route('admin.users.set-password', $teacher), [
            'password' => 'another-password',
            'password_confirmation' => 'another-password',
        ])
        ->assertSessionHasErrors('password');

    expect(Hash::check('password', $teacher->fresh()->password))->toBeTrue();
});
