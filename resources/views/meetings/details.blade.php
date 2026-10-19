@extends('layouts.app')

@section('content')
@php
    $statusMap = [
        'scheduled' => 'Scheduled',
        'waiting' => 'Waiting to Start',
        'live' => 'Live',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];
    $statusLabel = $statusMap[$stats['status']] ?? 'Scheduled';
    $durationMinutes = (int) $meeting->start_date->diffInMinutes($meeting->end_date);
    $durationLabel = $durationMinutes >= 60
        ? intdiv($durationMinutes, 60).'h '.($durationMinutes % 60 > 0 ? ($durationMinutes % 60).'m' : '')
        : $durationMinutes.' min';
    $typeLabel = match ($meeting->meeting_type) {
        'online' => $meeting->isMultiCompany() ? 'Multi-company online' : 'Online meeting',
        'hybrid' => 'Hybrid meeting',
        default => 'In-person meeting',
    };
    $shareMessage = 'You are invited to "'.$meeting->meeting_topic.'" on '.$meeting->start_date->format('D, M j, Y g:i A').'. Join: '.$joinUrl;
@endphp
<div class="mdd">
    <style>
        .mdd{padding:26px 30px 48px;max-width:1360px;margin:0 auto}
        .mdd-back{display:inline-flex;align-items:center;gap:7px;margin-bottom:16px;color:#6b7d91;font:700 12.5px "DM Sans",sans-serif;text-decoration:none}
        .mdd-back:hover{color:var(--navy)}
        .mdd-head{position:relative;display:grid;grid-template-columns:minmax(0,1fr) auto;gap:22px;align-items:start;padding:26px 28px;border-radius:20px;background:radial-gradient(ellipse at 90% 0%,rgba(247,201,72,.22),transparent 55%),linear-gradient(140deg,#0b1f38,#112b49 60%,#173a63);color:#fff;overflow:hidden;box-shadow:0 22px 50px rgba(8,22,42,.28)}
        .mdd-head::after{content:"";position:absolute;width:260px;height:260px;border-radius:50%;background:rgba(247,201,72,.12);filter:blur(70px);top:-90px;right:-40px}
        .mdd-eyebrow{display:inline-flex;align-items:center;gap:9px;font:800 10.5px "Space Grotesk",sans-serif;letter-spacing:2.6px;text-transform:uppercase;color:#f7c948}
        .mdd-eyebrow i{width:8px;height:8px;border-radius:50%;background:#f7c948;box-shadow:0 0 0 4px rgba(247,201,72,.25)}
        .mdd-head h1{position:relative;z-index:2;margin:14px 0 0;color:#fff;font:600 clamp(26px,3.4vw,40px)/1.08 "Fraunces",Georgia,serif;letter-spacing:-.5px}
        .mdd-meta{position:relative;z-index:2;display:flex;flex-wrap:wrap;gap:9px 10px;margin-top:18px}
        .mdd-chip{display:inline-flex;align-items:center;gap:7px;padding:8px 12px;border:1px solid rgba(255,255,255,.16);border-radius:999px;background:rgba(255,255,255,.07);color:#e7eef7;font:600 11.5px "DM Sans",sans-serif}
        .mdd-chip b{color:#f7c948;font-weight:800}
        .mdd-chip.code{background:#f7c948;border-color:#f7c948;color:#112b49;letter-spacing:.06em}
        .mdd-status{display:inline-flex;align-items:center;gap:7px;padding:8px 13px;border-radius:999px;font:800 11px "Space Grotesk",sans-serif;text-transform:uppercase;letter-spacing:.5px}
        .mdd-status.scheduled{background:rgba(255,255,255,.14);color:#fff}
        .mdd-status.waiting{background:#fdf1cf;color:#8a6a00}
        .mdd-status.live{background:#c9f0d6;color:#1c6b3d}
        .mdd-status.completed{background:rgba(255,255,255,.14);color:#dbe6f1}
        .mdd-status.cancelled{background:#f7c6c6;color:#8f2222}
        .mdd-head-actions{position:relative;z-index:2;display:flex;flex-wrap:wrap;gap:9px;justify-content:flex-end;max-width:520px}
        .mdd-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;height:44px;padding:0 17px;border:0;border-radius:12px;font:700 12.5px "Space Grotesk",sans-serif;cursor:pointer;text-decoration:none;transition:transform .16s ease,box-shadow .16s ease,background .16s ease}
        .mdd-btn:hover{transform:translateY(-1px)}
        .mdd-btn.start{background:linear-gradient(135deg,#2ecc71,#239b56);color:#fff;box-shadow:0 8px 22px rgba(46,204,113,.32)}
        .mdd-btn.gold{background:linear-gradient(135deg,#f7c948,#f0b420);color:#112b49;box-shadow:0 8px 22px rgba(247,201,72,.35)}
        .mdd-btn.ghost{background:rgba(255,255,255,.09);color:#fff;border:1px solid rgba(255,255,255,.2)}
        .mdd-btn.ghost:hover{background:rgba(255,255,255,.17)}
        .mdd-btn.danger{background:rgba(192,59,45,.16);color:#ffc9c3;border:1px solid rgba(255,120,110,.4)}
        .mdd-btn.danger:hover{background:rgba(192,59,45,.3)}
        .mdd-btn.sm{height:38px;padding:0 13px;font-size:11.5px;border-radius:10px}
        .mdd-btn.light{background:#f1f5f9;color:#112b49;border:1px solid #dbe3ea}
        .mdd-btn.light:hover{background:#e6edf4}
        .mdd-banner{display:flex;align-items:center;gap:12px;margin-top:18px;padding:15px 18px;border:1px solid #bfe6cc;border-left:4px solid #2ecc71;border-radius:14px;background:#eefaf1;color:#1c6b3d;font:700 13.5px "DM Sans",sans-serif}
        .mdd-banner .tick{display:grid;place-items:center;width:28px;height:28px;border-radius:50%;background:#2ecc71;color:#fff;font-size:15px;flex:0 0 28px}
        .mdd-banner small{display:block;margin-top:3px;color:#3d7a58;font-weight:500}
        .mdd-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-top:22px}
        .mdd-stat{position:relative;padding:20px 22px;border-radius:18px;background:#fff;border:1px solid #e4eaf1;border-top:4px solid var(--navy);box-shadow:0 12px 30px rgba(17,43,73,.06);overflow:hidden}
        .mdd-stat.accent{background:linear-gradient(135deg,#f7c948,#f0b420);border-color:#f0b420;border-top-color:#e0a800}
        .mdd-stat-label{display:block;color:#718096;font:800 10.5px "Space Grotesk",sans-serif;letter-spacing:1.2px;text-transform:uppercase}
        .mdd-stat strong{display:block;margin:12px 0 6px;color:#112b49;font:700 38px/1 "Space Grotesk",sans-serif}
        .mdd-stat small{color:#718096;font-size:12px}
        .mdd-stat.accent .mdd-stat-label,.mdd-stat.accent strong,.mdd-stat.accent small{color:#112b49}
        .mdd-stat-ico{position:absolute;top:16px;right:18px;font-size:20px;opacity:.5}
        .mdd-cols{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(300px,1fr);gap:20px;margin-top:22px;align-items:start}
        .mdd-col{display:grid;gap:20px;min-width:0}
        .mdd-card{padding:22px 24px;border-radius:18px;background:#fff;border:1px solid #e4eaf1;box-shadow:0 10px 30px rgba(17,43,73,.05);min-width:0}
        .mdd-card-head{display:flex;justify-content:space-between;align-items:flex-start;gap:14px;margin-bottom:18px}
        .mdd-card-head h2{margin:0;color:#112b49;font:700 18px "Space Grotesk",sans-serif}
        .mdd-card-head p{margin:5px 0 0;color:#6b7d91;font-size:12.5px}
        .mdd-count{padding:5px 11px;border-radius:999px;background:#f1f5f9;color:#112b49;font:800 11px "DM Sans",sans-serif;white-space:nowrap}
        .mdd-share{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:16px;align-items:end;padding:16px;border:1px dashed #cfdce8;border-radius:14px;background:#f7fafd;margin-bottom:14px}
        .mdd-share label{display:block;color:#112b49;font:800 10.5px "Space Grotesk",sans-serif;letter-spacing:.6px;text-transform:uppercase}
        .mdd-share label span{display:block;margin-top:4px;color:#718096;font:500 11px "DM Sans",sans-serif;letter-spacing:0;text-transform:none}
        .mdd-share input{width:100%;margin-top:9px;padding:11px 12px;border:1px solid #c9d5e2;border-radius:9px;background:#fff;color:#112b49;font:12px "DM Sans",sans-serif}
        .mdd-share-actions{display:flex;flex-wrap:wrap;gap:8px}
        .mdd-code{display:inline-flex;align-items:center;gap:10px;padding:10px 14px;border-radius:12px;background:#112b49;color:#f7c948;font:800 15px "Space Grotesk",sans-serif;letter-spacing:.12em}
        .mdd-code small{color:#9fb4cc;font:600 9.5px "DM Sans",sans-serif;letter-spacing:.08em;text-transform:uppercase}
        .mdd-company{border:1px solid #e4eaf1;border-radius:16px;background:#fff;padding:18px;margin-bottom:14px;transition:box-shadow .18s ease,transform .18s ease}
        .mdd-company:hover{box-shadow:0 14px 34px rgba(17,43,73,.1);transform:translateY(-2px)}
        .mdd-company-top{display:flex;align-items:flex-start;gap:13px}
        .mdd-logo{display:grid;place-items:center;width:48px;height:48px;flex:0 0 48px;border-radius:12px;background:linear-gradient(135deg,#112b49,#1a3d66);color:#f7c948;font:700 20px "Space Grotesk",sans-serif;overflow:hidden}
        .mdd-logo img{width:100%;height:100%;object-fit:cover}
        .mdd-company-name{flex:1;min-width:0}
        .mdd-company-name h3{margin:0;color:#112b49;font:700 16px "Space Grotesk",sans-serif;display:flex;flex-wrap:wrap;align-items:center;gap:8px}
        .mdd-company-name small{display:block;margin-top:5px;color:#6b7d91;font-size:12px;overflow-wrap:anywhere}
        .mdd-host-tag{padding:3px 8px;border-radius:999px;background:#fdf1cf;color:#8a6a00;font:800 9.5px "DM Sans",sans-serif;letter-spacing:.4px;text-transform:uppercase}
        .mdd-pill{padding:5px 11px;border-radius:999px;font:800 10px "Space Grotesk",sans-serif;text-transform:uppercase;letter-spacing:.4px;white-space:nowrap}
        .mdd-pill.pending{background:#fdeecd;color:#946a00}
        .mdd-pill.accepted{background:#ddf3e6;color:#1c6b3d}
        .mdd-pill.declined{background:#fbe0e0;color:#a5382c}
        .mdd-pill.sent{background:#dfeafa;color:#21508f}
        .mdd-rep .state{padding:3px 9px;border-radius:999px;font:800 9.5px "Space Grotesk",sans-serif;text-transform:uppercase;letter-spacing:.4px;white-space:nowrap}
        .mdd-rep .state.pending{background:#fdeecd;color:#946a00}
        .mdd-rep .state.sent{background:#dfeafa;color:#21508f}
        .mdd-rep .state.accepted{background:#ddf3e6;color:#1c6b3d}
        .mdd-rep .state.declined{background:#fbe0e0;color:#a5382c}
        .mdd-rep .approve{padding:4px 9px;border:0;border-radius:8px;background:#112b49;color:#f7c948;font:800 10px "Space Grotesk",sans-serif;cursor:pointer}
        .mdd-reps{margin:15px 0 0;padding:14px 15px;border-radius:12px;background:#f7fafd;border:1px solid #eaf0f6}
        .mdd-reps-title{display:flex;justify-content:space-between;align-items:center;margin-bottom:9px;color:#112b49;font:800 10.5px "Space Grotesk",sans-serif;letter-spacing:.6px;text-transform:uppercase}
        .mdd-rep{display:flex;align-items:center;gap:10px;padding:7px 0;border-top:1px solid #e7eef5;font-size:12.5px}
        .mdd-rep:first-of-type{border-top:0}
        .mdd-rep .avatar{display:grid;place-items:center;width:28px;height:28px;flex:0 0 28px;border-radius:50%;background:#e7eef5;color:#112b49;font:700 11px "DM Sans",sans-serif}
        .mdd-rep .who{flex:1;min-width:0}
        .mdd-rep .who strong{display:block;color:#112b49;font-weight:700}
        .mdd-rep .who small{display:block;color:#6b7d91;font-size:11px;overflow-wrap:anywhere}
        .mdd-rep .dept{padding:3px 8px;border-radius:8px;background:#eef3f8;color:#41576f;font-size:10.5px;white-space:nowrap}
        .mdd-empty{padding:16px;color:#718096;font-size:13px;text-align:center;border:1px dashed #dbe3ea;border-radius:12px}
        .mdd-company-actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px}
        .mdd-add{display:inline-flex;align-items:center;gap:8px;margin-top:6px;padding:12px 18px;border:1.5px dashed #b18800;border-radius:12px;background:#fffaf0;color:#8a6d00;font:800 12px "DM Sans",sans-serif;cursor:pointer}
        .mdd-add:hover{background:#fff3d6}
        .mdd-note{padding:13px 15px;border:1px solid #ead58b;border-radius:12px;background:#fff9e5;color:#6b5500;font-size:12px;line-height:1.55}
        .mdd-note strong{color:#112b49}
        .mdd-field{display:block;margin-bottom:13px}
        .mdd-field>span{display:block;margin-bottom:6px;color:#112b49;font:800 10.5px "Space Grotesk",sans-serif;letter-spacing:.5px;text-transform:uppercase}
        .mdd-field input[type=text],.mdd-field input[type=email],.mdd-field input[type=password],.mdd-field input[type=number],.mdd-field select,.mdd-field textarea{display:block;width:100%;padding:11px 12px;border:1.5px solid #d5e0ea;border-radius:10px;background:#fff;color:#172437;font:13px "DM Sans",sans-serif}
        .mdd-field input:focus,.mdd-field select:focus,.mdd-field textarea:focus{outline:0;border-color:#112b49;box-shadow:0 0 0 4px rgba(247,201,72,.28)}
        .mdd-grid2{display:grid;grid-template-columns:1fr 1fr;gap:0 12px}
        .mdd-toggle{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:11px 0;border-bottom:1px solid #eef2f6}
        .mdd-toggle:last-child{border-bottom:0}
        .mdd-toggle span{color:#172437;font-size:12.5px;font-weight:600}
        .mdd-toggle small{display:block;margin-top:2px;color:#718096;font-size:11px;font-weight:400}
        .mdd-switch{position:relative;width:44px;height:24px;flex:0 0 44px}
        .mdd-switch input{position:absolute;opacity:0;width:0;height:0}
        .mdd-switch i{position:absolute;inset:0;border-radius:999px;background:#cbd5e1;transition:background .18s ease}
        .mdd-switch i::after{content:"";position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;transition:transform .18s ease;box-shadow:0 1px 3px rgba(0,0,0,.25)}
        .mdd-switch input:checked+i{background:#2ecc71}
        .mdd-switch input:checked+i::after{transform:translateX(20px)}
        .mdd-group-title{margin:18px 0 8px;color:#112b49;font:800 11px "Space Grotesk",sans-serif;letter-spacing:.8px;text-transform:uppercase}
        .mdd-save{width:100%;margin-top:18px}
        .mdd-dialog{border:0;padding:0;width:min(620px,calc(100% - 32px));max-height:calc(100vh - 32px);border-radius:18px;background:transparent}
        .mdd-dialog::backdrop{background:rgba(10,20,40,.55);backdrop-filter:blur(5px)}
        .mdd-dialog-inner{background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 30px 80px rgba(8,20,40,.3);max-height:calc(100vh - 32px);display:flex;flex-direction:column}
        .mdd-dialog-head{display:flex;justify-content:space-between;align-items:flex-start;gap:14px;padding:22px 26px;background:linear-gradient(135deg,#112b49,#1a3d66);color:#fff}
        .mdd-dialog-head h3{margin:0;font:600 20px "Fraunces",Georgia,serif;color:#fff}
        .mdd-dialog-head p{margin:5px 0 0;color:rgba(255,255,255,.66);font-size:12px}
        .mdd-dialog-close{width:34px;height:34px;flex:0 0 34px;border:0;border-radius:50%;background:rgba(255,255,255,.12);color:#fff;font-size:20px;cursor:pointer;line-height:1;display:grid;place-items:center}
        .mdd-dialog-close:hover{background:#f7c948;color:#112b49}
        .mdd-dialog-body{padding:22px 26px;overflow:auto}
        .mdd-dialog-actions{display:flex;justify-content:flex-end;gap:10px;padding:16px 26px 22px;border-top:1px solid #eef2f6}
        .mdd-tabs{display:flex;gap:6px;padding:4px;background:#f1f5f9;border-radius:12px;margin-bottom:18px}
        .mdd-tab{flex:1;padding:10px;border:0;border-radius:9px;background:transparent;color:#41576f;font:700 12px "DM Sans",sans-serif;cursor:pointer}
        .mdd-tab.active{background:#112b49;color:#f7c948}
        .mdd-hint{color:#718096;font-size:11.5px;line-height:1.5;margin:2px 0 14px}
        .mdd-perms{display:grid;gap:8px;margin-bottom:14px}
        .mdd-check{display:flex;align-items:flex-start;gap:10px;padding:11px 13px;border:1px solid #e7eef5;border-radius:10px;background:#f7fafd;cursor:pointer}
        .mdd-check input{width:18px;height:18px;flex:0 0 18px;margin-top:1px;accent-color:#112b49}
        .mdd-check span{color:#172437;font:600 12.5px "DM Sans",sans-serif;line-height:1.35}
        .mdd-members{display:grid;gap:8px;margin-bottom:16px}
        .mdd-member{display:flex;justify-content:space-between;gap:10px;padding:11px 13px;border:1px solid #e7eef5;border-radius:10px;background:#f7fafd;font-size:12.5px}
        .mdd-member strong{color:#112b49}
        .mdd-member small{display:block;margin-top:2px;color:#718096;font-size:11px;overflow-wrap:anywhere}
        .mdd-subhead{margin:6px 0 12px;padding-top:14px;border-top:1px solid #eef2f6;color:#112b49;font:800 11px "Space Grotesk",sans-serif;letter-spacing:.7px;text-transform:uppercase}
        @media(max-width:1060px){.mdd-cols{grid-template-columns:1fr}.mdd-stats{grid-template-columns:1fr 1fr}}
        @media(max-width:720px){.mdd{padding:16px 14px 40px}.mdd-head{grid-template-columns:1fr;padding:22px 20px}.mdd-head-actions{justify-content:flex-start}.mdd-share{grid-template-columns:1fr}.mdd-grid2{grid-template-columns:1fr}}
    </style>

    <a href="{{ route('meetings.index') }}" class="mdd-back">&larr; Back to Dashboard</a>

    <header class="mdd-head">
        <div>
            <span class="mdd-eyebrow"><i></i>{{ $typeLabel }}</span>
            <h1>{{ $meeting->meeting_topic }}</h1>
            <div class="mdd-meta">
                <span class="mdd-chip code">ID {{ $meeting->meeting_code }}</span>
                <span class="mdd-chip"><b>&#128197;</b> {{ $meeting->start_date->format('D, M j, Y') }}</span>
                <span class="mdd-chip"><b>&#128337;</b> {{ $meeting->start_date->format('g:i A') }} &ndash; {{ $meeting->end_date->format('g:i A') }}</span>
                <span class="mdd-chip"><b>&#9203;</b> {{ trim($durationLabel) }}</span>
                <span class="mdd-status {{ $stats['status'] }}">{{ $statusLabel }}</span>
            </div>
        </div>
        <div class="mdd-head-actions">
            @if($meeting->canStart())
                <a class="mdd-btn start" href="{{ route('meetings.lobby', $meeting) }}">&#9654; Start Meeting</a>
            @else
                <button class="mdd-btn start" type="button" disabled style="opacity:.5;cursor:not-allowed">&#9654; {{ $stats['status'] === 'live' ? 'In progress' : 'Unavailable' }}</button>
            @endif
            <a class="mdd-btn gold" href="{{ route('meetings.edit', $meeting) }}">&#9998; Edit Meeting</a>
            <button class="mdd-btn ghost" type="button" data-copy="{{ $joinUrl }}">&#128279; Copy Meeting Link</button>
            @if($meeting->isCancelled())
                <form method="post" action="{{ route('meetings.reopen', $meeting) }}" style="margin:0">@csrf
                    <button class="mdd-btn ghost" type="submit">&#8635; Reopen Meeting</button>
                </form>
            @elseif($stats['status'] !== 'completed')
                <form method="post" action="{{ route('meetings.cancel', $meeting) }}" style="margin:0" data-confirm="Cancel this meeting? Invited participants will be notified.">@csrf
                    <button class="mdd-btn danger" type="submit">&#10005; Cancel Meeting</button>
                </form>
            @endif
        </div>
    </header>

    @if(session('meeting_created'))
        <div class="mdd-banner">
            <span class="tick">&#10003;</span>
            <div>Meeting Created Successfully<small>Your room is ready. Add companies, invite participants, and start when everyone is set.</small></div>
        </div>
    @endif

    <section class="mdd-stats" aria-label="Meeting overview">
        <div class="mdd-stat"><span class="mdd-stat-ico">&#127970;</span><span class="mdd-stat-label">Companies Invited</span><strong>{{ $stats['companies'] }}</strong><small>Organisations in this session</small></div>
        <div class="mdd-stat"><span class="mdd-stat-ico">&#128101;</span><span class="mdd-stat-label">Expected Participants</span><strong>{{ $stats['expected'] }}</strong><small>Invitations issued</small></div>
        <div class="mdd-stat accent"><span class="mdd-stat-ico">&#10003;</span><span class="mdd-stat-label">Accepted Invitations</span><strong>{{ $stats['accepted'] }}</strong><small>Confirmed or checked in</small></div>
        <div class="mdd-stat"><span class="mdd-stat-ico">&#128992;</span><span class="mdd-stat-label">Meeting Status</span><strong style="font-size:26px">{{ $statusLabel }}</strong><small>{{ $meeting->start_date->isFuture() ? 'Scheduled for '.$meeting->start_date->diffForHumans() : 'Scheduled time reached' }}</small></div>
    </section>

    <div class="mdd-cols">
        <div class="mdd-col">
            {{-- Invitation management --}}
            <section class="mdd-card">
                <div class="mdd-card-head">
                    <div><h2>Invitation Management</h2><p>Share the meeting link and email invitations to every participant.</p></div>
                </div>
                <div class="mdd-share">
                    <div>
                        <label>Meeting link<span>Anyone with this secure link can register and join this session.</span></label>
                        <input type="text" readonly value="{{ $joinUrl }}" id="mddJoinUrl" onclick="this.select()">
                    </div>
                    <div class="mdd-share-actions">
                        <button class="mdd-btn light sm" type="button" data-copy="{{ $joinUrl }}">&#128279; Copy link</button>
                        <button class="mdd-btn light sm" type="button" data-share="{{ $shareMessage }}">&#10150; Share</button>
                    </div>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between">
                    <span class="mdd-code"><span><small>Meeting ID</small>{{ $meeting->meeting_code }}</span></span>
                    <div style="display:flex;flex-wrap:wrap;gap:10px">
                        <form method="post" action="{{ route('meetings.invitations.remind', $meeting) }}" style="margin:0">
                            @csrf
                            <button class="mdd-btn light sm" type="submit">&#8635; Send Reminders</button>
                        </form>
                        <form method="post" action="{{ route('meetings.invitations.send', $meeting) }}" style="margin:0">
                            @csrf
                            <button class="mdd-btn gold sm" type="submit">&#9993; Send Invitations</button>
                        </form>
                    </div>
                </div>
            </section>

            {{-- Participating companies --}}
            @if($meeting->isMultiCompany())
                <section class="mdd-card">
                    <div class="mdd-card-head">
                        <div><h2>Participating Companies</h2><p>All companies join the same room and are grouped by company.</p></div>
                        <span class="mdd-count">{{ $companies->count() }} {{ \Illuminate\Support\Str::plural('company', $companies->count()) }}</span>
                    </div>

                    @forelse($companies as $company)
                        <article class="mdd-company">
                            <div class="mdd-company-top">
                                <span class="mdd-logo">@if($company->logo_path)<img src="{{ $company->logo_path }}" alt="">@else{{ $company->initial() }}@endif</span>
                                <div class="mdd-company-name">
                                    <h3>{{ $company->name }} @if($company->is_host_company)<span class="mdd-host-tag">Host</span>@endif</h3>
                                    <small>{{ $company->attendees->count() }} {{ \Illuminate\Support\Str::plural('participant', $company->attendees->count()) }}@if($company->contact_email) &middot; {{ $company->contact_email }}@endif</small>
                                </div>
                                <span class="mdd-pill {{ $company->aggregateInvitationTone() }}">{{ $company->aggregateInvitationLabel() }}</span>
                            </div>

                            {{-- Company invitation link --}}
                            <div class="mdd-company-actions" style="margin-top:10px;padding-top:10px;border-top:1px solid #eef2f6">
                                <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;flex:1">
                                    <span style="color:#718096;font-size:11.5px;letter-spacing:.4px;text-transform:uppercase;font-weight:600">Company link</span>
                                    @if($company->invitation_token)
                                        <span class="mdd-pill {{ $company->invitationTone() }}" style="margin:0">{{ $company->invitationLabel() }}</span>
                                        @if($company->invitation_delivery_status)
                                            <span class="mdd-pill {{ $company->invitation_delivery_status === 'sent' ? 'sent' : ($company->invitation_delivery_status === 'delivered' ? 'accepted' : ($company->invitation_delivery_status === 'failed' ? 'declined' : 'pending')) }}" style="margin:0;text-transform:capitalize">{{ $company->invitation_delivery_status }}</span>
                                            @if($company->invitation_delivery_status === 'failed' && $company->invitation_failure_reason)
                                                <span style="color:#a5382c;font-size:11px;margin-left:4px" title="{{ $company->invitation_failure_reason }}">Failed</span>
                                            @endif
                                        @endif
                                        <input type="text" readonly value="{{ $company->invitationUrl() }}" style="flex:1;min-width:200px;padding:6px 10px;border:1px solid #dbe3ea;border-radius:8px;background:#f8fafc;font-size:12px;font-family:monospace;color:#112b49" onclick="this.select()">
                                        <button class="mdd-btn light sm" type="button" data-copy="{{ $company->invitationUrl() }}">&#128279; Copy</button>
                                        <form method="post" action="{{ route('meetings.companies.invitation.issue', [$meeting, $company]) }}" style="margin:0">@csrf
                                            <button class="mdd-btn gold sm" type="submit">&#128260; Regenerate</button>
                                        </form>
                                        <form method="post" action="{{ route('meetings.companies.invitation.revoke', [$meeting, $company]) }}" style="margin:0" data-confirm="Revoke this company's invitation link?">@csrf @method('DELETE')
                                            <button class="mdd-btn light sm danger" type="submit">&#10005; Revoke</button>
                                        </form>
                                    @else
                                        <span class="mdd-pill pending" style="margin:0">No link yet</span>
                                        <form method="post" action="{{ route('meetings.companies.invitation.issue', [$meeting, $company]) }}" style="margin:0">@csrf
                                            <button class="mdd-btn gold sm" type="submit">&#128273; Issue Link</button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            {{-- Contact details & delivery status --}}
                            @if($company->contact_email || $company->contact_phone)
                            <div class="mdd-company-actions" style="margin-top:8px;padding-top:8px;border-top:1px solid #eef2f6">
                                <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;font-size:12px;color:#526176">
                                    @if($company->contact_email)
                                        <span>&#128231; {{ $company->contact_email }}</span>
                                    @endif
                                    @if($company->contact_phone)
                                        <span>&#128222; {{ $company->contact_phone }}</span>
                                    @endif
                                    @if($company->invitation_sent_at)
                                        <span style="color:#21508f">Sent {{ $company->invitation_sent_at->diffForHumans() }}</span>
                                    @endif
                                    @if($company->invitation_delivered_at)
                                        <span style="color:#1c6b3d">Delivered {{ $company->invitation_delivered_at->diffForHumans() }}</span>
                                    @endif
                                    @if($company->invitation_delivery_status === 'failed' && $company->invitation_retry_count > 0)
                                        <span style="color:#a5382c">Failed ({{ $company->invitation_retry_count }} retries)</span>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <div class="mdd-reps">
                                <div class="mdd-reps-title"><span>Invited Representatives</span><span>{{ $company->attendees->count() }}</span></div>
                                @forelse($company->attendees as $rep)
                                    <div class="mdd-rep">
                                        <span class="avatar">{{ strtoupper(substr($rep->full_name, 0, 1)) }}</span>
                                        <span class="who"><strong>{{ $rep->full_name }}</strong><small>{{ $rep->email }}</small></span>
                                        @if($rep->department)<span class="dept">{{ $rep->department }}</span>@endif
                                        <span class="state {{ $rep->invitationStateTone() }}">{{ $rep->invitationStateLabel() }}</span>
                                        @if($rep->requires_approval)
                                            <form method="post" action="{{ route('attendees.approve', $rep) }}" style="margin:0">@csrf
                                                <button class="approve" type="submit">Approve</button>
                                            </form>
                                            <form method="post" action="{{ route('attendees.reject', $rep) }}" style="margin:0" data-confirm="Reject {{ $rep->full_name }}'s request?">@csrf
                                                <button class="approve" type="submit" style="background:#fbe0e0;color:#a5382c">Reject</button>
                                            </form>
                                        @endif
                                    </div>
                                @empty
                                    <div class="mdd-reps-title" style="margin:0;color:#718096;font-weight:600;text-transform:none;letter-spacing:0">No representatives added yet.</div>
                                @endforelse
                            </div>

                            <div class="mdd-company-actions">
                                <button class="mdd-btn light sm" type="button" data-open="members-{{ $company->id }}">&#128101; View Members</button>
                                <button class="mdd-btn light sm" type="button" data-open="edit-{{ $company->id }}">&#9998; Edit Company</button>
                                <form method="post" action="{{ route('meetings.companies.invite', [$meeting, $company]) }}" style="margin:0">@csrf
                                    <button class="mdd-btn light sm" type="submit">&#9993; Send Invite</button>
                                </form>
                                <form method="post" action="{{ route('meetings.companies.remind', [$meeting, $company]) }}" style="margin:0">@csrf
                                    <button class="mdd-btn light sm" type="submit">&#8635; Remind</button>
                                </form>
                                <form method="post" action="{{ route('meetings.companies.destroy', [$meeting, $company]) }}" style="margin:0" data-confirm="Remove {{ $company->name }} from this meeting?">@csrf @method('DELETE')
                                    <button class="mdd-btn light sm" type="submit" style="color:#a5382c">&#10005; Remove</button>
                                </form>
                            </div>
                        </article>

                        {{-- Members dialog --}}
                        <dialog class="mdd-dialog" id="members-{{ $company->id }}">
                            <div class="mdd-dialog-inner">
                                <div class="mdd-dialog-head">
                                    <div><h3>{{ $company->name }} members</h3><p>{{ $company->attendees->count() }} invited representative(s)</p></div>
                                    <button class="mdd-dialog-close" type="button" data-close>&times;</button>
                                </div>
                                <div class="mdd-dialog-body">
                                    <div class="mdd-members">
                                        @forelse($company->attendees as $rep)
                                            <div class="mdd-member"><span><strong>{{ $rep->full_name }}</strong><small>{{ $rep->email }}@if($rep->phone) &middot; {{ $rep->phone }}@endif</small></span><span>{{ $rep->checked_in_at ? 'Checked in' : 'Invited' }}</span></div>
                                        @empty
                                            <div class="mdd-empty">No members yet.</div>
                                        @endforelse
                                    </div>
                                    <div class="mdd-subhead">Add a representative</div>
                                    <form method="post" action="{{ route('attendees.store', $meeting) }}">
                                        @csrf
                                        <input type="hidden" name="company_name" value="{{ $company->name }}">
                                        <div class="mdd-grid2">
                                            <label class="mdd-field"><span>Full name</span><input type="text" name="full_name" required maxlength="160"></label>
                                            <label class="mdd-field"><span>Email</span><input type="email" name="email" required maxlength="160"></label>
                                        </div>
                                        <div class="mdd-grid2">
                                            <label class="mdd-field"><span>Phone</span><input type="text" name="phone" maxlength="10" placeholder="e.g. 0712345678" required></label>
                                            <label class="mdd-field"><span>Department</span><input type="text" name="department" maxlength="160"></label>
                                        </div>
                                        <button class="mdd-btn gold sm" type="submit">+ Add representative</button>
                                    </form>
                                </div>
                            </div>
                        </dialog>

                        {{-- Edit company dialog --}}
                        <dialog class="mdd-dialog" id="edit-{{ $company->id }}">
                            <form method="post" action="{{ route('meetings.companies.update', [$meeting, $company]) }}">
                                @csrf @method('PATCH')
                                <div class="mdd-dialog-inner">
                                    <div class="mdd-dialog-head">
                                        <div><h3>Edit company</h3><p>Update the company profile and invitation status.</p></div>
                                        <button class="mdd-dialog-close" type="button" data-close>&times;</button>
                                    </div>
                                    <div class="mdd-dialog-body">
                                        <label class="mdd-field"><span>Company name</span><input type="text" name="name" value="{{ $company->name }}" required maxlength="160"></label>
                                        <div class="mdd-grid2">
                                            <label class="mdd-field"><span>Contact name</span><input type="text" name="contact_name" value="{{ $company->contact_name }}" maxlength="160"></label>
                                            <label class="mdd-field"><span>Official email</span><input type="email" name="contact_email" value="{{ $company->contact_email }}" maxlength="160"></label>
                                        </div>
                                        <div class="mdd-grid2">
                                            <label class="mdd-field"><span>Contact phone</span><input type="text" name="contact_phone" value="{{ $company->contact_phone }}" maxlength="40"></label>
                                            <label class="mdd-field"><span>Invitation status</span>
                                                <select name="status">
                                                    @foreach(\App\Models\MeetingCompany::STATUSES as $status)
                                                        <option value="{{ $status }}" @selected($company->status === $status)>{{ ucfirst($status) }}</option>
                                                    @endforeach
                                                </select>
                                            </label>
                                        </div>
                                        <label class="mdd-field"><span>Notes</span><textarea name="notes" rows="3" maxlength="2000">{{ $company->notes }}</textarea></label>
                                    </div>
                                    <div class="mdd-dialog-actions">
                                        <button class="mdd-btn light" type="button" data-close>Cancel</button>
                                        <button class="mdd-btn gold" type="submit">Save company</button>
                                    </div>
                                </div>
                            </form>
                        </dialog>
                    @empty
                        <div class="mdd-empty">No companies invited yet. Add the first company below.</div>
                    @endforelse

                    <button class="mdd-add" type="button" data-open="add-company">+ Add Company</button>
                </section>
            @else
                <section class="mdd-card">
                    <div class="mdd-card-head">
                        <div><h2>Participants</h2><p>Invite and manage the people joining this meeting.</p></div>
                        <span class="mdd-count">{{ $attendees->count() }} invited</span>
                    </div>
                    <div class="mdd-members">
                        @forelse($attendees as $person)
                            <div class="mdd-member"><span><strong>{{ $person->full_name }}</strong><small>{{ $person->email }}@if($person->phone) &middot; {{ $person->phone }}@endif</small></span><span>{{ $person->checked_in_at ? 'Checked in' : 'Invited' }}</span></div>
                        @empty
                            <div class="mdd-empty">No participants yet.</div>
                        @endforelse
                    </div>
                    <button class="mdd-add" type="button" data-open="add-company">+ Add Participant</button>
                </section>
            @endif

            {{-- Multi-company room logic --}}
            @if($meeting->isMultiCompany())
                <section class="mdd-card">
                    <div class="mdd-card-head"><div><h2>Multi-Company Room Logic</h2><p>Every company joins one shared session &mdash; never separate rooms.</p></div></div>
                    <div class="mdd-note"><strong>Grouped participants.</strong> Inside the live room, participants are automatically grouped under their verified company with a live count and collapsible list. The host can see all companies, manage individual participants, and monitor attendance. Anyone who opens the shared link without an invite appears under &ldquo;Guests&rdquo;.</div>
                </section>
            @endif
        </div>

        <div class="mdd-col">
            {{-- Meeting configuration --}}
            <section class="mdd-card">
                <div class="mdd-card-head"><div><h2>Meeting Settings</h2><p>Security, permissions and device defaults.</p></div></div>
                <form method="post" action="{{ route('meetings.settings.update', $meeting) }}">
                    @csrf
                    <label class="mdd-field"><span>Meeting password</span><input type="text" name="password" value="{{ $settings['password'] }}" maxlength="60" placeholder="Optional access code"></label>

                    <div class="mdd-toggle">
                        <span>Waiting room<small>Admit participants before they join</small></span>
                        <label class="mdd-switch"><input type="checkbox" name="waiting_room" value="1" @checked($settings['waiting_room'])><i></i></label>
                    </div>

                    <p class="mdd-group-title">Participant permissions</p>
                    <div class="mdd-toggle">
                        <span>Allow self-unmute<small>Participants can unmute themselves</small></span>
                        <label class="mdd-switch"><input type="checkbox" name="allow_participant_unmute" value="1" @checked($settings['allow_participant_unmute'])><i></i></label>
                    </div>
                    <div class="mdd-toggle">
                        <span>Allow camera<small>Participants can turn on video</small></span>
                        <label class="mdd-switch"><input type="checkbox" name="allow_participant_camera" value="1" @checked($settings['allow_participant_camera'])><i></i></label>
                    </div>
                    <div class="mdd-toggle">
                        <span>Screen sharing<small>Participants can share their screen</small></span>
                        <label class="mdd-switch"><input type="checkbox" name="allow_screen_share" value="1" @checked($settings['allow_screen_share'])><i></i></label>
                    </div>
                    <div class="mdd-toggle">
                        <span>Chat<small>Participants can use meeting chat</small></span>
                        <label class="mdd-switch"><input type="checkbox" name="allow_chat" value="1" @checked($settings['allow_chat'])><i></i></label>
                    </div>

                    <p class="mdd-group-title">Device defaults</p>
                    <div class="mdd-toggle">
                        <span>Microphone on by default<small>Join with the mic enabled</small></span>
                        <label class="mdd-switch"><input type="checkbox" name="default_mic_on" value="1" @checked($settings['default_mic_on'])><i></i></label>
                    </div>
                    <div class="mdd-toggle">
                        <span>Camera on by default<small>Join with video enabled</small></span>
                        <label class="mdd-switch"><input type="checkbox" name="default_camera_on" value="1" @checked($settings['default_camera_on'])><i></i></label>
                    </div>

                    <p class="mdd-group-title">Host &amp; recording</p>
                    <label class="mdd-field"><span>Co-host</span>
                        <select name="co_host">
                            <option value="">No co-host</option>
                            @foreach($coHostCandidates as $candidate)
                                <option value="{{ $candidate->full_name }}" @selected($settings['co_host'] === $candidate->full_name)>{{ $candidate->full_name }}@if($candidate->meetingCompany) &middot; {{ $candidate->meetingCompany->name }}@endif</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="mdd-toggle">
                        <span>Allow recording<small>Host can record the session</small></span>
                        <label class="mdd-switch"><input type="checkbox" name="allow_recording" value="1" @checked($settings['allow_recording'])><i></i></label>
                    </div>

                    <p class="mdd-group-title">Waiting room &amp; host controls</p>
                    <label class="mdd-field"><span>Waiting room message<small>Shown to people while they wait</small></span>
                        <input type="text" name="waiting_room_message" value="{{ $settings['waiting_room_message'] }}" maxlength="280" placeholder="Please wait, the host will admit you shortly.">
                    </label>
                    <div class="mdd-toggle">
                        <span>Mute participants on entry<small>Join everyone muted to keep the room quiet</small></span>
                        <label class="mdd-switch"><input type="checkbox" name="mute_on_entry" value="1" @checked($settings['mute_on_entry'])><i></i></label>
                    </div>
                    <div class="mdd-toggle">
                        <span>Allow raised hands<small>Participants can signal the host</small></span>
                        <label class="mdd-switch"><input type="checkbox" name="allow_participant_hand" value="1" @checked($settings['allow_participant_hand'])><i></i></label>
                    </div>

                    <p class="mdd-group-title">Co-host permissions</p>
                    <p class="mdd-hint">Capabilities granted to the selected co-host. The host always has full control.</p>
                    <div class="mdd-perms">
                        @foreach(\App\Models\OnlineSessionParticipant::CO_HOST_PERMISSIONS as $permission => $permissionLabel)
                            <label class="mdd-check">
                                <input type="checkbox" name="co_host_permissions[]" value="{{ $permission }}" @checked(in_array($permission, $settings['co_host_permissions'] ?? [], true))>
                                <span>{{ $permissionLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                    <label class="mdd-field" style="margin-top:13px"><span>Maximum participants</span><input type="number" name="max_participants" min="1" max="10000" value="{{ $settings['max_participants'] }}" placeholder="Unlimited"></label>
                    <div class="mdd-toggle">
                        <span>Allow company self-invitations<small>Representatives can add colleagues (host approval required)</small></span>
                        <label class="mdd-switch"><input type="checkbox" name="company_self_invite" value="1" @checked($settings['company_self_invite'] ?? false)><i></i></label>
                    </div>

                    <button class="mdd-btn gold mdd-save" type="submit">Save settings</button>
                </form>
            </section>

            {{-- Guest & mismatch approvals --}}
            @if($meeting->isMultiCompany())
                @php
                    $pendingApprovals = \App\Models\ParticipantApproval::with(['attendee', 'requestedCompany', 'currentCompany'])
                        ->where('meeting_id', $meeting->id)
                        ->where('status', \App\Models\ParticipantApproval::STATUS_PENDING)
                        ->orderBy('created_at', 'desc')
                        ->get();
                @endphp
                @if($pendingApprovals->isNotEmpty())
                <section class="mdd-card">
                    <div class="mdd-card-head"><div><h2>Guest & Mismatch Approvals</h2><p>Company link guests and company-switch requests awaiting your decision.</p></div></div>
                    <div class="mdd-members">
                        @foreach($pendingApprovals as $approval)
                            <div class="mdd-member" style="flex-wrap:wrap">
                                <span>
                                    <strong>{{ $approval->display_name ?: 'Unnamed guest' }}</strong>
                                    <small>{{ $approval->email }} @if($approval->type === 'mismatch')&middot; Requested: {{ $approval->requestedCompany?->name ?: 'Unknown' }} (currently {{ $approval->currentCompany?->name ?: 'unassigned' }}) @else &middot; Guest request @endif</small>
                                </span>
                                <span style="display:flex;gap:8px;flex-wrap:wrap">
                                    <form method="post" action="{{ route('approvals.approve', $approval) }}" style="margin:0">@csrf
                                        <button class="mdd-btn light sm" type="submit" style="background:#dfeafa;color:#21508f">&#10003; Approve</button>
                                    </form>
                                    <form method="post" action="{{ route('approvals.reject', $approval) }}" style="margin:0" data-confirm="Reject this request?">@csrf
                                        <button class="mdd-btn light sm" type="submit" style="color:#a5382c">&#10005; Reject</button>
                                    </form>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </section>
                @endif
            @endif

            <section class="mdd-card">
                <div class="mdd-card-head"><div><h2>Meeting Details</h2><p>Host and scheduling summary.</p></div></div>
                <div class="mdd-members">
                    <div class="mdd-member"><span><strong>Host</strong><small>{{ $meeting->name }} &middot; {{ $meeting->email }}</small></span></div>
                    <div class="mdd-member"><span><strong>Type</strong><small>{{ $typeLabel }}</small></span></div>
                    <div class="mdd-member"><span><strong>Start</strong><small>{{ $meeting->start_date->format('D, M j, Y g:i A') }}</small></span></div>
                    <div class="mdd-member"><span><strong>End</strong><small>{{ $meeting->end_date->format('D, M j, Y g:i A') }}</small></span></div>
                    @if($meeting->room)<div class="mdd-member"><span><strong>Room</strong><small>{{ $meeting->room->name }} &middot; {{ $meeting->room->number }}</small></span></div>@endif
                    @if($meeting->description)<div class="mdd-member"><span><strong>Description</strong><small>{{ $meeting->description }}</small></span></div>@endif
                </div>
            </section>
        </div>
    </div>

    {{-- Add company / participant dialog --}}
    <dialog class="mdd-dialog" id="add-company">
        <form method="post" action="{{ route('meetings.companies.store', $meeting) }}">
            @csrf
            <div class="mdd-dialog-inner">
                <div class="mdd-dialog-head">
                    <div><h3>{{ $meeting->isMultiCompany() ? 'Add a company' : 'Add a participant' }}</h3><p>Search a registered company or invite a new one with its details.</p></div>
                    <button class="mdd-dialog-close" type="button" data-close>&times;</button>
                </div>
                <div class="mdd-dialog-body">
                    @if($meeting->isMultiCompany())
                        <div class="mdd-tabs">
                            <button class="mdd-tab active" type="button" data-tab="existing">Search existing</button>
                            <button class="mdd-tab" type="button" data-tab="new">Invite new company</button>
                        </div>
                    @endif
                    <label class="mdd-field">
                        <span>Company / department name</span>
                        <input type="text" name="name" id="mddCompanyName" list="mddCompanyList" required maxlength="160" autocomplete="off" placeholder="e.g. Bright Future Ltd">
                        <datalist id="mddCompanyList"></datalist>
                    </label>
                    <p class="mdd-hint" id="mddExistingHint">Start typing to search companies already registered on AykonMeet.</p>

                    <p class="mdd-subhead">Company contact (optional)</p>
                    <div class="mdd-grid2">
                        <label class="mdd-field"><span>Contact name</span><input type="text" name="contact_name" maxlength="160"></label>
                        <label class="mdd-field"><span>Official email</span><input type="email" name="contact_email" maxlength="160"></label>
                    </div>
                    <label class="mdd-field"><span>Contact phone</span><input type="text" name="contact_phone" maxlength="40"></label>

                    <p class="mdd-subhead">Representative (optional)</p>
                    <div class="mdd-grid2">
                        <label class="mdd-field"><span>Full name</span><input type="text" name="rep_name" maxlength="160"></label>
                        <label class="mdd-field"><span>Email</span><input type="email" name="rep_email" maxlength="160"></label>
                    </div>
                    <div class="mdd-grid2">
                        <label class="mdd-field"><span>Phone</span><input type="text" name="rep_phone" maxlength="40"></label>
                        <label class="mdd-field"><span>Department</span><input type="text" name="rep_department" maxlength="160"></label>
                    </div>
                </div>
                <div class="mdd-dialog-actions">
                    <button class="mdd-btn light" type="button" data-close>Cancel</button>
                    <button class="mdd-btn gold" type="submit">{{ $meeting->isMultiCompany() ? '+ Add company' : '+ Add participant' }}</button>
                </div>
            </div>
        </form>
    </dialog>
</div>

<script>
(function () {
    document.querySelectorAll('[data-copy]').forEach(function (button) {
        button.addEventListener('click', async function () {
            var value = button.getAttribute('data-copy');
            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(value);
                } else {
                    var temp = document.createElement('textarea');
                    temp.value = value;
                    document.body.appendChild(temp);
                    temp.select();
                    document.execCommand('copy');
                    temp.remove();
                }
                var original = button.innerHTML;
                button.innerHTML = '&#10003; Copied';
                setTimeout(function () { button.innerHTML = original; }, 1800);
            } catch (error) {
                window.prompt('Copy meeting link:', value);
            }
        });
    });

    document.querySelectorAll('[data-share]').forEach(function (button) {
        button.addEventListener('click', async function () {
            var text = button.getAttribute('data-share');
            if (navigator.share) {
                try { await navigator.share({ title: document.title, text: text }); return; } catch (error) { return; }
            }
            if (navigator.clipboard) { await navigator.clipboard.writeText(text); }
            var original = button.innerHTML;
            button.innerHTML = '&#10003; Copied';
            setTimeout(function () { button.innerHTML = original; }, 1800);
        });
    });

    document.querySelectorAll('[data-open]').forEach(function (button) {
        button.addEventListener('click', function () {
            var dialog = document.getElementById(button.getAttribute('data-open'));
            if (dialog) dialog.showModal();
        });
    });

    document.querySelectorAll('[data-close]').forEach(function (button) {
        button.addEventListener('click', function () {
            var dialog = button.closest('dialog');
            if (dialog) dialog.close();
        });
    });

    document.querySelectorAll('dialog.mdd-dialog').forEach(function (dialog) {
        dialog.addEventListener('click', function (event) {
            if (event.target === dialog) dialog.close();
        });
    });

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.getAttribute('data-confirm'))) event.preventDefault();
        });
    });

    document.querySelectorAll('.mdd-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.mdd-tab').forEach(function (item) { item.classList.remove('active'); });
            tab.classList.add('active');
            var mode = tab.getAttribute('data-tab');
            var hint = document.getElementById('mddExistingHint');
            if (hint) hint.style.display = mode === 'existing' ? '' : 'none';
        });
    });

    var companyInput = document.getElementById('mddCompanyName');
    var list = document.getElementById('mddCompanyList');
    var searchUrl = @json(route('meetings.companies.search'));
    if (companyInput && list) {
        var timer = null;
        companyInput.addEventListener('input', function () {
            var term = companyInput.value.trim();
            clearTimeout(timer);
            if (term.length < 2) { list.innerHTML = ''; return; }
            timer = setTimeout(function () {
                fetch(searchUrl + '?q=' + encodeURIComponent(term), { headers: { 'Accept': 'application/json' } })
                    .then(function (response) { return response.ok ? response.json() : { companies: [] }; })
                    .then(function (data) {
                        list.innerHTML = '';
                        (data.companies || []).forEach(function (name) {
                            var option = document.createElement('option');
                            option.value = name;
                            list.appendChild(option);
                        });
                    })
                    .catch(function () {});
            }, 220);
        });
    }
})();
</script>
@endsection
