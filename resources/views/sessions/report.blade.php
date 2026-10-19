@extends('layouts.app')

@section('content')
<style>
    .assistant-drafts{display:grid;grid-template-columns:1fr 1fr;gap:24px}
    .assistant-list{padding-left:20px;color:var(--ink);font-size:13px;line-height:1.6}
    .assistant-list li{margin:8px 0}
    .assistant-list strong{color:var(--navy)}
    .company-breakdown{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;margin:0 0 16px}
    .company-breakdown-card{padding:12px 14px;border:1px solid var(--line);border-left:4px solid var(--yellow);border-radius:10px;background:var(--paper)}
    .company-breakdown-card strong{display:block;color:var(--navy);font:700 13px "Space Grotesk",sans-serif}
    .company-breakdown-card span{display:block;margin:4px 0;color:#315ab1;font-size:11px;font-weight:700}
    .company-breakdown-card small{display:block;color:var(--muted);font-size:11px;line-height:1.5}
    @media(max-width:700px){.assistant-drafts{grid-template-columns:1fr}}
</style>
<div class="meeting-page-head">
    <div>
        <a class="back-link" href="{{ route('meetings.show', $meeting->meeting_code) }}">← Back to meeting</a>
        <p class="eyebrow">ONLINE SESSION REPORT / {{ $meeting->meeting_code }}</p>
        <h1>{{ $meeting->meeting_topic }}</h1>
        <p class="hero-copy">Session ended {{ ($meeting->ended_at ?? $meeting->cancelled_at)?->format('D, d M Y · H:i') ?? 'Not ended yet' }}</p>
    </div>
</div>

<div class="meeting-detail-grid">
    <section class="panel">
        <div class="panel-title"><div><h3>Attendance</h3><p>{{ $participants->whereNotNull('attendee_id')->unique('attendee_id')->count() }} invited attendees joined; {{ $participants->count() }} total joins.</p></div></div>
        @if($meeting->isMultiCompany())
            @php($companyGroups = $participants->groupBy(fn ($participant) => $participant->attendee?->meetingCompany?->name ?? ($participant->is_host ? 'Host company' : 'Guests')))
            <div class="company-breakdown">
                @foreach($companyGroups as $companyName => $companyMembers)
                    <div class="company-breakdown-card">
                        <strong>{{ $companyName }}</strong>
                        <span>{{ $companyMembers->count() }} {{ \Illuminate\Support\Str::plural('join', $companyMembers->count()) }}</span>
                        <small>{{ $companyMembers->pluck('display_name')->unique()->implode(', ') }}</small>
                    </div>
                @endforeach
            </div>
        @endif
        <div class="attendee-list">
            @forelse($participants as $participant)
                @php($duration = $participant->joined_at?->diffInMinutes($participant->left_at ?? $meeting->ended_at ?? now()))
                <div class="attendee-row">
                    <div class="avatar">{{ strtoupper(substr($participant->display_name, 0, 1)) }}</div>
                    <div>
                        <strong>{{ $participant->display_name }} @if($participant->is_host)<small>Host</small>@endif</strong>
                        <small>{{ $participant->attendee?->email ?? 'Host' }}@if($meeting->isMultiCompany() && $participant->attendee?->meetingCompany) · {{ $participant->attendee->meetingCompany->name }}@endif · Joined {{ $participant->joined_at?->format('H:i:s') ?? '—' }} · Left {{ $participant->left_at?->format('H:i:s') ?? 'Still in session' }} · {{ $duration }} min</small>
                    </div>
                </div>
            @empty
                <p class="muted">No attendance was recorded for this session.</p>
            @endforelse
        </div>
    </section>
    <section class="panel">
        <div class="panel-title"><div><h3>Session recordings</h3><p>Recordings are stored privately with this session until an admin deletes them.</p></div></div>
        <div class="attendee-list">
            @forelse($recordings as $recording)
                <div class="attendee-row">
                    <div class="avatar">▶</div>
                    <div>
                        <strong>Recording by {{ $recording->participant->display_name }}</strong>
                        <small>{{ $recording->started_at->format('D, d M Y · H:i') }} · {{ gmdate('H:i:s', $recording->duration_seconds ?? 0) }} · {{ number_format(($recording->file_size ?? 0) / 1048576, 1) }} MB</small>
                    </div>
                    <a class="text-button" href="{{ route('sessions.recordings.download', [$meeting, $recording]) }}">Download ↓</a>
                    <form method="post" action="{{ route('sessions.recordings.delete', [$meeting, $recording]) }}" onsubmit="return confirm('Delete this recording permanently?');">
                        @csrf
                        @method('delete')
                        <button class="text-button" type="submit">Delete</button>
                    </form>
                </div>
            @empty
                <p class="muted">No completed recordings are available.</p>
            @endforelse
        </div>
    </section>
</div>

<section class="panel" style="margin-top:20px">
    <div class="panel-title">
        <div>
            <h3>Session Assistant · Transcript-based draft</h3>
            <p>Browser speech recognition transcript highlights and keyword-based suggestions. This is not AI-generated; review and correct before using as official minutes.</p>
        </div>
    </div>
    @if($transcript->isEmpty())
        <p class="muted">No live transcript was saved. Participants can opt in to browser transcription from the session room.</p>
    @else
        <h4>Discussion summary draft</h4>
        <ul class="assistant-list">
            @forelse($assistantDraft['summary'] as $highlight)
                <li><strong>{{ $highlight['speaker'] }}:</strong> {{ $highlight['text'] }}</li>
            @empty
                <li>Transcript captured; there are no long enough statements for highlights.</li>
            @endforelse
        </ul>
        <div class="assistant-drafts">
            <div>
                <h4>Possible decisions</h4>
                <ul class="assistant-list">
                    @forelse($assistantDraft['decisions'] as $decision)
                        <li><strong>{{ $decision['speaker'] }}:</strong> {{ $decision['text'] }}</li>
                    @empty
                        <li>No decision keywords detected. Review the transcript for decisions.</li>
                    @endforelse
                </ul>
            </div>
            <div>
                <h4>Possible action items</h4>
                <ul class="assistant-list">
                    @forelse($assistantDraft['action_items'] as $action)
                        <li><strong>{{ $action['speaker'] }}:</strong> {{ $action['text'] }}</li>
                    @empty
                        <li>No action-item keywords detected. Review the transcript for follow-ups.</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <h4>Draft meeting minutes · Transcript</h4>
        <div class="attendee-list">
            @foreach($assistantDraft['minutes'] as $minute)
                <div class="attendee-row">
                    <div><strong>{{ $minute['speaker'] }}</strong><small>{{ $minute['time']?->format('D, d M Y · H:i:s') }}</small><p>{{ $minute['text'] }}</p></div>
                </div>
            @endforeach
        </div>
    @endif
</section>

<section class="panel" style="margin-top:20px">
    <div class="panel-title"><div><h3>Chat transcript</h3><p>{{ $messages->count() }} messages</p></div></div>
    <div class="attendee-list">
        @forelse($messages as $message)
            <div class="attendee-row">
                <div><strong>{{ $message->participant->display_name }}</strong><small>{{ $message->created_at->format('D, d M Y · H:i:s') }}</small><p>{{ $message->message }}</p></div>
            </div>
        @empty
            <p class="muted">No chat messages were sent.</p>
        @endforelse
    </div>
</section>
@endsection
