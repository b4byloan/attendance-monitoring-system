<?php

use App\Models\User;

it('renders flash success messages as toasts', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->withSession(['success' => 'Backup created.'])
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('id="toast-container"', false)
        ->assertSeeInOrder(['data-toast-type="success"', 'Backup created.'], false);
});

it('renders validation errors as a single error toast', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->from(route('admin.students.index'))
        ->followingRedirects()
        ->post(route('admin.students.store'), [])
        ->assertOk()
        ->assertSeeInOrder(['data-toast-type="error"', 'The lrn field is required.'], false)
        ->assertSee('The first name field is required.');
});

it('renders toasts in the simple layout', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->withSession(['success' => 'Scanner ready.'])
        ->get(route('scanner.index'))
        ->assertOk()
        ->assertSee('id="toast-container"', false)
        ->assertSeeInOrder(['data-toast-type="success"', 'Scanner ready.'], false);
});
