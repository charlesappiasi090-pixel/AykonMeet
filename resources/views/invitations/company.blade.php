@extends('layouts.app')

@section('title', 'Invitation for ' . $company->name . ' · ' . $meeting->meeting_topic . ' · AykonMeet')

@section('content')
@php
    $when = $meeting->start_date?->format('l, F j, Y · g:i A');
    $duration = \App\Models\Meeting::durationLabel($meeting->start_date, $meeting->end_date);
    $hostCompanyName = $meeting->company_name ?: ($meeting->hostCompany()?->name ?: 'Host Organization');
    $status = $company->status ?? 'pending';
@endphp

<div class="civ-container">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Space+Grotesk:wght@500;600;700&display=swap');

        .civ-container {
            max-width: 1040px;
            margin: 0 auto;
            padding: 30px 20px 80px;
            font-family: "DM Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #172437;
        }

        .civ-container * {
            box-sizing: border-box;
        }

        /* Branding Header */
        .civ-brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .civ-logo {
            font: 700 24px "Space Grotesk", sans-serif;
            color: #112b49;
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .civ-logo span {
            color: #b18800;
        }

        .civ-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 999px;
            font: 700 12px "Space Grotesk", sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .civ-status-badge.pending { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .civ-status-badge.accepted { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .civ-status-badge.declined { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        /* Hero Banner */
        .civ-hero {
            position: relative;
            padding: 40px 42px;
            border-radius: 24px;
            overflow: hidden;
            color: #ffffff;
            background: radial-gradient(ellipse at 88% -15%, rgba(247, 201, 72, 0.32), transparent 55%),
                        linear-gradient(135deg, #091c33, #112b49 55%, #18416d);
            box-shadow: 0 24px 60px rgba(9, 28, 51, 0.28);
            margin-bottom: 28px;
        }

        .civ-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font: 800 11px "Space Grotesk", sans-serif;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: #f7c948;
            margin-bottom: 12px;
        }

        .civ-hero h1 {
            margin: 0 0 12px;
            color: #ffffff;
            font: 600 clamp(26px, 4vw, 40px) / 1.12 "Fraunces", Georgia, serif;
            letter-spacing: -0.5px;
            max-width: 760px;
        }

        .civ-hero p {
            margin: 0;
            color: #cbd5e1;
            font-size: 15px;
            line-height: 1.6;
            max-width: 680px;
        }

        .civ-hero p b {
            color: #f7c948;
        }

        /* Two Column Layout */
        .civ-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(300px, 1fr);
            gap: 26px;
            align-items: start;
        }

        .civ-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 30px 32px;
            box-shadow: 0 10px 30px rgba(17, 43, 73, 0.04);
        }

        .civ-card h2 {
            margin: 0 0 18px;
            font: 700 20px "Space Grotesk", sans-serif;
            color: #112b49;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .civ-card-sub {
            margin: -10px 0 20px;
            font-size: 13.5px;
            color: #64748b;
        }

        /* Actions Bar */
        .civ-action-cards {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }

        .civ-btn-tab {
            flex: 1;
            padding: 14px 16px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            font: 700 13px "Space Grotesk", sans-serif;
            cursor: pointer;
            text-align: center;
            transition: all 0.15s ease;
        }

        .civ-btn-tab.active-accept {
            border-color: #10b981;
            background: #ecfdf5;
            color: #065f46;
        }

        /* Form elements */
        .civ-field {
            margin-bottom: 18px;
        }

        .civ-field label {
            display: block;
            margin-bottom: 6px;
            font: 700 11.5px "Space Grotesk", sans-serif;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            color: #475569;
        }

        .civ-field input {
            width: 100%;
            height: 48px;
            padding: 0 15px;
            border: 1.5px solid #d5e0ea;
            border-radius: 12px;
            font: 500 14.5px "DM Sans", sans-serif;
            color: #112b49;
            background: #f8fafc;
            transition: all 0.15s ease;
        }

        .civ-field input:focus {
            outline: none;
            border-color: #112b49;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(247, 201, 72, 0.35);
        }

        .civ-btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            height: 52px;
            margin-top: 8px;
            border: 0;
            border-radius: 13px;
            cursor: pointer;
            background: linear-gradient(135deg, #f7c948, #f0b420);
            color: #112b49;
            font: 700 15px "Space Grotesk", sans-serif;
            box-shadow: 0 12px 28px rgba(247, 201, 72, 0.35);
            transition: all 0.15s ease;
        }

        .civ-btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 32px rgba(247, 201, 72, 0.45);
        }

        .civ-btn-decline {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 46px;
            margin-top: 12px;
            border: 1.5px solid #fed7d7;
            border-radius: 13px;
            background: #fff5f5;
            color: #991b1b;
            font: 700 13px "Space Grotesk", sans-serif;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .civ-btn-decline:hover {
            background: #fee2e2;
        }

        .civ-alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13.5px;
            line-height: 1.5;
        }

        .civ-alert.error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .civ-alert.success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }

        /* Security Notice */
        .civ-security-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            background: #f1f5f9;
            border-radius: 10px;
            margin-top: 18px;
            font-size: 12px;
            color: #475569;
        }

        /* Facts sidebar */
        .civ-facts-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .civ-fact-item {
            padding: 14px 16px;
            border: 1px solid #e8eef5;
            border-radius: 14px;
            background: #f8fafc;
        }

        .civ-fact-label {
            display: block;
            color: #718096;
            font: 700 10.5px "Space Grotesk", sans-serif;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .civ-fact-val {
            display: block;
            color: #112b49;
            font: 700 14.5px "DM Sans", sans-serif;
        }

        @media (max-width: 780px) {
            .civ-grid { grid-template-columns: 1fr; }
            .civ-hero { padding: 30px 24px; }
        }
    </style>

    <!-- Branding -->
    <div class="civ-brand">
        <a href="/" class="civ-logo">Aykon<span>Meet</span></a>
        <div>
            <span class="civ-status-badge {{ $status }}">
                ● Invitation Status: {{ ucfirst($status) }}
            </span>
        </div>
    </div>

    <!-- Hero Card -->
    <div class="civ-hero">
        <span class="civ-eyebrow">🏢 Formal Corporate Invitation</span>
        <h1>{{ $meeting->meeting_topic }}</h1>
        <p>
            You are invited by <b>{{ $hostCompanyName }}</b> to join this collaborative multi-company session on behalf of <b>{{ $company->name }}</b>.
        </p>
    </div>

    @if (session('status_message'))
        <div class="civ-alert success">
            {{ session('status_message') }}
        </div>
    @endif

    @if ($error)
        <div class="civ-alert error">{{ $error }}</div>
    @endif

    @if ($errors->any())
        <div class="civ-alert error">{{ $errors->first() }}</div>
    @endif

    <!-- Main Grid -->
    <div class="civ-grid">
        <!-- Action Card: Accept & Identify -->
        <div class="civ-card">
            <h2>Accept & Join Meeting</h2>
            <p class="civ-card-sub">
                Confirm your representative credentials. You will be authenticated directly under <strong>{{ $company->name }}</strong> and inherit company group privileges.
            </p>

            <form method="POST" action="{{ route('company-invitations.verify', ['token' => $token]) }}">
                @csrf
                <div class="civ-field">
                    <label for="full_name">Full Name <span style="color:#e53e3e">*</span></label>
                    <input id="full_name" name="full_name" type="text" maxlength="160" 
                           value="{{ old('full_name', $company->contact_name ?: $prefillName) }}" 
                           placeholder="e.g. Jane Doe" required>
                </div>

                <div class="civ-field">
                    <label for="email">Work Email Address <span style="color:#e53e3e">*</span></label>
                    <input id="email" name="email" type="email" maxlength="255" 
                           value="{{ old('email', $company->contact_email ?: $prefillEmail) }}" 
                           placeholder="e.g. jane.doe@{{ parse_url($company->email, PHP_URL_HOST) ?? 'company.com' }}" required>
                </div>

                <div class="civ-field">
                    <label for="department">Role / Department (Optional)</label>
                    <input id="department" name="department" type="text" maxlength="160" 
                           value="{{ old('department', $company->contact_department) }}" 
                           placeholder="e.g. Head of Partnerships">
                </div>

                <button class="civ-btn-submit" type="submit">
                    ✓ Accept Invitation & Proceed to Join
                </button>
            </form>

            <form method="POST" action="{{ route('company-invitations.decline', ['token' => $token]) }}" 
                  onsubmit="return confirm('Are you sure your company wishes to decline this invitation?');">
                @csrf
                <button class="civ-btn-decline" type="submit">
                    ✕ Decline Invitation
                </button>
            </form>

            <div class="civ-security-badge">
                <span>🔒</span>
                <span>
                    <strong>Strict Corporate Binding:</strong> You are anchored exclusively to <strong>{{ $company->name }}</strong>. URL tampering or company switching is prevented.
                </span>
            </div>
        </div>

        <!-- Meeting Details Facts Sidebar -->
        <div class="civ-card">
            <h2>Meeting Schedule & Details</h2>

            <ul class="civ-facts-list">
                <li class="civ-fact-item">
                    <span class="civ-fact-label">Host Company</span>
                    <strong class="civ-fact-val">{{ $hostCompanyName }}</strong>
                </li>

                <li class="civ-fact-item">
                    <span class="civ-fact-label">Invited Company</span>
                    <strong class="civ-fact-val">{{ $company->name }}</strong>
                </li>

                <li class="civ-fact-item">
                    <span class="civ-fact-label">Date & Start Time</span>
                    <strong class="civ-fact-val">{{ $when ?: 'To be advised' }}</strong>
                </li>

                <li class="civ-fact-item">
                    <span class="civ-fact-label">Meeting Duration</span>
                    <strong class="civ-fact-val">{{ $duration }}</strong>
                </li>

                @if($company->company_agenda || $meeting->description)
                    <li class="civ-fact-item">
                        <span class="civ-fact-label">Agenda & Briefing</span>
                        <div style="font-size:13px;line-height:1.5;color:#334155;margin-top:4px">
                            {{ $company->company_agenda ?: $meeting->description }}
                        </div>
                    </li>
                @endif

                @if($company->invitation_expires_at)
                    <li class="civ-fact-item">
                        <span class="civ-fact-label">Link Valid Until</span>
                        <strong class="civ-fact-val">{{ $company->invitation_expires_at->format('M j, Y') }}</strong>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
@endsection
