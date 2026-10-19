@extends('layouts.app')

@section('title', 'Live Session Monitor · Admin Console · AykonMeet')

@section('content')
@php
    $multiCompanyCount = $monitorData->where('is_multi_company', true)->count();
    $singleCompanyCount = $monitorData->where('is_multi_company', false)->count();
    $totalActive = $monitorData->sum('total_active');
    $totalWaiting = $monitorData->sum('total_waiting');
    $totalRecording = $monitorData->sum(fn ($m) => $m['active_recordings']->count());
@endphp

<div class="lsm-container">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Space+Grotesk:wght@500;600;700&display=swap');

        .lsm-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 24px 20px 80px;
            font-family: "DM Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #172437;
        }

        .lsm-container * { box-sizing: border-box; }

        /* Header */
        .lsm-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .lsm-title-block h1 {
            margin: 0 0 6px;
            font: 700 32px/1.1 "Space Grotesk", sans-serif;
            color: #112b49;
        }

        .lsm-title-block p {
            margin: 0;
            color: #64748b;
            font-size: 14px;
        }

        .lsm-live-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 999px;
            color: #991b1b;
            font: 700 13px "Space Grotesk", sans-serif;
        }

        .lsm-live-indicator::before {
            content: "";
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #ef4444;
            animation: lsm-pulse 1.5s ease-in-out infinite;
        }

        @keyframes lsm-pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.9); }
        }

        /* Stats Bar */
        .lsm-stats {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 14px;
            margin-bottom: 24px;
        }

        .lsm-stat {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px 20px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .lsm-stat.primary { border-top: 3px solid #112b49; }
        .lsm-stat.success { border-top: 3px solid #10b981; }
        .lsm-stat.warning { border-top: 3px solid #f59e0b; }
        .lsm-stat.danger { border-top: 3px solid #ef4444; }
        .lsm-stat.info { border-top: 3px solid #3b82f6; }

        .lsm-stat-label {
            font: 700 10.5px "Space Grotesk", sans-serif;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #64748b;
        }

        .lsm-stat-value {
            font: 700 28px "Space Grotesk", sans-serif;
            color: #112b49;
            line-height: 1;
        }

        /* Meeting Cards */
        .lsm-meeting {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            margin-bottom: 18px;
            overflow: hidden;
        }

        .lsm-meeting.multi-company { border-left: 4px solid #f7c948; }
        .lsm-meeting.single-company { border-left: 4px solid #3b82f6; }

        .lsm-meeting-head {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 16px;
            padding: 18px 24px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            align-items: center;
        }

        .lsm-meeting-info h2 {
            margin: 0 0 4px;
            font: 700 18px "Space Grotesk", sans-serif;
            color: #112b49;
        }

        .lsm-meeting-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            font-size: 12.5px;
            color: #64748b;
        }

        .lsm-meta-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .lsm-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            font: 700 10.5px "Space Grotesk", sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .lsm-badge.live { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .lsm-badge.live::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: #ef4444; }
        .lsm-badge.multi { background: #fef9c3; color: #854d0e; border: 1px solid #fde68a; }
        .lsm-badge.single { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
        .lsm-badge.recording { background: #fce7f3; color: #9d174d; border: 1px solid #fbcfe8; }

        .lsm-meeting-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .lsm-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 40px;
            padding: 0 16px;
            border-radius: 10px;
            font: 700 12px "Space Grotesk", sans-serif;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .lsm-btn.primary { background: #112b49; color: #fff; }
        .lsm-btn.primary:hover { background: #0b1f38; }
        .lsm-btn.secondary { background: #f1f5f9; color: #112b49; border: 1px solid #cbd5e1; }
        .lsm-btn.secondary:hover { background: #e2e8f0; }
        .lsm-btn.danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .lsm-btn.danger:hover { background: #fee2e2; }

        .lsm-join-btn { background: #10b981; color: #fff; }
        .lsm-join-btn:hover { background: #059669; }

        /* Company Grid */
        .lsm-meeting-body { padding: 18px 24px; }

        .lsm-companies-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 14px;
        }

        .lsm-company-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 18px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .lsm-company-card.host { background: #f0f7ff; border-color: #cce3fe; }

        .lsm-company-header {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .lsm-company-logo {
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: #112b49;
            color: #f7c948;
            font: 700 14px "Space Grotesk", sans-serif;
            overflow: hidden;
        }

        .lsm-company-logo img { width: 100%; height: 100%; object-fit: cover; }

        .lsm-company-name {
            flex: 1;
            min-width: 0;
        }

        .lsm-company-name strong {
            display: block;
            color: #112b49;
            font: 600 14px "DM Sans", sans-serif;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .lsm-company-name small { color: #64748b; font-size: 11.5px; }

        .lsm-company-counts {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
        }

        .lsm-count {
            text-align: center;
            padding: 8px 6px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .lsm-count.active { border-color: #bbf7d0; }
        .lsm-count.waiting { border-color: #fde68a; }
        .lsm-count.invited { border-color: #bfdbfe; }

        .lsm-count-value {
            display: block;
            font: 700 20px "Space Grotesk", sans-serif;
            color: #112b49;
            line-height: 1;
        }

        .lsm-count.active .lsm-count-value { color: #059669; }
        .lsm-count.waiting .lsm-count-value { color: #d97706; }
        .lsm-count.invited .lsm-count-value { color: #2563eb; }

        .lsm-count-label {
            display: block;
            font: 700 9px "Space Grotesk", sans-serif;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            color: #64748b;
            margin-top: 2px;
        }

        /* Participants List */
        .lsm-participants-section {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid #e2e8f0;
        }

        .lsm-section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 0 0 10px;
            font: 700 12px "Space Grotesk", sans-serif;
            color: #112b49;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .lsm-participants-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
            max-height: 220px;
            overflow-y: auto;
        }

        .lsm-participant-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 12.5px;
        }

        .lsm-participant-avatar {
            display: grid;
            place-items: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #112b49;
            font: 700 11px "DM Sans", sans-serif;
            flex: 0 0 28px;
        }

        .lsm-participant-name {
            flex: 1;
            min-width: 0;
            color: #112b49;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .lsm-participant-tags {
            display: flex;
            gap: 4px;
        }

        .lsm-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 7px;
            border-radius: 999px;
            font: 700 9px "Space Grotesk", sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            white-space: nowrap;
        }

        .lsm-tag.host { background: #112b49; color: #f7c948; }
        .lsm-tag.cohost { background: #fef9c3; color: #854d0e; }
        .lsm-tag.muted { background: #fee2e2; color: #991b1b; }
        .lsm-tag.hand { background: #fef3c7; color: #92400e; }

        /* Recording Card */
        .lsm-recordings-section {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid #e2e8f0;
        }

        .lsm-recording-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 12px;
            background: #fdf2f8;
            border: 1px solid #fbcfe8;
            border-radius: 8px;
            font-size: 12.5px;
        }

        .lsm-recording-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .lsm-recording-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #ef4444;
            animation: lsm-pulse 1.5s ease-in-out infinite;
        }

        .lsm-recording-name { font-weight: 600; color: #112b49; }
        .lsm-recording-time { color: #64748b; font-size: 11px; }

        .lsm-recording-actions {
            display: flex;
            gap: 8px;
        }

        /* Empty State */
        .lsm-empty {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }

        .lsm-empty-icon { font-size: 48px; margin-bottom: 16px; }
        .lsm-empty strong { display: block; font: 600 18px "Space Grotesk", sans-serif; color: #112b49; margin-bottom: 6px; }

        /* Responsive */
        @media (max-width: 1024px) {
            .lsm-stats { grid-template-columns: repeat(2, 1fr); }
            .lsm-stats .lsm-stat:last-child { grid-column: 1 / -1; }
        }

        @media (max-width: 768px) {
            .lsm-header { flex-direction: column; align-items: flex-start; }
            .lsm-meeting-head { grid-template-columns: 1fr; align-items: flex-start; }
            .lsm-stats { grid-template-columns: 1fr; }
            .lsm-companies-grid { grid-template-columns: 1fr; }
            .lsm-company-counts { grid-template-columns: 1fr 1fr 1fr; }
        }
    </style>

    <header class="lsm-header">
        <div class="lsm-title-block">
            <h1>Live Session Monitor</h1>
            <p>Real-time view of all active online meetings</p>
        </div>
        <div class="lsm-live-indicator" id="lsmLiveIndicator">
            <span id="lsmLiveCount">{{ $totalActive }}</span> active participant{{ $totalActive !== 1 ? 's' : '' }} across {{ $monitorData->count() }} live meeting{{ $monitorData->count() !== 1 ? 's' : '' }}
        </div>
    </header>

    <!-- Stats Bar -->
    <div class="lsm-stats" role="region" aria-label="Live session statistics">
        <div class="lsm-stat primary">
            <span class="lsm-stat-label">Live Meetings</span>
            <span class="lsm-stat-value">{{ $monitorData->count() }}</span>
        </div>
        <div class="lsm-stat success">
            <span class="lsm-stat-label">Multi-Company</span>
            <span class="lsm-stat-value">{{ $multiCompanyCount }}</span>
        </div>
        <div class="lsm-stat info">
            <span class="lsm-stat-label">Active Participants</span>
            <span class="lsm-stat-value">{{ $totalActive }}</span>
        </div>
        <div class="lsm-stat warning">
            <span class="lsm-stat-label">In Waiting Room</span>
            <span class="lsm-stat-value">{{ $totalWaiting }}</span>
        </div>
        <div class="lsm-stat danger">
            <span class="lsm-stat-label">Recording</span>
            <span class="lsm-stat-value">{{ $totalRecording }}</span>
        </div>
    </div>

    @if($monitorData->isEmpty())
        <div class="lsm-empty">
            <div class="lsm-empty-icon">🎥</div>
            <strong>No live meetings right now</strong>
            <p style="margin-top: 8px;">Meetings will appear here when hosts start their sessions.</p>
            <a href="{{ route('meetings.index') }}" class="lsm-btn primary" style="margin-top: 20px; display: inline-flex;">View All Meetings</a>
        </div>
    @else
        <section aria-label="Live meetings">
            @foreach($monitorData as $data)
                @php
                    $meeting = $data['meeting'];
                    $isMulti = $data['is_multi_company'];
                    $companyStats = $data['company_stats'];
                    $recordings = $data['active_recordings'];
                @endphp
                <article class="lsm-meeting {{ $isMulti ? 'multi-company' : 'single-company' }}">
                    <header class="lsm-meeting-head">
                        <div class="lsm-meeting-info">
                            <h2>{{ $meeting->meeting_topic }}</h2>
                            <div class="lsm-meeting-meta">
                                <span class="lsm-meta-item">
                                    <span class="lsm-badge live">● Live</span>
                                </span>
                                <span class="lsm-meta-item">
                                    <span class="lsm-badge {{ $isMulti ? 'multi' : 'single' }}">
                                        {{ $isMulti ? '🏢 Multi-Company' : '👥 Single Company' }}
                                    </span>
                                </span>
                                <span class="lsm-meta-item">
                                    <span class="lsm-meta-item">{{ $meeting->meeting_type === 'hybrid' ? '🏢 Hybrid' : '🌐 Online' }}</span>
                                </span>
                                @if($meeting->room)
                                    <span class="lsm-meta-item">📍 {{ $meeting->room->name }}</span>
                                @endif
                                <span class="lsm-meta-item">Started {{ ($meeting->started_at ?? $meeting->start_date)->diffForHumans() }}</span>
                                @if($recordings->count() > 0)
                                    <span class="lsm-meta-item">
                                        <span class="lsm-badge recording">● Recording ({{ $recordings->count() }})</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="lsm-meeting-actions">
                            <a class="lsm-btn primary" href="{{ route('meetings.details', $meeting) }}" target="_blank">Meeting Details</a>
                            <a class="lsm-btn join-btn" href="{{ \Illuminate\Support\Facades\URL::signedRoute('sessions.join', ['meeting' => $meeting]) }}" target="_blank">Join as Host</a>
                        </div>
                    </header>

                    <div class="lsm-meeting-body">
                        <!-- Company Grid -->
                        <div class="lsm-companies-grid">
                            @foreach($companyStats as $stat)
                                @php
                                    $company = $stat['company'];
                                    $isHost = $company->is_host_company;
                                    $activeCount = $stat['active_count'];
                                    $waitingCount = $stat['waiting_count'];
                                    $invitedCount = $stat['invited_count'];
                                    $activeParticipants = $stat['active_participants'];
                                    $waitingParticipants = $stat['waiting_participants'];
                                @endphp
                                <div class="lsm-company-card {{ $isHost ? 'host' : '' }}">
                                    <div class="lsm-company-header">
                                        <div class="lsm-company-logo">
                                            @if($company->logo_path)
                                                <img src="{{ asset('storage/' . $company->logo_path) }}" alt="{{ $company->name }}">
                                            @else
                                                {{ strtoupper(substr($company->name, 0, 1)) }}
                                            @endif
                                        </div>
                                        <div class="lsm-company-name">
                                            <strong>{{ $company->name }}</strong>
                                            @if($isHost)
                                                <small>Host Company</small>
                                            @endif
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            @if($isHost)
                                                <span class="lsm-tag host">Host</span>
                                            @endif
                                            @if($activeCount > 0)
                                                <span class="lsm-tag" style="background:#dcfce7;color:#166534">{{ $activeCount }} Active</span>
                                            @endif
                                            @if($waitingCount > 0)
                                                <span class="lsm-tag waiting">{{ $waitingCount }} Waiting</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="lsm-company-counts">
                                        <div class="lsm-count active">
                                            <span class="lsm-count-value">{{ $activeCount }}</span>
                                            <span class="lsm-count-label">Active</span>
                                        </div>
                                        <div class="lsm-count waiting">
                                            <span class="lsm-count-value">{{ $waitingCount }}</span>
                                            <span class="lsm-count-label">Waiting</span>
                                        </div>
                                        <div class="lsm-count invited">
                                            <span class="lsm-count-value">{{ $invitedCount }}</span>
                                            <span class="lsm-count-label">Invited</span>
                                        </div>
                                    </div>

                                    <!-- Active Participants -->
                                    @if($activeParticipants->count() > 0)
                                        <div class="lsm-participants-section">
                                            <div class="lsm-section-title">
                                                Active Participants
                                                <span style="font-size:11px;color:#64748b;font-weight:500;text-transform:none">{{ $activeParticipants->count() }}</span>
                                            </div>
                                            <div class="lsm-participants-list">
                                                @foreach($activeParticipants as $participant)
                                                    <div class="lsm-participant-row">
                                                        <div class="lsm-participant-avatar">
                                                            {{ strtoupper(substr($participant->display_name, 0, 1)) }}
                                                        </div>
                                                        <div class="lsm-participant-name">{{ $participant->display_name }}</div>
                                                        <div class="lsm-participant-tags">
                                                            @if($participant->is_host)
                                                                <span class="lsm-tag host">Host</span>
                                                            @endif
                                                            @if($participant->is_co_host)
                                                                <span class="lsm-tag cohost">Co-host</span>
                                                            @endif
                                                            @if($participant->is_muted_by_host)
                                                                <span class="lsm-tag muted">Muted</span>
                                                            @endif
                                                            @if($participant->hand_raised)
                                                                <span class="lsm-tag hand">Hand</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Waiting Participants -->
                                    @if($waitingParticipants->count() > 0)
                                        <div class="lsm-participants-section">
                                            <div class="lsm-section-title">
                                                Waiting Room
                                                <span style="font-size:11px;color:#64748b;font-weight:500;text-transform:none">{{ $waitingParticipants->count() }}</span>
                                            </div>
                                            <div class="lsm-participants-list">
                                                @foreach($waitingParticipants as $participant)
                                                    <div class="lsm-participant-row" style="background:#fffbeb;border-color:#fde68a">
                                                        <div class="lsm-participant-avatar" style="background:#fef9c3;color:#854d0e">
                                                            {{ strtoupper(substr($participant->display_name, 0, 1)) }}
                                                        </div>
                                                        <div class="lsm-participant-name">{{ $participant->display_name }}</div>
                                                        <span style="margin-left:auto;color:#d97706;font-size:10px;font-weight:600">
                                                            Waiting {{ $participant->requested_at ? \Carbon\Carbon::parse($participant->requested_at)->diffForHumans() : '' }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <!-- Recordings -->
                        @if($recordings->count() > 0)
                            <div class="lsm-recordings-section">
                                <div class="lsm-section-title">Active Recordings</div>
                                @foreach($recordings as $recording)
                                    <div class="lsm-recording-row">
                                        <div class="lsm-recording-info">
                                            <span class="lsm-recording-dot"></span>
                                            <div>
                                                <div class="lsm-recording-name">{{ $recording->participant?->display_name ?? 'Unknown' }}</div>
                                                <div class="lsm-recording-time">Started {{ $recording->started_at?->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                        <div class="lsm-recording-actions">
                                            <a class="lsm-btn secondary" href="{{ route('sessions.recordings.download', [$meeting, $recording]) }}" target="_blank">Download</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>
    @endif
</div>

<script>
(function () {
    var indicator = document.getElementById('lsmLiveIndicator');
    var countEl = document.getElementById('lsmLiveCount');
    var apiUrl = '{{ route("admin.live-monitor.api") }}';
    var pollInterval = 5000;

    function poll() {
        fetch(apiUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (!data) return;
                var totalActive = 0;
                var totalWaiting = 0;
                data.meetings.forEach(function (m) {
                    totalActive += m.total_active;
                    totalWaiting += m.total_waiting;
                });
                if (countEl) {
                    countEl.textContent = totalActive + ' active participant' + (totalActive !== 1 ? 's' : '') +
                        ' across ' + data.meetings.length + ' live meeting' + (data.meetings.length !== 1 ? 's' : '') +
                        (totalWaiting ? ' · ' + totalWaiting + ' waiting' : '');
                }
            })
            .catch(function () {});
    }

    poll();
    var timer = setInterval(poll, pollInterval);

    // Cleanup on page unload
    window.addEventListener('beforeunload', function () { clearInterval(timer); });
})();
</script>
@endsection