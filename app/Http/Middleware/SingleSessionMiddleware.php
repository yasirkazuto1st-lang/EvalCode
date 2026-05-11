<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SingleSessionMiddleware
{
    /**
     * Handle an incoming request.
     * Ensures only one active session per user account.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // If the user's stored session ID doesn't match the current one,
            // it means another device/browser has logged in — kick this session.
            if ($user->last_session_id && $user->last_session_id !== session()->getId()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Akun Anda telah login di perangkat lain. Sesi ini telah diakhiri otomatis.'], 401);
                }

                return redirect()->route('login')->withErrors([
                    'nim_nip' => 'Akun Anda telah login di perangkat lain. Silakan login kembali.',
                ]);
            }
        }

        return $next($request);
    }
}
