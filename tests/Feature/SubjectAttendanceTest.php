<?php

use App\Mail\AttendanceRecorded;
use App\Models\Guardian;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAttendanceRecord;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

function subjectScanFixture(): array
{
    $teacher = User::factory()->create(['role' => 'teacher', 'is_active' => true]);

    $section = Section::create([
        'name' => 'Grade 8 - Mabini',
        'grade_level' => 'Grade 8',
        'school_year' => '2026-2027',
        'adviser_id' => $teacher->id,
    ]);

    $subject = Subject::create([
        'name' => 'Mathematics',
        'code' => 'MATH',
        'grade_level' => 'Grade 8',
        'status' => 'active',
    ]);
    $subject->teachers()->attach($teacher);

    $student = Student::create([
        'lrn' => '123456789022',
        'first_name' => 'Class',
        'last_name' => 'Learner',
        'section_id' => $section->id,
        'qr_code' => 'class-learner-qr',
        'status' => 'active',
    ]);

    $guardian = Guardian::create([
        'first_name' => 'Parent',
        'last_name' => 'Learner',
        'phone_number' => '09171234567',
        'email' => 'parent@example.test',
        'status' => 'active',
    ]);
    $guardian->students()->attach($student);

    return [$teacher, $subject, $student, $section];
}

it('records classroom attendance under the selected subject', function () {
    Mail::fake();

    [$teacher, $subject, $student] = subjectScanFixture();

    $this->actingAs($teacher)
        ->get(route('teacher-scanner.index'))
        ->assertOk()
        ->assertSee($subject->name);

    $this->actingAs($teacher)
        ->postJson(route('teacher-scanner.verify'), [
            'token' => $student->qr_code,
            'subject_id' => $subject->id,
        ])
        ->assertOk()
        ->assertJson([
            'found' => true,
            'attendance' => 'present',
            'subject' => 'Mathematics',
        ]);

    $this->assertDatabaseHas('subject_attendance_records', [
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'recorded_by' => $teacher->id,
        'status' => 'present',
    ]);

    Mail::assertQueued(AttendanceRecorded::class);
});

it('rejects a scan when no subject is selected', function () {
    [$teacher, , $student] = subjectScanFixture();

    $this->actingAs($teacher)
        ->postJson(route('teacher-scanner.verify'), ['token' => $student->qr_code])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('subject_id');
});

it('prevents duplicate subject attendance on the same day', function () {
    [$teacher, $subject, $student] = subjectScanFixture();

    SubjectAttendanceRecord::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'recorded_by' => $teacher->id,
        'status' => 'present',
        'attendance_date' => now()->toDateString(),
        'scanned_at' => now(),
    ]);

    $this->actingAs($teacher)
        ->postJson(route('teacher-scanner.verify'), [
            'token' => $student->qr_code,
            'subject_id' => $subject->id,
        ])
        ->assertOk()
        ->assertJson(['attendance' => 'already_recorded']);

    expect(SubjectAttendanceRecord::count())->toBe(1);
});

it('shows saved subject attendance in student information for teachers and admins', function () {
    [$teacher, $subject, $student] = subjectScanFixture();

    SubjectAttendanceRecord::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'recorded_by' => $teacher->id,
        'status' => 'present',
        'attendance_date' => now()->toDateString(),
        'scanned_at' => now(),
    ]);

    $this->actingAs($teacher)
        ->get(route('sub-admin.students.show', [
            'student' => $student,
            'tab' => 'attendance',
            'subject' => $subject->id,
        ]))
        ->assertOk()
        ->assertSee('Subject Attendance')
        ->assertSee('Mathematics')
        ->assertSee('Present');

    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->get(route('admin.students.show', ['student' => $student, 'tab' => 'attendance']))
        ->assertOk()
        ->assertSee('Mathematics')
        ->assertSee('Gate Attendance');
});

it('does not let a teacher scan a student outside their sections', function () {
    [$teacher, $subject] = subjectScanFixture();

    $otherSection = Section::create([
        'name' => 'Grade 9 - Del Pilar',
        'grade_level' => 'Grade 9',
        'school_year' => '2026-2027',
    ]);

    $outsider = Student::create([
        'lrn' => '123456789023',
        'first_name' => 'Other',
        'last_name' => 'Section',
        'section_id' => $otherSection->id,
        'qr_code' => 'outside-section-qr',
        'status' => 'active',
    ]);

    $this->actingAs($teacher)
        ->postJson(route('teacher-scanner.verify'), [
            'token' => $outsider->qr_code,
            'subject_id' => $subject->id,
        ])
        ->assertForbidden();

    expect(SubjectAttendanceRecord::count())->toBe(0);
});
