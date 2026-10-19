@extends('layouts.app')

@section('title', 'Multi-Company Meeting Wizard · Step ' . $currentStep . ' · AykonMeet')

@section('content')
<div class="mcf-container">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Space+Grotesk:wght@500;600;700&display=swap');

        .mcf-container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 24px 20px 80px;
            font-family: "DM Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #172437;
        }

        .mcf-container * {
            box-sizing: border-box;
        }

        /* Top Progress Wizard Navigation */
        .mcf-wizard-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
            border: 1px solid #e4eaf1;
            border-radius: 18px;
            padding: 16px 28px;
            margin-bottom: 28px;
            box-shadow: 0 4px 20px rgba(17, 43, 73, 0.04);
        }

        .mcf-step-node {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            color: #718096;
            transition: all 0.2s ease;
        }

        .mcf-step-node.active {
            color: #112b49;
        }

        .mcf-step-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font: 700 14px "Space Grotesk", sans-serif;
            background: #edf2f7;
            color: #718096;
            border: 2px solid transparent;
            transition: all 0.2s ease;
        }

        .mcf-step-node.completed .mcf-step-circle {
            background: #e6f9f0;
            color: #10b981;
            border-color: #10b981;
        }

        .mcf-step-node.active .mcf-step-circle {
            background: #112b49;
            color: #f7c948;
            border-color: #f7c948;
            box-shadow: 0 4px 12px rgba(17, 43, 73, 0.2);
        }

        .mcf-step-info-title {
            display: block;
            font: 700 14px "Space Grotesk", sans-serif;
            color: inherit;
        }

        .mcf-step-info-sub {
            display: block;
            font-size: 12px;
            color: #a0aec0;
        }

        .mcf-step-divider {
            flex: 1;
            height: 2px;
            background: #e2e8f0;
            margin: 0 20px;
        }

        .mcf-step-divider.filled {
            background: #10b981;
        }

        /* Card panels */
        .mcf-card {
            background: #ffffff;
            border: 1px solid #e4eaf1;
            border-radius: 18px;
            padding: 28px 32px;
            margin-bottom: 22px;
            box-shadow: 0 6px 24px rgba(17, 43, 73, 0.04);
            position: relative;
        }

        .mcf-card-header {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f0f4f8;
        }

        .mcf-badge {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background: #112b49;
            color: #f7c948;
            font: 700 14px "Space Grotesk", sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .mcf-card-title {
            margin: 0 0 4px;
            font: 700 18px "Space Grotesk", sans-serif;
            color: #112b49;
        }

        .mcf-card-sub {
            margin: 0;
            font-size: 13.5px;
            color: #6b7d91;
        }

        /* Form grids and fields */
        .mcf-grid {
            display: grid;
            gap: 18px;
        }

        .mcf-grid-1 { grid-template-columns: 1fr; }
        .mcf-grid-2 { grid-template-columns: 1fr 1fr; }
        .mcf-grid-3 { grid-template-columns: 1fr 1fr 1fr; }

        .mcf-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .mcf-label {
            font: 700 12px "Space Grotesk", sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #4a5568;
        }

        .mcf-req {
            color: #e53e3e;
            font-weight: 700;
        }

        .mcf-hint {
            font-size: 12px;
            color: #718096;
            margin-top: 3px;
        }

        .mcf-field input[type="text"],
        .mcf-field input[type="email"],
        .mcf-field input[type="tel"],
        .mcf-field input[type="date"],
        .mcf-field input[type="time"],
        .mcf-field input[type="number"],
        .mcf-field select,
        .mcf-field textarea {
            width: 100%;
            height: 46px;
            padding: 0 14px;
            border: 1.5px solid #d5e0ea;
            border-radius: 11px;
            background: #ffffff;
            color: #112b49;
            font: 500 14px "DM Sans", sans-serif;
            transition: all 0.15s ease;
        }

        .mcf-field textarea {
            height: auto;
            padding: 12px 14px;
            font-family: inherit;
        }

        .mcf-field input:focus,
        .mcf-field select:focus,
        .mcf-field textarea:focus {
            outline: none;
            border-color: #112b49;
            box-shadow: 0 0 0 3px rgba(247, 201, 72, 0.35);
        }

        /* Upload box */
        .mcf-upload-box {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            background: #f8fafc;
            padding: 18px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .mcf-upload-box:hover {
            border-color: #b18800;
            background: #fffdf5;
        }

        .mcf-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            cursor: pointer;
        }

        .mcf-upload-icon {
            font-size: 26px;
        }

        .mcf-upload-label strong {
            display: block;
            font-size: 14px;
            color: #112b49;
        }

        .mcf-upload-label p {
            margin: 3px 0 0;
            font-size: 12px;
            color: #718096;
        }

        /* Radio cards */
        .mcf-radio-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 6px;
        }

        .mcf-radio-card {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .mcf-radio-card:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        .mcf-radio-card.selected {
            border-color: #f7c948;
            background: #fffdf5;
            box-shadow: 0 2px 10px rgba(247, 201, 72, 0.18);
        }

        .mcf-radio-card input {
            margin-top: 3px;
        }

        .mcf-radio-body strong {
            display: block;
            font-size: 13.5px;
            color: #112b49;
            margin-bottom: 2px;
        }

        .mcf-radio-body p {
            margin: 0;
            font-size: 12px;
            color: #64748b;
            line-height: 1.4;
        }

        /* Toggles */
        .mcf-toggles {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 6px;
        }

        .mcf-toggle-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid #e8eef5;
            cursor: pointer;
        }

        .mcf-toggle-item input {
            margin-top: 3px;
            width: 16px;
            height: 16px;
        }

        .mcf-toggle-text strong {
            display: block;
            font-size: 13px;
            color: #112b49;
        }

        .mcf-toggle-text span {
            display: block;
            font-size: 11.5px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Buttons */
        .mcf-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 48px;
            padding: 0 22px;
            border-radius: 12px;
            font: 700 14px "Space Grotesk", sans-serif;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.15s ease;
        }

        .mcf-btn:hover {
            transform: translateY(-1px);
        }

        .mcf-btn-primary {
            background: linear-gradient(135deg, #112b49, #1c4575);
            color: #ffffff;
            box-shadow: 0 4px 16px rgba(17, 43, 73, 0.25);
        }

        .mcf-btn-primary:hover {
            background: #112b49;
        }

        .mcf-btn-gold {
            background: linear-gradient(135deg, #f7c948, #f0b420);
            color: #112b49;
            box-shadow: 0 6px 20px rgba(247, 201, 72, 0.35);
        }

        .mcf-btn-gold:hover {
            background: #f0b420;
        }

        .mcf-btn-secondary {
            background: #f1f5f9;
            color: #112b49;
            border: 1px solid #cbd5e1;
        }

        .mcf-btn-secondary:hover {
            background: #e2e8f0;
        }

        .mcf-btn-outline {
            background: #ffffff;
            color: #112b49;
            border: 1.5px solid #d5e0ea;
        }

        .mcf-btn-outline:hover {
            background: #f8fafc;
            border-color: #112b49;
        }

        .mcf-btn-outline-danger {
            background: #ffffff;
            color: #c53030;
            border: 1.5px solid #fed7d7;
        }

        .mcf-btn-outline-danger:hover {
            background: #fff5f5;
            border-color: #e53e3e;
        }

        .mcf-btn-remove {
            background: #fff5f5;
            color: #c53030;
            border: 1px solid #fed7d7;
            padding: 6px 14px;
            border-radius: 8px;
            font: 700 12px "Space Grotesk", sans-serif;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .mcf-btn-remove:hover {
            background: #fed7d7;
        }

        .mcf-btn-add-company {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 24px;
            border-radius: 12px;
            background: #fff9e6;
            color: #975a16;
            border: 1.5px dashed #ecc94b;
            font: 700 14px "Space Grotesk", sans-serif;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .mcf-btn-add-company:hover {
            background: #fef08a;
            border-color: #d69e2e;
        }

        .mcf-btn-inline-edit {
            font: 700 12.5px "Space Grotesk", sans-serif;
            color: #1c4575;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 8px;
            background: #edf2f7;
            transition: background 0.15s ease;
        }

        .mcf-btn-inline-edit:hover {
            background: #e2e8f0;
        }

        /* Action bar footer */
        .mcf-footer-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding-top: 24px;
            margin-top: 14px;
            border-top: 1px solid #e2e8f0;
        }

        .mcf-footer-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Dividers & Alerts */
        .mcf-sub-divider {
            display: flex;
            align-items: center;
            margin: 20px 0 16px;
        }

        .mcf-sub-divider::before,
        .mcf-sub-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .mcf-sub-divider span {
            padding: 0 14px;
            font: 700 11px "Space Grotesk", sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #718096;
        }

        .mcf-alert {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 22px;
            font-size: 13.5px;
        }

        .mcf-alert.error {
            background: #fff5f5;
            border: 1px solid #fed7d7;
            color: #9b2c2c;
        }

        .mcf-info-banner {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 18px 22px;
            background: #f0f7ff;
            border: 1px solid #cce3fe;
            border-radius: 14px;
            margin-bottom: 24px;
        }

        .mcf-info-icon {
            font-size: 28px;
        }

        /* Summary Step 3 Elements */
        .mcf-summary-hero {
            background: radial-gradient(ellipse at 90% -10%, rgba(247, 201, 72, 0.25), transparent 60%),
                        linear-gradient(135deg, #0b1f38, #112b49 60%, #173a63);
            border-radius: 20px;
            padding: 34px 38px;
            color: #ffffff;
            margin-bottom: 24px;
            box-shadow: 0 20px 50px rgba(11, 31, 56, 0.25);
        }

        .mcf-summary-badge {
            display: inline-block;
            font: 800 11px "Space Grotesk", sans-serif;
            letter-spacing: 2px;
            color: #f7c948;
            margin-bottom: 8px;
        }

        .mcf-summary-title {
            margin: 0 0 10px;
            font: 600 clamp(24px, 3.5vw, 36px) / 1.15 "Fraunces", Georgia, serif;
            letter-spacing: -0.5px;
        }

        .mcf-summary-sub {
            margin: 0;
            color: #cbd5e1;
            font-size: 14.5px;
            line-height: 1.6;
        }

        .mcf-review-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
        }

        .mcf-review-cell {
            padding: 14px 16px;
            background: #f8fafc;
            border: 1px solid #e8eef5;
            border-radius: 12px;
        }

        .mcf-review-label {
            display: block;
            font: 700 11px "Space Grotesk", sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #718096;
            margin-bottom: 4px;
        }

        .mcf-review-val {
            display: block;
            color: #112b49;
            font-size: 14px;
            font-weight: 600;
        }

        /* Review table */
        .mcf-companies-table-wrapper {
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-top: 8px;
        }

        .mcf-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13.5px;
        }

        .mcf-table th {
            background: #f8fafc;
            padding: 12px 16px;
            font: 700 11px "Space Grotesk", sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
        }

        .mcf-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #edf2f7;
            vertical-align: middle;
        }

        .mcf-table tr:last-child td {
            border-bottom: none;
        }

        .mcf-table-idx {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 6px;
            background: #edf2f7;
            color: #4a5568;
            font: 700 11px "Space Grotesk", sans-serif;
        }

        .mcf-email-badge {
            display: inline-block;
            background: #f0f7ff;
            color: #1e40af;
            border: 1px solid #dbeafe;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 12px;
        }

        .mcf-delivery-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .mcf-delivery-list li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 13.5px;
            line-height: 1.5;
            color: #334155;
        }

        .mcf-delivery-check {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #10b981;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        @media (max-width: 860px) {
            .mcf-grid-2, .mcf-grid-3 { grid-template-columns: 1fr; }
            .mcf-wizard-nav { flex-direction: column; gap: 14px; align-items: flex-start; }
            .mcf-step-divider { display: none; }
            .mcf-footer-actions { flex-direction: column; align-items: stretch; }
            .mcf-footer-right { flex-direction: column; }
            .mcf-btn { width: 100%; }
        }
    </style>

    <!-- 3-Step Wizard Navigation -->
    <div class="mcf-wizard-nav">
        @foreach($steps as $step)
            <div class="mcf-step-node {{ $step['id'] < $currentStep ? 'completed' : ($step['id'] === $currentStep ? 'active' : '') }}">
                <div class="mcf-step-circle">
                    @if($step['id'] < $currentStep)
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    @else
                        @if($step['id'] === 1)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @elseif($step['id'] === 2)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4h5"/></svg>
                        @elseif($step['id'] === 3)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.149 1.318 10.908 1.024 16 1.024 5.096 0 10.857-1.024 16-1.024z"/></svg>
                        @else
                            {{ $step['id'] }}
                        @endif
                    @endif
                </div>
                <div>
                    <span class="mcf-step-info-title">{{ $step['title'] }}</span>
                    <span class="mcf-step-info-sub">{{ $step['description'] }}</span>
                </div>
            </div>

            @if(!$loop->last)
                <div class="mcf-step-divider {{ $step['id'] < $currentStep ? 'filled' : '' }}"></div>
            @endif
        @endforeach
    </div>

    <!-- Hidden Form for Cancel Action -->
    <form id="cancelForm" method="POST" action="{{ route('multi-company-meetings.store') }}" style="display:none">
        @csrf
        <input type="hidden" name="action" value="cancel">
    </form>

    <!-- Main Wizard Form -->
    <form method="POST" action="{{ route('multi-company-meetings.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="step" value="{{ $currentStep }}">

        @if($currentStep === 1)
            @include('meetings.multi-company._step1')
        @elseif($currentStep === 2)
            @include('meetings.multi-company._step2')
        @elseif($currentStep === 3)
            @include('meetings.multi-company._step3')
        @endif
    </form>
</div>
@endsection