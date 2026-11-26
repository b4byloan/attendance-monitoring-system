<?php

use App\Models\Section;
use App\Models\Subject;
use App\Models\User;

it('offers only grade 7 to 10 in the section form', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->get(route('admin.sections.index'))
        ->assertOk()
        ->assertSee('name="grade_level"', false)
        ->assertSee('<option value="Grade 7"', false)
        ->assertSee('<option value="Grade 8"', false)
        ->assertSee('<option value="Grade 9"', false)
        ->assertSee('<option value="Grade 10"', false)
        ->assertDontSee('<option value="Grade 11"', false);

    $this->actingAs($admin)
        ->post(route('admin.sections.store'), [
            'name' => 'Grade 11 - Invalid',
            'grade_level' => 'Grade 11',
            'school_year_start' => 2026,
            'school_year_end' => 2027,
            'max_capacity' => 50,
        ])
        ->assertSessionHasErrors('grade_level');

    expect(Section::where('name', 'Grade 11 - Invalid')->exists())->toBeFalse();
});

it('adds a non-submitting teacher search to the section and subject forms', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $teacher = User::factory()->create(['name' => 'Searchable Adviser', 'role' => 'teacher', 'is_active' => true]);

    $section = Section::create([
        'name' => 'Grade 7 - Sampaguita',
        'grade_level' => 'Grade 7',
        'school_year' => '2026-2027',
        'max_capacity' => 50,
    ]);
    $section->teachers()->attach($teacher);

    $subject = Subject::create([
        'name' => 'Science',
        'code' => 'SCI',
        'description' => 'Science',
        'status' => 'active',
    ]);
    $subject->teachers()->attach($teacher);

    foreach ([route('admin.sections.show', $section), route('admin.subjects.show', $subject)] as $url) {
        $this->actingAs($admin)
            ->get($url)
            ->assertOk()
            ->assertSee('data-teacher-picker', false)
            ->assertSee('data-teacher-search', false)
            ->assertSee('Search teachers by name', false)
            ->assertSee('data-teacher-option', false)
            ->assertSee("event.key !== 'Enter'", false);
    }
});

it('wraps the unified form password fields in a bordered group', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'teachers']))
        ->assertOk()
        ->assertSee('data-password-group', false)
        ->assertSee('name="password"', false)
        ->assertSee('name="password_confirmation"', false)
        ->assertSee('flex items-stretch overflow-hidden rounded-lg border bg-white', false);
});

it('offers fixed relationship options for guardians', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'guardians']))
        ->assertOk()
        ->assertSee('name="relationship_to_student"', false)
        ->assertSee('<option value="Mother"', false)
        ->assertSee('<option value="Father"', false)
        ->assertSee('<option value="Guardian"', false);
});
