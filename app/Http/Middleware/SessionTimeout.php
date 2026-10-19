<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $timeout = config('session.lifetime') * 60; // Convert minutes to seconds
        $lastActivity = Session::get('last_activity_time');

        if ($lastActivity && (time() - $lastActivity) > $timeout) {
            // Session has expired due to inactivity
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Your session has expired due to inactivity. Please log in again.');
        }

        // Update last activity time
        Session::put('last_activity_time', time());

        return $next($request);
    }
}
