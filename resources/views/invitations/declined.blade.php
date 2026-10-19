@extends('layouts.app')

@section('title', 'Response recorded · '.$meeting->meeting_topic)

@section('content')
<div class="dcn">
    <style>
        .dcn{max-width:720px;margin:0 auto;padding:54px 22px 70px;font-family:"DM Sans",sans-serif;color:#112b49}
        .dcn-card{position:relative;padding:44px 40px;border-radius:24px;background:#fff;border:1px solid #e4eaf1;box-shadow:0 22px 54px rgba(17,43,73,.09);text-align:center;overflow:hidden}
        .dcn-card::before{content:"";position:absolute;inset:0 0 auto;height:6px;background:#e6edf4}
        .dcn-mark{display:grid;place-items:center;width:72px;height:72px;margin:6px auto 20px;border-radius:50%;background:#f1f5f9;color:#6b7d91;font-size:34px}
        .dcn h1{margin:0 0 12px;font:600 clamp(24px,4vw,34px)/1.1 "Fraunces",Georgia,serif;letter-spacing:-.4px}
        .dcn p{max-width:520px;margin:0 auto;color:#5b6b7d;font-size:14px;line-height:1.6}
        .dcn p b{color:#112b49}
        .dcn-actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:28px}
        .dcn-btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;height:48px;padding:0 24px;border-radius:12px;font:700 13px "Space Grotesk",sans-serif;cursor:pointer;text-decoration:none;border:0}
        .dcn-btn.gold{background:linear-gradient(135deg,#f7c948,#f0b420);color:#112b49;box-shadow:0 12px 26px rgba(247,201,72,.36)}
        .dcn-btn.ghost{background:#fff;color:#112b49;border:1px solid #dbe3ea}
    </style>
    <div class="dcn-card">
        <div class="dcn-mark">✕</div>
        <h1>Response recorded</h1>
        <p>Thanks for letting us know, <b>{{ $attendee->full_name }}</b>. You've declined the invitation to <b>{{ $meeting->meeting_topic }}</b>. Changed your mind?</p>
        <div class="dcn-actions">
            <a class="dcn-btn gold" href="{{ $invitationUrl }}">Reconsider invitation</a>
            <a class="dcn-btn ghost" href="{{ route('landing') }}">Back to AykonMeet</a>
        </div>
    </div>
</div>
@endsection
