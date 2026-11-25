<?php

use App\Models\Section;
use App\Models\Student;
use App\Models\User;

function recognitionFixture(): array
{
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $section = Section::create([
        'name' => 'Grade 7 - Rizal',
        'grade_level' => 'Grade 7',
        'school_year' => '2026-2027',
    ]);

    $student = Student::create([
        'lrn' => '123456789016',
        'first_name' => 'Scan',
        'last_name' => 'Tolerant',
        'section_id' => $section->id,
        'qr_code' => 'aB3dE5fG7hJ9kLmN2pQrS4tU6vW8xY0z',
        'status' => 'active',
    ]);

    return [$admin, $student];
}

it('recognises a valid QR code when the scanner adds line breaks or changes case', function () {
    [$admin, $student] = recognitionFixture();

    $this->actingAs($admin)
        ->postJson(route('scanner.verify'), ['token' => "  AB3DE5FG7HJ9KLMN2PQRS4TU6VW8XY0Z\r\n"])
        ->assertOk()
        ->assertJson([
            'found' => true,
            'active' => true,
            'attendance' => 'check_in',
        ])
        ->assertJsonPath('student.lrn', $student->lrn);
});

it('still reports genuinely unknown QR codes as not found', function () {
    [$admin] = recognitionFixture();

    $this->actingAs($admin)
        ->postJson(route('scanner.verify'), ['token' => 'not-a-student-code'])
        ->assertOk()
        ->assertJson(['found' => false]);
});

it('shows a student QR code inside a modal on the user list', function () {
    [$admin, $student] = recognitionFixture();

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'students']))
        ->assertOk()
        ->assertSee('student-qr-modal-'.$student->id)
        ->assertSee('data:image/png;base64', false)
        ->assertSee($student->full_name);
});

it('shows a student QR code inside a modal on the student list', function () {
    [$admin, $student] = recognitionFixture();

    $this->actingAs($admin)
        ->get(route('admin.students.index'))
        ->assertOk()
        ->assertSee('qr-modal-'.$student->id)
        ->assertSee('data:image/png;base64', false);
});

it('shows a student QR code inside a modal on the student detail page', function () {
    [$admin, $student] = recognitionFixture();

    $this->actingAs($admin)
        ->get(route('admin.students.show', $student))
        ->assertOk()
        ->assertSee('student-qr-modal')
        ->assertSee('data:image/png;base64', false);
});
