@extends('layouts.app')

@section('title', $title ?? 'Invitation unavailable')

@section('content')
<div class="inv-err">
    <style>
        .inv-err{max-width:640px;margin:0 auto;padding:64px 22px 80px;font-family:"DM Sans",sans-serif;color:#112b49}
        .inv-err-card{padding:44px 38px;border-radius:24px;background:#fff;border:1px solid #e4eaf1;box-shadow:0 22px 54px rgba(17,43,73,.09);text-align:center}
        .inv-err-mark{display:grid;place-items:center;width:72px;height:72px;margin:0 auto 20px;border-radius:50%;background:#fdeceb;color:#c03b2d;font-size:34px}
        .inv-err h1{margin:0 0 12px;font:600 clamp(23px,4vw,32px)/1.15 "Fraunces",Georgia,serif;letter-spacing:-.4px}
        .inv-err p{max-width:480px;margin:0 auto;color:#5b6b7d;font-size:14px;line-height:1.65}
        .inv-err a{display:inline-flex;align-items:center;justify-content:center;height:46px;margin-top:26px;padding:0 24px;border-radius:12px;background:linear-gradient(135deg,#f7c948,#f0b420);color:#112b49;font:700 13px "Space Grotesk",sans-serif;text-decoration:none}
    </style>
    <div class="inv-err-card">
        <div class="inv-err-mark">!</div>
        <h1>{{ $title ?? 'Invitation unavailable' }}</h1>
        <p>{{ $message ?? 'This invitation is no longer available. Please contact the meeting organizer for help.' }}</p>
        <a href="{{ route('landing') }}">Back to AykonMeet</a>
    </div>
</div>
@endsection
