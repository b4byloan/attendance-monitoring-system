<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Setting;
use Illuminate\View\View;

class PublicAnnouncementController extends Controller
{
    public function __invoke(): View
    {
        return view('announcements.public-index', [
            'announcements' => Announcement::visible()
                ->whereNull('section_id')
                ->orderByDesc('is_pinned')
                ->orderByDesc('publish_date')
                ->get(),
            'schoolSettings' => Setting::current(),
        ]);
    }
}
