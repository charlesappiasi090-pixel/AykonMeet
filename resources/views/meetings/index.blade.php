@extends('layouts.app')

@section('content')
<style>
    .row-action { display: inline-flex; align-items: center; justify-content: center; background: #f7c948; border: 1px solid #f7c948; color: #112b49; padding: 9px 11px; font: 700 11px 'DM Sans', sans-serif; text-decoration: none; white-space: nowrap; }
    .row-action:hover { color: #112b49; opacity: .85; }
    .meeting-row { display: grid; gap: 6px; padding: 14px 20px; min-height: 0; }
    .meeting-head { display: flex; align-items: baseline; gap: 12px; }
    .date-block { display: inline-flex; align-items: baseline; gap: 6px; width: auto; text-align: left; }
    .meeting-main { display: inline-flex; align-items: baseline; gap: 8px; min-width: 0; flex-wrap: wrap; }
    .meeting-main strong, .meeting-main span { display: inline; margin-top: 0; }
    .meeting-main strong { font-weight: 700; }
    .meeting-main span { color: var(--muted); font-size: 12px; font-weight: 600; }
    .meeting-time { font-size: 12px; color: var(--muted); padding-left: 50px; font-weight: 600; }
    .meeting-time small { display: inline; margin-top: 0; color: var(--muted); font-weight: 600; }
    .meeting-time small::before { content: " · "; }
    .meeting-foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
    .meeting-meta { display: inline-flex; align-items: center; gap: 10px; }
    .row-actions { display: flex; align-items: center; gap: 8px; }
    .row-actions { display: flex; align-items: center; gap: 8px; justify-content: flex-end; margin-left: auto; padding-left: 14px; border-left: 1px solid #edf1f4; }
    @media (max-width: 800px) {
        .meeting-head { flex-wrap: wrap; }
        .meeting-main span, .meeting-time small { white-space: normal; }
        .meeting-time { padding-left: 0; }
        .row-actions { justify-content: flex-start; }
    }
    .start-row-form { margin: 0; }
    .start-row-button { border: 0; background: #112b49; color: #fff; padding: 9px 11px; font: 700 11px 'DM Sans', sans-serif; cursor: pointer; white-space: nowrap; }
    .start-row-button:disabled { opacity: .4; cursor: not-allowed; }
    .state-button { display: inline-flex; align-items: center; opacity: .45; cursor: default; }
    .status-badge.cancelled { background: #fdecea; color: #a5382c; }
</style>
<section class="dashboard-header">
    <div>
        <p class="eyebrow">ADMIN CONSOLE / {{ now()->format('l, d M Y') }}</p>
        <h1>Good morning,<br><em>let's get moving.</em></h1>
        <p class="hero-copy">A clear view of today's rooms, arrivals, and meetings.</p>
    </div>
    <div class="dashboard-actions"><button class="button primary" type="button" onclick="document.getElementById('new-meeting-dialog').showModal()">+ New meeting</button><button class="button secondary" type="button" onclick="document.getElementById('add-room-dialog').showModal()">+ Add room</button><button class="button secondary" type="button" onclick="document.getElementById('schedule-dialog').showModal()">View schedule <span>↓</span></button></div>
</section>

<section class="stat-grid" aria-label="Meeting overview">
    <div class="stat-card"><span class="stat-label">Total meetings</span><strong>{{ $stats['total_meetings'] }}</strong><small>All scheduled meetings</small></div>
    <div class="stat-card accent"><span class="stat-label">Today</span><strong>{{ $stats['today_meetings'] }}</strong><small>{{ now()->format('D, d M') }}</small></div>
    <div class="stat-card"><span class="stat-label">Live now</span><strong>{{ $stats['live_meetings'] }}</strong><small>{{ $stats['live_meetings'] === 1 ? 'Meeting in progress' : 'Meetings in progress' }}</small></div>
    <div class="stat-card"><span class="stat-label">Checked in</span><strong>{{ $stats['checked_in'] }}</strong><small>Arrivals recorded</small></div>
</section>

<section class="section-heading" id="workspace"><div><p class="eyebrow">WORKSPACE</p><h2>Rooms and meeting tools</h2></div><span class="section-count">{{ $rooms->count() }} rooms available</span></section>
<div class="dashboard-grid">
    <dialog class="meeting-dialog" id="new-meeting-dialog" @if($errors->any()) open @endif>
        <form class="panel form-panel" action="{{ route('meetings.store') }}" method="post">
        @csrf
        <div class="dialog-heading"><div class="panel-title"><span class="icon-box">+</span><div><h3>New meeting</h3><p>Give your team the details they need.</p></div></div><button class="dialog-close" type="button" onclick="document.getElementById('new-meeting-dialog').close()" aria-label="Close new meeting form">×</button></div>
        <div class="form-grid"><label>Host name<input name="name" required></label><label>Company name<input name="company_name"></label><label>Email<input type="email" name="email" required></label><label>Phone number<input name="phone" required></label><label class="wide">Meeting topic<input name="meeting_topic" required></label><label>Start date & time<input type="datetime-local" name="start_date" required></label><label>End date & time<input type="datetime-local" name="end_date" required></label><label class="wide">Meeting room<select name="meeting_room_id"><option value="">Select a room</option>@foreach($rooms as $room)<option value="{{ $room->id }}">{{ $room->name }} · {{ $room->number }} · Floor {{ $room->floor }}</option>@endforeach</select></label></div>
        <div class="dialog-actions"><button class="button secondary" type="button" onclick="document.getElementById('new-meeting-dialog').close()">Cancel</button><button class="button primary" type="submit">Create meeting <span>→</span></button></div>
        </form>
    </dialog>
    <dialog class="meeting-dialog room-dialog" id="add-room-dialog">
        <form class="panel compact-panel" action="{{ route('meeting-rooms.store') }}" method="post">
            @csrf
            <div class="dialog-heading"><div class="panel-title"><span class="icon-box yellow">⌂</span><div><h3>Add a room</h3><p>Keep your spaces easy to find.</p></div></div><button class="dialog-close" type="button" onclick="document.getElementById('add-room-dialog').close()" aria-label="Close add room form">×</button></div>
            <div class="form-grid room-grid"><label>Room name<input name="name" placeholder="The Studio" required></label><label>Number<input name="number" placeholder="A-101" required></label><label>Floor<input name="floor" placeholder="1" required></label></div>
            <div class="dialog-actions"><button class="button secondary" type="button" onclick="document.getElementById('add-room-dialog').close()">Cancel</button><button class="button primary" type="submit">Add room</button></div>
        </form>
    </dialog>
    <div class="side-stack">
        <div class="panel room-list"><div class="list-heading"><h3>Your rooms</h3><span>{{ $rooms->count() }}</span></div>@forelse($rooms as $room)<div class="room-row"><div><strong>{{ $room->name }}</strong><small>Room {{ $room->number }} · Floor {{ $room->floor }}</small></div><span class="room-dot"></span></div>@empty<p class="muted">No rooms yet. Add your first one above.</p>@endforelse</div>
    </div>
</div>

<dialog class="meeting-dialog schedule-dialog" id="schedule-dialog">
    <div class="schedule-dialog-heading"><section class="section-heading" id="schedule"><div><p class="eyebrow">LIVE SCHEDULE</p><h2>All meetings</h2></div><span class="section-count">{{ $meetings->count() }} records</span></section><button class="dialog-close" type="button" onclick="document.getElementById('schedule-dialog').close()" aria-label="Close schedule">×</button></div>
    <div class="meeting-table panel">@forelse($meetings as $meeting)<div class="meeting-row"><div class="meeting-head"><div class="date-block"><strong>{{ $meeting->start_date->format('d') }}</strong><span>{{ $meeting->start_date->format('M') }}</span></div><div class="meeting-main"><strong>{{ $meeting->meeting_topic }}</strong><span>{{ $meeting->name }}@if($meeting->company_name) · {{ $meeting->company_name }}@endif</span></div></div><div class="meeting-time">{{ $meeting->start_date->format('H:i') }} – {{ $meeting->end_date->format('H:i') }}<small>{{ $meeting->room?->name ?? 'Room to be assigned' }} · {{ $meeting->attendees_count }} attendees</small></div><div class="meeting-foot"><span class="meeting-meta"><span class="status-badge {{ $meeting->status ?? 'scheduled' }}">{{ str_replace('_', ' ', $meeting->status ?? 'scheduled') }}</span><div class="code-pill">{{ $meeting->meeting_code }}</div></span><div class="row-actions"><a class="row-action" href="{{ route('meetings.attendance', $meeting) }}">Attendance</a><a class="row-action" href="{{ route('meetings.edit', $meeting) }}">Edit</a><a class="row-action" href="{{ route('meetings.reschedule.edit', $meeting) }}">Reschedule</a>@if($meeting->status === 'cancelled')<form method="post" action="{{ route('meetings.reopen', $meeting) }}" class="start-row-form">@csrf<button class="start-row-button" type="submit">Reopen</button></form>@endif</div></div></div></div>@empty<div class="empty-state"><strong>Your schedule is clear.</strong><span>Create a meeting above to generate its access code.</span></div>@endforelse</div>
</dialog>
@endsection
