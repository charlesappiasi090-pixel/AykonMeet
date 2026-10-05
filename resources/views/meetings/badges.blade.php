<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Badges · {{ $meeting->meeting_topic }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #e8edf2; color: #172437; }
        .toolbar { max-width: 1000px; margin: 24px auto 12px; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 0 8px; }
        .toolbar a { color: #112b49; font-size: 13px; text-decoration: none; font-weight: 700; }
        .toolbar button { border: 0; background: #112b49; color: #fff; font: 700 13px 'Segoe UI', sans-serif; padding: 10px 18px; cursor: pointer; border-radius: 4px; }
        .sheet { max-width: 1000px; margin: 0 auto; padding: 10px; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .badge { background: #fff; border: 1px solid #d4dde6; border-radius: 10px; padding: 18px 16px 14px; display: grid; grid-template-columns: 1fr 92px; gap: 10px; align-items: center; min-height: 142px; break-inside: avoid; }
        .badge .brand { font-size: 11px; font-weight: 800; letter-spacing: 2.5px; color: #b18800; margin-bottom: 4px; }
        .badge h3 { font-size: 18px; line-height: 1.15; color: #112b49; margin-bottom: 8px; }
        .badge .meta { font-size: 11px; color: #718096; line-height: 1.5; }
        .badge .meta strong { color: #112b49; letter-spacing: 1px; }
        .badge .qr { text-align: center; }
        .badge .qr svg { width: 88px; height: 88px; display: block; margin: 0 auto; }
        .badge .qr small { display: block; font-size: 9px; font-weight: 700; letter-spacing: 1.5px; color: #718096; margin-top: 4px; }
        .empty { grid-column: 1 / -1; background: #fff; border: 1px dashed #b8c5cf; border-radius: 10px; padding: 48px 20px; text-align: center; color: #718096; font-size: 14px; }
        @page { size: A4 portrait; margin: 8mm; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet { max-width: none; padding: 0; gap: 5mm; }
            .badge { border-radius: 6px; min-height: 50mm; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ route('meetings.attendance', $meeting) }}">← Back to attendance</a>
        <button type="button" onclick="window.print()">⛶ Print all badges (A4)</button>
    </div>
    <div class="sheet">
        @forelse($meeting->attendees as $attendee)
            <div class="badge">
                <div>
                    <div class="brand">AYKON MEET · {{ $meeting->meeting_code }}</div>
                    <h3>{{ $attendee->full_name }}</h3>
                    <div class="meta">{{ $meeting->meeting_topic }}<br>{{ $meeting->start_date->format('D, d M Y · H:i') }}<br>Room: {{ $meeting->room?->name ?? 'To be assigned' }}</div>
                    <div class="meta"><strong>CODE {{ $attendee->attendee_code }}</strong></div>
                </div>
                <div class="qr">{!! $attendee->qr_svg !!}<small>SCAN TO CHECK IN</small></div>
            </div>
        @empty
            <div class="empty">No attendees yet — add a guest list before printing badges.</div>
        @endforelse
    </div>
</body>
</html>