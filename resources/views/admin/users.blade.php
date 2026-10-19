@extends('layouts.app')

@section('content')
@include('admin._styles')
<header class="admin-hero">
    <div>
        <p class="admin-kicker"><span class="dot"></span>Admin console / Users</p>
        <h1>Workspace <em>users.</em></h1>
        <p class="admin-sub">Review the accounts registered for AykonMeet and keep an eye on who has joined the workspace.</p>
        <div class="admin-actions">
            <a class="admin-btn admin-btn-gold" href="{{ route('meetings.index') }}">Meetings console <i>&rarr;</i></a>
            <a class="admin-btn admin-btn-ghost" href="{{ route('admin.dashboard') }}">Rooms &amp; meetings</a>
        </div>
    </div>
    <div class="admin-tiles" aria-label="User overview">
        <div class="admin-tile gold"><b>{{ $users->count() }}</b><span>Registered users</span><small>Accounts in this workspace</small></div>
        <div class="admin-tile"><b>{{ $users->whereNotNull('email_verified_at')->count() }}</b><span>Verified emails</span><small>Addresses confirmed</small></div>
        <div class="admin-tile"><b>{{ $users->filter(fn ($user) => $user->created_at?->isCurrentMonth())->count() }}</b><span>Joined this month</span><small>New accounts</small></div>
        <div class="admin-tile"><b class="text">{{ auth()->user()->name }}</b><span>Your account</span><small>{{ auth()->user()->email }}</small></div>
    </div>
</header>

<section class="admin-section">
    <div><p class="admin-eyebrow">Workspace</p><h2>Workspace accounts</h2></div>
    <span class="admin-chip">{{ $users->count() }} {{ \Illuminate\Support\Str::plural('account', $users->count()) }}</span>
</section>

<section class="admin-page-panel">
    @if($users->isEmpty())
        <p class="admin-page-empty">No user accounts have been registered yet.</p>
    @else
        <div style="overflow-x:auto">
            <table class="admin-page-table">
                <thead><tr><th>Name</th><th>Email</th><th>Joined</th><th>Status</th></tr></thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong>@if($user->is(auth()->user()))<small>This is you</small>@endif</td>
                        <td><a class="admin-page-link" href="mailto:{{ $user->email }}">{{ $user->email }}</a></td>
                        <td>{{ $user->created_at?->format('M j, Y') ?? '—' }}</td>
                        <td>
                            @if($user->email_verified_at)
                                <span class="admin-pill ok">Verified</span>
                            @else
                                <span class="admin-pill warn">Unverified</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection