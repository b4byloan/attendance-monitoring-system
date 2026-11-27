<?php

use App\Models\Announcement;
use App\Models\AttendanceRecord;
use App\Models\Guardian;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAttendanceRecord;
use App\Models\User;

function portalAttendanceFixture(): array
{
    $section = Section::create([
        'name' => 'Grade 10 - Rizal',
        'grade_level' => 'Grade 10',
        'school_year' => '2026-2027',
        'max_capacity' => 50,
    ]);

    $studentUser = User::factory()->create(['role' => 'student', 'is_active' => true]);
    $student = Student::create([
        'lrn' => '123456789501',
        'first_name' => 'Portal',
        'last_name' => 'Student',
        'section_id' => $section->id,
        'qr_code' => 'portal-attendance-qr',
        'status' => 'active',
        'user_id' => $studentUser->id,
    ]);

    $classmate = Student::create([
        'lrn' => '123456789502',
        'first_name' => 'Class',
        'last_name' => 'Mate',
        'section_id' => $section->id,
        'qr_code' => 'portal-attendance-qr-2',
        'status' => 'active',
    ]);

    $guardianUser = User::factory()->create(['role' => 'parent', 'is_active' => true]);
    $guardian = Guardian::create([
        'first_name' => 'Guardian',
        'last_name' => 'Parent',
        'phone_number' => '09171234567',
        'status' => 'active',
        'user_id' => $guardianUser->id,
    ]);
    $guardian->students()->attach($student->id);

    $subject = Subject::create([
        'name' => 'Mathematics',
        'code' => 'MATH',
        'description' => 'Math subject',
        'status' => 'active',
    ]);

    SubjectAttendanceRecord::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'status' => 'present',
        'attendance_date' => '2026-11-24',
        'scanned_at' => '2026-11-24 08:15:00',
    ]);

    SubjectAttendanceRecord::create([
        'student_id' => $classmate->id,
        'subject_id' => $subject->id,
        'status' => 'present',
        'attendance_date' => '2026-11-25',
        'scanned_at' => '2026-11-25 08:10:00',
    ]);

    AttendanceRecord::create([
        'student_id' => $student->id,
        'type' => 'check_in',
        'attendance_date' => '2026-11-25',
        'scanned_at' => '2026-11-25 07:45:00',
    ]);

    return [$studentUser, $student, $guardianUser, $guardian, $section, $subject];
}

it('shows subject attendance with present and away states on the student portal', function () {
    [$studentUser] = portalAttendanceFixture();

    $this->actingAs($studentUser)
        ->get(route('student.attendance'))
        ->assertOk()
        ->assertSee('Subject Attendance')
        ->assertSee('Mathematics')
        ->assertSee('Present')
        ->assertSee('Away')
        ->assertSee('Nov 24, 2026')
        ->assertSee('Nov 25, 2026');
});

it('shows subject attendance with present and away states on the guardian portal', function () {
    [, $student, $guardianUser] = portalAttendanceFixture();

    $this->actingAs($guardianUser)
        ->get(route('parent.students.attendance', $student))
        ->assertOk()
        ->assertSee('Subject Attendance')
        ->assertSee('Mathematics')
        ->assertSee('Present')
        ->assertSee('Away');
});

it('lists every announcement on the parent dashboard with a view more modal', function () {
    [, $student, $guardianUser, $guardian] = portalAttendanceFixture();

    foreach (range(1, 3) as $index) {
        Announcement::create([
            'title' => 'Announcement '.$index,
            'description' => 'Full body of announcement '.$index,
            'author_id' => $guardianUser->id,
            'section_id' => $student->section_id,
            'publish_date' => '2026-11-2'.$index,
            'status' => 'published',
        ]);
    }

    $this->actingAs($guardianUser)
        ->get(route('parent.students.dashboard', $student))
        ->assertOk()
        ->assertSee('Announcement 1')
        ->assertSee('Announcement 2')
        ->assertSee('Announcement 3')
        ->assertSee('View more')
        ->assertSee('announcement-modal', false)
        ->assertSee('announcement-prev', false)
        ->assertSee('announcement-next', false)
        ->assertSee('Full body of announcement 1', false)
        ->assertSee('grid-cols-[minmax(0,1fr)_auto]', false);
});
