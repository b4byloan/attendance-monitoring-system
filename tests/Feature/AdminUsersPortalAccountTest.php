<?php

use App\Models\Guardian;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;

it('does not expose portal account management on student and guardian records', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $section = Section::create([
        'name' => 'Grade 7 - Rizal',
        'grade_level' => 'Grade 7',
        'school_year' => '2026-2027',
    ]);

    $studentUser = User::factory()->create([
        'role' => 'student',
        'is_active' => true,
        'email' => 'student.portal@example.com',
    ]);

    $student = Student::create([
        'lrn' => '123456789097',
        'first_name' => 'Portal',
        'last_name' => 'Student',
        'section_id' => $section->id,
        'qr_code' => 'qr-portal-student',
        'status' => 'active',
        'user_id' => $studentUser->id,
    ]);

    $guardianUser = User::factory()->create([
        'role' => 'parent',
        'is_active' => true,
        'email' => 'guardian.portal@example.com',
    ]);

    $guardian = Guardian::create([
        'first_name' => 'Portal',
        'last_name' => 'Guardian',
        'phone_number' => '09171234567',
        'email' => 'guardian.portal@example.com',
        'relationship_to_student' => 'Parent',
        'status' => 'active',
        'user_id' => $guardianUser->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'students']))
        ->assertOk()
        ->assertSee($student->full_name)
        ->assertDontSee('Portal Account')
        ->assertDontSee('Edit account')
        ->assertDontSee('Student Portal Accounts');

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'guardians']))
        ->assertOk()
        ->assertSee($guardian->first_name.' '.$guardian->last_name)
        ->assertDontSee('Portal Account')
        ->assertDontSee('Edit account')
        ->assertDontSee('Guardian Portal Accounts');

    $this->actingAs($admin)
        ->get(route('admin.students.show', $student))
        ->assertOk()
        ->assertDontSee('Student Portal Account')
        ->assertDontSee('Create Account');

    $this->actingAs($admin)
        ->get(route('admin.guardians.show', $guardian))
        ->assertOk()
        ->assertDontSee('Parent Portal Account')
        ->assertDontSee('Create Account');
});
