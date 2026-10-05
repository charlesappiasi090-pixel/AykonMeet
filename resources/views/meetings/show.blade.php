@extends('layouts.app')

@section('content')
<style>
    .meeting-actions { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
    .meeting-actions form, .meeting-actions .button { width: 100%; }
    .meeting-actions .button { min-height: 48px; margin-top: 0; display: inline-flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; white-space: nowrap; transition: transform .15s ease, opacity .15s ease; }
    .meeting-actions .button:hover { transform: translateY(-1px); opacity: .9; }
    .meeting-actions > a.button { grid-column: auto; }
    @media (max-width: 620px) { .meeting-actions { grid-template-columns: 1fr; } }
    .attendee-row small { display: block; margin-top: 5px; font-weight: 700; letter-spacing: .01em; }
    .status-dot.cancelled { background: #c0392b; }
    .cancelled-banner { margin-top: 22px; background: #fdf3f2; border: 1px solid #e8b4ac; color: #a5382c; padding: 14px 16px; font-size: 13px; font-weight: 700; }
    .notes-panel textarea { width: 100%; min-height: 130px; border: 1px solid var(--line); padding: 12px 14px; font: inherit; color: var(--ink); outline: none; resize: vertical; }
    .notes-panel textarea:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(17,43,73,.08); }
    .notes-panel-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
</style>
<div class="meeting-page-head">
    <div>
        <a class="back-link" href="{{ route('meetings.index') }}">← Back to console</a>
        <p class="eyebrow">MEETING ACCESS / {{ $meeting->meeting_code }}</p>
        <h1>{{ $meeting->meeting_topic }}</h1>
        <p class="hero-copy">Hosted by {{ $meeting->name }}
            @if($meeting->company_name)
            for {{ $meeting->company_name }}
            @endif
        </p>
    </div>
    <div class="big-code"><span>MEETING CODE</span><strong>{{ $meeting->meeting_code }}</strong></div>
</div>
<div class="meeting-detail-grid">
    <section class="panel status-panel">
        <div class="status-line">
            <span class="status-dot {{ $meeting->status }}"></span>
            <span>{{ str_replace('_', ' ', ucfirst($meeting->status)) }}</span>
        </div>
        <div class="timer" id="timer" data-started="{{ $meeting->started_at?->timestamp ?? '' }}" data-ended="{{ $meeting->ended_at?->timestamp ?? '' }}">00:00:00</div>
        <p class="muted">Meeting duration</p>
        @auth
        <div class="meeting-actions">
            @if($meeting->isCancelled())
                <form method="post" action="{{ route('meetings.reopen', $meeting) }}">
                    @csrf
                    <button class="button secondary" type="submit">Reopen meeting <span>↻</span></button>
                </form>
            @elseif($meeting->status === 'scheduled')
                <form method="post" action="{{ route('meetings.start', $meeting) }}">
                    @csrf
                    <button class="button primary" type="submit">Start meeting <span>▶</span></button>
                </form>
            @elseif($meeting->status === 'in_progress')
                <form method="post" action="{{ route('meetings.end', $meeting) }}">
                    @csrf
                    <button class="button secondary" type="submit">End meeting <span>■</span></button>
                </form>
            @endif
            <a class="button secondary" href="{{ route('meetings.edit', $meeting) }}">Edit <span>✎</span></a>
            <a class="button secondary" href="{{ route('meetings.badges', $meeting) }}">Badges <span>▦</span></a>
        </div>
        @if(!$meeting->isCancelled() && $meeting->status !== 'completed')
            <div class="meeting-actions">
                <a class="button secondary" href="{{ route('kiosk.show', $meeting->meeting_code) }}">Kiosk <span>📷</span></a>
                <a class="button secondary" href="{{ route('meetings.pdf', $meeting) }}">Export PDF <span>↓</span></a>
                <form method="post" action="{{ route('meetings.cancel', $meeting) }}" onsubmit="return confirm('Cancel this meeting? Attendees will be emailed a cancellation notice.');">
                    @csrf
                    <button class="button secondary" type="submit" style="background:#c0392b;border-color:#c0392b;color:#fff;">Cancel <span>✕</span></button>
                </form>
            </div>
        @endif
        @endauth
        @if($meeting->isCancelled())
        <div class="cancelled-banner">
            This meeting was cancelled
            @if($meeting->cancelled_at)
            on {{ $meeting->cancelled_at->format('D, d M Y · H:i') }}
            @endif
            . Attendees have been notified.
        </div>
        @endif
        <div class="detail-list">
            <div><span>When</span><strong>{{ $meeting->start_date->format('D, d M Y · H:i') }}</strong></div>
            <div><span>Room</span><strong>{{ $meeting->room?->name ?? 'Not assigned' }}</strong></div>
            <div><span>Attendees</span><strong>{{ $meeting->attendees->whereNotNull('checked_in_at')->count() }}/{{ $meeting->attendees->count() }} present</strong></div>
        </div>
    </section>
    <section class="panel attendance-panel">
        <div class="panel-title">
            <span class="icon-box yellow">✓</span>
            <div>
                <h3>Check in attendees</h3>
                <p>Enter an attendee code to mark them present.</p>
            </div>
        </div>
        <form class="checkin-form" method="post" action="{{ route('attendees.check-in', $meeting) }}">
            @csrf
            <input name="attendee_code" placeholder="AT-XXXXXX" value="{{ old('attendee_code') }}" required @disabled($meeting->isCancelled() || $meeting->status === 'completed')>
            <button class="button secondary" type="submit" @disabled($meeting->isCancelled() || $meeting->status === 'completed')>Mark present</button>
        </form>
        <div class="attendee-list">
            <div class="list-heading">
                <h3>Guest list</h3>
                <span>{{ $meeting->attendees->whereNotNull('checked_in_at')->count() }}/{{ $meeting->attendees->count() }} present</span>
            </div>
            @forelse($meeting->attendees as $attendee)
                <div class="attendee-row">
                    <div class="avatar">{{ strtoupper(substr($attendee->full_name, 0, 1)) }}</div>
                    <div>
                        <strong>{{ $attendee->full_name }}</strong>
                        <small>{{ $attendee->attendee_code }} · {{ $attendee->email }}</small>
                    </div>
                    @if($attendee->checked_in_at)
                        <span class="{{ $attendee->checked_out_at ? 'pending' : 'present' }}">{{ $attendee->checked_out_at ? 'Departed' : 'Present' }}</span>
                    @else
                        <span class="pending">Expected</span>
                    @endif
                </div>
            @empty
                <p class="muted">No attendees added yet.</p>
            @endforelse
        </div>
        <form class="import-form" method="post" action="{{ route('attendees.import', $meeting) }}" enctype="multipart/form-data">
            @csrf
            <label class="file-label">Upload attendee file (.csv · .xlsx · .xls) <input type="file" name="attendees_file" accept=".csv,.xlsx,.xls" required></label>
            <button class="text-button" type="submit">Import list →</button>
            <a class="text-button" href="{{ route('attendees.template') }}">Download template ↓</a>
        </form>
    </section>
</div>
@auth
<section class="panel notes-panel" style="margin-top:20px;">
    <div class="panel-title">
        <span class="icon-box yellow">✎</span>
        <div>
            <h3>Minutes & notes</h3>
            <p>Record key points and decisions during the meeting.</p>
        </div>
    </div>
    <form method="post" action="{{ route('meetings.notes', $meeting) }}">
        @csrf
        <textarea name="notes" placeholder="Action items, decisions, follow-ups...">{{ old('notes', $meeting->notes) }}</textarea>
        <p style="text-align:right;margin:10px 0 0;"><button class="button secondary" type="submit">Save notes <span>→</span></button></p>
    </form>
</section>
@endauth
@if(session('unknown_attendee'))
<div class="modal-backdrop" id="unknown-modal">
    <div class="modal panel">
        <button class="modal-close" onclick="document.getElementById('unknown-modal').remove()">×</button>
        <p class="eyebrow">NEW ARRIVAL</p>
        <h2>We couldn't find that code.</h2>
        <p class="muted">No attendee matches <strong>{{ session('unknown_attendee_code', old('attendee_code')) }}</strong>. Add them and mark present now.</p>
        <form method="post" action="{{ route('attendees.store', $meeting) }}">
            @csrf
            <label>Full name<input name="full_name" value="{{ old('full_name') }}" required @error('full_name') class="invalid" @enderror></label>
            @error('full_name')<small class="field-error">{{ $message }}</small>@enderror
            <label>Email<input name="email" type="email" value="{{ old('email') }}" required @error('email') class="invalid" @enderror></label>
            @error('email')<small class="field-error">{{ $message }}</small>@enderror
            <label>Phone number<input name="phone" value="{{ old('phone') }}" required @error('phone') class="invalid" @enderror></label>
            @error('phone')<small class="field-error">{{ $message }}</small>@enderror
            <button class="button primary" type="submit">Add and mark present</button>
        </form>
    </div>
</div>
@endif
<script>const timer=document.getElementById('timer');const started=Number(timer.dataset.started);const ended=Number(timer.dataset.ended);function tick(){if(!started)return;const seconds=Math.max(0,(ended||Math.floor(Date.now()/1000))-started);const h=String(Math.floor(seconds/3600)).padStart(2,'0');const m=String(Math.floor(seconds%3600/60)).padStart(2,'0');const s=String(seconds%60).padStart(2,'0');timer.textContent=`${h}:${m}:${s}`}tick();setInterval(tick,1000);</script>
@endsection