<?php

use App\Models\Guardian;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;

function sortingFixture(): array
{
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true, 'name' => 'Acting Admin']);

    $section = Section::create([
        'name' => 'Grade 10 - Rizal',
        'grade_level' => 'Grade 10',
        'school_year' => '2026-2027',
    ]);

    foreach ([['Zulu', 'qr-sort-zulu'], ['Alpha', 'qr-sort-alpha']] as [$firstName, $qrCode]) {
        Student::create([
            'lrn' => $firstName === 'Zulu' ? '123456789030' : '123456789031',
            'first_name' => $firstName,
            'last_name' => 'Student',
            'section_id' => $section->id,
            'qr_code' => $qrCode,
            'status' => 'active',
        ]);
    }

    Guardian::create([
        'first_name' => 'Guardian',
        'last_name' => 'Zulu',
        'phone_number' => '09170000010',
        'relationship_to_student' => 'Parent',
        'status' => 'active',
    ]);

    Guardian::create([
        'first_name' => 'Guardian',
        'last_name' => 'Alpha',
        'phone_number' => '09170000011',
        'relationship_to_student' => 'Parent',
        'status' => 'active',
    ]);

    User::factory()->create(['role' => 'admin', 'is_active' => true, 'name' => 'Zulu Admin']);
    User::factory()->create(['role' => 'admin', 'is_active' => true, 'name' => 'Alpha Admin']);

    return [$admin, $section];
}

it('numbers the student records and sorts them in both directions', function () {
    [$admin] = sortingFixture();

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'students', 'sort' => 'name', 'direction' => 'asc']))
        ->assertOk()
        ->assertSeeInOrder(['Alpha Student', 'Zulu Student'], false)
        ->assertSee('direction=desc', false)
        ->assertSee('<td class="px-4 py-4 text-sm font-medium text-gray-500">1</td>', false)
        ->assertSee('<td class="px-4 py-4 text-sm font-medium text-gray-500">2</td>', false);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'students', 'sort' => 'name', 'direction' => 'desc']))
        ->assertOk()
        ->assertSeeInOrder(['Zulu Student', 'Alpha Student'], false);
});

it('numbers and sorts the guardian records', function () {
    [$admin] = sortingFixture();

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'guardians', 'sort' => 'name', 'direction' => 'desc']))
        ->assertOk()
        ->assertSeeInOrder(['Guardian Zulu', 'Guardian Alpha'], false)
        ->assertSee('<td class="px-4 py-4 text-sm font-medium text-gray-500">1</td>', false)
        ->assertSee('<td class="px-4 py-4 text-sm font-medium text-gray-500">2</td>', false);
});

it('numbers and sorts the account tabs', function () {
    [$admin] = sortingFixture();

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'admin', 'sort' => 'name', 'direction' => 'desc']))
        ->assertOk()
        ->assertSeeInOrder(['Zulu Admin', 'Alpha Admin'], false)
        ->assertSee('<td class="px-4 py-4 text-sm font-medium text-gray-500">1</td>', false)
        ->assertSee('<td class="px-4 py-4 text-sm font-medium text-gray-500">2</td>', false);
});
