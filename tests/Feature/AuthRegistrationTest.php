<?php

use App\Models\Guardian;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Notifications\OtpCodeNotification;
use Illuminate\Support\Facades\Notification;

test('parent can register and is linked to a guardian record', function () {
    Notification::fake();

    $response = $this->post('/register', [
        'name' => 'Maria Santos',
        'email' => 'maria@example.com',
        'phone_number' => '09171234567',
        'relationship_to_student' => 'Mother',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('otp.show'));

    $user = User::where('email', 'maria@example.com')->firstOrFail();

    expect($user->role)->toBe('parent')
        ->and($user->is_active)->toBeTrue()
        ->and($user->email_verified_at)->toBeNull()
        ->and($user->otp_code)->not->toBeNull();

    Notification::assertSentTo($user, OtpCodeNotification::class);

    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseHas('guardians', [
        'user_id' => $user->id,
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'phone_number' => '09171234567',
        'email' => 'maria@example.com',
        'relationship_to_student' => 'Mother',
    ]);

    expect(Guardian::where('user_id', $user->id)->firstOrFail()->user->is($user))->toBeTrue();
});

test('student can register and is linked to the matching student record', function () {
    $section = Section::create([
        'name' => 'Grade 10 - Rizal',
        'grade_level' => 'Grade 10',
        'school_year' => '2026-2027',
    ]);

    $student = Student::create([
        'lrn' => '123456789012',
        'first_name' => 'Juan',
        'middle_name' => 'Dela',
        'last_name' => 'Cruz',
        'suffix' => 'Jr.',
        'section_id' => $section->id,
        'qr_code' => 'qr-juan-cruz',
        'status' => 'active',
    ]);

    $response = $this->post('/register/student', [
        'lrn' => '123456789012',
        'first_name' => 'Juan',
        'middle_name' => 'Dela',
        'last_name' => 'Cruz',
        'suffix' => 'Jr.',
        'grade_level' => 'Grade 10',
        'section_id' => $section->id,
        'email' => 'juan@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('otp.show'));

    $user = User::where('email', 'juan@example.com')->firstOrFail();
    $student->refresh();

    expect($user->role)->toBe('student')
        ->and($user->is_active)->toBeTrue()
        ->and($user->name)->toBe('Juan Dela Cruz Jr.');

    $this->assertAuthenticatedAs($user);

    expect($student->user_id)->toBe($user->id)
        ->and($student->section_id)->toBe($section->id)
        ->and($student->full_name)->toBe('Juan Dela Cruz Jr.');

    $this->assertDatabaseCount('students', 1);
});

test('student registration rejects a suffix that does not match the school record', function () {
    $section = Section::create([
        'name' => 'Grade 10 - Rizal',
        'grade_level' => 'Grade 10',
        'school_year' => '2026-2027',
    ]);

    Student::create([
        'lrn' => '123456789012',
        'first_name' => 'Juan',
        'last_name' => 'Cruz',
        'suffix' => 'Jr.',
        'section_id' => $section->id,
        'qr_code' => 'qr-juan-cruz',
        'status' => 'active',
    ]);

    $this->post('/register/student', [
        'lrn' => '123456789012',
        'first_name' => 'Juan',
        'last_name' => 'Cruz',
        'suffix' => 'Sr.',
        'grade_level' => 'Grade 10',
        'section_id' => $section->id,
        'email' => 'juan@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('lrn');

    $this->assertDatabaseCount('users', 0);
});

test('student registration rejects details that do not match the school record', function () {
    $section = Section::create([
        'name' => 'Grade 10 - Rizal',
        'grade_level' => 'Grade 10',
        'school_year' => '2026-2027',
    ]);

    Student::create([
        'lrn' => '123456789012',
        'first_name' => 'Juan',
        'last_name' => 'Cruz',
        'section_id' => $section->id,
        'qr_code' => 'qr-juan-cruz',
        'status' => 'active',
    ]);

    $this->post('/register/student', [
        'lrn' => '123456789012',
        'first_name' => 'Pedro',
        'last_name' => 'Cruz',
        'grade_level' => 'Grade 10',
        'section_id' => $section->id,
        'email' => 'pedro@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('lrn');

    $this->assertDatabaseCount('users', 0);
});

test('student registration rejects a record that already has an account', function () {
    $section = Section::create([
        'name' => 'Grade 10 - Rizal',
        'grade_level' => 'Grade 10',
        'school_year' => '2026-2027',
    ]);

    $existingUser = User::factory()->create(['role' => 'student', 'is_active' => true]);

    Student::create([
        'lrn' => '123456789012',
        'first_name' => 'Juan',
        'last_name' => 'Cruz',
        'section_id' => $section->id,
        'qr_code' => 'qr-juan-cruz',
        'status' => 'active',
        'user_id' => $existingUser->id,
    ]);

    $this->post('/register/student', [
        'lrn' => '123456789012',
        'first_name' => 'Juan',
        'last_name' => 'Cruz',
        'grade_level' => 'Grade 10',
        'section_id' => $section->id,
        'email' => 'other@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('lrn');

    $this->assertDatabaseCount('users', 1);
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
        ->assertSee('Guardian Registration')
        ->assertSee('Student Registration')
        ->assertSee('name="suffix"', false);
});

test('student registration page uses a suffix dropdown and icon groups', function () {
    $this->get('/register')
        ->assertOk()
        ->assertSee('<select id="suffix" name="suffix"', false)
        ->assertSee('<option value="Jr."', false)
        ->assertSee('<option value="Sr."', false)
        ->assertSee('border-r border-slate-200', false);
});

test('parent registration page offers a role dropdown from the guardian options', function () {
    $this->get('/register')
        ->assertOk()
        ->assertSee('<select id="relationship_to_student" name="relationship_to_student"', false)
        ->assertSee('<option value="Mother"', false)
        ->assertSee('<option value="Father"', false)
        ->assertSee('<option value="Guardian"', false)
        ->assertSee('Your relationship to the student');
});

test('registration requires valid account details', function () {
    $this->post('/register', [
        'name' => '',
        'email' => 'not-an-email',
        'phone_number' => '',
        'relationship_to_student' => '',
        'password' => 'short',
        'password_confirmation' => 'different',
    ])->assertSessionHasErrors(['name', 'email', 'phone_number', 'relationship_to_student', 'password']);

    $this->assertDatabaseCount('users', 0);
});

test('parent registration only accepts a known role', function () {
    $this->post('/register', [
        'name' => 'Maria Santos',
        'email' => 'maria@example.com',
        'phone_number' => '09171234567',
        'relationship_to_student' => 'Wizard',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('relationship_to_student');

    $this->assertDatabaseCount('users', 0);
});

test('parent registration enforces a valid mobile number', function () {
    $this->post('/register', [
        'name' => 'Maria Santos',
        'email' => 'maria@example.com',
        'phone_number' => '12345',
        'relationship_to_student' => 'Father',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('phone_number');

    $this->assertDatabaseCount('users', 0);
});

test('student registration requires the suffix to match the school record', function () {
    $section = Section::create([
        'name' => 'Grade 10 - Rizal',
        'grade_level' => 'Grade 10',
        'school_year' => '2026-2027',
    ]);

    Student::create([
        'lrn' => '123456789012',
        'first_name' => 'Juan',
        'last_name' => 'Cruz',
        'suffix' => 'Jr.',
        'section_id' => $section->id,
        'qr_code' => 'qr-suffix-student',
        'status' => 'active',
    ]);

    $this->post('/register/student', [
        'lrn' => '123456789012',
        'first_name' => 'Juan',
        'last_name' => 'Cruz',
        'suffix' => 'Sr.',
        'grade_level' => 'Grade 10',
        'section_id' => $section->id,
        'email' => 'juan@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('lrn');

    $this->assertDatabaseCount('users', 0);
});
