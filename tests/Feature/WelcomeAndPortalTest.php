<?php

use App\Models\Announcement;
use App\Models\User;

it('shows active school announcements and hides expired announcements', function () {
    Announcement::withoutEvents(fn () => Announcement::create([
        'title' => 'Active school announcement',
        'description' => 'This announcement remains visible.',
        'section_id' => null,
        'publish_date' => now()->subDay()->toDateString(),
        'expiration_date' => now()->toDateString(),
        'status' => 'published',
        'is_pinned' => false,
    ]));

    Announcement::withoutEvents(fn () => Announcement::create([
        'title' => 'Expired school announcement',
        'description' => 'This announcement should not be visible.',
        'section_id' => null,
        'publish_date' => now()->subDays(3)->toDateString(),
        'expiration_date' => now()->subDay()->toDateString(),
        'status' => 'published',
        'is_pinned' => false,
    ]));

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Active school announcement');
    $response->assertDontSee('Expired school announcement');
});

it('shows all active announcements on the public announcements page', function () {
    Announcement::withoutEvents(fn () => Announcement::create([
        'title' => 'Public announcements page',
        'description' => 'The complete public announcement content.',
        'section_id' => null,
        'publish_date' => now()->subDay()->toDateString(),
        'expiration_date' => now()->toDateString(),
        'status' => 'published',
        'is_pinned' => false,
    ]));

    $this->get(route('public-announcements.index'))
        ->assertOk()
        ->assertSee('Public announcements page')
        ->assertSee('The complete public announcement content.');

    Announcement::withoutEvents(fn () => Announcement::create([
        'title' => 'Expired details announcement',
        'description' => 'This should be unavailable.',
        'section_id' => null,
        'publish_date' => now()->subDays(3)->toDateString(),
        'expiration_date' => now()->subDay()->toDateString(),
        'status' => 'published',
        'is_pinned' => false,
    ]));

    $this->get(route('public-announcements.index'))
        ->assertDontSee('Expired details announcement')
        ->assertDontSee('This should be unavailable.');
});

it('redirects an authenticated user to the dashboard assigned to their role', function (string $role, string $dashboardRoute) {
    $user = User::factory()->create(['role' => $role]);

    $this->actingAs($user)
        ->get('/login')
        ->assertRedirect(route($dashboardRoute));
})->with([
    'admin' => ['admin', 'admin.dashboard'],
    'sub-admin' => ['sub_admin', 'sub-admin.dashboard'],
    'parent' => ['parent', 'parent.dashboard'],
    'guard' => ['guard', 'guard.dashboard'],
    'student' => ['student', 'student.dashboard'],
]);
