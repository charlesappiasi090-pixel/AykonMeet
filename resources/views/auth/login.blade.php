@extends('layouts.app')

@section('content')
<div class="auth-shell">
    <form class="panel auth-panel" method="post" action="{{ route('login.submit') }}">
        @csrf
        <div class="panel-title">
            <span class="icon-box">A</span>
            <div>
                <h3>User access</h3>
                <p>Sign in to manage your meeting access.</p>
            </div>
        </div>

        <div class="form-grid auth-grid">
            <label>Email
                <input type="email" name="email" value="{{ old('email') }}" required autofocus>
            </label>
            <label>Password
                <span class="password-field"><input id="login-password" type="password" name="password" required><button class="password-toggle" type="button" onclick="togglePassword('login-password', this)" aria-label="Show password" aria-pressed="false">Show</button></span>
            </label>
            <label class="checkbox-row">
                <input type="checkbox" name="remember" value="1">
                <span>Keep me signed in</span>
            </label>
        </div>

        <button class="button primary" type="submit">Sign in <span>→</span></button>
    </form>
</div>
<script>
    function togglePassword(inputId, button) {
        const input = document.getElementById(inputId);
        const visible = input.type === 'text';
        input.type = visible ? 'password' : 'text';
        button.textContent = visible ? 'Show' : 'Hide';
        button.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
        button.setAttribute('aria-pressed', String(!visible));
    }
</script>
@endsection
