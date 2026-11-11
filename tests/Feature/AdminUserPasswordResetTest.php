<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('generates a new password on the server instead of accepting one from the client', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $target = User::factory()->create(['role' => 'guard']);

    $response = $this->actingAs($admin)->patch(route('admin.users.reset-password', $target));

    $generatedPassword = session('generated_password');

    $response->assertRedirect(route('admin.users.edit', $target));
    expect($generatedPassword)->toBeString()->toHaveLength(16);
    expect(Hash::check($generatedPassword, $target->fresh()->password))->toBeTrue();
    expect(Hash::check('password', $target->fresh()->password))->toBeFalse();
});

it('shows the generated password once on the edit page', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $target = User::factory()->create(['role' => 'guard']);

    $this->actingAs($admin)
        ->patch(route('admin.users.reset-password', $target))
        ->assertSessionHas('generated_password');

    $generatedPassword = session('generated_password');

    $this->actingAs($admin)
        ->get(route('admin.users.edit', $target))
        ->assertOk()
        ->assertSee($generatedPassword)
        ->assertSee('This password will not be shown again. Copy it now and share it securely with the user.');
});
