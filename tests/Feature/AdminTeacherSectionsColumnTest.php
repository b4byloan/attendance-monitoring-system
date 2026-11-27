<?php

use App\Models\Section;
use App\Models\User;

it('renders assigned sections as chips without a column separator', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $teacher = User::factory()->create(['name' => 'Chip Teacher', 'role' => 'teacher', 'is_active' => true]);

    $sections = collect([
        'Grade 7 - Narra',
        'Grade 8 - Rizal',
        'Grade 9 - Molave',
    ])->map(fn (string $name, int $index) => Section::create([
        'name' => $name,
        'grade_level' => 'Grade '.(7 + $index),
        'school_year' => '2026-2027',
        'max_capacity' => 50,
    ]));

    $teacher->sections()->attach($sections->pluck('id'));

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'teachers']))
        ->assertOk()
        ->assertSee('Assigned Sections')
        ->assertSee('Grade 7 - Narra')
        ->assertSee('Grade 8 - Rizal')
        ->assertSee('Grade 9 - Molave')
        ->assertSee('rounded-sm bg-indigo-100', false)
        ->assertSee('flex max-w-[240px] flex-wrap gap-1.5', false)
        ->assertDontSee('aria-hidden="true">::</span>', false)
        ->assertSee('<td class="px-4 py-4 whitespace-nowrap">', false);
});

it('keeps the status and last login cells from wrapping', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    User::factory()->create(['name' => 'Nowrap Teacher', 'role' => 'teacher', 'is_active' => true]);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'teachers']))
        ->assertOk()
        ->assertSee('whitespace-nowrap text-sm text-gray-500', false);
});
