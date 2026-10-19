@extends('layouts.app')

@section('content')
@include('admin._styles')
<header class="admin-page-head">
    <div><p class="eyebrow">ADMIN CONSOLE / EXPENSES</p><h1>Expenses</h1><p>See the room and meeting records currently available for cost planning.</p></div>
    <a class="button secondary" href="{{ route('admin.dashboard') }}#workspace" style="display:inline-flex;align-items:center;text-decoration:none">Manage rooms</a>
</header>
<div class="admin-page-note"><strong>Financial expense tracking is not configured.</strong> The current meeting records do not store costs or payments, so this page does not invent or estimate expense totals. The figures below summarize room assignments only.</div>
<section class="admin-page-stats" aria-label="Room planning overview">
    <div class="stat-card"><span class="stat-label">Available rooms</span><strong>{{ $roomCount }}</strong><small>Workspace spaces</small></div>
    <div class="stat-card"><span class="stat-label">Meetings with a room</span><strong>{{ $roomedMeetingCount }}</strong><small>Room assignments recorded</small></div>
    <div class="stat-card"><span class="stat-label">Without a room</span><strong>{{ $unassignedMeetingCount }}</strong><small>Meetings not assigned a space</small></div>
    <div class="stat-card"><span class="stat-label">Recent room bookings</span><strong>{{ $roomedMeetings->count() }}</strong><small>Latest 10 assigned meetings</small></div>
</section>
<section class="admin-page-panel">
    <h2>Recent room assignments</h2>
    @if($roomedMeetings->isEmpty())
        <p class="admin-page-empty">There are no room assignments yet. Assign rooms when creating or editing meetings.</p>
    @else
        <table class="admin-page-table"><thead><tr><th>Meeting</th><th>Room</th><th>Scheduled</th><th></th></tr></thead><tbody>
            @foreach($roomedMeetings as $meeting)<tr><td><strong>{{ $meeting->meeting_topic }}</strong><br><small>{{ $meeting->name }}</small></td><td>{{ $meeting->room?->name ?? 'Room unavailable' }}</td><td>{{ $meeting->start_date->format('M j, Y · g:i A') }}</td><td><a class="admin-page-link" href="{{ route('meetings.show', $meeting->meeting_code) }}">View</a></td></tr>@endforeach
        </tbody></table>
    @endif
</section>
@endsection
