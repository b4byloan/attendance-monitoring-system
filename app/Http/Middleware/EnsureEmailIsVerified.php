<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Keep accounts that still need OTP verification inside the verification flow.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user
            && $user->email_verified_at === null
            && ! $request->routeIs('otp.*')
            && ! $request->routeIs('logout')
        ) {
            return redirect()->route('otp.show');
        }

        return $next($request);
    }
}
