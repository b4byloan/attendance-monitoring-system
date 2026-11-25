<?php

use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

function studentPasswordFixture(): array
{
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $section = Section::create([
        'name' => 'Grade 10 - Rizal',
        'grade_level' => 'Grade 10',
        'school_year' => '2026-2027',
    ]);

    $studentUser = User::factory()->create([
        'role' => 'student',
        'is_active' => true,
        'email' => 'student.registered@example.com',
    ]);

    $student = Student::create([
        'lrn' => '123456789012',
        'first_name' => 'Registered',
        'last_name' => 'Student',
        'section_id' => $section->id,
        'qr_code' => 'qr-registered-student',
        'status' => 'active',
        'user_id' => $studentUser->id,
    ]);

    $unregistered = Student::create([
        'lrn' => '123456789013',
        'first_name' => 'Unregistered',
        'last_name' => 'Student',
        'section_id' => $section->id,
        'qr_code' => 'qr-unregistered-student',
        'status' => 'active',
    ]);

    return [$admin, $section, $student, $studentUser, $unregistered];
}

it('shows the registered email on the student records', function () {
    [$admin, $section, $student, $studentUser, $unregistered] = studentPasswordFixture();

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'students']))
        ->assertOk()
        ->assertSee('Email')
        ->assertSee($studentUser->email)
        ->assertSee('Not registered')
        ->assertSee('student-reset-password-modal-'.$student->id, false)
        ->assertDontSee('student-reset-password-modal-'.$unregistered->id, false);

    $this->actingAs($admin)
        ->get(route('admin.students.show', $student))
        ->assertOk()
        ->assertSee('Registered Email')
        ->assertSee($studentUser->email);
});

it('emails a password reset link instead of generating a password', function () {
    Notification::fake();

    [$admin, $section, $student, $studentUser, $unregistered] = studentPasswordFixture();

    $indexUrl = route('admin.users.index', ['tab' => 'students']);

    $this->actingAs($admin)
        ->from($indexUrl)
        ->patch(route('admin.students.reset-password', $student))
        ->assertRedirect($indexUrl)
        ->assertSessionHas('success')
        ->assertSessionMissing('generated_password');

    expect(Hash::check('password', $studentUser->fresh()->password))->toBeTrue();

    Notification::assertSentTo($studentUser, ResetPasswordNotification::class);
});

it('refuses to send a reset email for a student without an account', function () {
    Notification::fake();

    [$admin, $section, $student, $studentUser, $unregistered] = studentPasswordFixture();

    $this->actingAs($admin)
        ->patch(route('admin.students.reset-password', $unregistered))
        ->assertSessionHasErrors('password');

    Notification::assertNothingSent();
});
