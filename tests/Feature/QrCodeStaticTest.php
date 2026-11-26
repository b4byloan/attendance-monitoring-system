<?php

use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Route;

it('keeps student QR codes static and removes regeneration and expiry', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $section = Section::create([
        'name' => 'Grade 10 - Rizal',
        'grade_level' => 'Grade 10',
        'school_year' => '2026-2027',
    ]);

    $student = Student::create([
        'lrn' => '123456789012',
        'first_name' => 'Static',
        'last_name' => 'Qr',
        'section_id' => $section->id,
        'qr_code' => 'permanent-qr-token',
        'status' => 'active',
    ]);

    expect(Route::has('admin.students.qr-code.regenerate'))->toBeFalse();

    $this->actingAs($admin)
        ->get(route('admin.students.show', $student))
        ->assertOk()
        ->assertDontSee('Regenerate QR code');

    $this->actingAs($admin)
        ->get(route('admin.settings.edit'))
        ->assertOk()
        ->assertDontSee('QR Expiration')
        ->assertSee('never expire');

    expect($student->fresh()->qr_code)->toBe('permanent-qr-token');
});
