<?php

use App\Models\Section;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;

it('requires a section capacity between one and sixty', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->get(route('admin.sections.create'))
        ->assertOk()
        ->assertSee('min="1" max="60" step="1"', false);

    $this->post(route('admin.sections.store'), [
        'name' => 'Capacity Limit Section',
        'grade_level' => 'Grade 7',
        'school_year_start' => 2026,
        'school_year_end' => 2027,
        'max_capacity' => 10,
    ])->assertRedirect(route('admin.sections.index'));

    $section = Section::where('name', 'Capacity Limit Section')->firstOrFail();

    $this->get(route('admin.sections.show', $section))
        ->assertOk()
        ->assertSee('min="1" max="60" step="1"', false);

    foreach ([-1, 0, 61] as $capacity) {
        $this->put(route('admin.sections.update', $section), [
            'name' => $section->name,
            'grade_level' => $section->grade_level,
            'school_year_start' => 2026,
            'school_year_end' => 2027,
            'max_capacity' => $capacity,
        ])->assertSessionHasErrors('max_capacity');
    }

    $this->put(route('admin.sections.update', $section), [
        'name' => $section->name,
        'grade_level' => $section->grade_level,
        'school_year_start' => 2026,
        'school_year_end' => 2027,
    ])->assertSessionHasErrors('max_capacity');
});

it('splits school year values into consecutive years for section and school settings', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $this->actingAs($admin);

    $this->get(route('admin.sections.create'))
        ->assertOk()
        ->assertSee('name="school_year_start"', false)
        ->assertSee('name="school_year_end"', false);

    $this->post(route('admin.sections.store'), [
        'name' => 'School Year Range Section',
        'grade_level' => 'Grade 7',
        'school_year_start' => 2026,
        'school_year_end' => 2027,
        'max_capacity' => 40,
    ])->assertRedirect(route('admin.sections.index'));

    $section = Section::where('name', 'School Year Range Section')->firstOrFail();
    expect($section->school_year)->toBe('2026-2027');

    $this->get(route('admin.sections.show', $section))
        ->assertOk()
        ->assertSee('name="school_year_start" value="2026"', false)
        ->assertSee('name="school_year_end" value="2027"', false);

    $this->put(route('admin.sections.update', $section), [
        'name' => $section->name,
        'grade_level' => $section->grade_level,
        'school_year_start' => 2027,
        'school_year_end' => 2028,
        'max_capacity' => 40,
    ])->assertRedirect(route('admin.sections.show', $section));

    expect($section->fresh()->school_year)->toBe('2027-2028');

    $this->put(route('admin.sections.update', $section), [
        'name' => $section->name,
        'grade_level' => $section->grade_level,
        'school_year_start' => 2027,
        'school_year_end' => 2029,
        'max_capacity' => 40,
    ])->assertSessionHasErrors('school_year_end');

    $settings = Setting::current();
    $settings->update(['academic_year' => '2026-2027']);

    $this->get(route('admin.settings.edit'))
        ->assertOk()
        ->assertSee('name="academic_year_start" value="2026"', false)
        ->assertSee('name="academic_year_end" value="2027"', false);

    $settingsUpdate = [
        'school_name' => 'Test School',
        'academic_year_start' => 2028,
        'academic_year_end' => 2029,
        'timezone' => 'Asia/Manila',
        'qr_format' => 'png',
        'qr_default_size' => 400,
        'sms_provider' => 'semaphore',
    ];

    $this->put(route('admin.settings.update'), $settingsUpdate)->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($settings->fresh()->academic_year)->toBe('2028-2029');

    $this->put(route('admin.settings.update'), array_merge($settingsUpdate, [
        'academic_year_end' => 2030,
    ]))->assertSessionHasErrors('academic_year_end');
});

it('assigns multiple sections to a teacher and removes access when unchecked', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $teacher = User::factory()->create(['role' => 'teacher', 'is_active' => true]);
    $otherTeacher = User::factory()->create(['role' => 'sub_admin', 'is_active' => true]);

    $first = Section::create([
        'name' => 'Grade 7 - Rizal',
        'grade_level' => 'Grade 7',
        'school_year' => '2026-2027',
        'max_capacity' => 10,
    ]);
    $second = Section::create([
        'name' => 'Grade 7 - Bonifacio',
        'grade_level' => 'Grade 7',
        'school_year' => '2026-2027',
    ]);

    $this->actingAs($admin)
        ->put(route('admin.sections.update', $first), [
            'name' => $first->name,
            'grade_level' => $first->grade_level,
            'school_year_start' => 2026,
            'school_year_end' => 2027,
            'status' => 'active',
            'max_capacity' => 10,
            'teacher_ids' => [$teacher->id, $otherTeacher->id],
        ])
        ->assertRedirect(route('admin.sections.show', $first));

    $this->actingAs($admin)
        ->get(route('admin.sections.show', $first))
        ->assertOk()
        ->assertSee('0 / 10')
        ->assertDontSee('style="width:', false);

    $this->actingAs($admin)
        ->put(route('admin.sections.update', $second), [
            'name' => $second->name,
            'grade_level' => $second->grade_level,
            'school_year_start' => 2026,
            'school_year_end' => 2027,
            'status' => 'active',
            'max_capacity' => 10,
            'teacher_ids' => [$teacher->id],
        ])
        ->assertRedirect(route('admin.sections.show', $second));

    expect($teacher->fresh()->sections->pluck('id')->sort()->values()->all())
        ->toBe([$first->id, $second->id]);

    $this->actingAs($teacher)
        ->get(route('sub-admin.sections.index'))
        ->assertOk()
        ->assertSee($first->name)
        ->assertSee($second->name);

    $this->actingAs($admin)
        ->put(route('admin.sections.update', $first), [
            'name' => $first->name,
            'grade_level' => $first->grade_level,
            'school_year_start' => 2026,
            'school_year_end' => 2027,
            'status' => 'active',
            'max_capacity' => 10,
            'teacher_ids' => [$otherTeacher->id],
        ]);

    expect($teacher->fresh()->sections->pluck('id')->all())->toBe([$second->id]);
});

