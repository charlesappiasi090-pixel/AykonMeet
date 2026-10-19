@extends('layouts.app')

@section('title', $meeting->meeting_topic . ' · Host Management Dashboard · AykonMeet')

@section('content')
@php
    $startDate = $meeting->start_date;
    $endDate = $meeting->end_date;
    $isLive = $meeting->isLive();
    $isEnded = $meeting->hasEnded();
    $isCancelled = $meeting->isCancelled();
    $status = $meeting->dashboardStatus();
@endphp

<div class="mch-dashboard">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Space+Grotesk:wght@500;600;700&display=swap');

        .mch-dashboard {
            max-width: 1240px;
            margin: 0 auto;
            padding: 24px 20px 80px;
            font-family: "DM Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #172437;
        }

        .mch-dashboard * {
            box-sizing: border-box;
        }

        /* Flash Messages */
        .mch-alert {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-radius: 14px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
        }

        .mch-alert.success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .mch-alert.error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Header Navigation */
        .mch-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
            gap: 16px;
            flex-wrap: wrap;
        }

        .mch-breadcrumbs {
            display: flex;
            align-items: center;
            gap: 8px;
            font: 600 12px "Space Grotesk", sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
        }

        .mch-breadcrumbs a {
            color: #1e3a8a;
            text-decoration: none;
        }

        .mch-top-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Hero Meeting Overview Card */
        .mch-hero-card {
            background: radial-gradient(ellipse at 85% -15%, rgba(247, 201, 72, 0.28), transparent 60%),
                        linear-gradient(135deg, #091a30, #112b49 55%, #183e6b);
            border-radius: 24px;
            padding: 34px 38px;
            color: #ffffff;
            box-shadow: 0 20px 50px rgba(9, 26, 48, 0.28);
            margin-bottom: 28px;
            position: relative;
            overflow: hidden;
        }

        .mch-hero-eyebrow {
            display: flex;
            align-items: center;
            gap: 10px;
            font: 700 11px "Space Grotesk", sans-serif;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #f7c948;
            margin-bottom: 12px;
        }

        .mch-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 999px;
            font: 700 11px "Space Grotesk", sans-serif;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }

        .mch-status-pill.live {
            background: #ef4444;
            color: #ffffff;
            box-shadow: 0 0 12px rgba(239, 68, 68, 0.6);
            animation: pulse-red 2s infinite;
        }

        .mch-status-pill.scheduled {
            background: rgba(247, 201, 72, 0.2);
            color: #f7c948;
            border: 1px solid #f7c948;
        }

        .mch-status-pill.completed {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
            border: 1px solid #34d399;
        }

        .mch-status-pill.cancelled {
            background: rgba(148, 163, 184, 0.2);
            color: #cbd5e1;
            border: 1px solid #94a3b8;
        }

        @keyframes pulse-red {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.9; }
        }

        .mch-hero-title {
            margin: 0 0 14px;
            font: 600 clamp(26px, 3.8vw, 40px) / 1.1 "Fraunces", Georgia, serif;
            letter-spacing: -0.6px;
            max-width: 820px;
        }

        .mch-hero-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            color: #cbd5e1;
            font-size: 14px;
            margin-bottom: 24px;
        }

        .mch-hero-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .mch-hero-meta-item strong {
            color: #ffffff;
        }

        /* Link Box in Hero */
        .mch-link-box {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 14px;
            padding: 8px 12px;
            max-width: 720px;
            gap: 10px;
        }

        .mch-link-input {
            background: transparent;
            border: none;
            color: #f8fafc;
            font: 500 13px "DM Sans", sans-serif;
            flex: 1;
            outline: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mch-btn-copy {
            background: #f7c948;
            color: #112b49;
            border: none;
            padding: 8px 16px;
            border-radius: 10px;
            font: 700 12px "Space Grotesk", sans-serif;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }

        .mch-btn-copy:hover {
            background: #f0b420;
            transform: translateY(-1px);
        }

        /* Stat Counters Grid */
        .mch-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 28px;
        }

        .mch-stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 22px 24px;
            box-shadow: 0 4px 18px rgba(17, 43, 73, 0.04);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .mch-stat-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }

        .mch-stat-card.blue::before { background: #3b82f6; }
        .mch-stat-card.green::before { background: #10b981; }
        .mch-stat-card.amber::before { background: #f59e0b; }
        .mch-stat-card.purple::before { background: #8b5cf6; }

        .mch-stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .mch-stat-label {
            font: 700 11px "Space Grotesk", sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
        }

        .mch-stat-icon {
            font-size: 20px;
        }

        .mch-stat-val {
            font: 700 32px "Space Grotesk", sans-serif;
            color: #112b49;
            line-height: 1;
            margin-bottom: 6px;
        }

        .mch-stat-desc {
            font-size: 12px;
            color: #94a3b8;
        }

        /* Panel Cards */
        .mch-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 28px 32px;
            margin-bottom: 28px;
            box-shadow: 0 4px 20px rgba(17, 43, 73, 0.04);
        }

        .mch-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f1f5f9;
            flex-wrap: wrap;
            gap: 12px;
        }

        .mch-card-title {
            margin: 0 0 4px;
            font: 700 18px "Space Grotesk", sans-serif;
            color: #112b49;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .mch-card-sub {
            margin: 0;
            font-size: 13.5px;
            color: #64748b;
        }

        /* Waiting Room Live Panel */
        .mch-waiting-panel {
            background: #fffdf5;
            border: 1.5px solid #fef08a;
            border-radius: 18px;
            padding: 24px 28px;
            margin-bottom: 28px;
        }

        .mch-waiting-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 16px;
        }

        .mch-waiting-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
            border: 1px solid #fae8a4;
            border-radius: 14px;
            padding: 14px 18px;
            box-shadow: 0 2px 8px rgba(247, 201, 72, 0.1);
            gap: 14px;
            flex-wrap: wrap;
        }

        .mch-waiting-user {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .mch-avatar-circle {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #112b49;
            color: #f7c948;
            font: 700 15px "Space Grotesk", sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .mch-waiting-name {
            font: 700 14px "DM Sans", sans-serif;
            color: #112b49;
            margin-bottom: 2px;
        }

        .mch-waiting-company-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #eef2f6;
            color: #334155;
            border-radius: 6px;
            padding: 2px 8px;
            font: 600 11px "Space Grotesk", sans-serif;
        }

        .mch-waiting-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Companies Table */
        .mch-table-wrapper {
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
        }

        .mch-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13.5px;
        }

        .mch-table th {
            background: #f8fafc;
            padding: 14px 18px;
            font: 700 11px "Space Grotesk", sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .mch-table td {
            padding: 16px 18px;
            border-bottom: 1px solid #edf2f7;
            vertical-align: middle;
        }

        .mch-table tr:last-child td {
            border-bottom: none;
        }

        .mch-table tr:hover td {
            background: #fcfdfd;
        }

        .mch-company-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .mch-company-logo {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #e2e8f0;
        }

        .mch-company-logo-placeholder {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: #eef2f6;
            color: #112b49;
            display: flex;
            align-items: center;
            justify-content: center;
            font: 700 14px "Space Grotesk", sans-serif;
            border: 1px solid #d5e0ea;
        }

        .mch-badge-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            font: 700 11px "Space Grotesk", sans-serif;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .mch-badge-status.accepted { background: #dcfce7; color: #15803d; }
        .mch-badge-status.pending { background: #fef3c7; color: #b45309; }
        .mch-badge-status.sent { background: #e0e7ff; color: #4338ca; }
        .mch-badge-status.declined { background: #fee2e2; color: #b91c1c; }
        .mch-badge-status.cancelled { background: #f1f5f9; color: #64748b; }
        .mch-badge-status.expired { background: #f1f5f9; color: #64748b; }

        .mch-btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 8px;
            font: 700 12px "Space Grotesk", sans-serif;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.15s ease;
        }

        .mch-btn-action.primary { background: #112b49; color: #ffffff; }
        .mch-btn-action.primary:hover { background: #1c4575; }

        .mch-btn-action.secondary { background: #f1f5f9; color: #1e293b; border: 1px solid #cbd5e1; }
        .mch-btn-action.secondary:hover { background: #e2e8f0; }

        .mch-btn-action.danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .mch-btn-action.danger:hover { background: #fecaca; }

        .mch-btn-action.admit { background: #10b981; color: #ffffff; }
        .mch-btn-action.admit:hover { background: #059669; }

        .mch-btn-action.reject { background: #ef4444; color: #ffffff; }
        .mch-btn-action.reject:hover { background: #dc2626; }

        /* Top Action Buttons */
        .mch-btn-main {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 44px;
            padding: 0 18px;
            border-radius: 12px;
            font: 700 13px "Space Grotesk", sans-serif;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.15s ease;
        }

        .mch-btn-main:hover {
            transform: translateY(-1px);
        }

        .mch-btn-main.start {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
        }

        .mch-btn-main.gold {
            background: linear-gradient(135deg, #f7c948, #f0b420);
            color: #112b49;
            box-shadow: 0 4px 14px rgba(247, 201, 72, 0.35);
        }

        .mch-btn-main.outline {
            background: #ffffff;
            color: #112b49;
            border: 1.5px solid #cbd5e1;
        }

        .mch-btn-main.outline:hover {
            background: #f8fafc;
            border-color: #112b49;
        }

        .mch-btn-main.danger {
            background: #fff5f5;
            color: #c53030;
            border: 1.5px solid #fed7d7;
        }

        .mch-btn-main.danger:hover {
            background: #fed7d7;
        }

        @media (max-width: 960px) {
            .mch-stats-grid { grid-template-columns: repeat(2, 1fr); }
            .mch-hero-card { padding: 26px 24px; }
        }

        @media (max-width: 640px) {
            .mch-stats-grid { grid-template-columns: 1fr; }
            .mch-top-actions { width: 100%; flex-direction: column; align-items: stretch; }
            .mch-btn-main { width: 100%; justify-content: center; }
        }
    </style>

    <!-- Flash Notifications -->
    @if(session('success'))
        <div class="mch-alert success">
            <span>✅ {{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;font-weight:700">✕</button>
        </div>
    @endif

    @if(session('error'))
        <div class="mch-alert error">
            <span>⚠️ {{ session('error') }}</span>
            <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;font-weight:700">✕</button>
        </div>
    @endif

    <!-- Topbar & Action Buttons -->
    <div class="mch-topbar">
        <div class="mch-breadcrumbs">
            <a href="{{ route('meetings.index') }}">Meetings</a>
            <span>/</span>
            <span>Multi-Company Management</span>
        </div>

        <div class="mch-top-actions">
            @if(!$isEnded && !$isCancelled)
                <a href="{{ $joinUrl }}" target="_blank" class="mch-btn-main {{ $isLive ? 'start' : 'gold' }}">
                    <span>{{ $isLive ? '🔴 Join Live Session' : '🚀 Start Meeting Room' }}</span>
                </a>
            @endif

            <a href="{{ route('multi-company-meetings.attendance.export', $meeting) }}" class="mch-btn-main outline">
                📥 Export Attendance CSV
            </a>

            <a href="{{ route('meetings.edit', $meeting) }}" class="mch-btn-main outline">
                ✏️ Edit Meeting
            </a>

            @if(!$isCancelled && !$isEnded)
                <form method="POST" action="{{ route('multi-company-meetings.cancel', $meeting) }}" 
                      onsubmit="return confirm('Are you sure you want to cancel this meeting? All company links will be disabled.');" style="display:inline">
                    @csrf
                    <button type="submit" class="mch-btn-main danger">
                        ✕ Cancel Meeting
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Meeting Details Hero Card -->
    <div class="mch-hero-card">
        <div class="mch-hero-eyebrow">
            <span>🏛️ Multi-Company Session</span>
            <span>&bull;</span>
            <span class="mch-status-pill {{ $status }}">{{ ucfirst($status) }}</span>
        </div>

        <h1 class="mch-hero-title">{{ $meeting->meeting_topic }}</h1>

        <div class="mch-hero-meta">
            <div class="mch-hero-meta-item">
                <span>🏢 Host Company:</span>
                <strong>{{ $hostCompany?->name ?? ($meeting->company_name ?: 'Host Organization') }}</strong>
            </div>
            <div class="mch-hero-meta-item">
                <span>📅 Date:</span>
                <strong>{{ $startDate->format('D, M j, Y') }}</strong>
            </div>
            <div class="mch-hero-meta-item">
                <span>⏰ Time:</span>
                <strong>{{ $startDate->format('g:i A') }} – {{ $endDate->format('g:i A') }} ({{ $settings['timezone'] ?? 'UTC' }})</strong>
            </div>
            <div class="mch-hero-meta-item">
                <span>⏳ Duration:</span>
                <strong>{{ \App\Models\Meeting::durationLabel($startDate, $endDate) }}</strong>
            </div>
            <div class="mch-hero-meta-item">
                <span>🔑 Code:</span>
                <code>{{ $meeting->meeting_code }}</code>
            </div>
        </div>

        <!-- One-click Copy Host & Main Join Link -->
        <div class="mch-link-box">
            <span style="font-size:12px;font-weight:700;color:#f7c948;padding-left:4px">LINK:</span>
            <input type="text" id="hostMeetingLink" value="{{ $joinUrl }}" readonly class="mch-link-input">
            <button type="button" class="mch-btn-copy" onclick="copyToClipboard('hostMeetingLink', this)">
                📋 Copy Meeting Link
            </button>
        </div>
    </div>

    <!-- Live Stat Counters -->
    <div class="mch-stats-grid">
        <div class="mch-stat-card blue">
            <div class="mch-stat-header">
                <span class="mch-stat-label">Invited Companies</span>
                <span class="mch-stat-icon">🏢</span>
            </div>
            <div class="mch-stat-val">{{ $stats['total_companies'] }}</div>
            <div class="mch-stat-desc">Excluding host company</div>
        </div>

        <div class="mch-stat-card green">
            <div class="mch-stat-header">
                <span class="mch-stat-label">Accepted Invitations</span>
                <span class="mch-stat-icon">✅</span>
            </div>
            <div class="mch-stat-val">{{ $stats['accepted_companies'] }}</div>
            <div class="mch-stat-desc">{{ $stats['total_companies'] > 0 ? round(($stats['accepted_companies'] / $stats['total_companies']) * 100) : 0 }}% response rate</div>
        </div>

        <div class="mch-stat-card amber">
            <div class="mch-stat-header">
                <span class="mch-stat-label">Waiting in Lobby</span>
                <span class="mch-stat-icon">⏳</span>
            </div>
            <div class="mch-stat-val" id="waitingCountDisplay">{{ $stats['waiting_count'] }}</div>
            <div class="mch-stat-desc">Requires host admission</div>
        </div>

        <div class="mch-stat-card purple">
            <div class="mch-stat-header">
                <span class="mch-stat-label">Live in Meeting Room</span>
                <span class="mch-stat-icon">👥</span>
            </div>
            <div class="mch-stat-val" id="joinedCountDisplay">{{ $stats['joined_count'] }}</div>
            <div class="mch-stat-desc">Currently connected peers</div>
        </div>
    </div>

    <!-- Live Waiting Room & Approvals Section -->
    @if($waitingParticipants->isNotEmpty())
        <div class="mch-waiting-panel">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
                <div>
                    <h3 style="margin:0 0 4px;font:700 17px 'Space Grotesk',sans-serif;color:#854d0e;display:flex;align-items:center;gap:8px">
                        <span>⏳ Waiting Room Lobby</span>
                        <span style="background:#fef08a;color:#854d0e;font-size:12px;padding:2px 8px;border-radius:999px;border:1px solid #facc15">
                            {{ $waitingParticipants->count() }} Participant(s) Waiting
                        </span>
                    </h3>
                    <p style="margin:0;font-size:13px;color:#a16207">
                        These participants have completed camera/mic check and are waiting in the lobby for host approval.
                    </p>
                </div>

                <form method="POST" action="{{ route('sessions.participants.admit-all', $meeting) }}">
                    @csrf
                    <button type="submit" class="mch-btn-action admit" style="padding:8px 16px;font-size:13px">
                        ✓ Admit All Waiting Participants
                    </button>
                </form>
            </div>

            <div class="mch-waiting-list">
                @foreach($waitingParticipants as $wp)
                    <div class="mch-waiting-item">
                        <div class="mch-waiting-user">
                            <div class="mch-avatar-circle">
                                {{ strtoupper(substr($wp->display_name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <div class="mch-waiting-name">{{ $wp->display_name }}</div>
                                <div style="display:flex;align-items:center;gap:8px">
                                    <span class="mch-waiting-company-badge">
                                        🏢 {{ $wp->company?->name ?? 'External Company' }}
                                    </span>
                                    <span style="font-size:12px;color:#854d0e">
                                        Joined lobby {{ $wp->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mch-waiting-actions">
                            <form method="POST" action="{{ route('sessions.participants.admit-company', $meeting) }}" style="display:inline">
                                @csrf
                                <input type="hidden" name="company_id" value="{{ $wp->meeting_company_id }}">
                                <button type="submit" class="mch-btn-action secondary" title="Admit all members of this company">
                                    🏢 Admit Company
                                </button>
                            </form>

                            <form method="POST" action="{{ route('sessions.participants.reject', $meeting) }}" style="display:inline">
                                @csrf
                                <input type="hidden" name="participant_id" value="{{ $wp->id }}">
                                <button type="submit" class="mch-btn-action reject">
                                    ✕ Reject
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Invited Companies Management Table -->
    <div class="mch-card">
        <div class="mch-card-header">
            <div>
                <h3 class="mch-card-title">🏢 Invited Companies & Direct Invitation Links</h3>
                <p class="mcf-card-sub">Manage each company's secure 64-character link, track delivery, and resend or revoke access.</p>
            </div>
            <div>
                <span style="font-size:13px;color:#64748b;font-weight:600">
                    {{ $invitedCompanies->count() }} Companies Registered
                </span>
            </div>
        </div>

        <div class="mch-table-wrapper">
            <table class="mch-table">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Contact Person</th>
                        <th>Company Invitation Link</th>
                        <th>Delivery Status</th>
                        <th>RSVP Response</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invitedCompanies as $comp)
                        @php
                            $hasInvitationToken = !empty($comp->invitation_token);
                            $compLink = $hasInvitationToken ? route('company-invitations.show', ['token' => $comp->invitation_token]) : '#';
                            $inputId = 'company_link_' . $comp->id;
                            $rsvpStatus = $comp->status;
                            if ($comp->invitation_status === 'cancelled') {
                                $rsvpStatus = 'revoked';
                            } elseif ($comp->invitation_expires_at && $comp->invitation_expires_at->isPast()) {
                                $rsvpStatus = 'expired';
                            }
                        @endphp
                        <tr>
                            <td>
                                <div class="mch-company-cell">
                                    @if($comp->logo_path)
                                        <img src="{{ asset('storage/' . $comp->logo_path) }}" alt="{{ $comp->name }}" class="mch-company-logo">
                                    @else
                                        <div class="mch-company-logo-placeholder">
                                            {{ strtoupper(substr($comp->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <strong style="color:#112b49;display:block">{{ $comp->name }}</strong>
                                        <span style="font-size:12px;color:#64748b">{{ $comp->email }}</span>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <strong style="color:#112b49;display:block">{{ $comp->contact_name ?: '—' }}</strong>
                                <span style="font-size:12px;color:#1e40af">{{ $comp->contact_email }}</span>
                                @if($comp->contact_department)
                                    <div style="font-size:11px;color:#64748b">{{ $comp->contact_department }}</div>
                                @endif
                            </td>

                            <td style="max-width:280px">
                                @if($hasInvitationToken && $comp->invitation_status !== 'cancelled' && (!$comp->invitation_expires_at || $comp->invitation_expires_at->isFuture()))
                                    <div style="display:flex;align-items:center;gap:6px">
                                        <input type="text" id="{{ $inputId }}" value="{{ $compLink }}" readonly 
                                               style="width:170px;height:32px;padding:0 8px;border:1px solid #cbd5e1;border-radius:6px;font-size:11px;color:#475569;background:#f8fafc">
                                        <button type="button" class="mch-btn-action secondary" onclick="copyToClipboard('{{ $inputId }}', this)" title="Copy unique link">
                                            📋 Copy
                                        </button>
                                    </div>
                                    <div style="font-size:11px;color:#64748b;margin-top:4px">
                                        Expires: {{ $comp->invitation_expires_at ? $comp->invitation_expires_at->format('M j, Y') : '30 days' }}
                                    </div>
                                @elseif($comp->invitation_status === 'cancelled')
                                    <span style="color:#dc2626;font-size:12px;font-style:italic">Link Revoked</span>
                                @elseif($comp->invitation_expires_at && $comp->invitation_expires_at->isPast())
                                    <span style="color:#f59e0b;font-size:12px;font-style:italic">Link Expired</span>
                                @else
                                    <span style="color:#94a3b8;font-size:12px;font-style:italic">Invitation not sent yet</span>
                                @endif
                            </td>

                            <td>
                                @if($comp->invitation_delivery_status === 'delivered')
                                    <span style="color:#059669;font-weight:600;font-size:12px">✓ Delivered</span>
                                @elseif($comp->invitation_delivery_status === 'sent')
                                    <span style="color:#2563eb;font-weight:600;font-size:12px">✉️ Sent</span>
                                @elseif($comp->invitation_delivery_status === 'failed')
                                    <span style="color:#dc2626;font-weight:600;font-size:12px" title="{{ $comp->invitation_failure_reason }}">⚠️ Failed</span>
                                @else
                                    <span style="color:#64748b;font-size:12px">Pending</span>
                                @endif
                            </td>

                            <td>
                                <span class="mch-badge-status {{ $rsvpStatus }}">
                                    {{ ucfirst($rsvpStatus) }}
                                </span>
                            </td>

                            <td style="text-align:right">
                                <div style="display:inline-flex;align-items:center;gap:6px">
                                    <form method="POST" action="{{ route('multi-company-meetings.invitation.resend', [$meeting, $comp]) }}" style="display:inline">
                                        @csrf
                                        <button type="submit" class="mch-btn-action secondary" title="Resend invitation email">
                                            🔄 Resend
                                        </button>
                                    </form>

                                    @if($comp->invitation_status !== 'cancelled')
                                        <form method="POST" action="{{ route('multi-company-meetings.invitation.revoke', [$meeting, $comp]) }}" 
                                              onsubmit="return confirm('Revoke company invitation for {{ $comp->name }}? Their token will be permanently deactivated.');" style="display:inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="mch-btn-action danger" title="Revoke company access">
                                                ✕ Revoke
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:32px;color:#64748b">
                                No external companies registered for this meeting.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function copyToClipboard(elementId, btn) {
        var input = document.getElementById(elementId);
        if (!input) return;

        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value).then(function() {
            var origText = btn.innerHTML;
            btn.innerHTML = '✓ Copied!';
            btn.style.background = '#10b981';
            btn.style.color = '#ffffff';

            setTimeout(function() {
                btn.innerHTML = origText;
                btn.style.background = '';
                btn.style.color = '';
            }, 2000);
        }).catch(function(err) {
            console.error('Failed to copy: ', err);
        });
    }
</script>
@endsection
