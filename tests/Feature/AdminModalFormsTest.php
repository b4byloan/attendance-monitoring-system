<?php

use App\Models\Guardian;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;

function adminModalFixture(): array
{
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

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

    Student::create([
        'lrn' => '123456789013',
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'section_id' => $section->id,
        'qr_code' => 'qr-maria-santos',
        'status' => 'active',
    ]);

    return [$admin, $section];
}

it('removes the role filter and uses tab aware search placeholders', function () {
    [$admin] = adminModalFixture();

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'students']))
        ->assertOk()
        ->assertDontSee('All Roles')
        ->assertSee('Search by name, LRN, or registered email', false);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'guardians']))
        ->assertOk()
        ->assertSee('Search by name, email, or phone', false);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'admin']))
        ->assertOk()
        ->assertSee('Search by name or email', false);
});

it('searches students by a full name containing spaces', function () {
    [$admin] = adminModalFixture();

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'students', 'search' => 'Juan Cruz']))
        ->assertOk()
        ->assertSee('Juan Cruz')
        ->assertDontSee('Maria Santos');

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'students', 'search' => 'Cruz Juan']))
        ->assertOk()
        ->assertSee('Juan Cruz')
        ->assertDontSee('Maria Santos');
});

it('opens add forms in modals instead of navigating away', function () {
    [$admin] = adminModalFixture();

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'students']))
        ->assertOk()
        ->assertSee('add-user-modal', false)
        ->assertSee('name="_modal"', false)
        ->assertSee('name="redirect_to"', false)
        ->assertSee('name="account_type"', false)
        ->assertDontSee('add-student-modal', false)
        ->assertDontSee(route('admin.students.create'), false);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'guardians']))
        ->assertOk()
        ->assertSee('add-user-modal', false)
        ->assertDontSee('add-guardian-modal', false)
        ->assertDontSee(route('admin.guardians.create'), false);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'admin']))
        ->assertOk()
        ->assertSee('add-user-modal', false)
        ->assertDontSee(route('admin.users.create'), false);

    $this->actingAs($admin)
        ->get(route('admin.students.index'))
        ->assertOk()
        ->assertSee('add-student-modal', false)
        ->assertDontSee(route('admin.students.create'), false);

    $this->actingAs($admin)
        ->get(route('admin.sections.index'))
        ->assertOk()
        ->assertSee('add-section-modal', false)
        ->assertDontSee(route('admin.sections.create'), false);

    $this->actingAs($admin)
        ->get(route('admin.subjects.index'))
        ->assertOk()
        ->assertSee('add-subject-modal', false)
        ->assertDontSee(route('admin.subjects.create'), false);

    $this->actingAs($admin)
        ->get(route('announcements.index'))
        ->assertOk()
        ->assertSee('add-announcement-modal', false)
        ->assertDontSee(route('announcements.create'), false);
});

it('returns to the page a modal form was opened from', function () {
    [$admin, $section] = adminModalFixture();

    $this->actingAs($admin)
        ->post(route('admin.users.store-account'), [
            'account_type' => 'student',
            'lrn' => '123456789014',
            'first_name' => 'Modal',
            'last_name' => 'Student',
            'section_id' => $section->id,
            '_modal' => 'add-user-modal',
            'redirect_to' => route('admin.users.index', ['tab' => 'students']),
        ])
        ->assertRedirect(route('admin.users.index', ['tab' => 'students']));

    $this->assertDatabaseHas('students', ['lrn' => '123456789014']);
});

it('searches guardians by a full name containing spaces', function () {
    [$admin] = adminModalFixture();

    Guardian::create([
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'phone_number' => '09170000001',
        'status' => 'active',
    ]);

    Guardian::create([
        'first_name' => 'Pedro',
        'last_name' => 'Reyes',
        'phone_number' => '09170000002',
        'status' => 'active',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'guardians', 'search' => 'Maria Santos']))
        ->assertOk()
        ->assertSee('Maria Santos')
        ->assertDontSee('Pedro Reyes');
});

it('shows the announcement modal only when a teacher can post', function () {
    $section = Section::create([
        'name' => 'Grade 7 - Rizal',
        'grade_level' => 'Grade 7',
        'school_year' => '2026-2027',
    ]);

    $teacher = User::factory()->create(['role' => 'teacher', 'is_active' => true]);
    $teacher->sections()->attach($section);

    $this->actingAs($teacher)
        ->get(route('announcements.index'))
        ->assertOk()
        ->assertSee('add-announcement-modal', false);

    $lonelyTeacher = User::factory()->create(['role' => 'teacher', 'is_active' => true]);

    $this->actingAs($lonelyTeacher)
        ->get(route('announcements.index'))
        ->assertOk()
        ->assertDontSee('add-announcement-modal', false);
});

it('reopens the create modal when validation fails', function () {
    [$admin] = adminModalFixture();

    $this->actingAs($admin)
        ->from(route('admin.users.index', ['tab' => 'students']))
        ->followingRedirects()
        ->post(route('admin.users.store-account'), ['account_type' => 'student', '_modal' => 'add-user-modal'])
        ->assertOk()
        ->assertSee('document.getElementById("add-user-modal")', false);
});

it('takes the student create back button to the users tab', function () {
    [$admin] = adminModalFixture();

    $this->actingAs($admin)
        ->get(route('admin.students.create'))
        ->assertOk()
        ->assertSee(route('admin.users.index', ['tab' => 'students']), false);
});