it('shows every user category as a tab on one Users page', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $teacher = User::factory()->create(['name' => 'Tabbed Teacher', 'role' => 'teacher', 'is_active' => true]);
    $subAdmin = User::factory()->create(['name' => 'Tabbed Sub Admin', 'role' => 'sub_admin', 'is_active' => true]);
    $guard = User::factory()->create(['name' => 'Tabbed Guard', 'role' => 'guard', 'is_active' => true]);

    $section = Section::create([
        'name' => 'Grade 10 - Luna',
        'grade_level' => 'Grade 10',
        'school_year' => '2026-2027',
    ]);
    $section->teachers()->attach($teacher);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'teacher']))
        ->assertOk()
        ->assertSee('Tabbed Teacher')
        ->assertSee('Tabbed Sub Admin')
        ->assertSee($section->name)
        ->assertSee('Students')
        ->assertSee('Guardians')
        ->assertSee('Teacher')
        ->assertDontSee('Sub-Admin')
        ->assertSee('Guards')
        ->assertSee('bg-green-100 text-green-800', false)
        ->assertSee('overflow-x-auto', false)
        ->assertSee('min-w-max', false)
        ->assertDontSee(route('admin.users.index', ['tab' => 'sub_admin']), false);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'sub_admin']))
        ->assertOk()
        ->assertSee('Tabbed Teacher')
        ->assertSee('Tabbed Sub Admin');

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['role' => 'sub_admin']))
        ->assertOk()
        ->assertSee('Tabbed Sub Admin')
        ->assertDontSee('Tabbed Teacher');

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'guard']))
        ->assertOk()
        ->assertSee('Tabbed Guard');
});

it('renders the subject, users, and student management pages', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $teacher = User::factory()->create(['role' => 'teacher', 'is_active' => true]);

    $section = Section::create([
        'name' => 'Grade 9 - Mabini',
        'grade_level' => 'Grade 9',
        'school_year' => '2026-2027',
    ]);
    $section->teachers()->attach($teacher);

    $student = Student::create([
        'lrn' => '123456789030',
        'first_name' => 'Page',
        'last_name' => 'Check',
        'section_id' => $section->id,
        'qr_code' => 'page-check-qr',
        'status' => 'active',
    ]);

    $subject = Subject::create([
        'name' => 'English',
        'code' => 'ENG',
        'status' => 'active',
    ]);

    $this->actingAs($admin)->get(route('admin.subjects.create'))->assertOk()->assertSee('Assigned Teachers');
    $this->actingAs($admin)->get(route('admin.subjects.show', $subject))->assertOk()->assertSee('English');
    $this->actingAs($admin)->get(route('admin.students.show', $student))->assertOk()->assertSee('Student Information');
    $this->actingAs($admin)->get(route('admin.users.create'))->assertOk()->assertSee('Confirm Password');
    $this->actingAs($admin)->get(route('admin.users.edit', $teacher))->assertOk()->assertSee('Assigned Sections');
    $this->actingAs($admin)->get(route('admin.users.index', ['tab' => 'students']))->assertOk()->assertSee('Student Records')->assertDontSee('Student Portal Accounts');
    $this->actingAs($admin)->get(route('admin.users.index', ['tab' => 'guardians']))->assertOk()->assertSee('Guardian Records')->assertDontSee('Guardian Portal Accounts');
});

it('creates subjects and assigns several teachers to them', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $teacherA = User::factory()->create(['role' => 'teacher', 'is_active' => true]);
    $teacherB = User::factory()->create(['role' => 'sub_admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->post(route('admin.subjects.store'), [
            'name' => 'Science',
            'code' => 'SCI',
            'description' => 'Science subject',
            'status' => 'active',
            'teacher_ids' => [$teacherA->id, $teacherB->id],
        ])
        ->assertRedirect(route('admin.subjects.index'));

    $subject = Subject::where('code', 'SCI')->firstOrFail();

    expect($subject->teachers->pluck('id')->sort()->values()->all())
        ->toBe([$teacherA->id, $teacherB->id]);

    $this->actingAs($admin)
        ->get(route('admin.subjects.index'))
        ->assertOk()
        ->assertSee('Science');
});
