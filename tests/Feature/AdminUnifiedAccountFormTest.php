<?php

use App\Models\Guardian;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;

function unifiedAccountFixture(): array
{
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $section = Section::create([
        'name' => 'Grade 9 - Del Pilar',
        'grade_level' => 'Grade 9',
        'school_year' => '2026-2027',
        'max_capacity' => 50,
    ]);

    return [$admin, $section];
}

it('renders one unified add-user form on every users tab', function () {
    [$admin] = unifiedAccountFixture();

    foreach (['students', 'guardians', 'teachers', 'admin', 'guard'] as $tab) {
        $this->actingAs($admin)
            ->get(route('admin.users.index', ['tab' => $tab]))
            ->assertOk()
            ->assertSee('add-user-modal', false)
            ->assertSee('data-account-type', false)
            ->assertSee('data-account-panel="all"', false)
            ->assertSee('data-account-panel="student"', false)
            ->assertSee('data-account-panel="guardian"', false)
            ->assertSee('data-account-panel="teacher admin guard"', false)
            ->assertSee('data-account-panel="teacher"', false)
            ->assertSee('data-account-preview', false)
            ->assertSee('The form below changes automatically', false)
            ->assertDontSee('add-student-modal', false)
            ->assertDontSee('add-guardian-modal', false);
    }
});

it('creates a student from the unified form', function () {
    [$admin, $section] = unifiedAccountFixture();

    $this->actingAs($admin)
        ->post(route('admin.users.store-account'), [
            'account_type' => 'student',
            'first_name' => 'Unified',
            'middle_name' => 'M',
            'last_name' => 'Student',
            'suffix' => 'Jr.',
            'lrn' => '123456789045',
            'section_id' => $section->id,
        ])
        ->assertRedirect(route('admin.users.index', ['tab' => 'students']));

    $student = Student::where('lrn', '123456789045')->firstOrFail();

    expect($student->first_name)->toBe('Unified')
        ->and($student->suffix)->toBe('Jr.')
        ->and($student->section_id)->toBe($section->id)
        ->and($student->status)->toBe('active')
        ->and($student->qr_code)->not->toBeEmpty();
});

it('creates a guardian from the unified form with a normalized mobile number', function () {
    [$admin] = unifiedAccountFixture();

    $this->actingAs($admin)
        ->post(route('admin.users.store-account'), [
            'account_type' => 'guardian',
            'first_name' => 'Unified',
            'last_name' => 'Guardian',
            'email' => 'guardian.unified@example.com',
            'phone_number' => '0917-123-4567',
            'relationship_to_student' => 'Mother',
        ])
        ->assertRedirect(route('admin.users.index', ['tab' => 'guardians']));

    $guardian = Guardian::where('email', 'guardian.unified@example.com')->firstOrFail();

    expect($guardian->phone_number)->toBe('09171234567')
        ->and($guardian->status)->toBe('active');
});

it('creates staff accounts from the unified form and assigns teacher sections', function () {
    [$admin, $section] = unifiedAccountFixture();

    $this->actingAs($admin)
        ->post(route('admin.users.store-account'), [
            'account_type' => 'teacher',
            'first_name' => 'Jane',
            'middle_name' => 'Q',
            'last_name' => 'Teacher',
            'email' => 'jane.teacher@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'section_ids' => [$section->id],
        ])
        ->assertRedirect(route('admin.users.index', ['tab' => 'teachers']));

    $teacher = User::where('email', 'jane.teacher@example.com')->firstOrFail();

    expect($teacher->name)->toBe('Jane Q Teacher')
        ->and($teacher->role)->toBe('teacher')
        ->and($teacher->is_active)->toBeTrue()
        ->and($teacher->sections->pluck('id')->all())->toBe([$section->id]);

    $this->actingAs($admin)
        ->post(route('admin.users.store-account'), [
            'account_type' => 'guard',
            'first_name' => 'Gate',
            'last_name' => 'Keeper',
            'email' => 'gate.keeper@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])
        ->assertRedirect(route('admin.users.index', ['tab' => 'guard']));

    expect(User::where('email', 'gate.keeper@example.com')->value('role'))->toBe('guard');
});

it('validates only the fields that belong to the selected account type', function () {
    [$admin, $section] = unifiedAccountFixture();

    $this->actingAs($admin)
        ->post(route('admin.users.store-account'), [
            'account_type' => 'student',
            'first_name' => 'No',
            'last_name' => 'Section',
            'lrn' => '123456789046',
        ])
        ->assertSessionHasErrors(['section_id'])
        ->assertSessionDoesntHaveErrors(['email', 'password']);

    $this->actingAs($admin)
        ->post(route('admin.users.store-account'), [
            'account_type' => 'teacher',
            'first_name' => 'No',
            'last_name' => 'Email',
            'section_ids' => [$section->id],
        ])
        ->assertSessionHasErrors(['email', 'password'])
        ->assertSessionDoesntHaveErrors(['lrn', 'section_id']);

    expect(Student::where('lrn', '123456789046')->exists())->toBeFalse();
});

it('rejects unknown account types and invalid student data', function () {
    [$admin, $section] = unifiedAccountFixture();

    $this->actingAs($admin)
        ->post(route('admin.users.store-account'), [
            'account_type' => 'wizard',
            'first_name' => 'Bad',
            'last_name' => 'Type',
        ])
        ->assertSessionHasErrors('account_type');

    $this->actingAs($admin)
        ->post(route('admin.users.store-account'), [
            'account_type' => 'student',
            'first_name' => 'Bad',
            'last_name' => 'Suffix',
            'suffix' => 'Esq.',
            'lrn' => '123456789047',
            'section_id' => $section->id,
        ])
        ->assertSessionHasErrors('suffix');

    $this->actingAs($admin)
        ->post(route('admin.users.store-account'), [
            'account_type' => 'student',
            'first_name' => 'Bad',
            'last_name' => 'Lrn',
            'lrn' => 'not-digits',
            'section_id' => $section->id,
        ])
        ->assertSessionHasErrors('lrn');
});
