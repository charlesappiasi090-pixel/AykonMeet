@extends('layouts.app')

@section('content')
<style>
    .live-page-head{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin-bottom:26px}
    .live-page-head h1{margin:8px 0;color:var(--navy);font:700 38px/1.1 "Space Grotesk",sans-serif}
    .live-page-head p{margin:0;color:var(--muted);font-size:14px}
    .live-count{display:inline-flex;align-items:center;gap:9px;color:#267344;font-weight:700}
    .live-count::before{content:"";width:10px;height:10px;border-radius:50%;background:#2f9e5b}
    .live-meeting-list{display:grid;gap:12px}
    .live-meeting-card{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:18px;background:var(--white);border:1px solid var(--line);padding:20px}
    .live-meeting-card h2{margin:0;color:var(--navy);font:600 18px "Space Grotesk",sans-serif}
    .live-meeting-card p{margin:6px 0 0;color:#596a7c;font-size:13px}
    .live-meeting-meta{display:flex;flex-wrap:wrap;gap:16px;margin-top:12px;color:#596a7c;font-size:12px}
    .live-meeting-actions{display:flex;align-items:center;gap:10px}
    .live-pill{padding:6px 9px;background:#e3f4e8;color:#267344;font-size:11px;font-weight:700;text-transform:uppercase}
    .live-empty{padding:36px;background:var(--white);border:1px solid var(--line);color:#596a7c;text-align:center}
    @media(max-width:700px){.live-page-head{align-items:flex-start;flex-direction:column}.live-meeting-card{grid-template-columns:1fr}.live-meeting-actions{justify-content:space-between}}
</style>

<header class="live-page-head">
    <div>
        <a class="back-link" href="{{ route('meetings.index') }}">← Back to meetings</a>
        <p class="eyebrow" style="margin:18px 0 0">MEETINGS / LIVE NOW</p>
        <h1>Live meetings</h1>
        <p>Meetings currently in progress.</p>
    </div>
    <span class="live-count">{{ $meetings->count() }} {{ \Illuminate\Support\Str::plural('meeting', $meetings->count()) }} live</span>
</header>

@if($meetings->isEmpty())
    <section class="live-empty">
        <strong>No meetings are in progress.</strong>
        <p>When a meeting starts, it will appear here.</p>
    </section>
@else
    <section class="live-meeting-list" aria-label="Live meetings">
        @foreach($meetings as $meeting)
            <article class="live-meeting-card">
                <div>
                    <h2>{{ $meeting->meeting_topic }}</h2>
                    <p>Hosted by {{ $meeting->name }}</p>
                    <div class="live-meeting-meta">
                        <span>{{ $meeting->room?->name ?? ($meeting->is_online ? 'Online session' : 'Room not assigned') }}</span>
                        <span>{{ $meeting->attendees_count }} {{ \Illuminate\Support\Str::plural('attendee', $meeting->attendees_count) }}</span>
                        <span>Started {{ ($meeting->started_at ?? $meeting->start_date)->diffForHumans() }}</span>
                    </div>
                </div>
                <div class="live-meeting-actions">
                    <span class="live-pill">Live</span>
                    <a class="button primary" href="{{ route('meetings.show', $meeting->meeting_code) }}" style="display:inline-flex;align-items:center;text-decoration:none">Open meeting</a>
                    @if($meeting->is_online)
                        <a class="button secondary" href="{{ \Illuminate\Support\Facades\URL::signedRoute('sessions.join', ['meeting' => $meeting]) }}" style="display:inline-flex;align-items:center;text-decoration:none">Join session</a>
                    @endif
                </div>
            </article>
        @endforeach
    </section>
@endif
@endsection
