<?php

use App\Models\Section;
use App\Models\Student;
use App\Models\User;

it('shows the signed-in student their own QR code from the dashboard', function () {
    $user = User::factory()->create(['role' => 'student', 'is_active' => true]);
    $section = Section::create([
        'name' => 'Grade 7 - Rizal',
        'grade_level' => 'Grade 7',
        'school_year' => '2026-2027',
    ]);
    $student = Student::create([
        'lrn' => '123456789016',
        'first_name' => 'Own',
        'last_name' => 'Student',
        'section_id' => $section->id,
        'qr_code' => 'own-student-qr-code',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee('student-qr-dialog')
        ->assertSee('data:image/png;base64', false)
        ->assertSee(route('student.qr-code.download'))
        ->assertSee('Full history')
        ->assertDontSee('My Profile')
        ->assertDontSee('My Attendance');

    $this->get(route('student.qr-code'))
        ->assertOk()
        ->assertSee('data:image/png;base64', false)
        ->assertSee($student->full_name);
});
