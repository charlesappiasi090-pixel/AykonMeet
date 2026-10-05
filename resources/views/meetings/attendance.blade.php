@extends('layouts.app')

@section('content')
<style>
    .attendance-head { display:flex; justify-content:space-between; align-items:flex-end; gap:24px; margin-bottom:34px; }
    .attendance-head h1 { margin:10px 0 12px; color:var(--navy); font:700 clamp(38px,5vw,62px)/1 'Space Grotesk',sans-serif; letter-spacing:-1px; }
    .attendance-meta { color:var(--muted); font-size:14px; line-height:1.6; }
    .attendance-meta strong { color:var(--navy); }
    .attendance-grid { display:grid; grid-template-columns:minmax(0, .85fr) minmax(0, 1.15fr); gap:20px; align-items:start; }
    .attendance-tools { display:grid; gap:20px; }
    .attendance-card h2 { color:var(--navy); font:700 24px 'Space Grotesk',sans-serif; margin:0 0 8px; }
    .attendance-card > p { color:var(--muted); font-size:13px; line-height:1.5; margin:0 0 22px; }
    .attendance-card label { display:block; color:var(--navy); font-size:12px; font-weight:700; margin-bottom:8px; }
    .attendance-card input { width:100%; border:1px solid var(--line); background:var(--white); padding:13px 14px; font:inherit; color:var(--ink); outline:none; }
    .attendance-card input:focus { border-color:var(--navy); box-shadow:0 0 0 3px rgba(17,43,73,.08); }
    .attendance-card .button { margin-top:18px; }
    .attendance-form-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .attendance-form-grid .wide { grid-column:1/-1; }
    .upload-box { border:1px dashed #b8c5cf; background:#fbfcfd; padding:20px; }
    .upload-box input { background:var(--white); padding:10px; }
    .upload-actions { display:flex; align-items:center; gap:16px; flex-wrap:wrap; margin-top:16px; }
    .upload-actions .button { margin-top:0; }
    .attendee-list-card { padding:0; overflow:hidden; }
    .attendee-list-head { display:flex; justify-content:space-between; align-items:center; padding:22px 24px; border-bottom:1px solid var(--line); }
    .attendee-list-head h2 { margin:0; color:var(--navy); font:700 24px 'Space Grotesk',sans-serif; }
    .attendee-list-head span { color:var(--muted); font-size:12px; }
    .attendance-row { display:grid; grid-template-columns:36px 1fr auto; gap:12px; align-items:center; padding:16px 24px; border-bottom:1px solid #edf1f4; }
    .attendance-row:last-child { border-bottom:0; }
    .attendance-avatar { display:grid; place-items:center; width:34px; height:34px; background:var(--navy); color:var(--yellow); font-weight:700; }
    .attendance-row strong { color:var(--navy); font-size:14px; }
    .attendance-row small { display:block; color:var(--muted); font-size:11px; margin-top:4px; }
    .attendance-state { color:#267344; background:#e3f4e8; padding:5px 8px; font-size:10px; font-weight:700; white-space:nowrap; }
    .attendance-state.pending { color:var(--muted); background:#eef2f5; }
    .attendance-empty { padding:38px 24px; color:var(--muted); text-align:center; font-size:13px; }
    .attendance-actions { display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
    .attendance-actions form { margin:0; }
    .mini-action { border:1px solid var(--line); background:var(--white); color:var(--muted); font:600 10px 'DM Sans',sans-serif; padding:5px 8px; cursor:pointer; text-decoration:none; white-space:nowrap; }
    .mini-action:hover { color:var(--navy); border-color:var(--navy); }
    .mini-action.danger:hover { color:#c0392b; border-color:#c0392b; }
    .head-actions { display:flex; gap:10px; align-items:center; }
    .head-action-btn { display:inline-flex; align-items:center; gap:8px; background:var(--white); border:1px solid var(--line); color:var(--navy); font:600 12px 'DM Sans',sans-serif; padding:10px 14px; text-decoration:none; border-radius:6px; box-shadow:0 1px 2px rgba(17,43,73,.06); transition:border-color .15s, box-shadow .15s, transform .15s; }
    .head-action-btn svg { width:15px; height:15px; flex:0 0 auto; color:#b18800; }
    .head-action-btn:hover { border-color:var(--navy); box-shadow:0 2px 6px rgba(17,43,73,.12); transform:translateY(-1px); }
    .head-action-btn:hover svg { color:var(--navy); }
    @media(max-width:800px) { .attendance-head,.attendance-grid { grid-template-columns:1fr; display:grid; align-items:start; } .attendance-head .button { justify-self:start; } }
    @media(max-width:560px) { .attendance-form-grid { grid-template-columns:1fr; } .attendance-form-grid .wide { grid-column:auto; } .attendance-row { grid-template-columns:30px 1fr; } .attendance-state { grid-column:2; justify-self:start; } }
</style>
<div class="attendance-head">
    <div><a class="back-link" href="{{ route('admin.dashboard') }}">← Back to schedule</a><p class="eyebrow">ATTENDANCE / {{ $meeting->meeting_code }}</p><h1>{{ $meeting->meeting_topic }}</h1><p class="attendance-meta"><strong>{{ $meeting->start_date->format('D, d M Y · H:i') }}</strong> · {{ $meeting->room?->name ?? 'Room to be assigned' }}<br>Hosted by {{ $meeting->name }}@if($meeting->company_name) · {{ $meeting->company_name }}@endif</p></div>
    <div class="head-actions"><a class="head-action-btn" href="{{ route('meetings.badges', $meeting) }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/><circle cx="18" cy="6" r="1"/></svg>Print badges</a><a class="head-action-btn" href="{{ route('kiosk.show', $meeting->meeting_code) }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>Open kiosk</a></div>
</div>
<div class="attendance-grid">
    <div class="attendance-tools">
        <section class="panel attendance-card"><h2>Add attendee manually</h2><p>A code is generated and emailed automatically. They remain expected until you check them in.</p><form method="post" action="{{ route('attendees.store', $meeting) }}"><input type="hidden" name="mark_present" value="0">@csrf<div class="attendance-form-grid"><label>Full name<input name="full_name" value="{{ old('full_name') }}" required></label><label>Email<input type="email" name="email" value="{{ old('email') }}" required></label><label class="wide">Phone number<input name="phone" value="{{ old('phone') }}" required></label></div><button class="button secondary" type="submit">Add and email code</button></form></section>
        <section class="panel attendance-card"><h2>Upload attendee list</h2><p>Import a CSV or spreadsheet with Full Name, Email and Phone columns. Every new attendee receives their code by email.</p><form method="post" action="{{ route('attendees.import', $meeting) }}" enctype="multipart/form-data">@csrf<div class="upload-box"><label for="attendees_file">CSV or spreadsheet file</label><input id="attendees_file" type="file" name="attendees_file" accept=".csv,.xlsx,.xls" required></div><div class="upload-actions"><button class="button primary" type="submit">Upload list</button><a class="text-button" href="{{ route('attendees.template') }}">Download CSV template ↓</a></div></form></section>
    </div>
    <section class="panel attendee-list-card"><div class="attendee-list-head"><h2>Guest list</h2><span>{{ $meeting->attendees->whereNotNull('checked_in_at')->count() }}/{{ $meeting->attendees->count() }} present</span></div>@forelse($meeting->attendees as $attendee)<div class="attendance-row"><div class="attendance-avatar">{{ strtoupper(substr($attendee->full_name, 0, 1)) }}</div><div><strong>{{ $attendee->full_name }}</strong><small>{{ $attendee->attendee_code }} · {{ $attendee->email }}</small></div><div class="attendance-actions">@if($attendee->checked_in_at)<span class="attendance-state">Present</span>@if(!$attendee->checked_out_at)<form method="post" action="{{ route('attendees.check-out', $attendee) }}">@csrf<button class="mini-action" type="submit">Check out</button></form>@else<span class="attendance-state pending">Departed</span><form method="post" action="{{ route('attendees.check-out', $attendee) }}">@csrf<button class="mini-action" type="submit">Re-enter</button></form>@endif<form method="post" action="{{ route('attendees.undo-check-in', $attendee) }}">@csrf<button class="mini-action" type="submit">Undo</button></form>@else<span class="attendance-state pending">Expected</span><form method="post" action="{{ route('attendees.check-in', $meeting) }}">@csrf<input type="hidden" name="attendee_code" value="{{ $attendee->attendee_code }}"><button class="mini-action" type="submit">Check in</button></form>@endif<form method="post" action="{{ route('attendees.destroy', $attendee) }}" onsubmit="return confirm('Remove {{ $attendee->full_name }} from this meeting?');">@csrf<button class="mini-action danger" type="submit">Remove</button></form></div></div>@empty<div class="attendance-empty">No attendees registered yet.</div>@endforelse</section>
</div>
@endsection
