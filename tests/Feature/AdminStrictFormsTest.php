<?php

use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;

it('enforces strict values on the student form', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'students']))
        ->assertOk()
        ->assertSee('name="lrn"', false)
        ->assertSee('pattern="\d{12}"', false)
        ->assertSee('maxlength="12"', false)
        ->assertSee('data-digits-only', false)
        ->assertSee('name="suffix"', false)
        ->assertSee('<option value="Jr."', false)
        ->assertSee('Jr.', false)
        ->assertSee('III', false);
});

it('renders the section view/edit page with the edit form beside the information', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $section = Section::create([
        'name' => 'Grade 8 - Del Pilar',
        'grade_level' => 'Grade 8',
        'school_year' => '2026-2027',
        'max_capacity' => 55,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.sections.show', $section))
        ->assertOk()
        ->assertSee('Section Information')
        ->assertSee('Edit Section')
        ->assertSee('Grade 8 - Del Pilar')
        ->assertSee('55')
        ->assertSee('min="1" max="60" step="1"', false)
        ->assertSee('lg:grid-cols-5', false)
        ->assertDontSee('/sections/'.$section->id.'/edit', false);
});

it('requires a section capacity when creating a section', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->post(route('admin.sections.store'), [
            'name' => 'No Capacity Section',
            'grade_level' => 'Grade 9',
            'school_year_start' => 2026,
            'school_year_end' => 2027,
        ])
        ->assertSessionHasErrors('max_capacity');

    expect(Section::where('name', 'No Capacity Section')->exists())->toBeFalse();
});

it('shows subject information, teachers, and the edit form on one page', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $teacher = User::factory()->create(['name' => 'Subject Handler', 'role' => 'teacher', 'is_active' => true]);

    $subject = Subject::create([
        'name' => 'Mathematics',
        'code' => 'MATH',
        'description' => 'Core math subject',
        'status' => 'active',
    ]);
    $subject->teachers()->attach($teacher);

    $this->actingAs($admin)
        ->get(route('admin.subjects.show', $subject))
        ->assertOk()
        ->assertSee('Subject Information')
        ->assertSee('Edit Subject')
        ->assertSee('Mathematics')
        ->assertSee('MATH')
        ->assertSee('Teachers Handling this Subject')
        ->assertSee('Subject Handler')
        ->assertSee('Core math subject')
        ->assertSee('lg:grid-cols-5', false);
});

it('no longer asks for a subject grade level', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->get(route('admin.subjects.index'))
        ->assertOk()
        ->assertDontSee('name="grade_level"', false);

    $this->actingAs($admin)
        ->post(route('admin.subjects.store'), [
            'name' => 'Values Education',
            'code' => 'VE',
            'description' => 'Values subject',
            'status' => 'active',
        ])
        ->assertRedirect(route('admin.subjects.index'));

    expect(Subject::where('code', 'VE')->exists())->toBeTrue();
});

it('stores subject names in uppercase without changing the subject code casing', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->post(route('admin.subjects.store'), [
            'name' => 'biology',
            'code' => 'bio',
            'description' => 'Biology subject',
            'status' => 'active',
        ])
        ->assertRedirect(route('admin.subjects.index'));

    $subject = Subject::where('code', 'bio')->firstOrFail();

    expect($subject->name)->toBe('BIOLOGY')
        ->and($subject->code)->toBe('bio');

    $this->put(route('admin.subjects.update', $subject), [
        'name' => 'chemistry',
        'code' => 'sci',
        'description' => 'Chemistry subject',
        'status' => 'active',
    ])->assertRedirect(route('admin.subjects.index'));

    $subject->refresh();

    expect($subject->name)->toBe('CHEMISTRY')
        ->and($subject->code)->toBe('sci');
});

it('rejects a subject without a description', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->post(route('admin.subjects.store'), [
            'name' => 'No Description',
            'code' => 'NODESC',
            'status' => 'active',
        ])
        ->assertSessionHasErrors('description');
});

it('enforces digit-only mobile numbers on the guardian form', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'guardians']))
        ->assertOk()
        ->assertSee('name="phone_number"', false)
        ->assertSee('pattern="09[0-9]{9}"', false)
        ->assertSee('data-phone-number', false)
        ->assertSee('data-max-digits="11"', false);
});

it('keeps the student add form in a modal on the users page', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $section = Section::create([
        'name' => 'Grade 7 - Modal',
        'grade_level' => 'Grade 7',
        'school_year' => '2026-2027',
        'max_capacity' => 50,
    ]);

    Student::create([
        'lrn' => '123456789012',
        'first_name' => 'Modal',
        'last_name' => 'Student',
        'section_id' => $section->id,
        'status' => 'active',
        'qr_code' => 'modal-student-qr',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'students']))
        ->assertOk()
        ->assertSee('add-user-modal', false)
        ->assertDontSee(route('admin.students.create'), false);
});
