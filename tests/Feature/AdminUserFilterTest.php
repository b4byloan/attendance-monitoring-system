<?php

use App\Models\Section;
use App\Models\Student;
use App\Models\User;

it('filters the user list by the student role', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $section = Section::create([
        'name' => 'Grade 7 - Rizal',
        'grade_level' => 'Grade 7',
        'school_year' => '2026-2027',
    ]);

    $student = Student::create([
        'lrn' => '123456789098',
        'first_name' => 'Student',
        'last_name' => 'Record',
        'section_id' => $section->id,
        'qr_code' => 'qr-role-filter',
        'status' => 'active',
    ]);

    User::factory()->create([
        'name' => 'Parent Account',
        'role' => 'parent',
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['role' => 'student']))
        ->assertOk()
        ->assertSee('Student Records')
        ->assertSee($student->full_name)
        ->assertDontSee('Parent Account')
        ->assertDontSee('All Roles');
});
