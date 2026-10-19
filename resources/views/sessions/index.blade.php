@extends("layouts.app")
@section("content")
<style>
.sessions-hero{display:grid;grid-template-columns:1fr auto;gap:30px;align-items:end;padding-bottom:38px}
.sessions-hero h1{margin:12px 0 10px;color:var(--navy);font:700 48px/1.05 "Space Grotesk",sans-serif;letter-spacing:-1px}
.sessions-hero h1 em{color:#b18800;font-style:normal}
.session-stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:48px}
.session-stat{background:var(--white);border:1px solid var(--line);border-top:3px solid var(--navy);padding:20px 22px}
.session-stat.live{border-top-color:#e05858;background:#fffafa}.session-stat.live strong{color:#c03b2d}
.session-stat .stat-label{color:var(--muted);font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
.session-stat strong{color:var(--navy);font:700 32px/1 "Space Grotesk",sans-serif;margin:10px 0 6px;display:block}
.session-stat small{color:var(--muted);font-size:11px}
.session-section{margin-bottom:50px}
.session-section-head{display:flex;align-items:center;gap:12px;margin-bottom:18px}
.session-section-head h2{font:600 22px "Space Grotesk",sans-serif;color:var(--navy);margin:0}
.live-pill{display:inline-flex;align-items:center;gap:6px;background:#fdecea;color:#c03b2d;padding:4px 10px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;border-radius:20px}
.live-pill::before{content:"";width:7px;height:7px;border-radius:50%;background:#e05858}
.session-cards{display:grid;gap:14px}
.session-card{background:var(--white);border:1px solid var(--line);border-radius:4px;padding:22px 26px;display:grid;grid-template-columns:auto 1fr auto;gap:22px;align-items:center}
.session-card.live{border-left:4px solid #e05858;background:linear-gradient(90deg,#fffafa 0%,#fff 40%)}
.session-time{text-align:center;min-width:80px}.st-day{font-size:10px;font-weight:700;color:#b18800;text-transform:uppercase;letter-spacing:1px}.session-card.live .st-day{color:#c03b2d}
.st-time{font:700 24px "Space Grotesk",sans-serif;color:var(--navy);margin-top:4px}
.session-body h3{font:700 17px "Space Grotesk",sans-serif;color:var(--navy);margin:0 0 6px}.st-host{font-size:12px;color:var(--muted);font-weight:500}
.st-meta{display:flex;flex-wrap:wrap;gap:14px;margin-top:8px;font-size:12px;color:var(--muted);font-weight:600}.st-meta-item{display:inline-flex;align-items:center;gap:5px}
.provider-badge{display:inline-flex;align-items:center;gap:5px;font-size:10px;font-weight:700;padding:3px 8px;border-radius:3px;background:#e3f4e8;color:#267344;text-transform:uppercase;letter-spacing:.5px}
.session-actions{display:flex;gap:8px}
.session-invite{display:flex;align-items:center;gap:6px;min-width:0;margin-top:10px}
.session-invite input{width:min(260px,100%);min-width:0;padding:8px 9px;border:1px solid var(--line);background:var(--paper);color:var(--muted);font:11px "DM Sans",sans-serif}
.session-invite button{flex:none;padding:8px 10px;border:0;background:var(--yellow);color:var(--navy);font:700 10px "DM Sans",sans-serif;cursor:pointer}
.session-invite button:hover{filter:brightness(.96)}
.btn-j,.btn-c{padding:10px 16px;font:700 11px "DM Sans",sans-serif;border:0;cursor:pointer;text-decoration:none;border-radius:3px;display:inline-flex;align-items:center;gap:5px;color:#fff}.btn-j{background:#e05858}.btn-j.up{background:var(--navy)}.btn-c{background:var(--yellow);color:var(--navy)}
.empty-sessions{background:var(--white);border:1px dashed var(--line);padding:50px 30px;text-align:center;border-radius:4px}.empty-sessions strong{display:block;font:600 18px "Space Grotesk",sans-serif;color:var(--navy);margin-bottom:8px}.empty-sessions span{font-size:13px;color:var(--muted);display:block;margin-bottom:18px}
@media(max-width:800px){.sessions-hero{grid-template-columns:1fr}.session-stat-grid{grid-template-columns:1fr 1fr}.session-card{grid-template-columns:1fr}.session-time{text-align:left}}
</style>
<section class="sessions-hero"><div><p class="eyebrow">ONLINE SESSIONS</p><h1>Video meetings,<br><em>one click away.</em></h1><p>Start an instant video call with one click.</p></div><div><a class="button primary" href="{{ route("sessions.quick-start") }}" style="background:#267344;text-decoration:none;display:inline-flex;">🎥 Start instant session</a></div></section>
<section class="session-stat-grid">
<div class="session-stat live"><span class="stat-label">● Live now</span><strong>{{ $stats["live"] }}</strong><small>In progress</small></div>
<div class="session-stat"><span class="stat-label">Upcoming</span><strong>{{ $stats["upcoming"] }}</strong><small>Next 14 days</small></div>
<div class="session-stat"><span class="stat-label">This week</span><strong>{{ $stats["this_week"] }}</strong><small>Scheduled</small></div>
<div class="session-stat"><span class="stat-label">All time</span><strong>{{ $stats["total"] }}</strong><small>Online sessions</small></div>
</section>
@if($liveSessions->count()>0)
<section class="session-section"><div class="session-section-head"><span class="live-pill">Live</span><h2>In progress</h2></div><div class="session-cards">
@foreach($liveSessions as $m)
@php($inviteUrl = URL::signedRoute("sessions.join", ["meeting" => $m]))
<article class="session-card live"><div class="session-time"><div class="st-day">Live</div><div class="st-time">●</div></div><div class="session-body"><h3>{{ $m->meeting_topic }}</h3><div class="st-host">{{ $m->name }}</div><div class="st-meta"><span class="st-meta-item"><span class="provider-badge">AykonMeet</span></span><span class="st-meta-item">👥 {{ $m->attendees_count }}</span></div><div class="session-invite"><input aria-label="Invite link for {{ $m->meeting_topic }}" readonly value="{{ $inviteUrl }}"><button type="button" data-copy-invite="{{ $inviteUrl }}">Copy invite</button></div></div><div class="session-actions"><a class="btn-j" href="{{ $inviteUrl }}">▶ Join now</a></div></article>
@endforeach</div></section>
@endif
<section class="session-section"><div class="session-section-head"><h2>Upcoming sessions</h2></div>
@if($upcomingSessions->count()>0)
<div class="session-cards">@foreach($upcomingSessions as $m)
@php($inviteUrl = URL::signedRoute("sessions.join", ["meeting" => $m]))
<article class="session-card"><div class="session-time"><div class="st-day">{{ $m->start_date->isToday()?"Today":($m->start_date->isTomorrow()?"Tomorrow":$m->start_date->format("D, M j")) }}</div><div class="st-time">{{ $m->start_date->format("g:i A") }}</div></div><div class="session-body"><h3>{{ $m->meeting_topic }}</h3><div class="st-host">{{ $m->name }}</div><div class="st-meta"><span class="provider-badge">AykonMeet</span></div><div class="session-invite"><input aria-label="Invite link for {{ $m->meeting_topic }}" readonly value="{{ $inviteUrl }}"><button type="button" data-copy-invite="{{ $inviteUrl }}">Copy invite</button></div></div><div class="session-actions"><a class="btn-j up" href="{{ $inviteUrl }}">🎥 Open room</a></div></article>
@endforeach</div>
@else<div class="empty-sessions"><strong>No upcoming sessions.</strong><span>Click "Start instant session" to begin one.</span></div>@endif
</section>
@if($recentSessions->isNotEmpty())
<section class="session-section"><div class="session-section-head"><h2>Session reports</h2></div>
<div class="session-cards">@foreach($recentSessions as $m)
<article class="session-card"><div class="session-time"><div class="st-day">{{ ucfirst($m->status) }}</div><div class="st-time">{{ $m->start_date->format("M j") }}</div></div><div class="session-body"><h3>{{ $m->meeting_topic }}</h3><div class="st-host">{{ $m->name }}</div><div class="st-meta"><span class="provider-badge">Attendance, recording &amp; chat report</span></div></div><div class="session-actions"><a class="btn-j up" href="{{ route("sessions.report",$m) }}">View report</a></div></article>
@endforeach</div>
</section>
@endif
<script>
document.querySelectorAll("[data-copy-invite]").forEach(function (button) {
    button.addEventListener("click", async function () {
        var inviteUrl = button.getAttribute("data-copy-invite");
        try {
            await navigator.clipboard.writeText(inviteUrl);
            button.textContent = "Copied!";
        } catch (error) {
            var input = button.parentElement.querySelector("input");
            input.focus();
            input.select();
            button.textContent = "Select and copy";
        }
    });
});
</script>
@endsection