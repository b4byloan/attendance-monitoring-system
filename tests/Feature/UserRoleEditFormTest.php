<?php

use App\Models\Guardian;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;

function roleEditFixture(): array
{
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $secondAdmin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $section = Section::create([
        'name' => 'Grade 8 - Rizal',
        'grade_level' => 'Grade 8',
        'school_year' => '2026-2027',
        'max_capacity' => 50,
    ]);

    return [$admin, $secondAdmin, $section];
}

it('renders a role responsive edit form', function () {
    [$admin, , $section] = roleEditFixture();

    $teacher = User::factory()->create(['name' => 'Role Teacher', 'role' => 'teacher', 'is_active' => true]);
    $teacher->sections()->attach($section);

    $this->actingAs($admin)
        ->get(route('admin.users.edit', $teacher))
        ->assertOk()
        ->assertSee('data-role-form', false)
        ->assertSee('data-role-select', false)
        ->assertSee('data-role-panel="teacher"', false)
        ->assertSee('data-role-panel="student"', false)
        ->assertSee('data-role-panel="parent"', false)
        ->assertSee('Assigned Sections (Teachers)')
        ->assertSee('The rest of this form changes to match the selected role.')
        ->assertDontSee('data-role-panel="guard"', false)
        ->assertDontSee('Send Reset Email');
});

it('links an existing student record when the role changes to student', function () {
    [$admin, , $section] = roleEditFixture();

    $account = User::factory()->create(['name' => 'Converted Student', 'role' => 'guard', 'is_active' => true]);
    $student = Student::create([
        'lrn' => '123456789099',
        'first_name' => 'Converted',
        'last_name' => 'Student',
        'section_id' => $section->id,
        'status' => 'active',
        'qr_code' => 'converted-student-qr',
    ]);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $account), [
            'name' => 'Converted Student',
            'email' => $account->email,
            'role' => 'student',
            'lrn' => '123456789099',
        ])
        ->assertRedirect(route('admin.users.index', ['tab' => 'students']));

    expect($account->fresh()->role)->toBe('student')
        ->and($student->fresh()->user_id)->toBe($account->id);
});

it('rejects a student role without a matching student record', function () {
    [$admin] = roleEditFixture();

    $account = User::factory()->create(['role' => 'guard', 'is_active' => true]);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $account), [
            'name' => $account->name,
            'email' => $account->email,
            'role' => 'student',
            'lrn' => '999999999999',
        ])
        ->assertSessionHasErrors('lrn');

    expect($account->fresh()->role)->toBe('guard');
});

it('creates a linked guardian record when the role changes to guardian', function () {
    [$admin] = roleEditFixture();

    $account = User::factory()->create(['name' => 'Grace Guard', 'role' => 'guard', 'email' => 'grace.guard@example.com']);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $account), [
            'name' => 'Grace Guard',
            'email' => 'grace.guard@example.com',
            'role' => 'parent',
            'phone_number' => '0917-123-4567',
            'relationship_to_student' => 'Mother',
        ])
        ->assertRedirect(route('admin.users.index', ['tab' => 'guardians']));

    $guardian = Guardian::where('user_id', $account->id)->firstOrFail();

    expect($account->fresh()->role)->toBe('parent')
        ->and($guardian->phone_number)->toBe('09171234567')
        ->and($guardian->relationship_to_student)->toBe('Mother')
        ->and($guardian->email)->toBe('grace.guard@example.com');
});

it('drops teacher sections when the role changes to guard and keeps them for teachers', function () {
    [$admin, , $section] = roleEditFixture();

    $teacher = User::factory()->create(['role' => 'teacher', 'is_active' => true]);
    $teacher->sections()->attach($section);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $teacher), [
            'name' => $teacher->name,
            'email' => $teacher->email,
            'role' => 'guard',
        ])
        ->assertRedirect(route('admin.users.index', ['tab' => 'guard']));

    expect($teacher->fresh()->role)->toBe('guard')
        ->and($teacher->fresh()->sections)->toHaveCount(0);

    $other = User::factory()->create(['role' => 'guard', 'is_active' => true]);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $other), [
            'name' => $other->name,
            'email' => $other->email,
            'role' => 'teacher',
            'section_ids' => [$section->id],
        ])
        ->assertRedirect(route('admin.users.index', ['tab' => 'teachers']));

    expect($other->fresh()->sections->pluck('id')->all())->toBe([$section->id]);
});
