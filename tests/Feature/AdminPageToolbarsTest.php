<?php

use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;

it('places the add-user button in the users search toolbar without a page header', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'teachers']))
        ->assertOk()
        ->assertDontSee('<h2 class="text-lg font-semibold text-gray-900">Users</h2>', false)
        ->assertDontSee('All students, guardians, and school personnel in one place.')
        ->assertSeeInOrder(['name="search"', 'Add User'], false)
        ->assertSee("document.getElementById('add-user-modal').showModal()", false);
});

it('keeps add buttons next to the search button on sections, subjects and students pages', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    Section::create([
        'name' => 'Grade 9 - Rizal',
        'grade_level' => 'Grade 9',
        'school_year' => '2026-2027',
        'max_capacity' => 50,
    ]);

    Subject::create(['name' => 'Science', 'code' => 'SCI', 'description' => 'Science', 'status' => 'active']);

    Student::create([
        'lrn' => '123456789123',
        'first_name' => 'Toolbar',
        'last_name' => 'Student',
        'status' => 'active',
        'qr_code' => 'toolbar-student-qr',
        'section_id' => Section::first()->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.sections.index'))
        ->assertOk()
        ->assertDontSee('All Sections')
        ->assertSeeInOrder(['name="search"', 'Add Section'], false);

    $this->actingAs($admin)
        ->get(route('admin.subjects.index'))
        ->assertOk()
        ->assertDontSee('<h2 class="text-lg font-semibold text-gray-900">Subjects</h2>', false)
        ->assertSeeInOrder(['name="search"', 'Add Subject'], false);

    $this->actingAs($admin)
        ->get(route('admin.students.index'))
        ->assertOk()
        ->assertDontSee('All Students')
        ->assertSeeInOrder(['name="search"', 'Add Student'], false);
});

it('moves the announcement, backup and report actions into the toolbar rows', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->get(route('announcements.index'))
        ->assertOk()
        ->assertDontSee('<h2 class="text-lg font-semibold text-gray-900">Announcements</h2>', false)
        ->assertSeeInOrder(['name="search"', 'Create Announcements'], false);

    $this->actingAs($admin)
        ->get(route('admin.backups.index'))
        ->assertOk()
        ->assertDontSee('<h2 class="text-lg font-semibold text-gray-900">Backups</h2>', false)
        ->assertSee('Create Backup Now');

    $this->actingAs($admin)
        ->get(route('admin.reports.index'))
        ->assertOk()
        ->assertDontSee('<h2 class="text-lg font-semibold text-gray-900">Attendance Reports</h2>', false)
        ->assertSeeInOrder(['Apply Filters', 'Export PDF', 'Export Excel', 'Export CSV'], false);
});

it('drops the page headers on the remaining admin list pages', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->get(route('admin.audit-logs.index'))
        ->assertOk()
        ->assertDontSee('Audit Logs</h2>', false)
        ->assertSee('Apply Filters');
});
