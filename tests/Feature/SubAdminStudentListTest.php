<?php

use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAttendanceRecord;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-11-24 08:00:00', 'Asia/Manila'));
});

afterEach(function () {
    Carbon::setTestNow();
});

function studentListFixture(): array
{
    $teacher = User::factory()->create(['role' => 'teacher', 'is_active' => true]);

    $section = Section::create([
        'name' => 'Grade 7 - Rizal',
        'grade_level' => 'Grade 7',
        'school_year' => '2026-2027',
        'adviser_id' => $teacher->id,
    ]);

    $otherSection = Section::create([
        'name' => 'Grade 8 - Mabini',
        'grade_level' => 'Grade 8',
        'school_year' => '2026-2027',
    ]);

    $student = Student::create([
        'lrn' => '123456789100',
        'first_name' => 'Scanned',
        'last_name' => 'Learner',
        'section_id' => $section->id,
        'qr_code' => 'qr-scanned-learner',
        'status' => 'active',
    ]);

    $otherStudent = Student::create([
        'lrn' => '123456789101',
        'first_name' => 'Hidden',
        'last_name' => 'Learner',
        'section_id' => $otherSection->id,
        'qr_code' => 'qr-hidden-learner',
        'status' => 'active',
    ]);

    $math = Subject::create([
        'name' => 'Mathematics',
        'code' => 'MATH7',
        'grade_level' => 'Grade 7',
        'status' => 'active',
    ]);

    $science = Subject::create([
        'name' => 'Science',
        'code' => 'SCI7',
        'grade_level' => 'Grade 7',
        'status' => 'active',
    ]);

    $teacher->subjects()->attach([$math->id, $science->id]);

    SubjectAttendanceRecord::create([
        'student_id' => $student->id,
        'subject_id' => $math->id,
        'recorded_by' => $teacher->id,
        'status' => 'present',
        'attendance_date' => now()->toDateString(),
        'scanned_at' => now()->setTime(8, 15),
    ]);

    SubjectAttendanceRecord::create([
        'student_id' => $student->id,
        'subject_id' => $science->id,
        'recorded_by' => $teacher->id,
        'status' => 'present',
        'attendance_date' => now()->toDateString(),
        'scanned_at' => now()->setTime(9, 30),
    ]);

    SubjectAttendanceRecord::create([
        'student_id' => $otherStudent->id,
        'subject_id' => $math->id,
        'recorded_by' => $teacher->id,
        'status' => 'present',
        'attendance_date' => now()->toDateString(),
        'scanned_at' => now()->setTime(10, 0),
    ]);

    return [$teacher, $section, $student, $otherStudent, $math, $science];
}

it('lists every scanned student across subjects with a timestamp', function () {
    [$teacher, $section, $student, $otherStudent, $math, $science] = studentListFixture();

    $this->actingAs($teacher)
        ->get(route('sub-admin.roster.index'))
        ->assertOk()
        ->assertSee('Timestamp')
        ->assertSee($student->full_name)
        ->assertSee('Mathematics')
        ->assertSee('Science')
        ->assertSee('08:15 AM')
        ->assertSee('09:30 AM')
        ->assertDontSee($otherStudent->full_name);
});

it('filters the student list by subject, timestamp and name', function () {
    [$teacher, $section, $student, $otherStudent, $math, $science] = studentListFixture();

    $this->actingAs($teacher)
        ->get(route('sub-admin.roster.index', ['subject' => $math->id]))
        ->assertOk()
        ->assertSee('08:15 AM')
        ->assertDontSee('09:30 AM');

    $this->actingAs($teacher)
        ->get(route('sub-admin.roster.index', ['date' => now()->toDateString()]))
        ->assertOk()
        ->assertSee($student->full_name)
        ->assertSee('08:15 AM');

    $this->actingAs($teacher)
        ->get(route('sub-admin.roster.index', ['date' => now()->subDay()->toDateString()]))
        ->assertOk()
        ->assertDontSee($student->full_name)
        ->assertSee('No scanned students match these filters.');

    $this->actingAs($teacher)
        ->get(route('sub-admin.roster.index', ['search' => 'Scanned']))
        ->assertOk()
        ->assertSee($student->full_name);

    $this->actingAs($teacher)
        ->get(route('sub-admin.roster.index', ['search' => 'Hidden']))
        ->assertOk()
        ->assertDontSee($otherStudent->full_name)
        ->assertSee('No scanned students match these filters.');
});

it('exports the student list to excel, csv and pdf', function () {
    [$teacher] = studentListFixture();

    $filename = 'student-list-'.now()->format('Y-m-d');

    $this->actingAs($teacher)
        ->get(route('sub-admin.roster.export-excel'))
        ->assertOk()
        ->assertDownload($filename.'.xlsx');

    $this->actingAs($teacher)
        ->get(route('sub-admin.roster.export-csv'))
        ->assertOk()
        ->assertDownload($filename.'.csv');

    $this->actingAs($teacher)
        ->get(route('sub-admin.roster.export-pdf'))
        ->assertOk()
        ->assertDownload($filename.'.pdf');
});

it('shows the sections tab with a view students button', function () {
    [$teacher, $section, $student, $otherStudent, $math, $science] = studentListFixture();

    $this->actingAs($teacher)
        ->get(route('sub-admin.roster.index', ['tab' => 'sections']))
        ->assertOk()
        ->assertSee('Sections')
        ->assertSee($section->name)
        ->assertSee('View Students')
        ->assertSee(route('sub-admin.sections.students', $section), false)
        ->assertDontSee('Grade 8 - Mabini');
});

it('keeps the student list as a single top-level navigation entry', function () {
    [$teacher] = studentListFixture();

    $this->actingAs($teacher)
        ->get(route('sub-admin.dashboard'))
        ->assertOk()
        ->assertSee('href="'.route('sub-admin.roster.index').'"', false)
        ->assertDontSee('href="'.route('sub-admin.students.index').'"', false);
});
