<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Setting;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function __invoke(): View
    {
        return view('welcome', [
            'schoolSettings' => Setting::current(),
            'announcements' => Announcement::visible()
                ->whereNull('section_id')
                ->with('section')
                ->orderByDesc('is_pinned')
                ->orderByDesc('publish_date')
                ->get(),
        ]);
    }
}
