<?php

use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('redirects guests from account settings to the login page', function () {
    $this->get(route('account-settings.edit'))->assertRedirect(route('login'));
});

it('shows the account settings page to every role', function (string $role) {
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);

    $this->actingAs($user)
        ->get(route('account-settings.edit'))
        ->assertOk()
        ->assertSee('Account Settings')
        ->assertSee('Notifications')
        ->assertSee('Logout')
        ->assertSee($user->email);
})->with(['admin', 'sub_admin', 'student', 'parent', 'guard']);

it('shows linked student information in account settings', function () {
    $user = User::factory()->create(['role' => 'student', 'is_active' => true]);
    $section = Section::create([
        'name' => 'Grade 7 - Rizal',
        'grade_level' => 'Grade 7',
        'school_year' => '2026-2027',
    ]);
    $student = Student::create([
        'lrn' => '123456789097',
        'first_name' => 'Portal',
        'last_name' => 'Student',
        'section_id' => $section->id,
        'qr_code' => 'qr-portal-student',
        'status' => 'active',
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('account-settings.edit'))
        ->assertOk()
        ->assertSee('Information')
        ->assertDontSee('Account Information')
        ->assertDontSee('Student Information')
        ->assertSee($student->full_name)
        ->assertSee($student->lrn)
        ->assertSee($section->grade_level)
        ->assertSee($section->name);
});

it('redirects the old student profile page to account settings', function () {
    $user = User::factory()->create(['role' => 'student', 'is_active' => true]);

    $this->actingAs($user)
        ->get(route('student.profile'))
        ->assertRedirect(route('account-settings.edit'));
});

it('updates only the signed-in users own account', function () {
    $user = User::factory()->create(['role' => 'admin', 'name' => 'Old Name']);
    $otherUser = User::factory()->create(['role' => 'guard', 'name' => 'Other User']);

    $this->actingAs($user)
        ->put(route('account-settings.update'), [
            'name' => 'New Name',
            'email' => $user->email,
        ])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->name)->toBe('New Name')
        ->and($otherUser->fresh()->name)->toBe('Other User')
        ->and($otherUser->fresh()->email)->not->toBe($user->email);
});

it('requires the current password when changing the email address', function () {
    $user = User::factory()->create(['role' => 'parent']);

    $this->actingAs($user)
        ->put(route('account-settings.update'), [
            'name' => $user->name,
            'email' => 'changed@example.com',
        ])
        ->assertSessionHasErrors('current_password');

    expect($user->fresh()->email)->not->toBe('changed@example.com');
});

it('changes the password when the current password is confirmed', function () {
    $user = User::factory()->create(['role' => 'student']);

    $this->actingAs($user)
        ->put(route('account-settings.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => 'password',
            'new_password' => 'new-secret-password',
            'new_password_confirmation' => 'new-secret-password',
        ])
        ->assertSessionHasNoErrors();

    expect(Hash::check('new-secret-password', $user->fresh()->password))->toBeTrue();
});

it('rejects an incorrect current password', function () {
    $user = User::factory()->create(['role' => 'guard']);

    $this->actingAs($user)
        ->put(route('account-settings.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => 'wrong-password',
            'new_password' => 'new-secret-password',
            'new_password_confirmation' => 'new-secret-password',
        ])
        ->assertSessionHasErrors('current_password');

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

it('does not allow another users email address', function () {
    $user = User::factory()->create(['role' => 'sub_admin']);
    $otherUser = User::factory()->create(['role' => 'student']);

    $this->actingAs($user)
        ->put(route('account-settings.update'), [
            'name' => $user->name,
            'email' => $otherUser->email,
            'current_password' => 'password',
        ])
        ->assertSessionHasErrors('email');
});
