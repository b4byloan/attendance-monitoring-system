<?php

use App\Models\AttendanceRecord;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;

it('limits sub-admin student information to assigned sections', function () {
    $adviser = User::factory()->create(['role' => 'sub_admin', 'is_active' => true]);
    $otherAdviser = User::factory()->create(['role' => 'sub_admin', 'is_active' => true]);

    $section = Section::create([
        'name' => 'Grade 7 - Rizal',
        'grade_level' => 'Grade 7',
        'school_year' => '2026-2027',
        'adviser_id' => $adviser->id,
    ]);

    $otherSection = Section::create([
        'name' => 'Grade 8 - Mabini',
        'grade_level' => 'Grade 8',
        'school_year' => '2026-2027',
        'adviser_id' => $otherAdviser->id,
    ]);

    $assignedStudent = Student::create([
        'lrn' => '123456789012',
        'first_name' => 'Assigned',
        'last_name' => 'Student',
        'section_id' => $section->id,
        'qr_code' => 'qr-assigned',
        'status' => 'active',
    ]);

    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $otherStudent = Student::create([
        'lrn' => '123456789013',
        'first_name' => 'Other',
        'last_name' => 'Student',
        'section_id' => $otherSection->id,
        'qr_code' => 'qr-other',
        'status' => 'active',
    ]);

    $this->actingAs($adviser)
        ->get(route('sub-admin.students.index'))
        ->assertOk()
        ->assertSee($assignedStudent->full_name)
        ->assertDontSee($otherStudent->full_name)
        ->assertSee('View all notifications');

    $this->actingAs($adviser)
        ->get(route('sub-admin.roster.index'))
        ->assertOk()
        ->assertSee($assignedStudent->full_name);

    $this->actingAs($adviser)
        ->get(route('sub-admin.sections.students', $section))
        ->assertOk()
        ->assertSee($assignedStudent->full_name);

    $this->actingAs($adviser)
        ->get(route('sub-admin.students.show', $assignedStudent))
        ->assertOk()
        ->assertSee('Current Teacher')
        ->assertSee($adviser->name)
        ->assertSee('Read-only');

    $this->actingAs($admin)
        ->get(route('admin.students.show', $assignedStudent))
        ->assertOk()
        ->assertSee('Current Teacher')
        ->assertSee($adviser->name);

    $this->actingAs($adviser)
        ->get(route('sub-admin.students.show', $otherStudent))
        ->assertForbidden();

    $this->actingAs($adviser)
        ->get(route('sub-admin.sections.students', $otherSection))
        ->assertForbidden();

    $this->actingAs($adviser)
        ->get(route('admin.students.edit', $assignedStudent))
        ->assertForbidden();
});

it('shows present today per assigned section on the sub-admin dashboard', function () {
    $adviser = User::factory()->create(['role' => 'sub_admin', 'is_active' => true]);

    $section = Section::create([
        'name' => 'Grade 7 - Rizal',
        'grade_level' => 'Grade 7',
        'school_year' => '2026-2027',
        'adviser_id' => $adviser->id,
    ]);

    $presentStudent = Student::create([
        'lrn' => '123456789014',
        'first_name' => 'Present',
        'last_name' => 'Student',
        'section_id' => $section->id,
        'qr_code' => 'qr-present',
        'status' => 'active',
    ]);

    Student::create([
        'lrn' => '123456789015',
        'first_name' => 'Absent',
        'last_name' => 'Student',
        'section_id' => $section->id,
        'qr_code' => 'qr-absent',
        'status' => 'active',
    ]);

    AttendanceRecord::create([
        'student_id' => $presentStudent->id,
        'type' => 'check_in',
        'scanned_at' => now(),
        'attendance_date' => now()->toDateString(),
    ]);

    AttendanceRecord::create([
        'student_id' => $presentStudent->id,
        'type' => 'check_out',
        'scanned_at' => now(),
        'attendance_date' => now()->toDateString(),
    ]);

    $response = $this->actingAs($adviser)
        ->get(route('sub-admin.dashboard'))
        ->assertOk()
        ->assertSee($section->name);

    expect($response->viewData('sections'))->toHaveCount(1)
        ->and($response->viewData('sections')->first()->present_today)->toBe(1)
        ->and($response->viewData('totalStudents'))->toBe(2)
        ->and($response->viewData('presentTodayCount'))->toBe(1);
});
