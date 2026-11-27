<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(private readonly OtpService $otp) {}

    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();

            return redirect()->route($user->email_verified_at === null ? 'otp.show' : $user->dashboardRoute());
        }

        return view('auth.login', ['schoolSettings' => Setting::current()]);
    }

    public function store(Request $request)
    {

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'This account has been deactivated. Please contact the administrator.',
            ]);
        }

        if ($user->email_verified_at === null) {
            if (! $this->otp->hasPendingCode($user)) {
                $this->otp->send($user);
            }

            $request->session()->regenerate();

            return redirect()->route('otp.show')
                ->with('status', 'Enter the verification code we emailed to '.$user->email.'.');
        }

        $user->update(['last_login_at' => now()]);
        $request->session()->regenerate();

        return redirect()->intended(route($user->dashboardRoute()));
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
