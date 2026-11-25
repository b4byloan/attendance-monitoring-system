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

it('shows the email reset option on the edit page', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $target = User::factory()->create(['role' => 'guard']);

    $this->actingAs($admin)
        ->get(route('admin.users.edit', $target))
        ->assertOk()
        ->assertSee('Send Reset Email')
        ->assertSee('reset-password-modal', false)
        ->assertSee('Yes, send reset email');
});
