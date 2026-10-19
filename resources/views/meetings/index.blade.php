@extends('layouts.app')

@section('content')
<style>
    /* ============ AykonMeet Meetings Console Hero & Layout ============ */
    .meetings-dashboard .page-shell { padding-top: 24px; }
    
    .db-hero {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        background: radial-gradient(ellipse at 88% -15%, rgba(247,201,72,0.24) 0%, transparent 48%),
                    radial-gradient(ellipse at 12% 45%, rgba(247,201,72,0.14) 0%, transparent 45%),
                    linear-gradient(135deg, #0b1f38 0%, #112b49 52%, #173a63 100%);
        color: #ffffff;
        padding: 42px 46px;
        margin-bottom: 26px;
        box-shadow: 0 20px 50px rgba(17, 43, 73, 0.22);
        border: 1px solid rgba(255, 255, 255, 0.12);
    }
    .db-hero::before {
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
    .db-hero-inner {
        position: relative;
        z-index: 2;
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(320px, 1fr);
        gap: 36px;
        align-items: center;
    }
    .db-hero-copy {
        position: relative;
        z-index: 2;
        min-width: 0;
    }
    .db-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font: 700 11px/1 'Space Grotesk', sans-serif;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        color: #f7c948;
        margin-bottom: 16px;
    }
    .db-eyebrow .d {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #f7c948;
        box-shadow: 0 0 0 4px rgba(247,201,72,0.28);
        display: inline-block;
    }
    .db-title {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 500;
        font-size: clamp(34px, 3.8vw, 52px);
        line-height: 1.05;
        letter-spacing: -1.2px;
        color: #ffffff;
        margin: 0 0 16px;
    }
    .db-title em {
        font-style: italic;
        color: #f7c948;
        font-weight: 500;
    }
    .db-sub {
        font-size: 15px;
        line-height: 1.6;
        color: rgba(255, 255, 255, 0.78);
        max-width: 540px;
        margin: 0 0 28px;
        font-family: 'DM Sans', sans-serif;
    }
    .db-hero-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
    }
    .db-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        height: 48px;
        padding: 0 24px;
        border: 0;
        border-radius: 12px;
        font: 700 13.5px 'DM Sans', sans-serif;
        cursor: pointer;
        text-decoration: none;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        white-space: nowrap;
    }
    .db-btn-primary {
        background: linear-gradient(135deg, #f7c948, #f0b420);
        color: #112b49;
        box-shadow: 0 10px 26px rgba(247, 201, 72, 0.35);
    }
    .db-btn-primary:hover {
        background: linear-gradient(135deg, #ffd766, #f7c948);
        transform: translateY(-2px);
        box-shadow: 0 14px 32px rgba(247, 201, 72, 0.45);
    }
    .db-btn-ghost {
        background: rgba(255, 255, 255, 0.08);
        color: #ffffff;
        border: 1.5px solid rgba(255, 255, 255, 0.22);
    }
    .db-btn-ghost:hover {
        background: rgba(255, 255, 255, 0.16);
        transform: translateY(-2px);
        color: #ffffff;
    }

    /* Quick Actions Glass Card */
    .db-quickcard {
        position: relative;
        z-index: 2;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-radius: 20px;
        padding: 18px;
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
        display: grid;
        gap: 6px;
    }
    .db-quickcard-title {
        margin: 4px 6px 12px;
        color: #f7c948;
        font: 800 11px/1 'Space Grotesk', sans-serif;
        letter-spacing: 2.2px;
        text-transform: uppercase;
    }
    .db-quickcard-link {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 14px;
        border-radius: 12px;
        color: #ffffff;
        text-decoration: none;
        transition: background 0.18s ease, transform 0.18s ease;
    }
    .db-quickcard-link:hover {
        background: rgba(255, 255, 255, 0.12);
        transform: translateX(3px);
    }
    .db-quickcard-link .ico {
        display: grid;
        place-items: center;
        flex: none;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(247, 201, 72, 0.18);
        color: #f7c948;
        font-size: 16px;
        font-weight: 700;
    }
    .db-quickcard-link .ico.live {
        background: rgba(46, 204, 113, 0.22);
        color: #5deb9b;
    }
    .db-quickcard-link .txt {
        flex: 1;
        min-width: 0;
        font: 600 13px/1.3 'DM Sans', sans-serif;
        color: #ffffff;
    }
    .db-quickcard-link .txt small {
        display: block;
        margin-top: 3px;
        color: rgba(255, 255, 255, 0.65);
        font-size: 11px;
        font-weight: 400;
    }
    .db-quickcard-link .go {
        color: rgba(255, 255, 255, 0.5);
        font-size: 16px;
        transition: transform 0.15s ease;
    }
    .db-quickcard-link:hover .go {
        color: #f7c948;
        transform: translateX(2px);
    }

    /* Metric Cards Grid */
    .db-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin: 0 0 28px;
    }
    .db-stat-grid .stat-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid #e4eaf1;
        border-top: 4px solid var(--navy);
        padding: 22px;
        min-height: 140px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 10px 28px rgba(17, 43, 73, 0.05);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
        text-decoration: none;
        color: inherit;
    }
    .db-stat-grid .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 36px rgba(17, 43, 73, 0.12);
    }
    .db-stat-grid .stat-card:focus-visible {
        outline: 3px solid rgba(17, 43, 73, 0.45);
        outline-offset: 2px;
    }
    .db-stat-grid .stat-card strong {
        font: 700 38px/1 'Space Grotesk', sans-serif;
        color: var(--navy);
        margin: 14px 0 6px;
        letter-spacing: -0.5px;
    }
    .db-stat-grid .stat-card.accent {
        background: linear-gradient(135deg, #f7c948, #f0b420);
        border-color: #f0b420;
        border-top-color: #e0a800;
    }
    .db-stat-grid .stat-card.accent .stat-label,
    .db-stat-grid .stat-card.accent small,
    .db-stat-grid .stat-card.accent strong,
    .db-stat-grid .stat-card.accent .dashboard-stat-extra {
        color: var(--navy);
    }

    .dashboard-stat-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .dashboard-stat-icon {
        display: grid;
        place-items: center;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: #f1f5f8;
        color: var(--navy);
        font-size: 18px;
    }
    .dashboard-stat-icon.live {
        color: #24834b;
        background: #e8f6ed;
    }
    .dashboard-stat-live {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #267344;
        text-decoration: none;
        font-size: 13.5px;
        font-weight: 700;
        margin-top: 8px;
    }
    .dashboard-stat-live::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #2f9e5b;
    }
    .dashboard-stat-extra {
        margin-top: 6px;
        color: var(--muted);
        font-size: 12.5px;
        font-weight: 500;
    }

    /* Dashboard Operations */
    .dashboard-columns {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 20px;
        align-items: start;
    }
    .dashboard-column {
        display: grid;
        gap: 20px;
        min-width: 0;
    }
    .dashboard-panel {
        background: var(--white);
        border: 1px solid #e4eaf1;
        border-radius: 18px;
        padding: 24px;
        min-width: 0;
        box-shadow: 0 10px 30px rgba(17, 43, 73, 0.05);
    }
    .dashboard-panel-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 18px;
    }
    .dashboard-panel-head h2 {
        margin: 0;
        color: var(--navy);
        font: 700 20px 'Space Grotesk', sans-serif;
        letter-spacing: -0.3px;
    }
    .dashboard-panel-head p {
        margin: 5px 0 0;
        color: #596a7c;
        font-size: 13px;
    }
    .dashboard-panel-count {
        background: #f1f5f8;
        color: var(--navy);
        font: 700 12px 'Space Grotesk', sans-serif;
        padding: 6px 12px;
        border-radius: 999px;
        white-space: nowrap;
    }

    /* Timeline Table */
    .timeline-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }
    .timeline-table th {
        padding: 12px 10px;
        color: #596a7c;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        border-bottom: 1px solid var(--line);
        background: #fbfcfd;
    }
    .timeline-table td {
        padding: 14px 10px;
        border-bottom: 1px solid #edf1f4;
        color: var(--ink);
        font-size: 13.5px;
        vertical-align: middle;
    }
    .timeline-table td small {
        font-size: 12px !important;
    }
    .timeline-table tbody tr:hover {
        background: #fafcfe;
    }
    .timeline-table tbody tr:last-child td {
        border-bottom: 0;
    }
    .timeline-meeting {
        color: var(--navy);
        text-decoration: none;
        font-weight: 700;
    }
    .timeline-meeting:hover {
        text-decoration: underline;
    }
    .timeline-state {
        display: inline-flex;
        align-items: center;
        padding: 5px 10px;
        border-radius: 999px;
        background: #eef2f5;
        color: #46576a;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .timeline-state.live {
        background: #e3f4e8;
        color: #267344;
    }
    .timeline-state.done {
        background: #e8edf2;
        color: var(--navy);
    }
    .timeline-state.cancelled {
        background: #fdecea;
        color: #a5382c;
    }
    .timeline-empty, .dashboard-empty {
        padding: 24px 12px;
        color: #596a7c;
        font-size: 13.5px;
        text-align: center;
    }

    /* Room Occupancy */
    .room-occupancy {
        display: grid;
        gap: 16px;
    }
    .room-occupancy-row {
        display: grid;
        grid-template-columns: minmax(100px, 1fr) 1.5fr auto;
        align-items: center;
        gap: 14px;
        font-size: 13px;
    }
    .room-occupancy-name {
        color: var(--navy);
        font-weight: 700;
    }
    .occupancy-track {
        height: 10px;
        background: #edf1f4;
        border-radius: 999px;
        overflow: hidden;
    }
    .occupancy-fill {
        height: 100%;
        background: #2f9e5b;
        border-radius: 999px;
        transition: width 0.3s ease;
    }
    .occupancy-fill.free {
        background: #c9d3dc;
    }
    .occupancy-status {
        color: #596a7c;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }
    .occupancy-summary {
        margin-top: 18px;
        padding-top: 14px;
        border-top: 1px solid var(--line);
        font-size: 13px;
        color: #596a7c;
    }

    /* Quick Operations Grid (3 cols) */
    .dashboard-quick {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 20px;
        margin-top: 24px;
    }
    .quick-list {
        display: grid;
        gap: 0;
    }
    .quick-item {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 0;
        border-bottom: 1px solid #edf1f4;
        font-size: 13px;
        line-height: 1.5;
    }
    .quick-item:last-child {
        border-bottom: 0;
    }
    .quick-item strong {
        color: var(--navy);
    }
    .quick-item small {
        display: block;
        margin-top: 3px;
        color: #596a7c;
        font-size: 12px;
    }
    .quick-item a {
        color: var(--navy);
        text-decoration: none;
        font-weight: 700;
    }
    .quick-item a:hover {
        text-decoration: underline;
    }
    .arrival-badge {
        align-self: flex-start;
        padding: 4px 8px;
        border-radius: 6px;
        background: #e8f6ed;
        color: #267344;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .arrival-badge.late {
        background: #fff4dd;
        color: #946a00;
    }
    .arrival-badge.no-show {
        background: #fdecea;
        color: #a5382c;
    }

    /* Health Metrics */
    .health-metrics {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .health-metric {
        background: var(--paper);
        border-radius: 12px;
        padding: 14px;
    }
    .health-metric strong {
        display: block;
        color: var(--navy);
        font: 700 24px "Space Grotesk", sans-serif;
    }
    .health-metric span {
        display: block;
        margin-top: 4px;
        color: #596a7c;
        font-size: 12px;
        line-height: 1.4;
    }
    .health-note {
        margin: 14px 0 0;
        color: #596a7c;
        font-size: 12px;
        line-height: 1.6;
    }

    /* Responsive adjustments */
    @media (max-width: 980px) {
        .db-hero-inner {
            grid-template-columns: 1fr;
            gap: 28px;
        }
        .db-hero {
            padding: 32px 28px;
        }
        .db-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .dashboard-quick {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 600px) {
        .db-stat-grid {
            grid-template-columns: 1fr;
        }
        .db-hero {
            padding: 24px 20px;
        }
        .db-title {
            font-size: 32px;
        }
        .timeline-table th:nth-child(3),
        .timeline-table td:nth-child(3) {
            display: none;
        }
    }

    /* ============ AykonMeet Meeting Type Selection Modal ============ */
    #meeting-type-dialog {
        width: min(880px, calc(100% - 32px)) !important;
        border: 1px solid rgba(17, 43, 73, 0.14) !important;
        border-radius: 26px !important;
        padding: 0 !important;
        background: transparent !important;
        box-shadow: 0 32px 84px -16px rgba(11, 31, 56, 0.38), 0 0 0 1px rgba(255, 255, 255, 0.25) !important;
        overflow: hidden !important;
    }
    #meeting-type-dialog::backdrop {
        background: rgba(11, 31, 56, 0.68) !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
    }
    #meeting-type-dialog .form-panel.mto-dialog-panel,
    #meeting-type-dialog .form-panel {
        background: #ffffff !important;
        padding: 38px 42px !important;
        margin: 0 !important;
        border: 0 !important;
        border-radius: 26px !important;
        position: relative;
    }
    #meeting-type-dialog .form-panel::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(90deg, #0b1f38 0%, #112b49 35%, #f7c948 70%, #f0b420 100%);
        border-top-left-radius: 26px;
        border-top-right-radius: 26px;
    }

    /* Dialog Header */
    .mto-dialog-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 26px;
    }
    .mto-dialog-title-wrap {
        flex: 1;
        min-width: 0;
    }
    .mto-dialog-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font: 700 11px/1 'Space Grotesk', sans-serif;
        letter-spacing: 2.2px;
        text-transform: uppercase;
        color: #b18800;
        margin-bottom: 10px;
    }
    .mto-dialog-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #f7c948;
        box-shadow: 0 0 0 3px rgba(247, 201, 72, 0.3);
    }
    .mto-dialog-title {
        font-family: 'Fraunces', Georgia, serif;
        font-size: clamp(26px, 3.2vw, 34px);
        font-weight: 500;
        color: #112b49;
        margin: 0 0 6px;
        line-height: 1.15;
        letter-spacing: -0.6px;
    }
    .mto-dialog-title em {
        font-style: italic;
        color: #b18800;
    }
    .mto-dialog-desc {
        margin: 0;
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        color: #596a7c;
        line-height: 1.5;
    }
    .mto-close-btn {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #f1f5f8;
        border: 1px solid #e2e8ef;
        color: #112b49;
        font-size: 22px;
        line-height: 1;
        display: grid;
        place-items: center;
        cursor: pointer;
        transition: all 0.2s ease;
        flex: none;
    }
    .mto-close-btn:hover {
        background: #112b49;
        color: #f7c948;
        border-color: #112b49;
        transform: rotate(90deg);
    }

    /* 2x2 Option Cards Grid */
    .meeting-type-options {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    /* Option Card */
    .meeting-type-option.mto-card,
    .meeting-type-option {
        background: #ffffff;
        border: 1.5px solid #e4eaf1;
        border-radius: 18px;
        padding: 22px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 176px;
        text-align: left;
        cursor: pointer;
        position: relative;
        text-decoration: none;
        transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1),
                    box-shadow 0.22s cubic-bezier(0.16, 1, 0.3, 1),
                    border-color 0.22s cubic-bezier(0.16, 1, 0.3, 1),
                    background-color 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 4px 14px rgba(17, 43, 73, 0.04);
        width: 100%;
        font: inherit;
    }
    .meeting-type-option.mto-card:hover,
    .meeting-type-option.mto-card:focus-visible,
    .meeting-type-option:hover,
    .meeting-type-option:focus-visible {
        transform: translateY(-4px);
        border-color: #f7c948;
        background: linear-gradient(145deg, #ffffff 0%, #fffdf5 100%);
        box-shadow: 0 16px 36px rgba(17, 43, 73, 0.12), 0 0 0 1px #f7c948;
        outline: none;
    }

    /* Card Top: Icon + Badge */
    .mto-card-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
    }
    .meeting-type-icon {
        width: 48px;
        height: 48px;
        border-radius: 13px;
        display: grid;
        place-items: center;
        font-size: 22px;
        transition: transform 0.2s ease;
    }
    .meeting-type-option:hover .meeting-type-icon {
        transform: scale(1.08);
    }

    .mto-icon-online {
        background: linear-gradient(135deg, #0b1f38, #173a63);
        color: #f7c948;
        box-shadow: 0 6px 14px rgba(11, 31, 56, 0.22);
    }
    .mto-icon-multi {
        background: linear-gradient(135deg, #112b49, #1a4473);
        color: #f7c948;
        box-shadow: 0 6px 14px rgba(17, 43, 73, 0.20);
    }
    .mto-icon-inperson {
        background: linear-gradient(135deg, #173a63, #234e7f);
        color: #f7c948;
        box-shadow: 0 6px 14px rgba(23, 58, 99, 0.20);
    }
    .mto-icon-hybrid {
        background: linear-gradient(135deg, #f7c948, #f0b420);
        color: #112b49;
        box-shadow: 0 6px 16px rgba(247, 201, 72, 0.38);
    }

    /* Badges */
    .mto-badge {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 999px;
    }
    .mto-badge-online {
        background: #eef4fb;
        color: #173a63;
        border: 1px solid #d4e2f0;
    }
    .mto-badge-multi {
        background: #fdf6e2;
        color: #926d00;
        border: 1px solid #fae6a6;
    }
    .mto-badge-inperson {
        background: #f1f5f8;
        color: #112b49;
        border: 1px solid #dce4ed;
    }
    .mto-badge-hybrid {
        background: #112b49;
        color: #f7c948;
        border: 1px solid #f7c948;
    }

    /* Card Body */
    .mto-card-body {
        flex: 1;
        margin-bottom: 14px;
    }
    .meeting-type-option strong {
        display: block;
        font-family: 'Space Grotesk', sans-serif;
        font-size: 16px;
        font-weight: 700;
        color: #112b49;
        line-height: 1.3;
        letter-spacing: -0.2px;
        margin-bottom: 4px;
    }
    .meeting-type-option small {
        display: block;
        font-family: 'DM Sans', sans-serif;
        font-size: 13px;
        color: #596a7c;
        line-height: 1.5;
        font-weight: 400;
    }

    /* Card Footer: Feature & Arrow */
    .mto-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding-top: 12px;
        border-top: 1px solid #f0f4f8;
    }
    .mto-feature {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 11.5px;
        font-weight: 600;
        color: #718096;
    }
    .mto-arrow {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #f4f7fb;
        color: #112b49;
        display: grid;
        place-items: center;
        font-size: 15px;
        font-weight: 700;
        border: 1px solid #e4eaf1;
        transition: all 0.2s ease;
        flex: none;
    }
    .meeting-type-option:hover .mto-arrow {
        background: #f7c948;
        color: #112b49;
        border-color: #f7c948;
        transform: translateX(4px);
    }

    /* Room note card */
    .mto-note-card {
        margin-top: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        border-radius: 14px;
        background: #fffdf5;
        border: 1px solid #f6e6b4;
    }
    .mto-note-icon {
        font-size: 18px;
        color: #b18800;
        flex: none;
    }
    .mto-note-card .meeting-type-note {
        margin: 0;
        font-size: 13px;
        color: #596a7c;
        line-height: 1.5;
    }
    .mto-note-card .meeting-type-note a {
        color: #112b49;
        font-weight: 700;
        text-decoration: underline;
        text-underline-offset: 2px;
    }
    .mto-note-card .meeting-type-note a:hover {
        color: #b18800;
    }

    @media (max-width: 680px) {
        #meeting-type-dialog .form-panel.mto-dialog-panel,
        #meeting-type-dialog .form-panel {
            padding: 26px 18px !important;
        }
        .meeting-type-options {
            grid-template-columns: 1fr;
            gap: 12px;
        }
    }

    /* ============ New Meeting Dialog — AykonMeet Redesign ============ */
    #new-meeting-dialog {
        width: min(800px, calc(100% - 32px)) !important;
        max-height: calc(100vh - 32px) !important;
        border: 0 !important;
        padding: 0 !important;
        border-radius: 24px !important;
        background: transparent !important;
        box-shadow: 0 32px 80px -16px rgba(11, 31, 56, 0.42), 0 0 0 1px rgba(255,255,255,0.18) !important;
        overflow: hidden !important;
    }
    #new-meeting-dialog::backdrop {
        background: rgba(11, 31, 56, 0.72) !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
    }
    #new-meeting-dialog .form-panel {
        max-height: calc(100vh - 32px) !important;
        overflow-y: auto !important;
        padding: 0 !important;
        background: #ffffff !important;
        border-radius: 24px !important;
        border: 0 !important;
        margin: 0 !important;
        scrollbar-width: thin;
        scrollbar-color: #dde5ed transparent;
    }
    /* Branded top accent bar */
    #new-meeting-dialog .form-panel::before {
        content: "";
        display: block;
        height: 5px;
        background: linear-gradient(90deg, #0b1f38 0%, #112b49 35%, #f7c948 70%, #f0b420 100%);
        border-radius: 24px 24px 0 0;
        flex: none;
    }
    /* Dialog inner scroll wrapper */
    .nmf-body {
        padding: 32px 38px 36px;
    }
    /* Dialog header */
    .nmf-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 28px;
    }
    .nmf-header-left {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        flex: 1;
        min-width: 0;
    }
    .nmf-header-icon {
        flex: none;
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        font-size: 22px;
        background: linear-gradient(135deg, #0b1f38, #173a63);
        color: #f7c948;
        box-shadow: 0 6px 16px rgba(11,31,56,0.22);
        transition: transform 0.2s ease;
    }
    .nmf-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font: 700 10.5px/1 'Space Grotesk', sans-serif;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: #b18800;
        margin-bottom: 6px;
    }
    .nmf-eyebrow-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #f7c948;
        box-shadow: 0 0 0 3px rgba(247,201,72,0.28);
        display: inline-block;
    }
    #meeting-form-title {
        margin: 0 0 4px;
        font-family: 'Fraunces', Georgia, serif;
        font-size: clamp(22px, 2.8vw, 28px);
        font-weight: 500;
        color: #112b49;
        letter-spacing: -0.5px;
        line-height: 1.2;
    }
    #meeting-form-description {
        margin: 0;
        font-family: 'DM Sans', sans-serif;
        font-size: 13.5px;
        color: #596a7c;
        line-height: 1.5;
    }
    .nmf-close-btn {
        flex: none;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #f1f5f8;
        border: 1px solid #e2e8ef;
        color: #112b49;
        font-size: 20px;
        line-height: 1;
        display: grid;
        place-items: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .nmf-close-btn:hover {
        background: #112b49;
        color: #f7c948;
        border-color: #112b49;
        transform: rotate(90deg);
    }
    /* Section divider */
    .nmf-section {
        margin-top: 26px;
        padding-top: 22px;
        border-top: 1px solid #e8edf3;
    }
    .nmf-section-head {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
    }
    .nmf-section-icon {
        width: 30px;
        height: 30px;
        border-radius: 9px;
        background: #f1f5f9;
        color: #112b49;
        display: grid;
        place-items: center;
        font-size: 15px;
        flex: none;
    }
    .nmf-section-title {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 13.5px;
        font-weight: 700;
        color: #112b49;
        margin: 0;
        letter-spacing: -0.1px;
    }
    .nmf-section-sub {
        font-size: 12px;
        color: #718096;
        margin: 1px 0 0;
    }
    /* Form grid */
    .nmf-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px 18px;
    }
    .nmf-grid .wide { grid-column: 1 / -1; }
    /* Form field labels & inputs */
    .nmf-field {
        display: block;
        min-width: 0;
    }
    .nmf-label {
        display: block;
        font-family: 'Space Grotesk', sans-serif;
        font-size: 11.5px;
        font-weight: 700;
        color: #112b49;
        letter-spacing: 0.3px;
        margin-bottom: 7px;
    }
    .nmf-label .nmf-req { color: #e84545; }
    .nmf-field input,
    .nmf-field select,
    .nmf-field textarea {
        display: block;
        width: 100%;
        min-width: 0;
        height: 42px;
        padding: 0 14px;
        border: 1.5px solid #dce4ed;
        border-radius: 10px;
        background: #fafcfe;
        color: #112b49;
        font: 14px/1 'DM Sans', sans-serif;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        outline: none;
        box-sizing: border-box;
    }
    .nmf-field input[readonly] {
        background: #f1f5f9;
        color: #718096;
        cursor: default;
    }
    .nmf-field textarea {
        height: auto;
        min-height: 80px;
        padding: 12px 14px;
        resize: vertical;
        line-height: 1.5;
    }
    .nmf-field select {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23596a7c' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        padding-right: 38px;
    }
    .nmf-field input:focus,
    .nmf-field select:focus,
    .nmf-field textarea:focus {
        border-color: #f7c948;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(247, 201, 72, 0.2);
    }
    .nmf-field-hint {
        display: block;
        margin-top: 5px;
        font-size: 11.5px;
        color: #718096;
        line-height: 1.4;
    }
    /* Type badge pill shown in header area */
    .nmf-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 999px;
        font: 700 11px 'Space Grotesk', sans-serif;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        flex: none;
        align-self: center;
    }
    .nmf-type-badge-online  { background: #eef4fb; color: #173a63; border: 1px solid #c5d8ef; }
    .nmf-type-badge-inperson { background: #f1f5f8; color: #112b49; border: 1px solid #d1dce9; }
    /* Recurrence section */
    .nmf-recurrence-preview {
        padding: 12px 16px;
        background: #f7faff;
        border: 1px solid #d0e2f5;
        border-radius: 10px;
        font-size: 12.5px;
        color: #112b49;
        grid-column: 1 / -1;
    }
    .nmf-recurrence-preview strong { color: #112b49; font-weight: 700; }
    /* Weekly days checkboxes */
    .nmf-days-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 6px;
        grid-column: 1 / -1;
    }
    .nmf-day-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
        cursor: pointer;
        font-size: 11px;
        font-weight: 600;
        color: #596a7c;
    }
    .nmf-day-label input[type=checkbox] {
        width: 18px;
        height: 18px;
        accent-color: #f7c948;
        cursor: pointer;
    }
    /* Attendees section */
    .nmf-attendee-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto;
        gap: 10px;
        align-items: end;
        margin-bottom: 10px;
    }
    .nmf-attendee-row .nmf-field { margin: 0; }
    .nmf-remove-btn {
        width: 42px;
        height: 42px;
        display: grid;
        place-items: center;
        border: 1.5px solid #f5c3c3;
        border-radius: 10px;
        background: #fff5f5;
        color: #c03b2d;
        font-size: 18px;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.18s ease;
        flex: none;
    }
    .nmf-remove-btn:hover { background: #fde8e8; border-color: #e87b7b; }
    .nmf-add-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-top: 4px;
        padding: 10px 16px;
        border: 1.5px dashed #c5a100;
        border-radius: 10px;
        background: #fffbee;
        color: #896500;
        font: 700 12px 'DM Sans', sans-serif;
        cursor: pointer;
        transition: background 0.18s ease;
    }
    .nmf-add-btn:hover { background: #fff3cc; }
    /* Dialog footer actions */
    .nmf-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid #e8edf3;
    }
    .nmf-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 44px;
        padding: 0 22px;
        border-radius: 11px;
        font: 700 13.5px 'DM Sans', sans-serif;
        cursor: pointer;
        border: 0;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        text-decoration: none;
    }
    .nmf-btn-ghost {
        background: #f1f5f8;
        color: #112b49;
        border: 1.5px solid #dce4ed;
    }
    .nmf-btn-ghost:hover { background: #e4eaf1; }
    .nmf-btn-primary {
        background: linear-gradient(135deg, #f7c948, #f0b420);
        color: #112b49;
        box-shadow: 0 8px 22px rgba(247,201,72,0.36);
    }
    .nmf-btn-primary:hover {
        background: linear-gradient(135deg, #ffd766, #f7c948);
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(247,201,72,0.44);
    }
    /* Wide input number */
    input[type=number].nmf-small { width: 90px; }
    @media(max-width:760px) {
        .nmf-body { padding: 22px 18px 24px; }
        .nmf-grid { grid-template-columns: 1fr; }
        .nmf-attendee-row { grid-template-columns: 1fr auto; }
        .nmf-attendee-row .nmf-field:nth-child(2) { grid-column: 1; }
    }
    #new-meeting-dialog:has(#hybrid-form-layout:not([hidden])) .form-panel { background: #f2f5f8 !important; }
    #hybrid-form-layout { --hybrid-navy: #112b49; --hybrid-navy-light: #1a3b60; --hybrid-gold: #f7c948; }
    .hybrid-create-heading { display: flex; justify-content: space-between; align-items: center; gap: 18px; margin-bottom: 14px; padding: 14px 17px; border: 1px solid #d8e0e8; border-top: 3px solid var(--hybrid-gold); border-radius: 8px; background: #fff; }
    .hybrid-create-heading h2 { margin: 0; color: var(--hybrid-navy); font: 700 20px "Space Grotesk", sans-serif; }
    .hybrid-create-heading p { margin: 4px 0 0; color: #61748d; font-size: 11px; }
    .hybrid-heading-status { display: flex; align-items: center; gap: 12px; color: var(--hybrid-navy); font-size: 11px; font-weight: 800; }
    .hybrid-heading-status span { padding: 9px 12px; border: 1px solid var(--hybrid-gold); border-radius: 9px; background: var(--hybrid-navy); color: var(--hybrid-gold); white-space: nowrap; }
    .hybrid-create-layout { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(320px, 1fr); gap: 14px; align-items: start; }
    .hybrid-create-main, .hybrid-create-card { min-width: 0; padding: 15px; border: 1px solid #d8e0e8; border-radius: 8px; background: #fff; }
    .hybrid-create-sidebar { display: grid; gap: 10px; min-width: 0; }
    .hybrid-create-section-title { display: flex; align-items: center; gap: 9px; margin: 0 0 13px; color: var(--hybrid-navy); font-size: 13px; font-weight: 800; }
    .hybrid-create-section-title .section-symbol { display: grid; place-items: center; width: 20px; height: 20px; color: #b18800; font-size: 16px; }
    .hybrid-type-pill { display: flex; align-items: center; gap: 10px; width: 180px; margin-bottom: 14px; padding: 9px 11px; border: 1px solid var(--hybrid-gold); border-radius: 6px; background: var(--hybrid-navy); color: var(--hybrid-gold); font-size: 11px; font-weight: 800; }
    .hybrid-type-pill small { display: block; margin-top: 3px; color: #f5dda0; font-size: 9px; font-weight: 500; }
    .hybrid-create-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .hybrid-create-fields label { display: block; min-width: 0; color: var(--hybrid-navy); font-size: 11px; font-weight: 700; }
    .hybrid-create-fields [hidden] { display: none !important; }
    .hybrid-create-fields input, .hybrid-create-fields select, .hybrid-create-fields textarea { display: block; width: 100%; min-width: 0; min-height: 36px; margin-top: 7px; padding: 9px 10px; border: 1px solid #c9d5e2; border-radius: 5px; background: #fff; color: var(--hybrid-navy); font: 11px "DM Sans", sans-serif; }
    .hybrid-create-fields input:focus, .hybrid-create-fields select:focus, .hybrid-create-fields textarea:focus { outline: 3px solid rgba(247,201,72,.22); border-color: #b18800; }
    .hybrid-create-fields label small { color: #6b8098; font-size: 9px; font-weight: 400; }
    .hybrid-create-fields textarea { min-height: 62px; resize: vertical; }
    .hybrid-create-fields .wide { grid-column: 1 / -1; }
    .hybrid-create-required { color: #f04452; }
    .hybrid-create-subtitle { grid-column: 1 / -1; margin: 4px 0 -3px; padding-top: 9px; border-top: 1px solid #e2e8ef; color: var(--hybrid-navy); font-size: 10px; font-weight: 800; }
    .hybrid-detail-pair { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }
    .hybrid-detail-box { min-width: 0; min-height: 84px; padding: 12px; border: 1px solid #d8e0e8; border-radius: 7px; background: #f5f7f9; }
    .hybrid-detail-box strong { display: block; margin-bottom: 8px; color: var(--hybrid-navy); font-size: 10px; }
    .hybrid-detail-box span, .hybrid-detail-box small { display: block; overflow-wrap: anywhere; color: #627b99; font-size: 9px; line-height: 1.45; }
    .hybrid-counts { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 7px; }
    .hybrid-count-card { padding: 10px 5px; border: 1px solid #d8e0e8; border-radius: 6px; text-align: center; }
    .hybrid-count-card span { display: block; color: #375272; font-size: 9px; }
    .hybrid-count-card strong { display: block; margin-top: 4px; color: var(--hybrid-navy); font-size: 17px; }
    .hybrid-count-card i { display: inline-block; width: 7px; height: 7px; margin-right: 4px; border-radius: 50%; background: var(--hybrid-gold); }
    .hybrid-count-card.online i { background: var(--hybrid-navy); }
    .hybrid-count-card.pending i { background: #b9c7d8; }
    .hybrid-unlimited { margin: 9px 0 0; color: var(--hybrid-navy); font-size: 10px; }
    .hybrid-option-note { margin: -7px 0 10px; color: #6b8098; font-size: 9px; line-height: 1.4; }
    .hybrid-option-row { display: flex; align-items: center; gap: 9px; margin-top: 7px; padding: 9px; border: 1px solid #d8e0e8; border-radius: 6px; color: var(--hybrid-navy); font-size: 10px; font-weight: 700; }
    .hybrid-option-row span { display: block; margin-top: 3px; color: #6b8098; font-size: 9px; font-weight: 400; }
    .hybrid-equipment { display: flex; justify-content: space-between; gap: 6px; flex-wrap: wrap; }
    .hybrid-equipment span { color: #315579; font-size: 9px; text-align: center; }
    .hybrid-equipment b { display: block; margin-bottom: 4px; color: #b18800; font-size: 15px; }
    .hybrid-info { padding: 10px 12px; border: 1px solid #ead58b; border-radius: 6px; background: #fff9e5; color: var(--hybrid-navy); font-size: 9px; line-height: 1.5; }
    .hybrid-info strong { display: block; margin-bottom: 3px; color: var(--hybrid-navy); }
    .hybrid-organizer-fields { display: grid; grid-column: 1 / -1; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
    .hybrid-create-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 13px; padding-top: 12px; border-top: 1px solid #e2e8ef; }
    .hybrid-create-actions .button.primary { border: 1px solid var(--hybrid-gold); background: var(--hybrid-gold); color: var(--hybrid-navy); }
    .hybrid-create-actions .button.primary:hover { background: #ffda69; border-color: #ffda69; }
    .hybrid-create-actions .button.secondary { border: 1px solid var(--hybrid-navy); background: var(--hybrid-navy); color: #fff; }
    .hybrid-create-actions .button.secondary:hover { background: var(--hybrid-navy-light); }
    #hybrid-form-layout .dialog-close:focus-visible, .hybrid-create-actions .button:focus-visible { outline: 3px solid rgba(247,201,72,.55); outline-offset: 2px; }
    #hybrid-form-layout[hidden], #standard-form-layout[hidden], #multi-company-form-layout[hidden] { display: none; }
    #multi-company-form-layout { --hybrid-navy: #112b49; --hybrid-navy-light: #1a3b60; --hybrid-gold: #f7c948; }
    #new-meeting-dialog:has(#multi-company-form-layout:not([hidden])) .form-panel { background: #f2f5f8 !important; }
    .multi-company-rows { grid-column: 1 / -1; display: grid; gap: 8px; }
    .multi-company-row { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr) auto; gap: 8px; align-items: center; }
    .hybrid-create-fields .multi-company-row input { margin-top: 0; }
    .multi-company-remove { display: grid; place-items: center; width: 36px; height: 36px; border: 1px solid #e3b7b7; border-radius: 6px; background: #fff3f3; color: #c03b2d; font-size: 16px; font-weight: 700; cursor: pointer; }
    .multi-company-remove:hover { background: #fbe0e0; }
    .multi-company-add { grid-column: 1 / -1; justify-self: start; margin-top: 2px; padding: 9px 13px; border: 1px dashed #b18800; border-radius: 6px; background: #fffaf0; color: #8a6d00; font: 700 11px "DM Sans", sans-serif; cursor: pointer; }
    .multi-company-add:hover { background: #fff3d6; }
    @media(max-width:760px) {
        #new-meeting-dialog { width: calc(100% - 18px); max-height: calc(100vh - 18px); }
        #new-meeting-dialog .form-panel { max-height: calc(100vh - 18px); padding: 11px; }
        .hybrid-create-layout { grid-template-columns: 1fr; }
        .hybrid-create-heading { align-items: flex-start; flex-direction: column; }
        .hybrid-heading-status { flex-wrap: wrap; }
        .hybrid-organizer-fields { grid-template-columns: 1fr; }
        .hybrid-create-fields { gap: 10px; }
    }
    #meeting-room-field[hidden] { display: none; }
</style>

<div class="db-hero">
    <div class="db-hero-inner">
        <div class="db-hero-copy">
            <span class="db-eyebrow">
                <span class="d"></span>
                Admin console &middot; {{ now()->format('l, d M Y') }}
            </span>
            <h1 class="db-title">Welcome back, <em>{{ auth()->user()->name ?? 'admin' }}.</em></h1>
            <p class="db-sub">Manage meeting rooms, track live attendance, and launch instant online sessions.</p>
            <div class="db-hero-actions">
                <button class="db-btn db-btn-primary" type="button" onclick="document.getElementById('meeting-type-dialog').showModal()">+ New meeting</button>
                <a class="db-btn db-btn-ghost" href="{{ route('sessions.index') }}">🎥 Online sessions</a>
            </div>
        </div>
        <div class="db-quickcard">
            <p class="db-quickcard-title">Quick actions</p>
            <a class="db-quickcard-link" href="{{ route('meetings.check-ins') }}">
                <span class="ico">✓</span>
                <span class="txt">Check-in &amp; arrivals<small>Today's recorded arrivals</small></span>
                <span class="go">&rarr;</span>
            </a>
            <a class="db-quickcard-link" href="{{ route('meetings.live') }}">
                <span class="ico live">●</span>
                <span class="txt">Live meetings<small>{{ $stats['live_meetings'] }} in progress now</small></span>
                <span class="go">&rarr;</span>
            </a>
            <a class="db-quickcard-link" href="{{ route('meetings.all') }}">
                <span class="ico">▦</span>
                <span class="txt">All meetings<small>{{ $stats['total_meetings'] }} on record</small></span>
                <span class="go">&rarr;</span>
            </a>
            <a class="db-quickcard-link" href="{{ route('admin.dashboard') }}">
                <span class="ico">⌂</span>
                <span class="txt">Rooms<small>Manage meeting spaces</small></span>
                <span class="go">&rarr;</span>
            </a>
        </div>
    </div>
</div>


<section class="stat-grid db-stat-grid" aria-label="Meeting overview">
    <a class="stat-card meetings-total-card" href="{{ route('meetings.all') }}" aria-label="View all {{ $stats['total_meetings'] }} meetings"><div class="dashboard-stat-top"><span class="stat-label">Total meetings</span><span class="dashboard-stat-icon" aria-hidden="true">▦</span></div><strong>{{ $stats['total_meetings'] }}</strong><small>All scheduled meetings</small><span class="dashboard-stat-extra">{{ $stats['total_trend'] >= 0 ? '+' : '' }}{{ $stats['total_trend'] }} vs yesterday</span></a>
    <a class="stat-card accent today-stat-card" href="{{ route('meetings.all', ['date' => now()->toDateString()]) }}" aria-label="View today's {{ $stats['today_meetings'] }} meetings"><div class="dashboard-stat-top"><span class="stat-label">Today</span><span class="dashboard-stat-icon" aria-hidden="true">◷</span></div><strong>{{ $stats['today_meetings'] }}</strong><small>{{ now()->format('D, d M') }}</small><span class="dashboard-stat-extra">{{ $nextMeeting ? 'Next at '.$nextMeeting->start_date->format('g:i A') : 'No more meetings today' }}</span></a>
    <a class="stat-card live-stat-card" href="{{ route('meetings.live') }}" aria-label="View {{ $stats['live_meetings'] }} live meetings">
        <div class="dashboard-stat-top"><span class="stat-label">Live now</span><span class="dashboard-stat-icon live" aria-hidden="true">●</span></div>
        <strong>{{ $stats['live_meetings'] }}</strong>
        @if($stats['live_meetings'] > 0)
            <span class="dashboard-stat-live">View live meetings</span>
        @else
            <small>No meetings in progress</small>
        @endif
    </a>
    <a class="stat-card checked-in-stat-card" href="{{ route('meetings.check-ins') }}" aria-label="View today's check-in details">
        <div class="dashboard-stat-top"><span class="stat-label">Checked in</span><span class="dashboard-stat-icon" aria-hidden="true">✓</span></div>
        <strong>{{ $stats['checked_in'] }}</strong>
        <small>Arrivals recorded today</small>
        <span class="dashboard-stat-extra">{{ $stats['pending_check_in'] }} pending check-in</span>
    </a>
</section>

<section class="dashboard-columns" aria-label="Meeting operations">
    <div class="dashboard-column">
        <section class="dashboard-panel">
            <header class="dashboard-panel-head"><div><h2>Today's Meetings Timeline</h2><p>Time, room, host, and current status</p></div><span class="dashboard-panel-count">{{ $todayMeetings->count() }} meetings</span></header>
            @if($todayMeetings->isEmpty())
                <div class="timeline-empty">No meetings scheduled for today.</div>
            @else
                <div style="overflow-x:auto">
                    <table class="timeline-table">
                        <thead><tr><th>Time</th><th>Room</th><th>Host</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach($todayMeetings as $meeting)
                            @php
                                $timelineStatus = $meeting->status === 'in_progress' ? 'live' : (in_array($meeting->status, ['completed', 'cancelled'], true) ? ($meeting->status === 'cancelled' ? 'cancelled' : 'done') : 'upcoming');
                                $timelineLabel = $timelineStatus === 'live' ? 'Live' : ($timelineStatus === 'done' ? 'Done' : ($timelineStatus === 'cancelled' ? 'Cancelled' : 'Upcoming'));
                            @endphp
                            <tr>
                                <td>{{ $meeting->start_date->format('g:i A') }}<small style="display:block;color:var(--muted);margin-top:3px">{{ $meeting->end_date->format('g:i A') }}</small></td>
                                <td><strong>{{ $meeting->room?->name ?? ($meeting->is_online ? 'Online' : 'Room not assigned') }}</strong><a class="timeline-meeting" style="display:block;margin-top:3px" href="{{ route('meetings.show', $meeting->meeting_code) }}">{{ $meeting->meeting_topic }}</a></td>
                                <td>{{ $meeting->name }}</td>
                                <td><span class="timeline-state {{ $timelineStatus }}">{{ $timelineLabel }}</span></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="dashboard-panel">
            <header class="dashboard-panel-head"><div><h2>Room Occupancy</h2><p>Rooms with meetings in progress</p></div><span class="dashboard-panel-count">{{ $occupiedRooms }}/{{ $rooms->count() }} occupied</span></header>
            @if($rooms->isEmpty())
                <div class="dashboard-empty">No meeting rooms have been added yet.</div>
            @else
                <div class="room-occupancy">
                    @foreach($rooms as $room)
                        @php($occupiedMeeting = $room->meetings->first())
                        <div class="room-occupancy-row">
                            <span class="room-occupancy-name">{{ $room->name }} <small style="display:block;color:var(--muted);font-weight:400">{{ $room->number }}</small></span>
                            <div class="occupancy-track" role="img" aria-label="{{ $room->name }} {{ $occupiedMeeting ? 'occupied' : 'available' }}"><div class="occupancy-fill {{ $occupiedMeeting ? '' : 'free' }}" style="width:{{ $occupiedMeeting ? '100%' : '0%' }}"></div></div>
                            <span class="occupancy-status">{{ $occupiedMeeting ? 'Occupied' : 'Available' }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="occupancy-summary">{{ $stats['room_occupancy_percent'] }}% of rooms are occupied right now.</div>
            @endif
        </section>
    </div>

</section>

<section class="dashboard-quick" aria-label="Quick operations">
    <section class="dashboard-panel" id="upcoming-meetings">
        <header class="dashboard-panel-head"><div><h2>Upcoming Meetings</h2><p>Starting in the next 3 hours</p></div><span class="dashboard-panel-count">{{ $upcomingMeetings->count() }}</span></header>
        <div class="quick-list">
            @forelse($upcomingMeetings as $meeting)
                <div class="quick-item"><div><a href="{{ route('meetings.show', $meeting->meeting_code) }}">{{ $meeting->meeting_topic }}</a><small>{{ $meeting->start_date->format('g:i A') }} · {{ $meeting->room?->name ?? 'Online' }}</small></div><small>{{ $meeting->name }}</small></div>
            @empty
                <div class="dashboard-empty">No meetings starting in the next 3 hours.</div>
            @endforelse
        </div>
    </section>

    <section class="dashboard-panel" id="rooms-needing-attention">
        <header class="dashboard-panel-head"><div><h2>Rooms Needing Attention</h2><p>Overstays and check-outs to review</p></div></header>
        <div class="quick-list">
            @foreach($overstayedMeetings as $meeting)
                <div class="quick-item"><div><a href="{{ route('meetings.show', $meeting->meeting_code) }}">{{ $meeting->room?->name ?? 'Online meeting' }} overstayed</a><small>{{ $meeting->meeting_topic }} · ended {{ $meeting->end_date->diffForHumans() }}</small></div><span class="arrival-badge late">Overdue</span></div>
            @endforeach
            @foreach($notCheckedOut as $attendee)
                <div class="quick-item"><div><a href="{{ route('meetings.attendance', $attendee->meeting) }}">{{ $attendee->full_name }} not checked out</a><small>{{ $attendee->meeting->room?->name ?? $attendee->meeting->meeting_topic }}</small></div></div>
            @endforeach
            @if($overstayedMeetings->isEmpty() && $notCheckedOut->isEmpty())
                <div class="dashboard-empty">All rooms are clear. No overdue check-outs.</div>
            @endif
        </div>
    </section>

    <section class="dashboard-panel">
        <header class="dashboard-panel-head"><div><h2>System Health</h2><p>Live session connectivity</p></div></header>
        <div class="health-metrics">
            <div class="health-metric"><strong>{{ $activeDevices }}</strong><span>Active participants / devices</span></div>
            <div class="health-metric"><strong>{{ $stats['live_meetings'] }}</strong><span>Meetings live now</span></div>
            <div class="health-metric"><strong>{{ $stats['room_occupancy_percent'] }}%</strong><span>Physical room occupancy</span></div>
            <div class="health-metric"><strong>{{ $stats['live_meetings'] > 0 ? 'Live' : 'Idle' }}</strong><span>Session service</span></div>
        </div>
        <p class="health-note">Camera permission and device health are managed by each participant's browser and are not reported to the server.</p>
    </section>
</section>

<div>
    <dialog class="mdd-dialog" id="meeting-type-dialog">
        <div class="panel form-panel mto-dialog-panel">
            <div class="dialog-heading mto-dialog-header">
                <div class="mto-dialog-title-wrap">
                    <span class="mto-dialog-eyebrow"><span class="mto-dialog-dot"></span> AykonMeet Workspace</span>
                    <h3 class="mto-dialog-title">Choose meeting type</h3>
                    <p class="mto-dialog-desc">Select how people will attend. Choose the ideal format for your session.</p>
                </div>
                <button class="dialog-close mto-close-btn" type="button" onclick="document.getElementById('meeting-type-dialog').close()" aria-label="Close meeting type selection">×</button>
            </div>

            <div class="meeting-type-options">
                <button class="meeting-type-option mto-card mto-card-online" type="button" onclick="selectMeetingType('online_single')">
                    <div class="mto-card-top">
                        <span class="meeting-type-icon mto-icon-online" aria-hidden="true">&#127909;</span>
                        <span class="mto-badge mto-badge-online">Virtual</span>
                    </div>
                    <div class="mto-card-body">
                        <strong>Online meeting</strong>
                        <small>One company in one shared session</small>
                    </div>
                    <div class="mto-card-footer">
                        <span class="mto-feature">Video, audio &amp; chat</span>
                        <span class="mto-arrow" aria-hidden="true">&rarr;</span>
                    </div>
                </button>

                <button class="meeting-type-option mto-card mto-card-multi" type="button" onclick="selectMeetingType('online_multi')">
                    <div class="mto-card-top">
                        <span class="meeting-type-icon mto-icon-multi" aria-hidden="true">&#127760;</span>
                        <span class="mto-badge mto-badge-multi">Multi-Org</span>
                    </div>
                    <div class="mto-card-body">
                        <strong>Multi company / departmental online meeting</strong>
                        <small>Multiple companies in one shared session</small>
                    </div>
                    <div class="mto-card-footer">
                        <span class="mto-feature">Grouped by company</span>
                        <span class="mto-arrow" aria-hidden="true">&rarr;</span>
                    </div>
                </button>

                <button class="meeting-type-option mto-card mto-card-inperson" type="button" onclick="selectMeetingType('in_person')">
                    <div class="mto-card-top">
                        <span class="meeting-type-icon mto-icon-inperson" aria-hidden="true">&#127970;</span>
                        <span class="mto-badge mto-badge-inperson">On-Site</span>
                    </div>
                    <div class="mto-card-body">
                        <strong>In-person meeting</strong>
                        <small>Meet in a physical room</small>
                    </div>
                    <div class="mto-card-footer">
                        <span class="mto-feature">Physical room booking</span>
                        <span class="mto-arrow" aria-hidden="true">&rarr;</span>
                    </div>
                </button>

                <button class="meeting-type-option mto-card mto-card-hybrid" type="button" onclick="selectMeetingType('hybrid')">
                    <div class="mto-card-top">
                        <span class="meeting-type-icon mto-icon-hybrid" aria-hidden="true">&#128101;</span>
                        <span class="mto-badge mto-badge-hybrid">★ Popular</span>
                    </div>
                    <div class="mto-card-body">
                        <strong>Hybrid meeting</strong>
                        <small>Meet in a room and online</small>
                    </div>
                    <div class="mto-card-footer">
                        <span class="mto-feature">Room + online session</span>
                        <span class="mto-arrow" aria-hidden="true">&rarr;</span>
                    </div>
                </button>
            </div>

            @if($rooms->isEmpty())
                <div class="mto-note-card">
                    <span class="mto-note-icon">&#9432;</span>
                    <p class="meeting-type-note">In-person and hybrid meetings need a room. <a href="{{ route('admin.dashboard') }}">Add a room first &rarr;</a></p>
                </div>
            @endif
        </div>
    </dialog>
    <dialog class="mdd-dialog" id="new-meeting-dialog">
        <form class="panel form-panel" action="{{ route('meetings.store') }}" method="post">
        @csrf
        <input type="hidden" name="meeting_type" id="meeting-type" value="{{ old('meeting_type') }}">
        <input type="hidden" name="is_multi_company" id="is-multi-company" value="{{ old('is_multi_company') }}">

        <div class="nmf-body" id="standard-form-body">

        {{-- ── Header ── --}}
        <div class="nmf-header" id="standard-form-heading">
            <div class="nmf-header-left">
                <span class="nmf-header-icon" id="nmf-type-icon">📅</span>
                <div>
                    <div class="nmf-eyebrow"><span class="nmf-eyebrow-dot"></span> <span id="nmf-type-label">New meeting</span></div>
                    <h3 id="meeting-form-title">Schedule a meeting</h3>
                    <p id="meeting-form-description">Fill in the details to create and invite participants.</p>
                </div>
            </div>
            <span id="nmf-type-badge" class="nmf-type-badge nmf-type-badge-online" style="display:none"></span>
            <button class="nmf-close-btn" type="button" onclick="document.getElementById('new-meeting-dialog').close()" aria-label="Close new meeting form">×</button>
        </div>

        {{-- ── Standard Form Fields ── --}}
        <fieldset id="standard-form-layout" style="border:0;padding:0;margin:0;min-width:0">

            {{-- Section: Organizer --}}
            <div class="nmf-section-head" style="margin-bottom:16px">
                <span class="nmf-section-icon">👤</span>
                <div>
                    <p class="nmf-section-title">Organizer</p>
                    <p class="nmf-section-sub">Who is hosting this meeting?</p>
                </div>
            </div>
            <div class="nmf-grid">
                <label class="nmf-field">
                    <span class="nmf-label">Host name <span class="nmf-req">*</span></span>
                    <input name="name" value="{{ old('name', auth()->user()->name) }}" required placeholder="Full name">
                </label>
                <label class="nmf-field">
                    <span class="nmf-label">Email <span class="nmf-req">*</span></span>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required placeholder="host@example.com">
                </label>
                <label class="nmf-field">
                    <span class="nmf-label">Phone number <span class="nmf-req">*</span></span>
                    <input name="phone" value="{{ old('phone') }}" required placeholder="+1 555 000 0000">
                </label>
                <label class="nmf-field">
                    <span class="nmf-label">Company name</span>
                    <input name="company_name" value="{{ old('company_name') }}" placeholder="Optional">
                </label>
                <label class="nmf-field wide">
                    <span class="nmf-label">Company contact email</span>
                    <input type="email" name="company_contact_email" value="{{ old('company_contact_email') }}" placeholder="Optional — used to send invitation link">
                </label>
            </div>

            {{-- Section: Meeting Details --}}
            <div class="nmf-section">
                <div class="nmf-section-head">
                    <span class="nmf-section-icon">📋</span>
                    <div>
                        <p class="nmf-section-title">Meeting details</p>
                        <p class="nmf-section-sub">Topic, timing, and location</p>
                    </div>
                </div>
                <div class="nmf-grid">
                    <label class="nmf-field wide">
                        <span class="nmf-label">Meeting topic <span class="nmf-req">*</span></span>
                        <input name="meeting_topic" value="{{ old('meeting_topic') }}" required placeholder="e.g. Q4 Strategy Review">
                    </label>
                    <label class="nmf-field">
                        <span class="nmf-label">Start date &amp; time <span class="nmf-req">*</span></span>
                        <input type="datetime-local" name="start_date" value="{{ old('start_date') }}" required>
                    </label>
                    <label class="nmf-field">
                        <span class="nmf-label">End date &amp; time <span class="nmf-req">*</span></span>
                        <input type="datetime-local" name="end_date" value="{{ old('end_date') }}" required>
                    </label>
                    <label class="nmf-field wide" id="meeting-room-field">
                        <span class="nmf-label">Meeting room <span class="nmf-req">*</span></span>
                        <select name="meeting_room_id" id="meeting-room-select">
                            <option value="">Select a room</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" @selected((string) old('meeting_room_id') === (string) $room->id)>
                                    {{ $room->name }} · {{ $room->number }} · Floor {{ $room->floor }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </div>


            {{-- Section: Recurrence --}}
            <div class="nmf-section">
                <div class="nmf-section-head">
                    <span class="nmf-section-icon">🔁</span>
                    <div>
                        <p class="nmf-section-title">Recurrence <span style="font-weight:400;color:#718096">(optional)</span></p>
                        <p class="nmf-section-sub">Make this meeting repeat automatically</p>
                    </div>
                </div>
                <fieldset id="recurrence-layout" style="border:0;padding:0;margin:0;min-width:0">
                    <div class="nmf-grid">
                        <label class="nmf-field wide">
                            <span class="nmf-label">Repeat</span>
                            <select name="is_recurring" id="recurrence-enabled" onchange="toggleRecurrenceFields()">
                                <option value="0" @selected(!old('is_recurring'))>Does not repeat (single meeting)</option>
                                <option value="1" @selected(old('is_recurring'))>Repeat…</option>
                            </select>
                        </label>
                    </div>
                    <div id="recurrence-fields" style="display:{{ old('is_recurring') ? 'grid' : 'none' }};grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 18px;margin-top:14px">
                        <label class="nmf-field">
                            <span class="nmf-label">Frequency</span>
                            <select name="recurrence_frequency" id="recurrence-frequency" onchange="updateRecurrenceFields()">
                                <option value="daily" @selected(old('recurrence_frequency') === 'daily')>Daily</option>
                                <option value="weekly" @selected(old('recurrence_frequency') === 'weekly')>Weekly</option>
                                <option value="monthly" @selected(old('recurrence_frequency') === 'monthly')>Monthly</option>
                                <option value="quarterly" @selected(old('recurrence_frequency') === 'quarterly')>Quarterly</option>
                            </select>
                        </label>
                        <label class="nmf-field">
                            <span class="nmf-label">Every</span>
                            <input type="number" name="recurrence_interval" id="recurrence-interval" value="{{ old('recurrence_interval', 1) }}" min="1" max="52" style="max-width:110px">
                        </label>
                        <div id="weekly-days" class="nmf-days-grid" style="display:{{ in_array(old('recurrence_frequency'), ['weekly']) ? 'grid' : 'none' }}">
                            @foreach([1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'] as $num => $day)
                                <label class="nmf-day-label">
                                    <input type="checkbox" name="recurrence_days_of_week[]" value="{{ $num }}" @checked(in_array($num, old('recurrence_days_of_week', [])))>
                                    {{ $day }}
                                </label>
                            @endforeach
                        </div>
                        <label class="nmf-field" id="monthly-day" style="display:{{ in_array(old('recurrence_frequency'), ['monthly', 'quarterly']) ? 'block' : 'none' }}">
                            <span class="nmf-label">Day of month</span>
                            <input type="number" name="recurrence_day_of_month" value="{{ old('recurrence_day_of_month', 1) }}" min="1" max="28" style="max-width:110px">
                            <span class="nmf-field-hint">1–28 (use 28 for last day of month)</span>
                        </label>
                        <label class="nmf-field wide">
                            <span class="nmf-label">Ends</span>
                            <select name="recurrence_end_type" id="recurrence-end-type" onchange="toggleRecurrenceEndFields()">
                                <option value="never" @selected(old('recurrence_end_type', 'never') === 'never')>Never</option>
                                <option value="on_date" @selected(old('recurrence_end_type') === 'on_date')>On date</option>
                                <option value="after_occurrences" @selected(old('recurrence_end_type') === 'after_occurrences')>After N occurrences</option>
                            </select>
                        </label>
                        <div id="recurrence-end-date-field" style="display:{{ old('recurrence_end_type') === 'on_date' ? 'block' : 'none' }}">
                            <label class="nmf-field">
                                <span class="nmf-label">End date</span>
                                <input type="date" name="recurrence_end_date" value="{{ old('recurrence_end_date') }}" min="{{ old('start_date') ? date('Y-m-d', strtotime(old('start_date'))) : date('Y-m-d') }}">
                            </label>
                        </div>
                        <div id="recurrence-end-occurrences-field" style="display:{{ old('recurrence_end_type') === 'after_occurrences' ? 'block' : 'none' }}">
                            <label class="nmf-field">
                                <span class="nmf-label">Number of occurrences</span>
                                <input type="number" name="recurrence_max_occurrences" value="{{ old('recurrence_max_occurrences', 10) }}" min="1" max="1000" style="max-width:110px">
                            </label>
                        </div>
                        <div class="nmf-recurrence-preview">
                            <strong>Preview:</strong> <span id="recurrence-preview">This meeting will not repeat.</span>
                        </div>
                    </div>
                </fieldset>
            </div>

            {{-- Invite Participants section --}}
            @if(old('meeting_type') === 'online' || old('meeting_type') === 'online_single')
            <div class="nmf-section" id="attendees-layout">
                <div class="nmf-section-head">
                    <span class="nmf-section-icon">✉️</span>
                    <div>
                        <p class="nmf-section-title">Invite participants</p>
                        <p class="nmf-section-sub">Add people who will receive individual invitation links</p>
                    </div>
                </div>
                <div id="attendeeRows">
                    @forelse(old('attendees', [['full_name' => '', 'email' => '']]) as $index => $attendee)
                        <div class="nmf-attendee-row">
                            <label class="nmf-field">
                                <span class="nmf-label">Full name <span class="nmf-req">*</span></span>
                                <input type="text" name="attendees[{{ $index }}][full_name]" value="{{ $attendee['full_name'] ?? '' }}" required maxlength="160" placeholder="e.g. Jane Smith">
                            </label>
                            <label class="nmf-field">
                                <span class="nmf-label">Email <span class="nmf-req">*</span></span>
                                <input type="email" name="attendees[{{ $index }}][email]" value="{{ $attendee['email'] ?? '' }}" required maxlength="160" placeholder="jane@example.com">
                            </label>
                            <button type="button" class="nmf-remove-btn multi-company-remove" aria-label="Remove participant" style="align-self:end;margin-bottom:0">&times;</button>
                        </div>
                    @empty
                        <div class="nmf-attendee-row">
                            <label class="nmf-field">
                                <span class="nmf-label">Full name <span class="nmf-req">*</span></span>
                                <input type="text" name="attendees[0][full_name]" required maxlength="160" placeholder="e.g. Jane Smith">
                            </label>
                            <label class="nmf-field">
                                <span class="nmf-label">Email <span class="nmf-req">*</span></span>
                                <input type="email" name="attendees[0][email]" required maxlength="160" placeholder="jane@example.com">
                            </label>
                            <button type="button" class="nmf-remove-btn multi-company-remove" aria-label="Remove participant" style="align-self:end;margin-bottom:0">&times;</button>
                        </div>
                    @endforelse
                </div>
                <button type="button" class="nmf-add-btn" id="attendeeAdd">+ Add another participant</button>
            </div>
            @endif

        </fieldset>{{-- end #standard-form-layout --}}

        {{-- Footer actions --}}
        <div class="nmf-actions">
            <button class="nmf-btn nmf-btn-ghost" type="button" onclick="document.getElementById('new-meeting-dialog').close()">Cancel</button>
            <button class="nmf-btn nmf-btn-ghost" type="button" onclick="document.getElementById('new-meeting-dialog').close();document.getElementById('meeting-type-dialog').showModal()">← Back</button>
            <button class="nmf-btn nmf-btn-primary" type="submit">Create meeting →</button>
        </div>

        </div>{{-- end .nmf-body --}}

        <div id="hybrid-form-layout" hidden>
            <header class="hybrid-create-heading">
                <div><h2>Create New Meeting</h2><p>Schedule a meeting and invite participants. Choose the meeting type and set the details below.</p></div>
                <div class="hybrid-heading-status"><span>⟳ &nbsp; HYBRID SESSION</span><button class="dialog-close" type="button" onclick="document.getElementById('new-meeting-dialog').close()" aria-label="Close new meeting form">×</button><small>Both in-person and online participants can join this meeting.</small></div>
            </header>
            <div class="hybrid-create-layout">
                <section class="hybrid-create-main">
                    <h3 class="hybrid-create-section-title"><span class="section-symbol">☷</span> Meeting Type</h3>
                    <div class="hybrid-type-pill"><span>♟</span><span>Hybrid<small>Both in-person and online</small></span><span style="margin-left:auto">●</span></div>
                    <div class="hybrid-create-fields">
                        <label class="wide">Meeting Title <span class="hybrid-create-required">*</span><input name="meeting_topic" value="{{ old('meeting_topic') }}" required maxlength="180" placeholder="Enter meeting title"></label>
                        <label class="wide">Description<textarea name="description" maxlength="2000" placeholder="Add a brief description about the meeting...">{{ old('description') }}</textarea></label>
                        <label>Start Date &amp; Time <span class="hybrid-create-required">*</span><input type="datetime-local" name="start_date" value="{{ old('start_date') }}" required></label>
                        <label>End Date &amp; Time <span class="hybrid-create-required">*</span><input type="datetime-local" name="end_date" value="{{ old('end_date') }}" required></label>
                        <label class="wide">Physical Location / Meeting Room <span class="hybrid-create-required">*</span><select name="meeting_room_id" id="hybrid-room-select" required><option value="">Select a meeting room</option>@foreach($rooms as $room)<option value="{{ $room->id }}" data-location="{{ $room->location }}" data-room-name="{{ $room->name }}" data-room-number="{{ $room->number }}" data-room-floor="{{ $room->floor }}" @selected((string) old('meeting_room_id') === (string) $room->id)>{{ $room->name }} · {{ $room->number }} · Floor {{ $room->floor }}</option>@endforeach</select></label>
                        <label class="wide">Online Meeting Link <span class="hybrid-create-required">*</span><input value="Generated AykonMeet video call link (created on save)" readonly><small>Participants join through the built-in video call. The secure link is generated when you create the meeting.</small></label>
                        <label class="wide">Meeting Topic<select name="topic_category" id="hybrid-topic-category"><option value="">Select a meeting topic</option>@foreach(['Planning', 'Team meeting', 'Training', 'Workshop', 'Interview', 'Other'] as $topic)<option value="{{ $topic }}" @selected(old('topic_category') === $topic)>{{ $topic }}</option>@endforeach<option value="Custom" @selected(old('topic_category') === 'Custom')>Type your own topic</option></select></label>
                        <label class="wide" id="hybrid-custom-topic-field" @if(old('topic_category') !== 'Custom') hidden @endif>Custom meeting topic <span class="hybrid-create-required">*</span><input name="custom_topic" id="hybrid-custom-topic" value="{{ old('custom_topic') }}" maxlength="80" placeholder="Enter your meeting topic" @required(old('topic_category') === 'Custom')></label>
                        <p class="hybrid-create-subtitle">Organizer Contact</p>
                        <div class="hybrid-organizer-fields">
                            <label>Host Name <span class="hybrid-create-required">*</span><input name="name" value="{{ old('name', auth()->user()->name) }}" required></label>
                            <label>Email <span class="hybrid-create-required">*</span><input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required></label>
                            <label>Phone <span class="hybrid-create-required">*</span><input name="phone" value="{{ old('phone') }}" required></label>
                            <label>Company contact email<input type="email" name="company_contact_email" value="{{ old('company_contact_email') }}" placeholder="Optional: for sending invitation link"></label>
                            <input type="hidden" name="company_name" value="{{ old('company_name') }}">
                        </div>
                    </div>
                    <div class="hybrid-create-actions"><button class="button secondary" type="button" onclick="document.getElementById('new-meeting-dialog').close()">Cancel</button><button class="button primary" type="submit">▦ &nbsp; Create Meeting</button></div>
                </section>
                <aside class="hybrid-create-sidebar">
                    <section class="hybrid-create-card">
                        <h3 class="hybrid-create-section-title"><span class="section-symbol">▣</span> Hybrid Session Details</h3>
                        <div class="hybrid-detail-pair">
                            <div class="hybrid-detail-box"><strong>⌖ &nbsp; Physical Location</strong><span id="hybrid-location-preview">Select a meeting room</span><small id="hybrid-room-preview"></small></div>
                            <div class="hybrid-detail-box"><strong>↗ &nbsp; Online Meeting Link</strong><span>Secure AykonMeet video call</span><small>The shareable link is generated when the meeting is created.</small></div>
                        </div>
                    </section>
                    <section class="hybrid-create-card">
                        <h3 class="hybrid-create-section-title"><span class="section-symbol">♟</span> Participant Attendance</h3>
                        <div class="hybrid-counts"><div class="hybrid-count-card"><span><i></i>Online Participants</span><strong>0</strong></div><div class="hybrid-count-card online"><span><i></i>In-Person Participants</span><strong>0</strong></div><div class="hybrid-count-card pending"><span><i></i>Not Responded</span><strong>0</strong></div></div>
                        <p class="hybrid-unlimited">⛓ &nbsp; Unlimited participants (no fixed limit)</p>
                    </section>
                    <section class="hybrid-create-card">
                        <h3 class="hybrid-create-section-title"><span class="section-symbol">♙</span> Attendance Options <small>(for invited participants)</small></h3>
                        <p class="hybrid-option-note">Participants will choose how they will attend when they accept the invitation.</p>
                        <div class="hybrid-option-row">◯ &nbsp; ▣ <div>Attend In Person<span>Join at the physical location</span></div></div>
                        <div class="hybrid-option-row">◯ &nbsp; ▣ <div>Attend Online<span>Join through the video call link</span></div></div>
                    </section>
                    <section class="hybrid-create-card">
                        <h3 class="hybrid-create-section-title"><span class="section-symbol">⚙</span> Meeting Equipment &amp; Technology</h3>
                        <p class="hybrid-option-note">Recommended setup for a smooth hybrid session.</p>
                        <div class="hybrid-equipment"><span><b>♩</b>Microphone</span><span><b>▣</b>Camera</span><span><b>◖</b>Speakers</span><span><b>▱</b>Display / Projector</span><span><b>⌁</b>Wi-Fi</span></div>
                    </section>
                    <div class="hybrid-info"><strong>ⓘ &nbsp; Hybrid Meeting Information</strong>This meeting allows both in-person and online participants. Make sure to share the location and online link with all invited participants.</div>
                </aside>
            </div>
        </div>
        <div id="multi-company-form-layout" hidden>
            <header class="hybrid-create-heading">
                <div><h2>Create Multi-Company / Departmental Meeting</h2><p>Invite multiple companies or departments into one shared online room. Participants are grouped by company.</p></div>
                <div class="hybrid-heading-status"><span>&#9673; &nbsp; MULTI-COMPANY SESSION</span><button class="dialog-close" type="button" onclick="document.getElementById('new-meeting-dialog').close()" aria-label="Close new meeting form">&times;</button><small>All companies join one shared session &mdash; no separate rooms.</small></div>
            </header>
            <div class="hybrid-create-layout">
                <section class="hybrid-create-main">
                    <h3 class="hybrid-create-section-title"><span class="section-symbol">&#9783;</span> Meeting Details</h3>
                    <div class="hybrid-type-pill"><span>&#127760;</span><span>Multi-company<small>Multiple companies &middot; one room</small></span><span style="margin-left:auto">&#9679;</span></div>
                    <div class="hybrid-create-fields">
                        <label class="wide">Meeting Title <span class="hybrid-create-required">*</span><input name="meeting_topic" value="{{ old('meeting_topic') }}" required maxlength="180" placeholder="Enter meeting title"></label>
                        <label class="wide">Description<textarea name="description" maxlength="2000" placeholder="Add a brief description about the meeting...">{{ old('description') }}</textarea></label>
                        <label>Start Date &amp; Time <span class="hybrid-create-required">*</span><input type="datetime-local" name="start_date" value="{{ old('start_date') }}" required></label>
                        <label>End Date &amp; Time <span class="hybrid-create-required">*</span><input type="datetime-local" name="end_date" value="{{ old('end_date') }}" required></label>
                        <p class="hybrid-create-subtitle">Organizer Contact</p>
                        <div class="hybrid-organizer-fields">
                            <label>Host Name <span class="hybrid-create-required">*</span><input name="name" value="{{ old('name', auth()->user()->name) }}" required></label>
                            <label>Email <span class="hybrid-create-required">*</span><input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required></label>
                            <label>Phone <span class="hybrid-create-required">*</span><input name="phone" value="{{ old('phone') }}" required></label>
                            <label class="wide">Host Company / Department (optional)<input name="company_name" value="{{ old('company_name') }}" maxlength="160" placeholder="e.g. Aykon Group"></label>
                        </div>
                        <p class="hybrid-create-subtitle">Participating Companies / Departments</p>
                        <div class="multi-company-rows" id="multiCompanyRows">
                            @forelse(old('companies', ['']) as $index => $companyValue)
                                <div class="multi-company-row">
                                    <input name="companies[]" maxlength="160" placeholder="Company or department name" value="{{ $companyValue }}">
                                    <input name="departments[]" maxlength="160" placeholder="Department / team (optional)" value="{{ old('departments.'.$index) }}">
                                    <input name="company_emails[]" type="email" maxlength="160" placeholder="Contact email (required for invitation)" value="{{ old('company_emails.'.$index) }}">
                                    <input name="company_phones[]" maxlength="40" placeholder="Contact phone (optional)" value="{{ old('company_phones.'.$index) }}">
                                    <button type="button" class="multi-company-remove" aria-label="Remove company">&times;</button>
                                </div>
                            @empty
                                <div class="multi-company-row">
                                    <input name="companies[]" maxlength="160" placeholder="Company or department name">
                                    <input name="departments[]" maxlength="160" placeholder="Department / team (optional)">
                                    <input name="company_emails[]" type="email" maxlength="160" placeholder="Contact email (required for invitation)">
                                    <input name="company_phones[]" maxlength="40" placeholder="Contact phone (optional)">
                                    <button type="button" class="multi-company-remove" aria-label="Remove company">&times;</button>
                                </div>
                            @endforelse
                        </div>
                        <button type="button" class="multi-company-add" id="multiCompanyAdd">+ Add another company</button>
                    </div>
                    <div class="hybrid-create-actions"><button class="button secondary" type="button" onclick="document.getElementById('new-meeting-dialog').close()">Cancel</button><button class="button primary" type="submit">&#9638; &nbsp; Create Meeting</button></div>
                </section>
                <aside class="hybrid-create-sidebar">
                    <section class="hybrid-create-card">
                        <h3 class="hybrid-create-section-title"><span class="section-symbol">&#9823;</span> How multi-company rooms work</h3>
                        <div class="hybrid-option-row">&#9673; &nbsp; &#127760; <div>One shared room<span>Every company joins the same video session.</span></div></div>
                        <div class="hybrid-option-row">&#9673; &nbsp; &#9823; <div>Grouped participants<span>People are listed under their company with a live count.</span></div></div>
                        <div class="hybrid-option-row">&#9673; &nbsp; &#9635; <div>Verified identity<span>Company comes from each invited email, not typed at join time.</span></div></div>
                    </section>
                    <section class="hybrid-create-card">
                        <h3 class="hybrid-create-section-title"><span class="section-symbol">&#8977;</span> After creating</h3>
                        <p class="hybrid-option-note">Add attendees and assign each one a company. On the meeting page, import a CSV with <strong>Company</strong> and <strong>Department</strong> columns. Anyone who opens the shared link without an invite appears under &ldquo;Guests&rdquo;.</p>
                    </section>
                    <div class="hybrid-info"><strong>&#9432; &nbsp; Multi-company information</strong>All invited companies share a single session. The room groups participants by their registered company so everyone can see who represents whom.</div>
                </aside>
            </div>
        </div>
        </form>
    </dialog>
</div>

<script>
function configureMeetingType(type, isMultiCompany) {
    if (typeof isMultiCompany === 'undefined') isMultiCompany = false;
    var titles = {
        online: [isMultiCompany ? 'New multi-company / departmental online meeting' : 'New online meeting',
                 isMultiCompany ? 'Multiple companies or departments join one shared session.' : 'Attendees will join by video call.'],
        in_person: ['New in-person meeting', 'Choose a room for attendees to meet in person.'],
        hybrid: ['New hybrid meeting', 'Choose a room for in-person attendees; online attendees can join by video.']
    };
    if (!Object.prototype.hasOwnProperty.call(titles, type)) return false;

    var roomField = document.getElementById('meeting-room-field');
    var roomSelect = document.getElementById('meeting-room-select');
    var standardLayout = document.getElementById('standard-form-layout');
    var hybridLayout = document.getElementById('hybrid-form-layout');
    var multiLayout = document.getElementById('multi-company-form-layout');
    var hybridMode = type === 'hybrid';
    var multiMode = type === 'online' && isMultiCompany;
    document.getElementById('meeting-type').value = type;
    var mcField = document.getElementById('is-multi-company');
    if (mcField) mcField.value = isMultiCompany ? '1' : '0';
    document.getElementById('meeting-form-title').textContent = titles[type][0];
    document.getElementById('meeting-form-description').textContent = titles[type][1];
    document.getElementById('standard-form-heading').hidden = hybridMode || multiMode;
    standardLayout.hidden = hybridMode || multiMode;
    standardLayout.style.display = (hybridMode || multiMode) ? 'none' : 'block';
    standardLayout.disabled = hybridMode || multiMode;
    hybridLayout.hidden = !hybridMode;
    hybridLayout.style.display = hybridMode ? 'block' : 'none';
    hybridLayout.querySelectorAll('input, select, textarea, button').forEach(function (element) {
        element.disabled = !hybridMode;
    });
    if (multiLayout) {
        multiLayout.hidden = !multiMode;
        multiLayout.style.display = multiMode ? 'block' : 'none';
        multiLayout.querySelectorAll('input, select, textarea, button').forEach(function (element) {
            element.disabled = !multiMode;
        });
    }
    roomField.hidden = type === 'online';
    roomSelect.required = type === 'in_person';
    if (type === 'online') roomSelect.value = '';
    var hybridRoom = document.getElementById('hybrid-room-select');
    var hybridTopicCategory = document.getElementById('hybrid-topic-category');
    var customTopicField = document.getElementById('hybrid-custom-topic-field');
    var customTopicInput = document.getElementById('hybrid-custom-topic');
    var locationPreview = document.getElementById('hybrid-location-preview');
    var roomPreview = document.getElementById('hybrid-room-preview');
    function updateHybridLocation() {
        var selectedRoom = hybridRoom.options[hybridRoom.selectedIndex];
        locationPreview.textContent = selectedRoom.value
            ? (selectedRoom.dataset.location || selectedRoom.dataset.roomName || selectedRoom.textContent)
            : 'Select a meeting room';
        roomPreview.textContent = selectedRoom.value
            ? [selectedRoom.dataset.roomName, selectedRoom.dataset.roomNumber && 'Room ' + selectedRoom.dataset.roomNumber, selectedRoom.dataset.roomFloor && 'Floor ' + selectedRoom.dataset.roomFloor].filter(Boolean).join(' · ')
            : '';
    }
    hybridRoom.onchange = updateHybridLocation;
    updateHybridLocation();
    function updateCustomTopic() {
        var isCustom = hybridTopicCategory.value === 'Custom';
        customTopicField.hidden = !isCustom;
        customTopicInput.required = isCustom;
        if (!isCustom) customTopicInput.value = '';
    }
    hybridTopicCategory.addEventListener('change', updateCustomTopic);
    updateCustomTopic();
    return true;
}

function selectMeetingType(type) {
  var isMultiCompany = type === 'online_multi';
  if (type === 'online_single' || type === 'online_multi') type = 'online';
  if (!configureMeetingType(type, isMultiCompany)) return;
  var typeDialog = document.getElementById('meeting-type-dialog');
  if (typeDialog.open) typeDialog.close();
  if (isMultiCompany) {
    window.location.href = "{{ route('multi-company-meetings.create') }}";
    return;
  }
  if (type === 'hybrid') {
    window.location.href = "{{ route('meetings.create.hybrid') }}";
    return;
  }
  document.getElementById('new-meeting-dialog').showModal();
}

(function () {
  var rows = document.getElementById('multiCompanyRows');
  var addButton = document.getElementById('multiCompanyAdd');
  if (!rows || !addButton) return;
  addButton.addEventListener('click', function () {
    var firstRow = rows.querySelector('.multi-company-row');
    if (!firstRow) return;
    var clone = firstRow.cloneNode(true);
    clone.querySelectorAll('input').forEach(function (input) { input.value = ''; });
    rows.appendChild(clone);
    var firstInput = clone.querySelector('input');
    if (firstInput) firstInput.focus();
  });
  rows.addEventListener('click', function (event) {
    var removeButton = event.target.closest('.multi-company-remove');
    if (!removeButton) return;
    var row = removeButton.closest('.multi-company-row');
    if (!row) return;
    if (rows.querySelectorAll('.multi-company-row').length > 1) {
      row.remove();
    } else {
      row.querySelectorAll('input').forEach(function (input) { input.value = ''; });
    }
  });
})();

(function () {
  var attendeeRows = document.getElementById('attendeeRows');
  var addAttendeeButton = document.getElementById('attendeeAdd');
  if (!attendeeRows || !addAttendeeButton) return;
  addAttendeeButton.addEventListener('click', function () {
    var firstRow = attendeeRows.querySelector('.attendee-row');
    if (!firstRow) return;
    var clone = firstRow.cloneNode(true);
    clone.querySelectorAll('input').forEach(function (input) { input.value = ''; });
    attendeeRows.appendChild(clone);
    var firstInput = clone.querySelector('input');
    if (firstInput) firstInput.focus();
  });
  attendeeRows.addEventListener('click', function (event) {
    var removeButton = event.target.closest('.multi-company-remove');
    if (!removeButton) return;
    var row = removeButton.closest('.attendee-row');
    if (!row) return;
    if (attendeeRows.querySelectorAll('.attendee-row').length > 1) {
      row.remove();
    } else {
      row.querySelectorAll('input').forEach(function (input) { input.value = ''; });
    }
  });
})();

@if($errors->any() && in_array(old('meeting_type'), ['online', 'in_person', 'hybrid'], true))
if (configureMeetingType(@json(old('meeting_type')), @json(old('is_multi_company') === '1'))) {
    document.getElementById('new-meeting-dialog').showModal();
}
@endif
(function () {
  ['hybrid-form-layout', 'multi-company-form-layout'].forEach(function (id) {
    var layout = document.getElementById(id);
    if (layout && layout.hidden) {
      layout.querySelectorAll('input, select, textarea, button').forEach(function (element) { element.disabled = true; });
    }
  });
})();

// Recurrence UI handlers
function toggleRecurrenceFields() {
    var enabled = document.getElementById('recurrence-enabled').value === '1';
    var fields = document.getElementById('recurrence-fields');
    if (fields) {
        fields.style.display = enabled ? 'grid' : 'none';
    }
    updateRecurrenceFields();
}

function updateRecurrenceFields() {
    var frequency = document.getElementById('recurrence-frequency').value;
    var weeklyDays = document.getElementById('weekly-days');
    var monthlyDay = document.getElementById('monthly-day');

    if (weeklyDays) {
        weeklyDays.style.display = frequency === 'weekly' ? 'grid' : 'none';
    }
    if (monthlyDay) {
        monthlyDay.style.display = (frequency === 'monthly' || frequency === 'quarterly') ? 'block' : 'none';
    }
    updateRecurrencePreview();
}

function toggleRecurrenceEndFields() {
    var endType = document.getElementById('recurrence-end-type').value;
    var dateField = document.getElementById('recurrence-end-date-field');
    var occurrencesField = document.getElementById('recurrence-end-occurrences-field');

    if (dateField) dateField.style.display = endType === 'on_date' ? 'block' : 'none';
    if (occurrencesField) occurrencesField.style.display = endType === 'after_occurrences' ? 'block' : 'none';
    updateRecurrencePreview();
}

function updateRecurrencePreview() {
    var enabled = document.getElementById('recurrence-enabled').value === '1';
    var preview = document.getElementById('recurrence-preview');
    if (!preview) return;

    if (!enabled) {
        preview.textContent = 'This meeting will not repeat.';
        return;
    }

    var frequency = document.getElementById('recurrence-frequency').value;
    var interval = parseInt(document.getElementById('recurrence-interval').value) || 1;
    var endType = document.getElementById('recurrence-end-type').value;
    var endDate = document.getElementById('recurrence-end-date-field')?.querySelector('input')?.value;
    var maxOccurrences = document.getElementById('recurrence-end-occurrences-field')?.querySelector('input')?.value;

    var freqLabels = { daily: 'day', weekly: 'week', monthly: 'month', quarterly: 'quarter' };
    var intervalText = interval > 1 ? 'every ' + interval + ' ' + freqLabels[frequency] + 's' : 'every ' + freqLabels[frequency];
    var daysText = '';
    if (frequency === 'weekly') {
        var checkedDays = document.querySelectorAll('#weekly-days input[type="checkbox"]:checked');
        if (checkedDays.length > 0) {
            var dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            var days = Array.from(checkedDays).map(function(cb) { return dayNames[parseInt(cb.value) - 1]; });
            daysText = ' on ' + days.join(', ');
        }
    } else if (frequency === 'monthly' || frequency === 'quarterly') {
        var dayOfMonth = document.querySelector('#monthly-day input')?.value;
        if (dayOfMonth) {
            daysText = ' on day ' + dayOfMonth;
        }
    }

    var endText = '';
    if (endType === 'on_date' && endDate) {
        endText = ', ends on ' + endDate;
    } else if (endType === 'after_occurrences' && maxOccurrences) {
        endText = ', ends after ' + maxOccurrences + ' occurrences';
    } else {
        endText = ', never ends';
    }

    preview.textContent = 'Repeats ' + intervalText + daysText + endText + '.';
}

// Initialize recurrence fields on load
document.addEventListener('DOMContentLoaded', function() {
    toggleRecurrenceFields();
    updateRecurrenceFields();
    toggleRecurrenceEndFields();
});

if (window.location.hash === '#new-meeting') {
    document.getElementById('meeting-type-dialog').showModal();
}
</script>

@endsection
