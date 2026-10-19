@extends('layouts.app')

@section('content')
@include('admin._styles')
@php
    $liveCount = $liveMeetings->count();
    $upcomingCount = $notifications->count() - $liveCount;
@endphp
<header class="admin-hero">
    <div>
        <p class="admin-kicker"><span class="dot"></span>Admin console / Notifications</p>
        <h1>Notifications <em>& alerts.</em></h1>
        <p class="admin-sub">Invitation responses, live meetings and the next seven days of your schedule, in one place.</p>
        <div class="admin-actions">
            <a class="admin-btn admin-btn-gold" href="{{ route('meetings.index') }}">View schedule <i>&rarr;</i></a>
            <a class="admin-btn admin-btn-ghost" href="{{ route('meetings.live') }}">Live meetings</a>
        </div>
    </div>
    <div class="admin-tiles" aria-label="Alert overview">
        <div class="admin-tile gold"><b>{{ $inbox->count() }}</b><span>Invitations</span><small>Latest responses</small></div>
        <div class="admin-tile"><b>{{ $liveCount }}</b><span>Live now</span><small>Meetings in progress</small></div>
        <div class="admin-tile"><b>{{ $upcomingCount }}</b><span>Upcoming</span><small>Starting in the next 7 days</small></div>
    </div>
</header>

@if($inbox->isNotEmpty())
<section class="admin-section">
    <div><p class="admin-eyebrow">Inbox</p><h2>Invitation activity</h2></div>
    <span class="admin-chip">{{ $inbox->count() }} {{ \Illuminate\Support\Str::plural('update', $inbox->count()) }}</span>
</section>

<section class="admin-page-panel">
    <div class="admin-page-list">
        @foreach($inbox as $item)
            <article class="admin-page-item">
                <span class="admin-item-icon" aria-hidden="true">&#9993;</span>
                <div class="admin-item-body">
                    <strong>{{ $item['title'] }}</strong>
                    <small>{{ $item['message'] }}</small>
                    <small>{{ $item['date']->format('D, M j · g:i A') }}</small>
                </div>
                <span class="admin-pill {{ ($item['response'] ?? '') === 'declined' ? 'warn' : 'live' }}">
                    {{ ($item['kind'] ?? '') === 'invitation_response' ? ucfirst($item['response'] ?? 'update') : 'New' }}
                </span>
                @if($item['url'])
                    <a class="admin-page-list-link" href="{{ $item['url'] }}">Open &rarr;</a>
                @endif
            </article>
        @endforeach
    </div>
</section>
@endif

<section class="admin-section">
    <div><p class="admin-eyebrow">Schedule</p><h2>Schedule alerts</h2></div>
    <span class="admin-chip">{{ $liveMeetings->count() + $notifications->count() }} {{ \Illuminate\Support\Str::plural('alert', $liveMeetings->count() + $notifications->count()) }}</span>
</section>

<section class="admin-page-panel">
    @if($liveMeetings->isEmpty() && $notifications->isEmpty())
        <p class="admin-page-empty">You're all caught up. Live and upcoming meeting alerts will appear here.</p>
    @else
        <div class="admin-page-list">
            @foreach($liveMeetings as $meeting)
                <article class="admin-page-item">
                    <span class="admin-item-icon" aria-hidden="true">&#9679;</span>
                    <div class="admin-item-body">
                        <strong>{{ $meeting->meeting_topic }} · Meeting in progress</strong>
                        <small>{{ $meeting->started_at->format('D, M j · g:i A') }}</small>
                    </div>
                    <span class="admin-pill live">Live</span>
                    <a class="admin-page-list-link" href="{{ route('meetings.details', $meeting) }}">Open meeting &rarr;</a>
                </article>
            @endforeach
            @foreach($notifications as $notification)
                <article class="admin-page-item">
                    <span class="admin-item-icon" aria-hidden="true">&#9655;</span>
                    <div class="admin-item-body">
                        <strong>{{ $notification['title'] }} · {{ $notification['message'] }}</strong>
                        <small>{{ $notification['date']->format('D, M j · g:i A') }}</small>
                    </div>
                    <span class="admin-pill warn">Upcoming</span>
                    <a class="admin-page-list-link" href="{{ $notification['url'] }}">View details &rarr;</a>
                </article>
            @endforeach
        </div>
    @endif
</section>
@endsection