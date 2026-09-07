<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
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
        $user->update(['last_login_at' => now()]);
        $request->session()->regenerate();

        return redirect()->intended($this->redirectPathFor($user->role));
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function redirectPathFor(string $role): string
    {
        return match ($role) {
            'admin' => '/admin/dashboard',
            'sub_admin' => '/sub-admin/dashboard',
            'parent' => '/parent/dashboard',
            'guard' => '/guard/dashboard',
            default => '/login',
        };
    }
}