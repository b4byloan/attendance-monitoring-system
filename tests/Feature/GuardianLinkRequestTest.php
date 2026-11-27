<?php

use App\Models\Guardian;
use App\Models\GuardianLinkRequest;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Notifications\GuardianLinkRequestedNotification;

function linkRequestFixture(): array
{
    $section = Section::create([
        'name' => 'Grade 10 - Rizal',
        'grade_level' => 'Grade 10',
        'school_year' => '2026-2027',
    ]);

    $studentUser = User::factory()->create(['role' => 'student', 'is_active' => true]);

    $student = Student::create([
        'lrn' => '123456789012',
        'first_name' => 'Juan',
        'last_name' => 'Cruz',
        'section_id' => $section->id,
        'qr_code' => 'qr-link-student',
        'status' => 'active',
        'user_id' => $studentUser->id,
    ]);

    $guardianUser = User::factory()->create(['role' => 'parent', 'is_active' => true]);

    $guardian = Guardian::create([
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'phone_number' => '09171234567',
        'email' => 'maria@example.com',
        'relationship_to_student' => 'Mother',
        'status' => 'active',
        'user_id' => $guardianUser->id,
    ]);

    return [$section, $student, $studentUser, $guardian, $guardianUser];
}

it('lets a guardian send a link request to the matching student', function () {
    [$section, $student, $studentUser, $guardian, $guardianUser] = linkRequestFixture();
    $student->update(['middle_name' => 'Dela', 'suffix' => 'Jr.']);

    $this->actingAs($guardianUser)
        ->post(route('parent.link-student.store'), [
            'lrn' => $student->lrn,
            'first_name' => 'Juan',
            'middle_name' => 'Dela',
            'last_name' => 'Cruz',
            'grade_level' => 'Grade 10',
            'section_id' => $section->id,
        ])
        ->assertRedirect(route('parent.link-student.index'));

    $this->assertDatabaseHas('guardian_link_requests', [
        'guardian_id' => $guardian->id,
        'student_id' => $student->id,
        'status' => 'pending',
    ]);

    expect($studentUser->notifications()->count())->toBe(1)
        ->and($studentUser->notifications()->first()->data['url'])->toBe(route('notifications.index'))
        ->and($studentUser->notifications()->first()->data['link_request_id'])
        ->toBe(GuardianLinkRequest::first()->id);
});

it('rejects guardian link requests with a mismatched middle name', function () {
    [$section, $student, $studentUser, $guardian, $guardianUser] = linkRequestFixture();
    $student->update(['middle_name' => 'Dela']);

    $this->actingAs($guardianUser)
        ->post(route('parent.link-student.store'), [
            'lrn' => $student->lrn,
            'first_name' => 'Juan',
            'middle_name' => 'Wrong',
            'last_name' => 'Cruz',
            'grade_level' => 'Grade 10',
            'section_id' => $section->id,
        ])
        ->assertSessionHasErrors('lrn');

    $this->assertDatabaseCount('guardian_link_requests', 0);
});

it('rejects guardian link requests that do not match the student record', function () {
    [$section, $student, $studentUser, $guardian, $guardianUser] = linkRequestFixture();

    $this->actingAs($guardianUser)
        ->post(route('parent.link-student.store'), [
            'lrn' => $student->lrn,
            'first_name' => 'Pedro',
            'last_name' => 'Cruz',
            'grade_level' => 'Grade 10',
            'section_id' => $section->id,
        ])
        ->assertSessionHasErrors('lrn');

    $this->assertDatabaseCount('guardian_link_requests', 0);
});

it('requires the student to have a registered account before a link request can be sent', function () {
    [$section, $student, $studentUser, $guardian, $guardianUser] = linkRequestFixture();

    $student->update(['user_id' => null]);

    $this->actingAs($guardianUser)
        ->post(route('parent.link-student.store'), [
            'lrn' => $student->lrn,
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
            'grade_level' => 'Grade 10',
            'section_id' => $section->id,
        ])
        ->assertSessionHasErrors('lrn');

    $this->assertDatabaseCount('guardian_link_requests', 0);
});

it('lets the student accept a guardian link request', function () {
    [$section, $student, $studentUser, $guardian, $guardianUser] = linkRequestFixture();

    $linkRequest = GuardianLinkRequest::create([
        'guardian_id' => $guardian->id,
        'student_id' => $student->id,
        'status' => 'pending',
    ]);

    $this->actingAs($studentUser)
        ->post(route('student.link-requests.accept', $linkRequest))
        ->assertRedirect();

    expect($linkRequest->fresh()->status)->toBe('accepted')
        ->and($linkRequest->fresh()->responded_at)->not->toBeNull();

    $this->assertDatabaseHas('guardian_student', [
        'guardian_id' => $guardian->id,
        'student_id' => $student->id,
    ]);

    expect($guardianUser->notifications()->count())->toBe(1);
});

it('lets the student deny a guardian link request', function () {
    [$section, $student, $studentUser, $guardian, $guardianUser] = linkRequestFixture();

    $linkRequest = GuardianLinkRequest::create([
        'guardian_id' => $guardian->id,
        'student_id' => $student->id,
        'status' => 'pending',
    ]);

    $this->actingAs($studentUser)
        ->post(route('student.link-requests.deny', $linkRequest))
        ->assertRedirect();

    expect($linkRequest->fresh()->status)->toBe('denied');

    $this->assertDatabaseMissing('guardian_student', [
        'guardian_id' => $guardian->id,
        'student_id' => $student->id,
    ]);

    expect($guardianUser->notifications()->count())->toBe(1);
});

