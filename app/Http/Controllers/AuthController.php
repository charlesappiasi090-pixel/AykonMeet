<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (auth()->check()) {
            return redirect()->route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'dashboard');
        }

        return view('auth.login');
    }

    public function showRegisterForm()
    {
        abort_unless(! User::exists(), 403, 'Admin registration is closed.');

        return view('auth.register');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'meeting_code' => ['nullable', 'string', 'exists:meetings,meeting_code'],
        ]);

        $rateLimitKey = 'login:'.$request->ip().':'.$request->input('email');

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ])->onlyInput('email');
        }

        $credentials = $request->only('email', 'password');
        $meetingCode = $request->input('meeting_code');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($rateLimitKey);
            $request->session()->regenerate();

            if ($meetingCode !== null) {
                return redirect()->route('meetings.show', $meetingCode);
            }

            return redirect()->intended(
                auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard')
            );
        }

        RateLimiter::hit($rateLimitKey);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function register(Request $request)
    {
        abort_unless(! User::exists(), 403, 'Admin registration is closed.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'confirmed', new StrongPassword],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_admin' => true,
        ]);

        Auth::login($user);

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }
}
