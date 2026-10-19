@extends('layouts.app')

@section('content')
@include('admin._styles')
<header class="admin-hero">
    <div>
        <p class="admin-kicker"><span class="dot"></span>Admin console / Reports</p>
        <h1>Reports &amp; Analytics</h1>
        <p class="admin-sub">A live summary of meetings, attendance, and room usage across the AykonMeet workspace.</p>
        <div class="admin-actions">
            <a class="admin-btn admin-btn-gold" href="{{ route('meetings.index') }}">Open meetings <i>&rarr;</i></a>
            <a class="admin-btn admin-btn-ghost" href="{{ route('meetings.check-ins') }}">Check-ins &amp; arrivals</a>
        </div>
    </div>
    <div class="admin-tiles" aria-label="Meeting report overview">
        <div class="admin-tile gold"><b>{{ $meetingCount }}</b><span>Total meetings</span><small>All recorded meetings</small></div>
        <div class="admin-tile"><b>{{ $attendeeCount }}</b><span>Attendees</span><small>Across all meetings</small></div>
        <div class="admin-tile"><b>{{ $checkedInCount }}</b><span>Checked in</span><small>Arrivals recorded</small></div>
        <div class="admin-tile"><b>{{ $attendanceRate }}%</b><span>Check-in rate</span><small>Of registered attendees</small></div>
    </div>
</header>

<section class="admin-page-panel">
    <header class="admin-panel-head">
        <div><h2>Meetings by status</h2><p>How the schedule is split across each meeting state.</p></div>
        <span class="admin-chip">{{ $meetingCount }} total</span>
    </header>
    @if($statusCounts->isEmpty())
        <p class="admin-page-empty">Meeting status data will appear here after meetings are scheduled.</p>
    @else
        <div class="admin-page-bars">@foreach($statusCounts as $status => $total)
            <div class="admin-page-bar"><span>{{ str_replace('_', ' ', ucfirst($status)) }}</span><div class="admin-page-bar-track"><div class="admin-page-bar-fill" style="width:{{ $meetingCount ? round($total / $meetingCount * 100) : 0 }}%"></div></div><strong>{{ $total }}</strong></div>
        @endforeach</div>
    @endif
</section>

<section class="admin-page-panel">
    <header class="admin-panel-head">
        <div><h2>Meetings by month</h2><p>Scheduled volume across the last six months.</p></div>
        <span class="admin-chip">Last 6 months</span>
    </header>
    <div class="admin-page-bars">@foreach($monthlyMeetings as $month)
        <div class="admin-page-bar"><span>{{ $month['label'] }}</span><div class="admin-page-bar-track"><div class="admin-page-bar-fill" style="width:{{ $meetingCount ? round($month['total'] / $meetingCount * 100) : 0 }}%"></div></div><strong>{{ $month['total'] }}</strong></div>
    @endforeach</div>
</section>

<section class="admin-page-panel">
    <header class="admin-panel-head">
        <div><h2>Room usage</h2><p>How often each space has been booked.</p></div>
        <span class="admin-chip">{{ $rooms->count() }} {{ \Illuminate\Support\Str::plural('room', $rooms->count()) }}</span>
    </header>
    @if($rooms->isEmpty())
        <p class="admin-page-empty">Add rooms to see how often each space is used.</p>
    @else
        <div style="overflow-x:auto">
            <table class="admin-page-table"><thead><tr><th>Room</th><th>Number</th><th>Meetings booked</th></tr></thead><tbody>
                @foreach($rooms as $room)<tr><td><strong>{{ $room->name }}</strong></td><td>{{ $room->number }}</td><td>{{ $room->meetings_count }}</td></tr>@endforeach
            </tbody></table>
        </div>
    @endif
</section>
@endsection