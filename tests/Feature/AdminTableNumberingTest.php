<?php

use App\Models\ActivityLog;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;

it('numbers the section table and keeps pagination aware counts', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    foreach (range(1, 12) as $index) {
        Section::create([
            'name' => "Grade 7 - Section {$index}",
            'grade_level' => 'Grade 7',
            'school_year' => '2026-2027',
            'max_capacity' => 50,
        ]);
    }

    $this->actingAs($admin)
        ->get(route('admin.sections.index'))
        ->assertOk()
        ->assertSee('<th class="px-4 py-3 text-left">#</th>', false)
        ->assertSee('>1</td>', false)
        ->assertSee('>10</td>', false)
        ->assertDontSee('>11</td>', false);

    $this->actingAs($admin)
        ->get(route('admin.sections.index', ['page' => 2]))
        ->assertOk()
        ->assertSee('>11</td>', false)
        ->assertSee('>12</td>', false);
});

it('numbers the subject and audit log tables', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    Subject::create(['name' => 'English', 'code' => 'ENG', 'description' => 'English', 'status' => 'active']);
    Subject::create(['name' => 'Science', 'code' => 'SCI', 'description' => 'Science', 'status' => 'active']);

    ActivityLog::create([
        'user_id' => $admin->id,
        'action' => 'created',
        'description' => 'Created something',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.subjects.index'))
        ->assertOk()
        ->assertSee('<th class="px-4 py-3 text-left">#</th>', false)
        ->assertSee('>1</td>', false)
        ->assertSee('>2</td>', false);

    $this->actingAs($admin)
        ->get(route('admin.audit-logs.index'))
        ->assertOk()
        ->assertSee('<th class="px-4 py-3 text-left">#</th>', false)
        ->assertSee('>1</td>', false);
});

it('adds a searchable scrollable teacher picker to the subject form', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $teacher = User::factory()->create(['name' => 'Searchable Teacher', 'role' => 'teacher', 'is_active' => true]);

    $subject = Subject::create([
        'name' => 'Mathematics',
        'code' => 'MATH',
        'description' => 'Math',
        'status' => 'active',
    ]);
    $subject->teachers()->attach($teacher);

    $this->actingAs($admin)
        ->get(route('admin.subjects.show', $subject))
        ->assertOk()
        ->assertSee('data-teacher-picker', false)
        ->assertSee('data-teacher-search', false)
        ->assertSee('Search teachers by name', false)
        ->assertSee('max-h-64 space-y-2 overflow-y-auto', false)
        ->assertSee('data-teacher-option', false)
        ->assertSee('data-teacher-empty', false);
});

it('caps the height of description textareas', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $teacher = User::factory()->create(['role' => 'teacher', 'is_active' => true]);

    $section = Section::create([
        'name' => 'Grade 10 - Rizal',
        'grade_level' => 'Grade 10',
        'school_year' => '2026-2027',
        'max_capacity' => 50,
    ]);
    $section->teachers()->attach($teacher);

    $student = Student::create([
        'lrn' => '123456789098',
        'first_name' => 'Textarea',
        'last_name' => 'Check',
        'section_id' => $section->id,
        'status' => 'active',
        'qr_code' => 'textarea-check-qr',
    ]);
    $student->guardians()->create([
        'first_name' => 'Guardian',
        'last_name' => 'Check',
        'phone_number' => '09171234567',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.subjects.create'))
        ->assertOk()
        ->assertSee('max-h-32 w-full resize-y overflow-y-auto', false);

    $this->actingAs($admin)
        ->get(route('admin.settings.edit'))
        ->assertOk()
        ->assertSee('max-h-32 w-full resize-y overflow-y-auto', false);

    $this->actingAs($admin)
        ->get(route('announcements.create'))
        ->assertOk()
        ->assertSee('max-h-48 w-full resize-y overflow-y-auto', false);
});

it('renders section cards in a wider two column grid', function () {
    $teacher = User::factory()->create(['role' => 'teacher', 'is_active' => true]);

    $section = Section::create([
        'name' => 'Grade 9 - Mabini',
        'grade_level' => 'Grade 9',
        'school_year' => '2026-2027',
        'max_capacity' => 50,
    ]);
    $section->teachers()->attach($teacher);

    $this->actingAs($teacher)
        ->get(route('sub-admin.dashboard'))
        ->assertOk()
        ->assertSee('sm:grid-cols-2 2xl:grid-cols-3', false)
        ->assertSee('break-words text-lg font-bold', false);
});
