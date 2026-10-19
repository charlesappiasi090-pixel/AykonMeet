@extends('layouts.app')

@section('title', 'Attendance confirmed · '.$meeting->meeting_topic)

@section('content')
@php
    $typeLabel = match (true) {
        $meeting->isHybrid() => 'Hybrid meeting',
        $meeting->is_online => 'Online meeting',
        default => 'In-person meeting',
    };
    $stateUrl = route('invitations.state', ['attendee' => $attendee->invitation_token]);
    $lobbyUrl = route('invitations.lobby', ['attendee' => $attendee->invitation_token]);
@endphp
<div class="cfm">
    <style>
        .cfm{max-width:860px;margin:0 auto;padding:44px 22px 60px;font-family:"DM Sans",sans-serif;color:#112b49}
        .cfm *{box-sizing:border-box}
        .cfm-card{position:relative;padding:44px 42px;border-radius:26px;background:#fff;border:1px solid #e4eaf1;box-shadow:0 24px 60px rgba(17,43,73,.10);overflow:hidden;text-align:center}
        .cfm-card::before{content:"";position:absolute;inset:0 0 auto;height:6px;background:linear-gradient(90deg,#2ecc71,#f7c948)}
        .cfm-check{display:grid;place-items:center;width:78px;height:78px;margin:6px auto 20px;border-radius:50%;background:linear-gradient(135deg,#2ecc71,#239b56);color:#fff;font-size:38px;box-shadow:0 16px 34px rgba(46,204,113,.35)}
        .cfm h1{margin:0 0 10px;font:600 clamp(26px,4vw,38px)/1.1 "Fraunces",Georgia,serif;letter-spacing:-.5px}
        .cfm p.lead{margin:0 auto;max-width:560px;color:#5b6b7d;font-size:14.5px;line-height:1.6}
        .cfm p.lead b{color:#112b49}
        .cfm-meta{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin:30px 0 6px}
        .cfm-meta div{padding:16px;border:1px solid #e8eef5;border-radius:14px;background:#f8fafc}
        .cfm-meta span{display:block;color:#718096;font:800 10px "Space Grotesk",sans-serif;letter-spacing:1px;text-transform:uppercase}
        .cfm-meta strong{display:block;margin-top:7px;font-size:13.5px}
        .cfm-actions{display:flex;flex-wrap:wrap;gap:12px;justify-content:center;margin-top:30px}
        .cfm-btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;height:52px;padding:0 28px;border:0;border-radius:13px;font:700 14px "Space Grotesk",sans-serif;cursor:pointer;text-decoration:none;transition:transform .16s ease,box-shadow .16s ease}
        .cfm-btn:hover{transform:translateY(-1px)}
        .cfm-btn.join{background:linear-gradient(135deg,#2ecc71,#239b56);color:#fff;box-shadow:0 14px 30px rgba(46,204,113,.36)}
        .cfm-btn.gold{background:linear-gradient(135deg,#f7c948,#f0b420);color:#112b49;box-shadow:0 14px 30px rgba(247,201,72,.4)}
        .cfm-btn.ghost{background:#fff;color:#112b49;border:1px solid #dbe3ea}
        .cfm-btn[disabled]{opacity:.6;cursor:not-allowed;transform:none;box-shadow:none}
        .cfm-wait{display:flex;align-items:center;gap:14px;margin:26px auto 4px;max-width:560px;padding:18px 20px;border:1px dashed #f0b420;border-radius:16px;background:#fffaf0;text-align:left}
        .cfm-wait .spin{width:30px;height:30px;flex:0 0 30px;border-radius:50%;border:3px solid #f7c948;border-top-color:transparent;animation:cfm-spin 1s linear infinite}
        @keyframes cfm-spin{to{transform:rotate(360deg)}}
        .cfm-wait b{display:block;font:700 13.5px "DM Sans",sans-serif;color:#8a6a00}
        .cfm-wait small{display:block;margin-top:3px;color:#9a7c2a;font-size:12px}
        .cfm-foot{margin-top:26px;color:#8798ab;font-size:12px}
        @media(max-width:640px){.cfm-meta{grid-template-columns:1fr}.cfm-card{padding:34px 22px}}
    </style>

    <div class="cfm-card">
        <div class="cfm-check">✓</div>
        <h1>You're confirmed</h1>
        <p class="lead">Thanks <b>{{ $attendee->full_name }}</b>, your attendance is confirmed. We've let <b>{{ $invitingCompany }}</b> know you'll be joining{{ $myCompany ? ' from '.$myCompany->name : '' }}.</p>

        <div class="cfm-meta">
            <div><span>Meeting</span><strong>{{ $meeting->meeting_topic }}</strong></div>
            <div><span>When</span><strong>{{ $meeting->start_date->format('M j, Y · g:i A') }}</strong></div>
            <div><span>Format</span><strong>{{ $typeLabel }}</strong></div>
        </div>

        @if(! $meeting->is_online)
            <div class="cfm-meta" style="grid-template-columns:1fr">
                <div><span>Location</span><strong>{{ $meeting->physical_location ?: $meeting->from_location ?: 'To be advised' }}</strong></div>
            </div>
            <p class="lead" style="margin-top:22px">We look forward to seeing you in person. Please arrive a few minutes early.</p>
        @elseif($joinState === 'open')
            <div class="cfm-actions">
                <a class="cfm-btn join" href="{{ $lobbyUrl }}">🎥 Join Meeting</a>
                <a class="cfm-btn ghost" href="{{ $invitationUrl }}">View invitation</a>
            </div>
        @else
            <div class="cfm-wait">
                <span class="spin"></span>
                <span><b>Waiting for the host to start…</b><small>This page will update automatically and the Join button will appear once the room opens.</small></span>
            </div>
            <div class="cfm-actions">
                <button type="button" class="cfm-btn join" disabled id="cfm-join" data-state-url="{{ $stateUrl }}">🎥 Join Meeting</button>
                <a class="cfm-btn ghost" href="{{ $invitationUrl }}">View invitation</a>
            </div>
        @endif

        <p class="cfm-foot">Keep this page open, or return using the secure link in your invitation email.</p>
    </div>

    @if(session('invitation_success'))
        <div style="margin-top:22px;padding:14px 18px;border:1px solid #bfe6cc;border-left:4px solid #2ecc71;border-radius:14px;background:#eefaf1;color:#1c6b3d;font:700 13.5px 'DM Sans',sans-serif">✓ {{ session('invitation_success') }}</div>
    @endif
    @if(session('invitation_error'))
        <div style="margin-top:22px;padding:14px 18px;border:1px solid #f0d489;border-left:4px solid #f0b420;border-radius:14px;background:#fffaf0;color:#8a6a00;font:600 13px 'DM Sans',sans-serif">{{ session('invitation_error') }}</div>
    @endif

    @if($canInviteColleague)
        <div class="cfm-card" style="margin-top:22px;text-align:left">
            <h2 style="margin:0 0 6px;font:700 20px 'Space Grotesk',sans-serif;color:#112b49">Invite a colleague</h2>
            <p style="margin:0 0 20px;color:#6b7d91;font-size:12.5px">Add someone from <b>{{ $myCompany?->name }}</b> to this meeting. The organizer approves every request before an invitation is sent.</p>
            <form method="post" action="{{ route('invitations.colleague', ['attendee' => $attendee->invitation_token]) }}">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <label style="display:block"><span style="display:block;margin-bottom:6px;color:#112b49;font:800 10.5px 'Space Grotesk',sans-serif;letter-spacing:.5px;text-transform:uppercase">Full name</span><input type="text" name="full_name" required maxlength="160" style="width:100%;padding:11px 12px;border:1.5px solid #d5e0ea;border-radius:10px;font:13px 'DM Sans',sans-serif"></label>
                    <label style="display:block"><span style="display:block;margin-bottom:6px;color:#112b49;font:800 10.5px 'Space Grotesk',sans-serif;letter-spacing:.5px;text-transform:uppercase">Email</span><input type="email" name="email" required maxlength="160" style="width:100%;padding:11px 12px;border:1.5px solid #d5e0ea;border-radius:10px;font:13px 'DM Sans',sans-serif"></label>
                </div>
                <label style="display:block;margin-top:12px"><span style="display:block;margin-bottom:6px;color:#112b49;font:800 10.5px 'Space Grotesk',sans-serif;letter-spacing:.5px;text-transform:uppercase">Department (optional)</span><input type="text" name="department" maxlength="160" style="width:100%;padding:11px 12px;border:1.5px solid #d5e0ea;border-radius:10px;font:13px 'DM Sans',sans-serif"></label>
                <button type="submit" class="cfm-btn gold" style="margin-top:18px">+ Add colleague</button>
            </form>
        </div>
    @endif
</div>

@if($joinState !== 'open')
<script>
(function () {
    var btn = document.getElementById('cfm-join');
    if (!btn) return;
    var url = btn.getAttribute('data-state-url');
    var timer = setInterval(function () {
        fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (data && data.state === 'open') {
                    clearInterval(timer);
                    window.location.href = @json($lobbyUrl);
                }
            })
            .catch(function () {});
    }, 8000);
})();
</script>
@endif
@endsection
