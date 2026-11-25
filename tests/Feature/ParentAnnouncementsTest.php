<?php

use App\Models\Announcement;
use App\Models\User;

it('shows the parent announcements page', function () {
    $parent = User::factory()->create(['role' => 'parent', 'is_active' => true]);

    Announcement::create([
        'title' => 'Parent Portal Announcement',
        'description' => 'This is a school-wide announcement for parents.',
        'section_id' => null,
        'author_id' => null,
        'publish_date' => now()->toDateString(),
        'expiration_date' => null,
        'is_pinned' => false,
        'status' => 'published',
    ]);

    $this->actingAs($parent)
        ->get(route('parent.announcements'))
        ->assertOk()
        ->assertSee('Parent Portal Announcement')
        ->assertSee('School-wide');
});
