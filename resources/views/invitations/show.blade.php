@extends('layouts.app')

@section('title', 'You are invited · '.$meeting->meeting_topic)

@section('content')
@php
    $typeLabel = match (true) {
        $meeting->isHybrid() => 'Hybrid meeting',
        $meeting->is_online => 'Online meeting',
        default => 'In-person meeting',
    };
    $location = $meeting->is_online && ! $meeting->isHybrid()
        ? 'Online'
        : ($meeting->physical_location ?: $meeting->from_location ?: 'To be advised');
    $initials = fn ($name) => strtoupper(substr(preg_replace('/[^A-Za-z]/', '', (string) $name) ?: 'A', 0, 1));
@endphp
<div class="inv">
    <style>
        .inv{max-width:1040px;margin:0 auto;padding:30px 22px 60px;font-family:"DM Sans",sans-serif;color:#112b49}
        .inv *{box-sizing:border-box}
        .inv-hero{position:relative;padding:38px 40px;border-radius:24px;overflow:hidden;color:#fff;background:radial-gradient(ellipse at 88% -10%,rgba(247,201,72,.30),transparent 55%),linear-gradient(140deg,#0b1f38,#112b49 58%,#173a63);box-shadow:0 26px 60px rgba(8,22,42,.30)}
        .inv-hero::after{content:"";position:absolute;width:320px;height:320px;border-radius:50%;background:rgba(247,201,72,.14);filter:blur(80px);top:-120px;right:-60px}
        .inv-eyebrow{position:relative;z-index:2;display:inline-flex;align-items:center;gap:9px;font:800 11px "Space Grotesk",sans-serif;letter-spacing:3px;text-transform:uppercase;color:#f7c948}
        .inv-eyebrow i{width:8px;height:8px;border-radius:50%;background:#f7c948;box-shadow:0 0 0 4px rgba(247,201,72,.25)}
        .inv-hero h1{position:relative;z-index:2;margin:16px 0 8px;color:#fff;font:600 clamp(28px,4vw,46px)/1.05 "Fraunces",Georgia,serif;letter-spacing:-.6px;max-width:760px}
        .inv-hero p.lead{position:relative;z-index:2;margin:0;color:#c9d6e6;font-size:14.5px;max-width:620px}
        .inv-hero p.lead b{color:#f7c948}
        .inv-status{position:relative;z-index:2;display:inline-flex;align-items:center;gap:7px;margin-top:22px;padding:9px 15px;border-radius:999px;font:800 11px "Space Grotesk",sans-serif;text-transform:uppercase;letter-spacing:.6px}
        .inv-status.pending{background:rgba(247,201,72,.18);color:#f7c948;border:1px solid rgba(247,201,72,.4)}
        .inv-status.accepted{background:#c9f0d6;color:#1c6b3d}
        .inv-status.declined{background:#f7c6c6;color:#8f2222}
        .inv-grid{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(280px,1fr);gap:22px;margin-top:24px;align-items:start}
        .inv-card{padding:26px 28px;border-radius:20px;background:#fff;border:1px solid #e4eaf1;box-shadow:0 14px 34px rgba(17,43,73,.06)}
        .inv-card h2{margin:0 0 4px;font:700 18px "Space Grotesk",sans-serif}
        .inv-card .sub{margin:0 0 20px;color:#6b7d91;font-size:12.5px}
        .inv-facts{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
        .inv-fact{padding:15px 16px;border:1px solid #e8eef5;border-radius:14px;background:#f8fafc}
        .inv-fact span{display:block;color:#718096;font:800 10px "Space Grotesk",sans-serif;letter-spacing:1.1px;text-transform:uppercase}
        .inv-fact strong{display:block;margin-top:7px;color:#112b49;font:700 15px "DM Sans",sans-serif}
        .inv-fact small{display:block;margin-top:3px;color:#6b7d91;font-size:11.5px}
        .inv-code{display:inline-flex;align-items:center;gap:9px;margin-top:6px;padding:8px 12px;border-radius:10px;background:#112b49;color:#f7c948;font:800 14px "Space Grotesk",sans-serif;letter-spacing:.12em}
        .inv-companies{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:10px}
        .inv-company{display:flex;align-items:center;gap:12px;padding:12px 14px;border:1px solid #e8eef5;border-radius:13px;background:#f8fafc}
        .inv-company.mine{border-color:#f0b420;background:#fffaf0}
        .inv-logo{display:grid;place-items:center;width:38px;height:38px;border-radius:10px;background:#112b49;color:#f7c948;font:800 15px "Space Grotesk",sans-serif;flex:0 0 38px}
        .inv-logo img{width:100%;height:100%;object-fit:cover;border-radius:10px}
        .inv-company-info{min-width:0;flex:1}
        .inv-company-info strong{display:block;font-size:13.5px;color:#112b49;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .inv-company-info small{display:block;color:#6b7d91;font-size:11px;margin-top:1px}
        .inv-tag{font:800 9.5px "Space Grotesk",sans-serif;letter-spacing:.5px;text-transform:uppercase;padding:4px 9px;border-radius:999px;white-space:nowrap}
        .inv-tag.host{background:#112b49;color:#f7c948}
        .inv-tag.mine{background:#f7c948;color:#112b49}
        .inv-actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:22px}
        .inv-btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;height:50px;padding:0 26px;border:0;border-radius:13px;font:700 14px "Space Grotesk",sans-serif;cursor:pointer;text-decoration:none;transition:transform .16s ease,box-shadow .16s ease}
        .inv-btn:hover{transform:translateY(-1px)}
        .inv-btn.primary{background:linear-gradient(135deg,#2ecc71,#239b56);color:#fff;box-shadow:0 12px 28px rgba(46,204,113,.34)}
        .inv-btn.gold{background:linear-gradient(135deg,#f7c948,#f0b420);color:#112b49;box-shadow:0 12px 28px rgba(247,201,72,.4)}
        .inv-btn.ghost{background:#fff;color:#8f2222;border:1px solid #f0c9c9}
        .inv-btn.ghost:hover{background:#fdf3f3}
        .inv-btn.full{width:100%}
        .inv-note{margin-top:16px;padding:13px 15px;border-radius:12px;background:#f8fafc;border:1px solid #e8eef5;color:#6b7d91;font-size:12px;line-height:1.55}
        .inv-note b{color:#112b49}
        .inv-flash{margin-bottom:18px;padding:15px 18px;border:1px solid #bfe6cc;border-left:4px solid #2ecc71;border-radius:14px;background:#eefaf1;color:#1c6b3d;font:700 13.5px "DM Sans",sans-serif}
        .inv-alert{margin-bottom:18px;padding:14px 16px;border:1px solid #f0d489;border-left:4px solid #f0b420;border-radius:14px;background:#fffaf0;color:#8a6a00;font:600 13px "DM Sans",sans-serif}
        @media(max-width:820px){.inv-grid{grid-template-columns:1fr}.inv-facts{grid-template-columns:1fr}.inv-hero{padding:30px 24px}}
    </style>

    @if(session('invitation_success'))
        <div class="inv-flash">✓ {{ session('invitation_success') }}</div>
    @endif
    @if($joinState === 'waiting' && $status === 'accepted')
        <div class="inv-alert">⏳ The host has not opened the room yet. You can join as soon as the meeting starts.</div>
    @endif

    <div class="inv-hero">
        <span class="inv-eyebrow"><i></i> You are invited</span>
        <h1>{{ $meeting->meeting_topic }}</h1>
        <p class="lead">Hello <b>{{ $attendee->full_name }}</b>, <b>{{ $invitingCompany }}</b> has invited you to join this meeting{{ $myCompany ? ' on behalf of '.$myCompany->name : '' }}.</p>
        <span class="inv-status {{ $status }}">{{ $statusLabel }}</span>
    </div>

    <div class="inv-grid">
        <div>
            <div class="inv-card">
                <h2>Meeting details</h2>
                <p class="sub">Everything you need to plan your attendance.</p>
                <div class="inv-facts">
                    <div class="inv-fact"><span>Date</span><strong>{{ $meeting->start_date->format('l, F j, Y') }}</strong><small>{{ $meeting->start_date->format('g:i A') }} – {{ $meeting->end_date->format('g:i A') }}</small></div>
                    <div class="inv-fact"><span>Duration</span><strong>{{ $durationLabel ?: 'To be confirmed' }}</strong><small>{{ $typeLabel }}</small></div>
                    <div class="inv-fact"><span>Location</span><strong>{{ $location }}</strong><small>{{ $meeting->is_online ? 'Join from anywhere' : 'Please arrive on time' }}</small></div>
                    <div class="inv-fact"><span>Meeting code</span><div class="inv-code">{{ $meeting->meeting_code }}</div></div>
                </div>
                @if($meeting->description)
                    <div class="inv-note"><b>About this meeting:</b> {{ $meeting->description }}</div>
                @endif
            </div>
        </div>

        <div>
            <div class="inv-card">
                <h2>Participating companies</h2>
                <p class="sub">Who is taking part in this meeting.</p>
                <ul class="inv-companies">
                    @foreach($companies as $company)
                        <li class="inv-company {{ $company['is_mine'] ? 'mine' : '' }}">
                            <span class="inv-logo">
                                @if($company['logo'])
                                    <img src="{{ asset($company['logo']) }}" alt="">
                                @else
                                    {{ $initials($company['name']) }}
                                @endif
                            </span>
                            <span class="inv-company-info">
                                <strong>{{ $company['name'] }}</strong>
                                <small>{{ $company['is_host'] ? 'Organizer' : 'Participant company' }}</small>
                            </span>
                            @if($company['is_host'])
                                <span class="inv-tag host">Host</span>
                            @elseif($company['is_mine'])
                                <span class="inv-tag mine">You</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="inv-card" style="margin-top:22px">
                <h2>Your response</h2>
                <p class="sub">Let the organizer know if you can attend.</p>
                <form method="post" action="{{ route('invitations.accept', ['attendee' => $attendee->invitation_token]) }}">
                    @csrf
                    <button type="submit" class="inv-btn primary full">✓ Accept invitation</button>
                </form>
                <form method="post" action="{{ route('invitations.decline', ['attendee' => $attendee->invitation_token]) }}" style="margin-top:12px">
                    @csrf
                    <button type="submit" class="inv-btn ghost full">Decline</button>
                </form>
                <div class="inv-note">Your response is recorded securely against your invitation for <b>{{ $attendee->email }}</b>.</div>
            </div>
        </div>
    </div>
</div>
@endsection
