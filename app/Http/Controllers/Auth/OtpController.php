<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OtpController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->email_verified_at !== null) {
            return redirect()->route($user->dashboardRoute());
        }

        return view('auth.otp', [
            'schoolSettings' => Setting::current(),
            'email' => $user->email,
            'expiresAt' => $user->otp_expires_at,
        ]);
    }

    public function verify(Request $request, OtpService $otp): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:'.OtpService::CODE_LENGTH],
        ], [
            'code.digits' => 'Enter the '.OtpService::CODE_LENGTH.'-digit code we emailed you.',
        ]);

        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $otp->verify($user, $validated['code'])) {
            return back()->withErrors([
                'code' => 'That code is invalid or has expired. Request a new code and try again.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route($user->dashboardRoute())
            ->with('success', 'Your email address has been verified.');
    }

    public function resend(Request $request, OtpService $otp): RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->email_verified_at !== null) {
            return redirect()->route($user->dashboardRoute());
        }

        $otp->send($user);

        return back()->with('success', 'A new verification code was sent to '.$user->email.'.');
    }

    public function cancel(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You can sign in again once you have your verification code.');
    }
}