it('shows the request form on the guardian side and the approval actions in the student notifications', function () {
    [$section, $student, $studentUser, $guardian, $guardianUser] = linkRequestFixture();

    $linkRequest = GuardianLinkRequest::create([
        'guardian_id' => $guardian->id,
        'student_id' => $student->id,
        'status' => 'pending',
    ]);

    $this->actingAs($guardianUser)
        ->get(route('parent.link-student.index'))
        ->assertOk()
        ->assertSee('Send Link Request')
        ->assertSee('Confirm the student details')
        ->assertSee('Are you sure the information you entered is correct?')
        ->assertSee('name="middle_name"', false)
        ->assertDontSee('name="suffix"', false)
        ->assertSee('Delete All')
        ->assertSee('permanently delete the linking request')
        ->assertSee($student->full_name);

    $studentUser->notify(new GuardianLinkRequestedNotification(
        guardianName: trim($guardian->first_name.' '.$guardian->last_name),
        relationship: $guardian->relationship_to_student ?: 'Guardian',
        linkRequestId: $linkRequest->id,
    ));

    $this->actingAs($studentUser)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Guardian link request')
        ->assertSee('Accept')
        ->assertSee('Deny')
        ->assertSee('action="'.route('student.link-requests.accept', $linkRequest).'"', false)
        ->assertSee('action="'.route('student.link-requests.deny', $linkRequest).'"', false);

    $this->actingAs($studentUser)
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee('waiting in your notifications');

    expect(Route::has('student.link-requests.index'))->toBeFalse();
});

it('lets a guardian permanently delete their own link request without unlinking an accepted student', function () {
    [$section, $student, $studentUser, $guardian, $guardianUser] = linkRequestFixture();
    $guardian->students()->attach($student->id);

    $linkRequest = GuardianLinkRequest::create([
        'guardian_id' => $guardian->id,
        'student_id' => $student->id,
        'status' => 'accepted',
    ]);

    $this->actingAs($guardianUser)
        ->delete(route('parent.link-requests.destroy', $linkRequest))
        ->assertRedirect(route('parent.link-student.index'));

    $this->assertDatabaseMissing('guardian_link_requests', ['id' => $linkRequest->id]);
    $this->assertDatabaseHas('guardian_student', [
        'guardian_id' => $guardian->id,
        'student_id' => $student->id,
    ]);
});

it('prevents a guardian from deleting a link request owned by another account', function () {
    [$section, $student, $studentUser, $guardian, $guardianUser] = linkRequestFixture();
    $otherGuardianUser = User::factory()->create(['role' => 'parent', 'is_active' => true]);
    $otherGuardian = Guardian::create([
        'first_name' => 'Other',
        'last_name' => 'Guardian',
        'phone_number' => '09170000000',
        'status' => 'active',
        'user_id' => $otherGuardianUser->id,
    ]);
    $linkRequest = GuardianLinkRequest::create([
        'guardian_id' => $guardian->id,
        'student_id' => $student->id,
        'status' => 'pending',
    ]);

    $this->actingAs($otherGuardianUser)
        ->delete(route('parent.link-requests.destroy', $linkRequest))
        ->assertForbidden();

    $this->assertModelExists($linkRequest);
});

it('lets a guardian delete all their link requests without affecting other guardians or accepted links', function () {
    [$section, $student, $studentUser, $guardian, $guardianUser] = linkRequestFixture();
    $guardian->students()->attach($student->id);
    $ownRequest = GuardianLinkRequest::create([
        'guardian_id' => $guardian->id,
        'student_id' => $student->id,
        'status' => 'accepted',
    ]);

    $otherGuardianUser = User::factory()->create(['role' => 'parent', 'is_active' => true]);
    $otherGuardian = Guardian::create([
        'first_name' => 'Other',
        'last_name' => 'Guardian',
        'phone_number' => '09170000000',
        'status' => 'active',
        'user_id' => $otherGuardianUser->id,
    ]);
    $otherRequest = GuardianLinkRequest::create([
        'guardian_id' => $otherGuardian->id,
        'student_id' => $student->id,
        'status' => 'pending',
    ]);

    $this->actingAs($guardianUser)
        ->delete(route('parent.link-requests.destroy-all'))
        ->assertRedirect(route('parent.link-student.index'));

    $this->assertModelMissing($ownRequest);
    $this->assertModelExists($otherRequest);
    $this->assertDatabaseHas('guardian_student', [
        'guardian_id' => $guardian->id,
        'student_id' => $student->id,
    ]);
});

it('no longer exposes the student link request page or its delete endpoints', function () {
    expect(Route::has('student.link-requests.index'))->toBeFalse()
        ->and(Route::has('student.link-requests.destroy'))->toBeFalse()
        ->and(Route::has('student.link-requests.destroy-all'))->toBeFalse();
});

it('prevents another student from answering someone else\'s link request', function () {
    [$section, $student, $studentUser, $guardian, $guardianUser] = linkRequestFixture();

    $otherStudentUser = User::factory()->create(['role' => 'student', 'is_active' => true]);
    Student::create([
        'lrn' => '123456789013',
        'first_name' => 'Other',
        'last_name' => 'Student',
        'section_id' => $section->id,
        'qr_code' => 'qr-other-link-student',
        'status' => 'active',
        'user_id' => $otherStudentUser->id,
    ]);

    $linkRequest = GuardianLinkRequest::create([
        'guardian_id' => $guardian->id,
        'student_id' => $student->id,
        'status' => 'pending',
    ]);

    $this->actingAs($otherStudentUser)
        ->post(route('student.link-requests.accept', $linkRequest))
        ->assertForbidden();

    expect($linkRequest->fresh()->status)->toBe('pending');
});
