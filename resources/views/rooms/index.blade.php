@extends('layouts.app')

@section('content')
<style>
.rm-hero{position:relative;overflow:hidden;border-radius:20px;background:radial-gradient(ellipse at 85% 0%,rgba(247,201,72,.22),transparent 42%),linear-gradient(135deg,#0b1f38 0%,#112b49 48%,#1a3d66 100%);color:#fff;padding:44px 48px;display:grid;grid-template-columns:minmax(0,1.15fr) minmax(0,1fr);gap:44px;align-items:center;margin-bottom:40px;box-shadow:0 24px 60px rgba(17,43,73,.25)}
.rm-hero::before{content:"";position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:46px 46px;mask-image:radial-gradient(ellipse at 80% 50%,black 5%,transparent 75%);pointer-events:none}
.rm-kicker{position:relative;z-index:2;display:inline-flex;align-items:center;gap:9px;font-family:'Space Grotesk',sans-serif;font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:#f7c948;margin-bottom:18px}
.rm-kicker .dot{width:7px;height:7px;border-radius:50%;background:#f7c948;box-shadow:0 0 0 4px rgba(247,201,72,.25)}
.rm-hero h1{position:relative;z-index:2;margin:0 0 14px;font:500 clamp(34px,3.6vw,50px)/1.04 'Fraunces',Georgia,serif;letter-spacing:-1.4px;color:#fff}
.rm-hero h1 em{font-style:italic;color:#f7c948}
.rm-hero .rm-sub{position:relative;z-index:2;margin:0;max-width:460px;color:rgba(255,255,255,.75);font-size:15px;line-height:1.65}
.rm-actions{position:relative;z-index:2;display:flex;flex-wrap:wrap;gap:12px;margin-top:26px}
.rm-btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;height:50px;padding:0 26px;border-radius:12px;border:0;font:700 14px 'DM Sans',sans-serif;cursor:pointer;text-decoration:none;transition:transform .18s ease,box-shadow .18s ease,background .18s ease}
.rm-btn span{font-size:17px;transition:transform .15s}
.rm-btn:hover span{transform:translateX(4px)}
.rm-btn-gold{background:#f7c948;color:#112b49;box-shadow:0 12px 30px rgba(247,201,72,.3)}
.rm-btn-gold:hover{transform:translateY(-2px);background:#ffd766;box-shadow:0 16px 36px rgba(247,201,72,.4)}
.rm-btn-ghost{background:rgba(255,255,255,.08);color:#fff;border:1.5px solid rgba(255,255,255,.24)}
.rm-btn-ghost:hover{background:rgba(255,255,255,.16);transform:translateY(-2px)}
.rm-tiles{position:relative;z-index:2;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.rm-tile{background:rgba(255,255,255,.09);border:1px solid rgba(255,255,255,.16);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border-radius:16px;padding:18px 18px 16px}
.rm-tile b{display:block;font:700 34px/1 'Space Grotesk',sans-serif;color:#f7c948;margin-bottom:8px}
.rm-tile span{display:block;font-size:10px;font-weight:800;letter-spacing:1.1px;text-transform:uppercase;color:rgba(255,255,255,.72)}
.rm-tile small{display:block;margin-top:4px;font-size:11px;color:rgba(255,255,255,.5)}
.rm-tile.gold{background:linear-gradient(135deg,rgba(247,201,72,.95),rgba(247,201,72,.82));border-color:rgba(247,201,72,.6)}
.rm-tile.gold b{color:#112b49}
.rm-tile.gold span,.rm-tile.gold small{color:#112b49}
.rm-tile.gold small{opacity:.7}
.rm-section{display:flex;justify-content:space-between;align-items:center;gap:18px;padding-bottom:14px;border-bottom:2px solid #eef2f6;position:relative;margin-bottom:20px}
.rm-section::after{content:"";position:absolute;left:0;bottom:-2px;width:60px;height:3px;background:#f7c948;border-radius:3px}
.rm-section .rm-eyebrow{color:#c49700;font-size:11px;font-weight:800;letter-spacing:2px;text-transform:uppercase;margin:0 0 6px}
.rm-section h2{margin:0;color:#112b49;font:700 26px/1.15 'Space Grotesk',sans-serif;letter-spacing:-.5px}
.rm-count{flex:none;background:#fff9e7;border:1px solid #f3e2ae;color:#8a6600;padding:7px 14px;border-radius:999px;font-size:12px;font-weight:800;letter-spacing:.3px}
.rm-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(330px,1fr));gap:16px}
.rm-card{position:relative;display:flex;gap:16px;background:#fff;border:1px solid #e4eaf1;border-radius:16px;padding:20px 22px;overflow:hidden;transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease}
.rm-card::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;background:linear-gradient(to bottom,#112b49,#1a3d66);opacity:0;transition:opacity .18s ease}
.rm-card:hover{transform:translateY(-3px);box-shadow:0 18px 40px rgba(17,43,73,.14);border-color:#f7c948}
.rm-card:hover::before{opacity:1}
.rm-card-icon{flex:none;display:grid;place-items:center;width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#112b49,#1a3d66);color:#f7c948;font-size:24px;box-shadow:0 8px 18px rgba(17,43,73,.25)}
.rm-card-body{flex:1;min-width:0}
.rm-card-top{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
.rm-card-top h2{margin:0;color:#112b49;font:600 18px 'Space Grotesk',sans-serif;letter-spacing:-.2px}
.rm-pill{flex:none;background:#e8f6ec;border:1px solid #bfe5cb;color:#267344;font-size:9px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;padding:4px 9px;border-radius:999px}
.rm-meta{margin:8px 0 0;color:#718096;font-size:12.5px;font-weight:600}
.rm-loc{margin:5px 0 0;color:#8b98a8;font-size:12px;line-height:1.5}
.rm-card-foot{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:14px;padding-top:12px;border-top:1px dashed #e4eaf1}
.rm-meetings{display:inline-flex;align-items:center;gap:7px;color:#112b49;font-size:12px;font-weight:700}
.rm-meetings .tick{display:grid;place-items:center;width:20px;height:20px;border-radius:6px;background:#fff9e7;color:#b18800;font-size:11px;font-weight:800}
.rm-floor{color:#98a5b5;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase}
.rm-empty{grid-column:1/-1;background:#fff;border:1.5px dashed #d5dde7;border-radius:16px;padding:52px 24px;text-align:center}
.rm-empty .rm-empty-icon{display:grid;place-items:center;width:64px;height:64px;margin:0 auto 16px;border-radius:18px;background:#fff9e7;color:#b18800;font-size:28px}
.rm-empty strong{display:block;color:#112b49;font:600 19px 'Space Grotesk',sans-serif;margin-bottom:8px}
.rm-empty span{display:block;color:#718096;font-size:13.5px;max-width:380px;margin:0 auto 22px;line-height:1.6}
.rm-footer{display:flex;justify-content:flex-end;margin-top:26px}
.rm-link{display:inline-flex;align-items:center;gap:8px;height:46px;padding:0 22px;border-radius:12px;background:#112b49;color:#f7c948;font:700 13.5px 'DM Sans',sans-serif;text-decoration:none;transition:all .18s ease;box-shadow:0 8px 20px rgba(17,43,73,.2)}
.rm-link:hover{transform:translateY(-2px);background:#1a3d66;box-shadow:0 12px 26px rgba(17,43,73,.28)}
@media(max-width:980px){.rm-hero{grid-template-columns:1fr;padding:34px 30px;gap:30px}}
@media(max-width:640px){.rm-hero{padding:28px 22px}.rm-tiles{grid-template-columns:1fr 1fr}.rm-grid{grid-template-columns:1fr}.rm-section{flex-direction:column;align-items:flex-start}}
</style>

<header class="rm-hero">
    <div class="rm-hero-copy">
        <p class="rm-kicker"><span class="dot"></span>Admin console / Rooms</p>
        <h1>Your spaces,<br><em>ready to meet.</em></h1>
        <p class="rm-sub">Add meeting rooms and keep track of the spaces available to your team.</p>
        <div class="rm-actions">
            <button class="rm-btn rm-btn-gold" type="button" onclick="document.getElementById('add-room-dialog').showModal()">+ Add room <span>→</span></button>
            <a class="rm-btn rm-btn-ghost" href="{{ route('meetings.index') }}">Meetings console</a>
        </div>
    </div>
    <div class="rm-tiles" aria-label="Workspace overview">
        <div class="rm-tile gold"><b>{{ $stats['rooms'] }}</b><span>Rooms</span><small>Ready to book</small></div>
        <div class="rm-tile"><b>{{ $stats['today'] }}</b><span>Today</span><small>Meetings scheduled</small></div>
        <div class="rm-tile"><b>{{ $stats['upcoming'] }}</b><span>Upcoming</span><small>On the calendar</small></div>
        <div class="rm-tile"><b>{{ $stats['scheduled'] }}</b><span>Total</span><small>Non-cancelled</small></div>
    </div>
</header>

<section class="rm-section" id="workspace">
    <div>
        <p class="rm-eyebrow">Workspace</p>
        <h2>Available rooms</h2>
    </div>
    <span class="rm-count">{{ $rooms->count() }} {{ \Illuminate\Support\Str::plural('room', $rooms->count()) }}</span>
</section>

<div class="rm-grid">
    @forelse($rooms as $room)
        <article class="rm-card">
            <div class="rm-card-icon">⌂</div>
            <div class="rm-card-body">
                <div class="rm-card-top">
                    <h2>{{ $room->name }}</h2>
                    <span class="rm-pill">Available</span>
                </div>
                <p class="rm-meta">Room {{ $room->number }} · Floor {{ $room->floor }}</p>
                @if($room->location)<p class="rm-loc">{{ $room->location }}</p>@endif
                <div class="rm-card-foot">
                    <span class="rm-meetings"><span class="tick">{{ $room->meetings_count }}</span> {{ \Illuminate\Support\Str::plural('meeting', $room->meetings_count) }} scheduled</span>
                    <span class="rm-floor">Floor {{ $room->floor }}</span>
                </div>
            </div>
        </article>
    @empty
        <div class="rm-empty">
            <div class="rm-empty-icon">⌂</div>
            <strong>No rooms added yet.</strong>
            <span>Add your first room to make it available when scheduling meetings.</span>
            <button class="rm-btn rm-btn-gold" type="button" style="margin:0 auto" onclick="document.getElementById('add-room-dialog').showModal()">+ Add room <span>→</span></button>
        </div>
    @endforelse
</div>

<div class="rm-footer">
    <a class="rm-link" href="{{ route('meetings.index') }}">Go to meetings <span>→</span></a>
</div>

<dialog class="meeting-dialog room-dialog" id="add-room-dialog" @if($errors->any()) open @endif>
    <form class="panel compact-panel" action="{{ route('meeting-rooms.store') }}" method="post">
        @csrf
        <div class="dialog-heading">
            <div class="panel-title"><span class="icon-box yellow">⌂</span><div><h3>Add a room</h3><p>Keep your spaces easy to find.</p></div></div>
            <button class="dialog-close" type="button" onclick="document.getElementById('add-room-dialog').close()" aria-label="Close add room form">×</button>
        </div>
        <div class="form-grid room-grid">
            <label>Room name<input name="name" value="{{ old('name') }}" placeholder="The Studio" required></label>
            <label>Number<input name="number" value="{{ old('number') }}" placeholder="A-101" required></label>
            <label>Floor<input name="floor" value="{{ old('floor') }}" placeholder="1" required></label>
            <label class="wide">Physical address<input name="location" value="{{ old('location') }}" placeholder="Street address or building location"></label>
        </div>
        <div class="dialog-actions">
            <button class="button secondary" type="button" onclick="document.getElementById('add-room-dialog').close()">Cancel</button>
            <button class="button primary" type="submit">Add room</button>
        </div>
    </form>
</dialog>
@endsection
