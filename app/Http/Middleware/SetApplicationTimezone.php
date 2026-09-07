<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetApplicationTimezone
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $timezone = Setting::current()->timezone;

            if ($timezone) {
                config(['app.timezone' => $timezone]);
                date_default_timezone_set($timezone);
            }
        } catch (\Throwable $e) {
            // settings table may not exist yet (e.g. before first migrate) -
            // fall back to config/app.php's value instead of breaking every request.
        }

        return $next($request);
    }
}