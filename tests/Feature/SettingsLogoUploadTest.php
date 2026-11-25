<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('allows 10 MB school logos and explains invalid logo uploads', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $settingsUrl = route('admin.settings.edit');
    $settingsData = [
        'school_name' => 'Test School',
        'academic_year_start' => 2026,
        'academic_year_end' => 2027,
        'timezone' => 'Asia/Manila',
        'qr_format' => 'png',
        'qr_default_size' => 400,
        'sms_provider' => 'semaphore',
        '_method' => 'PUT',
    ];

    $this->actingAs($admin)
        ->from($settingsUrl)
        ->post(route('admin.settings.update'), array_merge($settingsData, [
            'logo' => UploadedFile::fake()->image('school-logo.png')->size(10240),
        ]))
        ->assertRedirect($settingsUrl)
        ->assertSessionHasNoErrors();

    Storage::disk('public')->assertExists(Setting::current()->school_logo_path);

    $this->from($settingsUrl)
        ->followingRedirects()
        ->post(route('admin.settings.update'), array_merge($settingsData, [
            'logo' => UploadedFile::fake()->image('school-logo.png')->size(10241),
        ]))
        ->assertOk()
        ->assertSee('The logo must not be larger than 10 MB.');

    $this->from($settingsUrl)
        ->followingRedirects()
        ->post(route('admin.settings.update'), array_merge($settingsData, [
            'logo' => UploadedFile::fake()->create('school-logo.txt', 10, 'text/plain'),
        ]))
        ->assertOk()
        ->assertSee('The logo must be a JPG, JPEG, PNG, GIF, BMP, or WebP image.');
});
