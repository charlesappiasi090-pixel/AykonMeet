<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Meeting {{ $meeting->meeting_code }}</title>
    <style>
        body { color: #172437; font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { color: #112b49; font-size: 24px; margin-bottom: 4px; }
        h2 { color: #112b49; font-size: 15px; margin-top: 28px; border-bottom: 1px solid #dbe3ea; padding-bottom: 7px; }
        .muted { color: #718096; }
        .code { color: #b18800; font-weight: bold; letter-spacing: 2px; }
        .summary { border: 1px solid #dbe3ea; padding: 14px; margin-top: 20px; }
        .summary div { padding: 6px 0; }
        .label { color: #718096; display: inline-block; width: 120px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border-bottom: 1px solid #dbe3ea; padding: 8px 6px; text-align: left; }
        th { color: #112b49; }
        .present { color: #1a8a5f; font-weight: bold; }
        .expected { color: #9b7400; }
        .notes { background: #f4f7fa; border: 1px solid #dbe3ea; padding: 12px 14px; font-size: 12px; line-height: 1.7; white-space: pre-wrap; }
    </style>
</head>
<body>
    <p class="code">AYKONMEET / COMPLETED MEETING</p>
    <h1>{{ $meeting->meeting_topic }}</h1>
    <p class="muted">Meeting code: {{ $meeting->meeting_code }}</p>

    <div class="summary">
        <div><span class="label">Host</span>{{ $meeting->name }}</div>
        <div><span class="label">Company</span>{{ $meeting->company_name ?: '—' }}</div>
        <div><span class="label">Scheduled</span>{{ $meeting->start_date->format('D, d M Y H:i') }} - {{ $meeting->end_date->format('H:i') }}</div>
        <div><span class="label">Started</span>{{ $meeting->started_at?->format('D, d M Y H:i') ?: '—' }}</div>
        <div><span class="label">Ended</span>{{ $meeting->ended_at?->format('D, d M Y H:i') ?: '—' }}</div>
        <div><span class="label">Room</span>{{ $meeting->room?->name ?: 'Not assigned' }}</div>
    </div>

    @if($meeting->notes)
    <h2>Minutes &amp; notes</h2>
    <div class="notes">{{ trim($meeting->notes) }}</div>
    @endif

    <h2>Attendees ({{ $meeting->attendees->count() }})</h2>
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Attendee code</th><th>Status</th><th>Checked in / out</th></tr></thead>
        <tbody>
            @forelse($meeting->attendees as $attendee)
                <tr><td>{{ $attendee->full_name }}</td><td>{{ $attendee->email }}</td><td>{{ $attendee->attendee_code }}</td><td class="{{ $attendee->checked_in_at ? 'present' : 'expected' }}">{{ $attendee->checked_in_at ? 'Present' : 'Expected' }}</td><td>{{ $attendee->checked_in_at ? $attendee->checked_in_at->format('H:i') . ($attendee->checked_out_at ? ' — ' . $attendee->checked_out_at->format('H:i') : '') : '—' }}</td></tr>
            @empty
                <tr><td colspan="5">No attendees were registered.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
