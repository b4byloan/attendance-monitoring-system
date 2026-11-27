<?php

use App\Models\Guardian;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

it('renders the otp expiry time for a freshly loaded user', function () {
    $user = User::factory()->create([
        'role' => 'guard',
        'is_active' => true,
        'email_verified_at' => null,
    ]);

    $user->forceFill([
        'otp_code' => Hash::make('123456'),
        'otp_expires_at' => now()->addMinutes(10),
        'otp_attempts' => 0,
    ])->save();

    $fresh = User::findOrFail($user->id);

    expect($fresh->otp_expires_at)->toBeInstanceOf(Carbon::class);

    $this->actingAs($fresh)
        ->get(route('otp.show'))
        ->assertOk()
        ->assertSee('It expires at');
});

it('verifies an otp for a freshly loaded user model', function () {
    $user = User::factory()->create([
        'role' => 'guard',
        'is_active' => true,
        'email_verified_at' => null,
    ]);

    $user->forceFill([
        'otp_code' => Hash::make('123456'),
        'otp_expires_at' => now()->addMinutes(10),
        'otp_attempts' => 0,
    ])->save();

    $fresh = User::findOrFail($user->id);
    $service = app(OtpService::class);

    expect($service->hasPendingCode($fresh))->toBeTrue()
        ->and($service->verify($fresh, '123456'))->toBeTrue();

    expect($fresh->fresh()->email_verified_at)->not->toBeNull()
        ->and($fresh->fresh()->otp_code)->toBeNull();
});

it('deletes the linked portal account when a student record is deleted', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $section = Section::create([
        'name' => 'Grade 11 - Rizal',
        'grade_level' => 'Grade 10',
        'school_year' => '2026-2027',
        'max_capacity' => 50,
    ]);

    $studentUser = User::factory()->create([
        'role' => 'student',
        'is_active' => true,
        'email' => 'free.me@example.com',
    ]);

    $student = Student::create([
        'lrn' => '123456789950',
        'first_name' => 'Free',
        'last_name' => 'Email',
        'section_id' => $section->id,
        'status' => 'active',
        'qr_code' => 'free-email-qr',
        'user_id' => $studentUser->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.students.destroy', $student))
        ->assertRedirect(route('admin.students.index'));

    $this->assertModelMissing($student);
    $this->assertDatabaseMissing('users', ['email' => 'free.me@example.com']);
});

it('deletes the linked portal account when a guardian record is deleted', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $guardianUser = User::factory()->create([
        'role' => 'parent',
        'is_active' => true,
        'email' => 'guardian.free@example.com',
    ]);

    $guardian = Guardian::create([
        'first_name' => 'Free',
        'last_name' => 'Guardian',
        'phone_number' => '09171234567',
        'email' => 'guardian.free@example.com',
        'status' => 'active',
        'user_id' => $guardianUser->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.guardians.destroy', $guardian))
        ->assertRedirect(route('admin.users.index', ['tab' => 'guardians']));

    $this->assertModelMissing($guardian);
    $this->assertDatabaseMissing('users', ['email' => 'guardian.free@example.com']);
});

it('allows registering again with the email of a deleted student account', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $section = Section::create([
        'name' => 'Grade 10 - Rizal',
        'grade_level' => 'Grade 10',
        'school_year' => '2026-2027',
        'max_capacity' => 50,
    ]);

    $studentUser = User::factory()->create([
        'role' => 'student',
        'is_active' => true,
        'email' => 'reuse.me@example.com',
    ]);

    $student = Student::create([
        'lrn' => '123456789951',
        'first_name' => 'Reuse',
        'last_name' => 'Me',
        'section_id' => $section->id,
        'status' => 'active',
        'qr_code' => 'reuse-email-qr',
        'user_id' => $studentUser->id,
    ]);

    $this->actingAs($admin)->delete(route('admin.students.destroy', $student));

    Auth::logout();

    // The admin re-adds the student record; the freed email can now be used again.
    Student::create([
        'lrn' => '123456789951',
        'first_name' => 'Reuse',
        'last_name' => 'Me',
        'section_id' => $section->id,
        'status' => 'active',
        'qr_code' => 'reuse-email-qr-2',
    ]);

    $this->post('/register/student', [
        'lrn' => '123456789951',
        'first_name' => 'Reuse',
        'last_name' => 'Me',
        'grade_level' => 'Grade 10',
        'section_id' => $section->id,
        'email' => 'reuse.me@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('otp.show'));

    expect(User::where('email', 'reuse.me@example.com')->exists())->toBeTrue();
});
