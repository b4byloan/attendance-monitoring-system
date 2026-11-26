<?php

use App\Models\Guardian;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;

function avatarFixture(): array
{
    $admin = User::factory()->create(['name' => 'Ada Admin', 'role' => 'admin', 'is_active' => true]);
    $section = Section::create([
        'name' => 'Grade 10 - Rizal',
        'grade_level' => 'Grade 10',
        'school_year' => '2026-2027',
        'max_capacity' => 50,
    ]);

    return [$admin, $section];
}

it('shows a default initials avatar for the signed in user in every layout', function () {
    [$admin] = avatarFixture();

    $teacher = User::factory()->create(['name' => 'Tina Teacher', 'role' => 'teacher', 'is_active' => true]);
    $guard = User::factory()->create(['name' => 'Greg Guard', 'role' => 'guard', 'is_active' => true]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('aria-label="Ada Admin"', false)
        ->assertSee('AA', false)
        ->assertSee('rounded-full', false);

    $this->actingAs($teacher)
        ->get(route('sub-admin.dashboard'))
        ->assertOk()
        ->assertSee('aria-label="Tina Teacher"', false)
        ->assertSee('TT', false);

    $this->actingAs($guard)
        ->get(route('guard.dashboard'))
        ->assertOk()
        ->assertSee('aria-label="Greg Guard"', false)
        ->assertSee('GG', false);
});

it('shows initials avatars for students, guardians, and staff in the users module', function () {
    [$admin, $section] = avatarFixture();

    Student::create([
        'lrn' => '123456789011',
        'first_name' => 'Avatar',
        'last_name' => 'Student',
        'section_id' => $section->id,
        'status' => 'active',
        'qr_code' => 'avatar-student-qr',
    ]);

    Guardian::create([
        'first_name' => 'Avatar',
        'last_name' => 'Guardian',
        'phone_number' => '09171234567',
        'status' => 'active',
    ]);

    User::factory()->create(['name' => 'Tina Teacher', 'role' => 'teacher', 'is_active' => true]);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'students']))
        ->assertOk()
        ->assertSee('aria-label="Avatar Student"', false)
        ->assertSee('AS', false);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'guardians']))
        ->assertOk()
        ->assertSee('aria-label="Avatar Guardian"', false)
        ->assertSee('AG', false);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'teachers']))
        ->assertOk()
        ->assertSee('aria-label="Tina Teacher"', false)
        ->assertSee('TT', false);
});

it('shows the avatar on the view and edit pages', function () {
    [$admin, $section] = avatarFixture();

    $student = Student::create([
        'lrn' => '123456789012',
        'first_name' => 'Page',
        'last_name' => 'Student',
        'section_id' => $section->id,
        'status' => 'active',
        'qr_code' => 'page-student-qr',
    ]);

    $guardian = Guardian::create([
        'first_name' => 'Page',
        'last_name' => 'Guardian',
        'phone_number' => '09171234567',
        'status' => 'active',
    ]);

    $teacher = User::factory()->create(['name' => 'Page Teacher', 'role' => 'teacher', 'is_active' => true]);

    $this->actingAs($admin)->get(route('admin.students.show', $student))->assertOk()->assertSee('aria-label="Page Student"', false);
    $this->actingAs($admin)->get(route('admin.guardians.show', $guardian))->assertOk()->assertSee('aria-label="Page Guardian"', false);
    $this->actingAs($admin)->get(route('admin.users.edit', $teacher))->assertOk()->assertSee('aria-label="Page Teacher"', false);
    $this->actingAs($admin)->get(route('account-settings.edit'))->assertOk()->assertSee('aria-label="Ada Admin"', false);
});

it('does not expose any profile photo upload controls', function () {
    [$admin] = avatarFixture();

    $response = $this->actingAs($admin)->get(route('account-settings.edit'))->assertOk();

    $response->assertDontSee('name="photo"', false)
        ->assertDontSee('name="remove_photo"', false)
        ->assertDontSee('type="file"', false);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'students']))
        ->assertOk()
        ->assertDontSee('name="photo"', false);

    $this->actingAs($admin)
        ->get(route('admin.sections.index'))
        ->assertOk()
        ->assertDontSee('name="photo"', false);
});
