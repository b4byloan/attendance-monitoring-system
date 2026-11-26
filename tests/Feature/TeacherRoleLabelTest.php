<?php

use App\Models\User;

it('presents sub-admin accounts as teachers', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    User::factory()->create(['name' => 'Legacy Teacher Account', 'role' => 'sub_admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'teachers']))
        ->assertOk()
        ->assertSee('Legacy Teacher Account')
        ->assertSee('Teacher')
        ->assertDontSee('Sub-Admin');
});
