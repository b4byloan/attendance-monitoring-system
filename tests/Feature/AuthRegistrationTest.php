<?php

use App\Models\Guardian;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;

test('parent can register and is linked to a guardian record', function () {
    $response = $this->post('/register', [
        'name' => 'Maria Santos',
        'email' => 'maria@example.com',
        'phone_number' => '09171234567',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('parent.dashboard'));

    $user = User::where('email', 'maria@example.com')->firstOrFail();

    expect($user->role)->toBe('parent')
        ->and($user->is_active)->toBeTrue();

    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseHas('guardians', [
        'user_id' => $user->id,
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'phone_number' => '09171234567',
        'email' => 'maria@example.com',
    ]);

    expect(Guardian::where('user_id', $user->id)->firstOrFail()->user->is($user))->toBeTrue();
});

test('student can register and is linked to a student record', function () {
    $section = Section::create([
        'name' => 'Grade 10 - Rizal',
        'grade_level' => 'Grade 10',
        'school_year' => '2026-2027',
    ]);

    $response = $this->post('/register/student', [
        'lrn' => '123456789012',
        'first_name' => 'Juan',
        'middle_name' => 'Dela',
        'last_name' => 'Cruz',
        'grade_level' => 'Grade 10',
        'section_id' => $section->id,
        'email' => 'juan@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('student.dashboard'));

    $user = User::where('email', 'juan@example.com')->firstOrFail();
    $student = Student::where('lrn', '123456789012')->firstOrFail();

    expect($user->role)->toBe('student')
        ->and($user->is_active)->toBeTrue()
        ->and($user->name)->toBe('Juan Dela Cruz');

    $this->assertAuthenticatedAs($user);

    expect($student->user_id)->toBe($user->id)
        ->and($student->section_id)->toBe($section->id)
        ->and($student->status)->toBe('active')
        ->and($student->full_name)->toBe('Juan Dela Cruz');
});

test('student registration requires the section to match the grade level', function () {
    Section::create([
        'name' => 'Grade 9 - Mabini',
        'grade_level' => 'Grade 9',
        'school_year' => '2026-2027',
    ]);

    $gradeTenSection = Section::create([
        'name' => 'Grade 10 - Rizal',
        'grade_level' => 'Grade 10',
        'school_year' => '2026-2027',
    ]);

    $this->post('/register/student', [
        'lrn' => '123456789012',
        'first_name' => 'Juan',
        'last_name' => 'Cruz',
        'grade_level' => 'Grade 9',
        'section_id' => $gradeTenSection->id,
        'email' => 'juan@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('section_id');

    $this->assertDatabaseCount('users', 0);
    $this->assertDatabaseCount('students', 0);
});

test('student registration requires a valid lrn and account details', function () {
    $section = Section::create([
        'name' => 'Grade 10 - Rizal',
        'grade_level' => 'Grade 10',
        'school_year' => '2026-2027',
    ]);

    $this->post('/register/student', [
        'lrn' => '12345',
        'first_name' => '',
        'last_name' => '',
        'grade_level' => 'Grade 10',
        'section_id' => $section->id,
        'email' => 'not-an-email',
        'password' => 'short',
        'password_confirmation' => 'different',
    ])->assertSessionHasErrors(['lrn', 'first_name', 'last_name', 'email', 'password']);

    $this->assertDatabaseCount('users', 0);
});

test('registration page shows both registration tabs', function () {
    $this->get('/register')
        ->assertOk()
        ->assertSee('Parent Registration')
        ->assertSee('Student Registration');
});

test('registration requires valid account details', function () {
    $this->post('/register', [
        'name' => '',
        'email' => 'not-an-email',
        'phone_number' => '',
        'password' => 'short',
        'password_confirmation' => 'different',
    ])->assertSessionHasErrors(['name', 'email', 'phone_number', 'password']);

    $this->assertDatabaseCount('users', 0);
});
