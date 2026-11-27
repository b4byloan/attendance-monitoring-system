<?php

use App\Exports\AttendanceExport;
use App\Models\AttendanceRecord;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;

function dashboardReportFixture(): array
{
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $grade9 = Section::create([
        'name' => 'Grade 9 - Rizal',
        'grade_level' => 'Grade 9',
        'school_year' => '2026-2027',
        'max_capacity' => 50,
    ]);

    $grade10 = Section::create([
        'name' => 'Grade 10 - Bonifacio',
        'grade_level' => 'Grade 10',
        'school_year' => '2026-2027',
        'max_capacity' => 50,
    ]);

    $present = Student::create([
        'lrn' => '123456789601',
        'first_name' => 'Present',
        'last_name' => 'Student',
        'section_id' => $grade9->id,
        'status' => 'active',
        'qr_code' => 'dash-present-qr',
    ]);

    $away = Student::create([
        'lrn' => '123456789602',
        'first_name' => 'Away',
        'last_name' => 'Student',
        'section_id' => $grade10->id,
        'status' => 'active',
        'qr_code' => 'dash-away-qr',
    ]);

    $inactive = Student::create([
        'lrn' => '123456789603',
        'first_name' => 'Inactive',
        'last_name' => 'Student',
        'section_id' => $grade10->id,
        'status' => 'inactive',
        'qr_code' => 'dash-inactive-qr',
    ]);

    AttendanceRecord::create([
        'student_id' => $present->id,
        'type' => 'check_in',
        'attendance_date' => now()->toDateString(),
        'scanned_at' => now(),
    ]);

    AttendanceRecord::create([
        'student_id' => $inactive->id,
        'type' => 'check_in',
        'attendance_date' => now()->toDateString(),
        'scanned_at' => now(),
    ]);

    return [$admin, $present, $away, $inactive, $grade9, $grade10];
}

it('counts only active students in the dashboard present and absent statistics', function () {
    [$admin, $present] = dashboardReportFixture();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Present Today')
        ->assertSee('Absent Today')
        ->assertSee('>3</p>', false)
        ->assertSee('2 active')
        ->assertSee('>1</p>', false)
        ->assertSee('50% of active students')
        ->assertSee('bg-indigo-50', false)
        ->assertSee('bg-green-50', false)
        ->assertSee('bg-red-50', false)
        ->assertSee('bg-emerald-50', false)
        ->assertSee('bg-purple-50', false)
        ->assertSee('bg-amber-50', false);
});

it('shows a grade level column with sorting on the users students tab', function () {
    [$admin, $present, $away] = dashboardReportFixture();

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'students']))
        ->assertOk()
        ->assertSee('Grade Level')
        ->assertSee('Grade 9')
        ->assertSee('Grade 10');

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'students', 'sort' => 'grade_level', 'direction' => 'desc']))
        ->assertOk()
        ->assertSeeInOrder(['Grade 10', 'Grade 9']);
});

it('shows a grade level column with sorting in the reports module', function () {
    [$admin, $present, $away] = dashboardReportFixture();

    $this->actingAs($admin)
        ->get(route('admin.reports.index'))
        ->assertOk()
        ->assertSee('Grade Level')
        ->assertSee('Grade 9')
        ->assertSee('Grade 10');

    $this->actingAs($admin)
        ->get(route('admin.reports.index', ['sort' => 'grade_level', 'direction' => 'asc']))
        ->assertOk()
        ->assertSeeInOrder(['Grade 9 - Rizal', 'Grade 10 - Bonifacio']);
});

it('includes the grade level column in the attendance export', function () {
    $headings = (new AttendanceExport(new Collection))->headings();

    expect($headings)->toContain('Grade Level')
        ->and(array_search('Grade Level', $headings, true))->toBe(4);
});
