<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Http\Controllers\Controller;

class DesignPreviewController extends Controller
{
    use LoadsNotificationBell;

    public function index()
    {
        return view('admin.design-preview')->with($this->notificationBellData());
    }
}