<?php

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Guardian;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

function uiPolishFixture(): array
{
    $admin = User::factory()->create(['name' => 'Ada Admin', 'role' => 'admin', 'is_active' => true]);
    $section = Section::create([
        'name' => 'Grade 9 - Rizal',
        'grade_level' => 'Grade 9',
        'school_year' => '2026-2027',
        'max_capacity' => 50,
    ]);

    $studentUser = User::factory()->create(['name' => 'Email Holder', 'role' => 'student', 'is_active' => true]);

    $student = Student::create([
        'lrn' => '123456789011',
        'first_name' => 'Copy',
        'last_name' => 'Student',
        'section_id' => $section->id,
        'status' => 'active',
        'qr_code' => 'copy-student-qr',
        'user_id' => $studentUser->id,
    ]);

    return [$admin, $section, $student, $studentUser];
}

it('renders missing optional values with a muted fallback and preserves present values', function () {
    $missing = Blade::render('<x-ui.optional-value />');
    $customFallback = Blade::render('<x-ui.optional-value fallback="Unassigned" />');
    $present = Blade::render('<x-ui.optional-value value="Assigned" />');
    $zero = Blade::render('<x-ui.optional-value :value="$value" />', ['value' => 0]);

    expect($missing)->toContain('class="text-slate-400 italic"')
        ->toContain('style="color: #94a3b8; font-style: italic;"')
        ->toContain('>N/A</span>');
    expect($customFallback)->toContain('class="text-slate-400 italic"')
        ->toContain('>Unassigned</span>');
    expect($present)->toContain('>Assigned</span>')->not->toContain('text-slate-400 italic');
    expect($zero)->toContain('>0</span>')->not->toContain('>N/A</span>');
});

it('offers copy buttons for emails and phone numbers', function () {
    [$admin, , , $studentUser] = uiPolishFixture();

    Guardian::create([
        'first_name' => 'Copy',
        'last_name' => 'Guardian',
        'phone_number' => '09171234567',
        'email' => 'guardian.copy@example.com',
        'status' => 'active',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'students']))
        ->assertOk()
        ->assertSee('aria-label="Copy email '.$studentUser->email.'"', false)
        ->assertSee('data-copy-value="'.$studentUser->email.'"', false);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'guardians']))
        ->assertOk()
        ->assertSee('aria-label="Copy phone number 09171234567"', false)
        ->assertSee('aria-label="Copy email guardian.copy@example.com"', false);

    $this->actingAs($admin)
        ->get(route('account-settings.edit'))
        ->assertOk()
        ->assertSee('aria-label="Copy email '.$admin->email.'"', false);
});

it('keeps the announcement search bar sticky while scrolling', function () {
    [$admin] = uiPolishFixture();

    Announcement::create([
        'title' => 'Sticky Search Announcement',
        'description' => 'Body',
        'author_id' => $admin->id,
        'publish_date' => now()->toDateString(),
        'status' => 'published',
    ]);

    $this->actingAs($admin)
        ->get(route('announcements.index'))
        ->assertOk()
        ->assertSee('sticky top-0 z-20', false)
        ->assertSee('Search announcements by title');
});

it('renders a close button on the modals', function () {
    [$admin] = uiPolishFixture();

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['tab' => 'students']))
        ->assertOk()
        ->assertSee('add-user-modal', false)
        ->assertSee('aria-label="Close dialog"', false);

    $this->actingAs($admin)
        ->get(route('admin.sections.index'))
        ->assertOk()
        ->assertSee('aria-label="Close dialog"', false);
});

it('shows audit log details in a modal instead of a separate page', function () {
    [$admin] = uiPolishFixture();

    ActivityLog::create([
        'user_id' => $admin->id,
        'action' => 'created',
        'description' => 'Created a student record',
        'ip_address' => '127.0.0.1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $log = ActivityLog::firstOrFail();

    $this->actingAs($admin)
        ->get(route('admin.audit-logs.index'))
        ->assertOk()
        ->assertSee('audit-log-modal-'.$log->id, false)
        ->assertSee('Log details')
        ->assertSee('Created a student record')
        ->assertSee('aria-label="Copy email '.$admin->email.'"', false);

    expect(Route::has('admin.audit-logs.show'))->toBeFalse();
});
