<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    use LoadsNotificationBell;

    public function edit()
    {
        $settings = Setting::current();
        [$academicYearStart, $academicYearEnd] = array_pad(explode('-', $settings->academic_year, 2), 2, '');
        $apiKeyConfigured = filled(config('services.semaphore.api_key'));

        return view('admin.settings.edit', compact('settings', 'academicYearStart', 'academicYearEnd', 'apiKeyConfigured'))->with($this->notificationBellData());
    }

    public function update(Request $request)
    {
        $settings = Setting::current();

        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'school_address' => ['nullable', 'string', 'max:500'],
            'principal_name' => ['nullable', 'string', 'max:255'],
            'school_contact_number' => ['nullable', 'regex:/^09\d{9}$/'],
            'school_email' => ['nullable', 'email'],
            'academic_year_start' => ['required', 'integer', 'digits:4', 'min:1000', 'max:9999'],
            'academic_year_end' => ['required', 'integer', 'digits:4', 'min:1000', 'max:9999', Rule::in([(string) ((int) $request->input('academic_year_start') + 1)])],
            'timezone' => ['required', 'timezone'],
            'school_start_time' => ['nullable', 'date_format:H:i'],
            'school_end_time' => ['nullable', 'date_format:H:i'],
            'checkin_start_time' => ['nullable', 'date_format:H:i'],
            'checkin_cutoff_time' => ['nullable', 'date_format:H:i'],
            'checkout_start_time' => ['nullable', 'date_format:H:i'],
            'checkout_cutoff_time' => ['nullable', 'date_format:H:i'],
            'late_threshold_minutes' => ['nullable', 'integer', 'min:0'],
            'qr_expiration_days' => ['nullable', 'integer', 'min:1'],
            'qr_format' => ['required', 'in:png'],
            'qr_default_size' => ['required', 'integer', 'min:100', 'max:1000'],
            'sms_provider' => ['required', 'in:semaphore'],
            'sms_sender_name' => ['nullable', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'max:10240'],
        ], [
            'academic_year_end.in' => 'The end year must be one year after the start year.',
            'logo.image' => 'The logo must be a JPG, JPEG, PNG, GIF, BMP, or WebP image.',
            'logo.max' => 'The logo must not be larger than 10 MB.',
            'logo.uploaded' => 'The logo upload failed. Ensure the file is no larger than 10 MB and try again.',
        ]);

        $validated['academic_year'] = $validated['academic_year_start'].'-'.$validated['academic_year_end'];
        unset($validated['academic_year_start'], $validated['academic_year_end']);
        $validated['sms_enabled'] = $request->boolean('sms_enabled');

        if ($request->hasFile('logo')) {
            $validated['school_logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $settings->update($validated);

        return back()->with('success', 'Settings updated.');
    }
}
