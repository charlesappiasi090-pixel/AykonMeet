@extends('layouts.app')

@section('content')
<style>
    /* ============ AykonMeet Check-in & Arrivals Page ============ */
    .check-ins-page {
        max-width: 1320px;
        margin: 0 auto;
        padding-bottom: 40px;
    }

    /* Back Button Pill */
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        padding: 9px 18px;
        background: #ffffff;
        border: 1.5px solid #dce4ed;
        border-radius: 999px;
        color: var(--navy);
        font: 700 12.5px 'Space Grotesk', sans-serif;
        text-decoration: none;
        margin-bottom: 22px;
        box-shadow: 0 4px 12px rgba(17, 43, 73, 0.04);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .back-link:hover {
        background: var(--navy);
        color: #f7c948;
        border-color: var(--navy);
        transform: translateX(-4px);
        box-shadow: 0 8px 22px rgba(17, 43, 73, 0.16);
    }

    /* Hero Banner */
    .check-ins-hero {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        background: radial-gradient(ellipse at 88% -15%, rgba(247,201,72,0.24) 0%, transparent 48%),
                    radial-gradient(ellipse at 12% 85%, rgba(247,201,72,0.12) 0%, transparent 45%),
                    linear-gradient(135deg, #0b1f38 0%, #112b49 52%, #173a63 100%);
        color: #ffffff;
        padding: 38px 44px;
        margin-bottom: 26px;
        box-shadow: 0 20px 50px rgba(17, 43, 73, 0.20);
        border: 1px solid rgba(255, 255, 255, 0.12);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 28px;
    }
    .check-ins-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
                          linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
        background-size: 42px 42px;
        mask-image: radial-gradient(ellipse at 85% 45%, black 10%, transparent 80%);
        -webkit-mask-image: radial-gradient(ellipse at 85% 45%, black 10%, transparent 80%);
        pointer-events: none;
    }
    .check-ins-hero-copy {
        position: relative;
        z-index: 2;
        min-width: 0;
    }
    .check-ins-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font: 700 11px/1 'Space Grotesk', sans-serif;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        color: #f7c948;
        margin-bottom: 12px;
    }
    .check-ins-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #f7c948;
        box-shadow: 0 0 0 3px rgba(247, 201, 72, 0.3);
    }
    .check-ins-title {
        font-family: 'Fraunces', Georgia, serif;
        font-size: clamp(32px, 3.5vw, 44px);
        font-weight: 500;
        color: #ffffff;
        margin: 0 0 10px;
        line-height: 1.1;
        letter-spacing: -0.8px;
    }
    .check-ins-sub {
        margin: 0;
        color: rgba(255, 255, 255, 0.78);
        font-size: 14.5px;
        line-height: 1.6;
        max-width: 580px;
        font-family: 'DM Sans', sans-serif;
    }
    .check-ins-date-card {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 14px;
        background: rgba(255, 255, 255, 0.09);
        border: 1px solid rgba(255, 255, 255, 0.18);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        padding: 16px 22px;
        border-radius: 16px;
        color: #ffffff;
        white-space: nowrap;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12);
    }
    .check-ins-date-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: rgba(247, 201, 72, 0.2);
        color: #f7c948;
        display: grid;
        place-items: center;
        font-size: 20px;
    }
    .check-ins-date-text strong {
        display: block;
        font: 700 15px/1.2 'Space Grotesk', sans-serif;
        color: #ffffff;
    }
    .check-ins-date-text small {
        display: block;
        color: #f7c948;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-top: 3px;
    }

    /* 4 Stat Overview Cards */
    .check-ins-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 26px;
    }
    .check-ins-metric {
        background: #ffffff;
        border-radius: 18px;
        border: 1.5px solid #e4eaf1;
        padding: 22px 24px;
        box-shadow: 0 8px 24px rgba(17, 43, 73, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 148px;
    }
    .check-ins-metric:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 36px rgba(17, 43, 73, 0.10);
    }

    /* Metric Top Row */
    .metric-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }
    .metric-label {
        font: 700 11px/1 'Space Grotesk', sans-serif;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: #718096;
    }
    .metric-ico {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        font-size: 16px;
        font-weight: 700;
    }
    .check-ins-metric strong {
        display: block;
        color: var(--navy);
        font: 700 36px/1 'Space Grotesk', sans-serif;
        letter-spacing: -0.5px;
        margin-bottom: 6px;
    }
    .metric-caption {
        font-size: 12px;
        color: #718096;
        font-family: 'DM Sans', sans-serif;
    }

    /* Color Specific Top Borders & Badges */
    .check-ins-metric.checked {
        border-top: 4px solid #2ecc71;
    }
    .check-ins-metric.checked .metric-ico {
        background: #eaf7ef;
        color: #239b56;
    }
    .check-ins-metric.late {
        border-top: 4px solid #f0b420;
    }
    .check-ins-metric.late .metric-ico {
        background: #fff8e6;
        color: #b57a00;
    }
    .check-ins-metric.no-show {
        border-top: 4px solid #e74c3c;
    }
    .check-ins-metric.no-show .metric-ico {
        background: #fdf0ee;
        color: #c0392b;
    }
    .check-ins-metric.pending {
        border-top: 4px solid var(--navy);
    }
    .check-ins-metric.pending .metric-ico {
        background: #eef3f8;
        color: var(--navy);
    }

    /* Arrivals Panel */
    .check-ins-list {
        background: #ffffff;
        border: 1.5px solid #e4eaf1;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(17, 43, 73, 0.05);
        overflow: hidden;
    }
    .check-ins-list-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 22px 28px;
        border-bottom: 1.5px solid #edf1f4;
        background: #ffffff;
    }
    .check-ins-list-head h2 {
        margin: 0;
        color: var(--navy);
        font: 700 20px 'Space Grotesk', sans-serif;
        letter-spacing: -0.3px;
    }
    .check-ins-chip {
        padding: 6px 14px;
        border-radius: 999px;
        background: #f1f5f8;
        color: var(--navy);
        font: 700 12px 'Space Grotesk', sans-serif;
        letter-spacing: 0.3px;
    }

    /* Table-like row layout */
    .check-in-row {
        display: grid;
        grid-template-columns: minmax(220px, 1.2fr) minmax(240px, 1.5fr) minmax(160px, 1fr) auto;
        gap: 20px;
        align-items: center;
        padding: 18px 28px;
        border-bottom: 1px solid #edf1f4;
        transition: background-color 0.15s ease;
    }
    .check-in-row:hover {
        background-color: #fbfcfe;
    }
    .check-in-row:last-child {
        border-bottom: 0;
    }

    /* Attendee Block */
    .attendee-info {
        display: flex;
        align-items: center;
        gap: 13px;
        min-width: 0;
    }
    .attendee-avatar {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: linear-gradient(135deg, #0b1f38, #173a63);
        color: #f7c948;
        display: grid;
        place-items: center;
        font: 700 14px 'Space Grotesk', sans-serif;
        flex: none;
        box-shadow: 0 4px 10px rgba(11, 31, 56, 0.15);
    }
    .check-in-name {
        color: var(--navy);
        font: 700 14.5px/1.3 'Space Grotesk', sans-serif;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .check-in-detail {
        margin-top: 3px;
        color: #718096;
        font-size: 12.5px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Meeting Block */
    .check-in-meeting {
        display: block;
        color: var(--navy);
        font: 700 14px/1.3 'Space Grotesk', sans-serif;
        text-decoration: none;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        transition: color 0.15s ease;
    }
    .check-in-meeting:hover {
        color: #b18800;
        text-decoration: underline;
    }
    .meeting-meta {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 4px;
        color: #718096;
        font-size: 12px;
    }

    /* Timing Block */
    .time-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--navy);
        font: 600 13px 'DM Sans', sans-serif;
    }
    .time-meta .clock-icon {
        color: #b18800;
        font-size: 14px;
    }

    /* Status Badges */
    .check-in-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font: 700 11px/1 'Space Grotesk', sans-serif;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }
    .check-in-status.checked {
        background: #e8f6ed;
        color: #267344;
        border: 1px solid #c2e8d1;
    }
    .check-in-status.late {
        background: #fff4dd;
        color: #946a00;
        border: 1px solid #fae6a6;
    }
    .check-in-status.no-show {
        background: #fdecea;
        color: #a5382c;
        border: 1px solid #f8c9c4;
    }
    .check-in-status.pending {
        background: #f1f5f8;
        color: var(--navy);
        border: 1px solid #dce4ed;
    }

    /* Empty State */
    .check-ins-empty {
        padding: 64px 28px;
        text-align: center;
        background: #ffffff;
    }
    .empty-icon-box {
        width: 68px;
        height: 68px;
        border-radius: 20px;
        background: linear-gradient(135deg, #0b1f38, #173a63);
        color: #f7c948;
        display: grid;
        place-items: center;
        font-size: 28px;
        margin: 0 auto 20px;
        box-shadow: 0 12px 28px rgba(11, 31, 56, 0.18);
    }
    .check-ins-empty strong {
        display: block;
        color: var(--navy);
        font: 700 20px 'Space Grotesk', sans-serif;
        margin-bottom: 8px;
    }
    .check-ins-empty p {
        margin: 0 auto 22px;
        color: #596a7c;
        font-size: 14px;
        max-width: 440px;
        line-height: 1.6;
    }
    .empty-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 44px;
        padding: 0 22px;
        background: linear-gradient(135deg, #f7c948, #f0b420);
        color: #112b49;
        font: 700 13px 'DM Sans', sans-serif;
        border-radius: 11px;
        text-decoration: none;
        box-shadow: 0 8px 20px rgba(247, 201, 72, 0.32);
        transition: all 0.18s ease;
    }
    .empty-action-btn:hover {
        background: linear-gradient(135deg, #ffd766, #f7c948);
        transform: translateY(-2px);
        box-shadow: 0 12px 26px rgba(247, 201, 72, 0.42);
    }

    /* Responsive */
    @media (max-width: 980px) {
        .check-ins-hero {
            flex-direction: column;
            align-items: flex-start;
            padding: 30px 24px;
            gap: 20px;
        }
        .check-ins-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .check-in-row {
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .check-in-row > *:nth-child(2) {
            grid-column: 1 / -1;
        }
    }
    @media (max-width: 600px) {
        .check-ins-summary {
            grid-template-columns: 1fr;
        }
        .check-in-row {
            grid-template-columns: 1fr;
            padding: 16px 20px;
        }
        .check-in-row > *:nth-child(2) {
            grid-column: auto;
        }
    }
</style>

<div class="check-ins-page">
    <a class="back-link" href="{{ route('meetings.index') }}">← Back to meetings dashboard</a>

    <header class="check-ins-hero">
        <div class="check-ins-hero-copy">
            <div class="check-ins-kicker">
                <span class="check-ins-dot"></span>
                MEETINGS / CHECK-IN
            </div>
            <h1 class="check-ins-title">Check-in &amp; arrivals</h1>
            <p class="check-ins-sub">Today's attendee check-ins, late arrivals, no-shows, and pending arrivals.</p>
        </div>
        <div class="check-ins-date-card">
            <span class="check-ins-date-icon">📅</span>
            <div class="check-ins-date-text">
                <strong>{{ now()->format('D, d M Y') }}</strong>
                <small>Today's Roster</small>
            </div>
        </div>
    </header>

    <section class="check-ins-summary" aria-label="Today's check-in summary">
        <article class="check-ins-metric checked">
            <div class="metric-head">
                <span class="metric-label">Checked in</span>
                <span class="metric-ico">✓</span>
            </div>
            <strong>{{ $stats['checked_in'] }}</strong>
            <span class="metric-caption">Arrivals confirmed today</span>
        </article>

        <article class="check-ins-metric late">
            <div class="metric-head">
                <span class="metric-label">Late</span>
                <span class="metric-ico">◷</span>
            </div>
            <strong>{{ $stats['late'] }}</strong>
            <span class="metric-caption">Meeting started without check-in</span>
        </article>

        <article class="check-ins-metric no-show">
            <div class="metric-head">
                <span class="metric-label">No-show</span>
                <span class="metric-ico">✕</span>
            </div>
            <strong>{{ $stats['no_show'] }}</strong>
            <span class="metric-caption">Meeting ended without check-in</span>
        </article>

        <article class="check-ins-metric pending">
            <div class="metric-head">
                <span class="metric-label">Pending</span>
                <span class="metric-ico">⋯</span>
            </div>
            <strong>{{ $stats['pending'] }}</strong>
            <span class="metric-caption">Awaiting check-in today</span>
        </article>
    </section>

    <section class="check-ins-list" aria-label="Today's attendee check-in details">
        <header class="check-ins-list-head">
            <h2>Today's arrivals</h2>
            <span class="check-ins-chip">{{ $attendees->count() }} {{ \Illuminate\Support\Str::plural('attendee', $attendees->count()) }}</span>
        </header>

        @forelse($attendees as $attendee)
            @php
                $meeting = $attendee->meeting;
                $isNoShow = $attendee->checked_in_at === null
                    && ($meeting->status === 'completed' || $meeting->end_date->isPast());
                $isLate = $attendee->checked_in_at === null && !$isNoShow && $meeting->start_date->isPast();
                $arrivalStatus = $attendee->checked_in_at !== null ? 'checked' : ($isNoShow ? 'no-show' : ($isLate ? 'late' : 'pending'));
                $arrivalLabel = $arrivalStatus === 'no-show' ? 'No-show' : ucfirst($arrivalStatus);
            @endphp
            <article class="check-in-row">
                <div class="attendee-info">
                    <div class="attendee-avatar">{{ strtoupper(substr($attendee->full_name, 0, 1)) }}</div>
                    <div>
                        <div class="check-in-name">{{ $attendee->full_name }}</div>
                        <div class="check-in-detail">{{ $attendee->email }}</div>
                    </div>
                </div>

                <div>
                    <a class="check-in-meeting" href="{{ route('meetings.show', $meeting->meeting_code) }}">{{ $meeting->meeting_topic }}</a>
                    <div class="meeting-meta">
                        <span>{{ $meeting->room?->name ?? ($meeting->is_online ? '🌐 Online meeting' : '🏢 Room not assigned') }}</span>
                    </div>
                </div>

                <div class="time-meta">
                    <span class="clock-icon">◷</span>
                    <span>{{ $attendee->checked_in_at ? 'Checked in '.$attendee->checked_in_at->format('g:i A') : 'Meeting '.$meeting->start_date->format('g:i A') }}</span>
                </div>

                <div>
                    <span class="check-in-status {{ $arrivalStatus }}">{{ $arrivalLabel }}</span>
                </div>
            </article>
        @empty
            <div class="check-ins-empty">
                <div class="empty-icon-box">✓</div>
                <strong>No attendees to check in today</strong>
                <p>Attendees for today's meetings will appear here automatically as sessions are scheduled.</p>
                <a class="empty-action-btn" href="{{ route('meetings.index') }}">Return to meetings dashboard &rarr;</a>
            </div>
        @endforelse
    </section>
</div>
@endsection
