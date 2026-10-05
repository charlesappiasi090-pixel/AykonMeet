@extends('layouts.app')

@section('content')
<div class="auth-shell">
    <form class="panel auth-panel" method="post" action="{{ route('register.submit') }}">
        @csrf
        <div class="panel-title">
            <span class="icon-box yellow">A</span>
            <div>
                <h3>Create admin</h3>
                <p>Set up the first admin account for the console.</p>
            </div>
        </div>

        <div class="form-grid auth-grid">
            <label>Name
                <input type="text" name="name" value="{{ old('name') }}" required autofocus>
            </label>
            <label>Email
                <input type="email" name="email" value="{{ old('email') }}" required>
            </label>
            <label>Password
                <input type="password" name="password" required>
            </label>
            <label>Confirm password
                <input type="password" name="password_confirmation" required>
            </label>
        </div>

        <button class="button primary" type="submit">Create admin <span>→</span></button>
        <p class="auth-switch">
            Already have access? <a href="{{ route('login') }}">Sign in</a>
        </p>
    </form>
</div>
@endsection
