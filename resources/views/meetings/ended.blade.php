@extends('layouts.app')

@section('title', 'Meeting Ended · ' . $meeting->meeting_topic)

@section('content')
@php
    $typeLabel = match (true) {
        $meeting->isHybrid() => 'Hybrid meeting',
        $meeting->is_online => 'Online meeting',
        default => 'In-person meeting',
    };
    $hostCompanyName = $meeting->company_name ?: ($meeting->hostCompany()?->name ?: 'Host Organization');
@endphp
<div class="meeting-ended">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Space+Grotesk:wght@500;600;700&display=swap');

        .meeting-ended {
            max-width: 640px;
            margin: 0 auto;
            padding: 64px 22px 80px;
            font-family: "DM Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #172437;
        }

        .meeting-ended * { box-sizing: border-box; }

        .meeting-ended-card {
            padding: 44px 38px;
            border-radius: 24px;
            background: #fff;
            border: 1px solid #e4eaf1;
            box-shadow: 0 22px 54px rgba(17, 43, 73, 0.09);
            text-align: center;
        }

        .meeting-ended-mark {
            display: grid;
            place-items: center;
            width: 72px;
            height: 72px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: #fdeceb;
            color: #c03b2d;
            font-size: 34px;
        }

        .meeting-ended h1 {
            margin: 0 0 12px;
            font: 600 clamp(23px, 4vw, 32px) / 1.15 "Fraunces", Georgia, serif;
            letter-spacing: -0.4px;
        }

        .meeting-ended p {
            max-width: 480px;
            margin: 0 auto;
            color: #5b6b7d;
            font-size: 14px;
            line-height: 1.65;
        }

        .meeting-ended .details {
            margin-top: 24px;
            padding: 18px 20px;
            border-radius: 14px;
            background: #f8fafc;
            border: 1px solid #e8eef5;
            text-align: left;
        }

        .meeting-ended .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eef2f6;
            font-size: 13.5px;
        }

        .meeting-ended .detail-row:last-child {
            border-bottom: none;
        }

        .meeting-ended .detail-label {
            color: #718096;
            font-weight: 600;
        }

        .meeting-ended .detail-value {
            color: #112b49;
            font-weight: 600;
        }

        .meeting-ended a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 46px;
            margin-top: 26px;
            padding: 0 24px;
            border-radius: 12px;
            background: linear-gradient(135deg, #f7c948, #f0b420);
            color: #112b49;
            font: 700 13px "Space Grotesk", sans-serif;
            text-decoration: none;
        }

        .meeting-ended .note {
            margin-top: 16px;
            padding: 12px 14px;
            border-radius: 10px;
            background: #fff9e5;
            border: 1px solid #f3e2ae;
            color: #8a6d00;
            font-size: 12px;
            line-height: 1.5;
        }

        .meeting-ended .note strong { color: #112b49; }
    </style>

    <div class="meeting-ended-card">
        <div class="meeting-ended-mark">✕</div>
        <h1>This meeting has ended</h1>
        <p>The meeting <strong>{{ $meeting->meeting_topic }}</strong> was concluded by the host on {{ $meeting->ended_at ? $meeting->ended_at->format('l, F j, Y \a\t g:i A') : 'an unspecified time' }}.</p>

        <div class="details">
            <div class="detail-row">
                <span class="detail-label">Meeting</span>
                <span class="detail-value">{{ $meeting->meeting_topic }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Host</span>
                <span class="detail-value">{{ $hostCompanyName }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Format</span>
                <span class="detail-value">{{ $typeLabel }}</span>
            </div>
            @if($meeting->ended_at)
                <div class="detail-row">
                    <span class="detail-label">Ended</span>
                    <span class="detail-value">{{ $meeting->ended_at->format('l, F j, Y \a\t g:i A') }}</span>
                </div>
            @endif
        </div>

        <a href="{{ route('landing') }}">Back to AykonMeet</a>

        <div class="note">
            <strong>Note:</strong> If you were a participant in this meeting, your attendance has been recorded. You can contact the host if you need a recording or follow-up materials.
        </div>
    </div>
</div>
@endsection