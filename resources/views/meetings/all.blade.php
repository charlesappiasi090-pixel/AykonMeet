@extends('layouts.app')

@section('content')
<style>
    .all-meetings-page{max-width:1440px;margin:auto}
    .all-meetings-head{display:flex;justify-content:space-between;align-items:flex-end;gap:24px;margin-bottom:24px}
    .all-meetings-head h1{margin:10px 0 6px;color:var(--navy);font:700 40px/1.1 "Space Grotesk",sans-serif;letter-spacing:-.7px}
    .all-meetings-head p{margin:0;color:#596a7c;font-size:14px}
    .all-meetings-head .eyebrow{margin:18px 0 0;color:#9a7600;font-size:11px;font-weight:700;letter-spacing:1.4px}
    .all-meetings-head-actions{display:flex;align-items:center;gap:10px}
    .all-meetings-count{padding:11px 15px;background:#fff;border:1px solid var(--line);border-radius:7px;color:var(--navy);font-size:13px;font-weight:700;white-space:nowrap}
    .all-meetings-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:22px}
    .all-meetings-summary article{display:flex;align-items:center;gap:13px;padding:15px 17px;background:#fff;border:1px solid var(--line);border-radius:8px}
    .all-meetings-summary-icon{display:grid;place-items:center;width:40px;height:40px;flex:0 0 40px;border-radius:10px;background:#f2f5f8;color:var(--navy);font-size:18px}
    .all-meetings-summary-icon.live{background:#e8f6ed;color:#267344}
    .all-meetings-summary-icon.completed{background:#eef2f7;color:#46576a}
    .all-meetings-summary strong{display:block;color:var(--navy);font:700 22px/1.1 "Space Grotesk",sans-serif}
    .all-meetings-summary span:last-child{display:block;margin-top:4px;color:#596a7c;font-size:12px}
    .all-meetings-toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:14px 16px;background:#fff;border:1px solid var(--line);border-bottom:0;border-radius:9px 9px 0 0}
    .all-meetings-search{position:relative;flex:1;max-width:520px}
    .all-meetings-search input{width:100%;height:42px;padding:0 14px 0 40px;border:1px solid #d5dee6;border-radius:6px;background:#fbfcfd;color:var(--ink);font:13px "DM Sans",sans-serif}
    .all-meetings-search input:focus,.all-meetings-toolbar select:focus{outline:3px solid rgba(247,201,72,.28);border-color:#c69a13}
    .all-meetings-search span{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#718096;font-size:16px}
    .all-meetings-toolbar select{height:42px;min-width:160px;padding:0 32px 0 12px;border:1px solid #d5dee6;border-radius:6px;background:#fff;color:var(--ink);font:13px "DM Sans",sans-serif}
    .all-meetings-results{color:#596a7c;font-size:12px;white-space:nowrap}
    .all-meetings-list{overflow:hidden;background:#fff;border:1px solid var(--line);border-radius:0 0 9px 9px;box-shadow:0 4px 18px rgba(17,43,73,.04)}
    .all-meeting-row{display:grid;grid-template-columns:minmax(210px,1.35fr) minmax(185px,1fr) minmax(170px,.8fr) auto;gap:20px;align-items:center;padding:17px 20px;border-bottom:1px solid #edf1f4;transition:background .15s ease}
    .all-meeting-row:hover{background:#fbfcfd}
    .all-meeting-row:last-child{border-bottom:0}
    .all-meeting-primary{display:flex;align-items:center;gap:13px;min-width:0}
    .all-meeting-date-tile{display:grid;place-content:center;min-width:48px;height:52px;padding:4px;background:#f5f7f9;border:1px solid #e5ebf0;border-radius:7px;text-align:center}
    .all-meeting-date-tile strong{color:var(--navy);font:700 18px/1 "Space Grotesk",sans-serif}
    .all-meeting-date-tile span{margin-top:4px;color:#596a7c;font-size:10px;font-weight:700;text-transform:uppercase}
    .all-meeting-primary-text{min-width:0}
    .all-meeting-topic{display:block;overflow:hidden;color:var(--navy);font-size:14px;font-weight:700;text-decoration:none;text-overflow:ellipsis;white-space:nowrap}
    .all-meeting-topic:hover{text-decoration:underline}
    .all-meeting-host{overflow:hidden;margin-top:5px;color:#596a7c;font-size:12px;text-overflow:ellipsis;white-space:nowrap}
    .all-meeting-meta{display:grid;gap:6px;min-width:0;color:#435367;font-size:12px}
    .all-meeting-meta span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .all-meeting-meta-label{margin-right:5px;color:#718096}
    .all-meeting-state{display:flex;align-items:center;gap:8px}
    .all-meeting-status{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border-radius:20px;background:#eef2f5;color:#46576a;font-size:10px;font-weight:700;text-transform:capitalize;white-space:nowrap}
    .all-meeting-status::before{width:7px;height:7px;border-radius:50%;background:currentColor;content:""}
    .all-meeting-status.in_progress{background:#e8f6ed;color:#267344}
    .all-meeting-status.completed{background:#eef2f7;color:#46576a}
    .all-meeting-status.cancelled{background:#fdecea;color:#a5382c}
    .all-meeting-type{color:#596a7c;font-size:11px;white-space:nowrap}
    .all-meeting-actions{display:flex;justify-content:flex-end;align-items:center;gap:6px}
    .all-meeting-actions .row-action{display:inline-flex;align-items:center;justify-content:center;min-height:36px;padding:8px 11px;border:1px solid transparent;border-radius:6px;font:700 11px "DM Sans",sans-serif;text-decoration:none;white-space:nowrap;transition:transform .15s ease,box-shadow .15s ease,background-color .15s ease}
    .all-meeting-actions .row-action:hover{transform:translateY(-1px);box-shadow:0 3px 9px rgba(17,43,73,.12)}
    .all-meeting-actions .row-action:focus-visible{outline:3px solid rgba(17,43,73,.22);outline-offset:2px}
    .all-meeting-actions .action-attendance{background:#e8f1fb;border-color:#cfdef0;color:#214f7e}
    .all-meeting-actions .action-edit{background:#fff4cf;border-color:#f0dfaa;color:#795c00}
    .all-meeting-actions .action-reschedule{background:#e6f5f1;border-color:#c8e7df;color:#216c5a}
    .all-meeting-actions .action-reopen{background:#edf2f7;border-color:#d8e1ea;color:#344b63;cursor:pointer}
    .all-meeting-actions form{margin:0}
    .all-meetings-empty{padding:52px 20px;text-align:center;color:#596a7c}
    .all-meetings-empty strong{display:block;color:var(--navy);font-size:16px}
    .all-meetings-empty p{margin:7px 0 0;font-size:13px}
    .all-meeting-hidden{display:none!important}
    @media(max-width:1120px){.all-meeting-row{grid-template-columns:minmax(200px,1.2fr) minmax(170px,1fr) auto;gap:14px}.all-meeting-state{grid-column:2}.all-meeting-actions{grid-column:3;grid-row:1 / span 2}}
    @media(max-width:800px){.all-meetings-summary{grid-template-columns:repeat(2,minmax(0,1fr))}.all-meeting-row{grid-template-columns:1fr 1fr;gap:13px}.all-meeting-primary{grid-column:1/-1}.all-meeting-state{grid-column:auto}.all-meeting-actions{grid-column:1/-1;grid-row:auto;justify-content:flex-start}.all-meetings-head h1{font-size:34px}}
    @media(max-width:520px){.all-meetings-head{align-items:flex-start;flex-direction:column}.all-meetings-head-actions{width:100%;justify-content:space-between}.all-meetings-summary article{padding:12px;gap:9px}.all-meetings-summary-icon{width:34px;height:34px;flex-basis:34px}.all-meetings-toolbar{align-items:stretch;flex-direction:column}.all-meetings-search{max-width:none}.all-meetings-toolbar select{width:100%}.all-meeting-row{grid-template-columns:1fr}.all-meeting-primary,.all-meeting-state,.all-meeting-actions{grid-column:1}.all-meeting-actions{flex-wrap:wrap}.all-meeting-type{display:none}}
</style>

<div class="all-meetings-page">
    <header class="all-meetings-head">
        <div>
            <a class="back-link" href="{{ route('meetings.index') }}">← Back to meetings dashboard</a>
            <p class="eyebrow">MEETINGS / {{ $selectedDate ? 'DAILY SCHEDULE' : 'FULL SCHEDULE' }}</p>
            <h1>{{ $isToday ? "Today's meetings" : ($selectedDate ? 'Meetings for '.\Illuminate\Support\Carbon::parse($selectedDate)->format('d M Y') : 'All meetings') }}</h1>
            <p>{{ $selectedDate ? 'Meetings scheduled for '.\Illuminate\Support\Carbon::parse($selectedDate)->format('l, d M Y').'.' : 'Find, review, and manage every meeting in your workspace.' }}</p>
        </div>
        <div class="all-meetings-head-actions">
            <span class="all-meetings-count">{{ $meetings->count() }} {{ \Illuminate\Support\Str::plural('meeting', $meetings->count()) }}</span>
            <a class="button primary" href="{{ route('meetings.index') }}#new-meeting" style="display:inline-flex;align-items:center;text-decoration:none">＋ New meeting</a>
        </div>
    </header>

    <section class="all-meetings-summary" aria-label="Meeting totals">
        <article><span class="all-meetings-summary-icon">▦</span><div><strong>{{ $meetings->count() }}</strong><span>Total meetings</span></div></article>
        <article><span class="all-meetings-summary-icon live">●</span><div><strong>{{ $meetings->where('status', 'in_progress')->count() }}</strong><span>In progress</span></div></article>
        <article><span class="all-meetings-summary-icon">◷</span><div><strong>{{ $meetings->where('status', 'scheduled')->count() }}</strong><span>Scheduled</span></div></article>
        <article><span class="all-meetings-summary-icon completed">✓</span><div><strong>{{ $meetings->where('status', 'completed')->count() }}</strong><span>Completed</span></div></article>
    </section>

    <section class="all-meetings-toolbar" aria-label="Filter meetings">
        <label class="all-meetings-search"><span aria-hidden="true">⌕</span><input id="meetingSearch" type="search" placeholder="Search by topic, host, room, or code" autocomplete="off" aria-label="Search meetings"></label>
        <select id="meetingStatusFilter" aria-label="Filter by meeting status">
            <option value="">All statuses</option>
            <option value="scheduled">Scheduled</option>
            <option value="in_progress">In progress</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>
        <span class="all-meetings-results" id="meetingResults" aria-live="polite">{{ $meetings->count() }} meetings</span>
    </section>

    <section class="all-meetings-list" aria-label="All meetings">
        @forelse($meetings as $meeting)
            @php
                $searchTerms = implode(' ', [
                    $meeting->meeting_topic,
                    $meeting->name,
                    $meeting->company_name,
                    $meeting->meeting_code,
                    $meeting->room?->name,
                    $meeting->room?->number,
                ]);
            @endphp
            <article class="all-meeting-row" data-status="{{ $meeting->status }}" data-search="{{ strtolower($searchTerms) }}">
                <div class="all-meeting-primary">
                    <div class="all-meeting-date-tile"><strong>{{ $meeting->start_date->format('d') }}</strong><span>{{ $meeting->start_date->format('M') }}</span></div>
                    <div class="all-meeting-primary-text">
                        <a class="all-meeting-topic" href="{{ route('meetings.show', $meeting->meeting_code) }}">{{ $meeting->meeting_topic }}</a>
                        <div class="all-meeting-host">Hosted by {{ $meeting->name }}@if($meeting->company_name) · {{ $meeting->company_name }}@endif</div>
                    </div>
                </div>
                <div class="all-meeting-meta">
                    <span><span class="all-meeting-meta-label">Time</span>{{ $meeting->start_date->format('g:i A') }} – {{ $meeting->end_date->format('g:i A') }}</span>
                    <span><span class="all-meeting-meta-label">Room</span>{{ $meeting->room?->name ?? ($meeting->is_online ? 'Online' : 'Not assigned') }} · {{ $meeting->attendees_count }} {{ \Illuminate\Support\Str::plural('attendee', $meeting->attendees_count) }}</span>
                    <span><span class="all-meeting-meta-label">Code</span>{{ $meeting->meeting_code }}</span>
                </div>
                <div class="all-meeting-state">
                    <span class="all-meeting-status {{ $meeting->status }}">{{ str_replace('_', ' ', $meeting->status) }}</span>
                    <span class="all-meeting-type">{{ $meeting->is_online ? ($meeting->meeting_room_id ? 'Hybrid' : 'Online') : 'In-person' }}</span>
                </div>
                <div class="all-meeting-actions">
                    <a class="row-action action-attendance" href="{{ route('meetings.attendance', $meeting) }}">Attendance</a>
                    <a class="row-action action-edit" href="{{ route('meetings.edit', $meeting) }}">Edit</a>
                    <a class="row-action action-reschedule" href="{{ route('meetings.reschedule.edit', $meeting) }}">Reschedule</a>
                    @if($meeting->status === 'cancelled')
                        <form method="post" action="{{ route('meetings.reopen', $meeting) }}">
                            @csrf
                            <button class="row-action action-reopen" type="submit">Reopen</button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="all-meetings-empty"><strong>No meetings yet</strong><p>Create a meeting to see it in your full schedule.</p></div>
        @endforelse
        @if($meetings->isNotEmpty())
            <div class="all-meetings-empty" id="noMeetingResults" hidden><strong>No meetings match your search</strong><p>Try another keyword or choose a different status.</p></div>
        @endif
    </section>
</div>

<script>
    (function () {
        var search = document.getElementById('meetingSearch');
        var status = document.getElementById('meetingStatusFilter');
        var rows = Array.from(document.querySelectorAll('.all-meeting-row'));
        var resultCount = document.getElementById('meetingResults');
        var emptyState = document.getElementById('noMeetingResults');

        function filterMeetings() {
            var query = search.value.trim().toLowerCase();
            var selectedStatus = status.value;
            var visibleCount = 0;

            rows.forEach(function (row) {
                var matches = row.dataset.search.includes(query)
                    && (!selectedStatus || row.dataset.status === selectedStatus);
                row.classList.toggle('all-meeting-hidden', !matches);
                if (matches) visibleCount++;
            });

            resultCount.textContent = visibleCount + (visibleCount === 1 ? ' meeting' : ' meetings');
            if (emptyState) emptyState.hidden = visibleCount !== 0;
        }

        search.addEventListener('input', filterMeetings);
        status.addEventListener('change', filterMeetings);
    })();
</script>
@endsection
