<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your AykonMeet attendee code</title>
</head>
<body style="margin:0;background:#f7f9fb;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#172437;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f7f9fb;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border:1px solid #dbe3ea;border-top:4px solid #f7c948;">
                    <tr><td style="padding:28px 32px 8px;">
                        <div style="font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-weight:700;font-size:20px;color:#112b49;">Aykon<span style="color:#b18800;">Meet</span></div>
                    </td></tr>
                    <tr><td style="padding:12px 32px;">
                        <p style="margin:0 0 6px;font-size:11px;font-weight:700;letter-spacing:1.5px;color:#b18800;">MEETING INVITATION</p>
                        <h1 style="margin:0 0 22px;font-size:26px;line-height:1.2;color:#112b49;">{{ $meeting->meeting_topic }}</h1>
                        <p style="margin:0 0 22px;font-size:14px;line-height:1.6;color:#718096;">Hello {{ $attendee->full_name }}, you have been added to a meeting. Show this code at the door to check in.</p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#112b49;border-radius:8px;">
                            <tr><td align="center" style="padding:20px;">
                                <p style="margin:0 0 6px;font-size:10px;font-weight:700;letter-spacing:2px;color:#f7c948;">YOUR ATTENDEE CODE</p>
                                <p style="margin:0;font-family:'Courier New',monospace;font-size:22px;font-weight:700;color:#ffffff;letter-spacing:2px;">{{ $attendee->attendee_code }}</p>
                            </td></tr>
                        </table>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:22px;">
                            <tr>
                                <td style="border-top:1px solid #dbe3ea;padding:12px 0;font-size:12px;color:#718096;">Meeting code</td>
                                <td align="right" style="border-top:1px solid #dbe3ea;padding:12px 0;font-size:12px;color:#112b49;font-weight:600;">{{ $meeting->meeting_code }}</td>
                            </tr>
                            <tr>
                                <td style="border-top:1px solid #dbe3ea;padding:12px 0;font-size:12px;color:#718096;">Date &amp; time</td>
                                <td align="right" style="border-top:1px solid #dbe3ea;padding:12px 0;font-size:12px;color:#112b49;font-weight:600;">{{ $meeting->start_date->format('D, d M Y · H:i') }}</td>
                            </tr>
                            <tr>
                                <td style="border-top:1px solid #dbe3ea;padding:12px 0;font-size:12px;color:#718096;">Room</td>
                                <td align="right" style="border-top:1px solid #dbe3ea;padding:12px 0;font-size:12px;color:#112b49;font-weight:600;">{{ $meeting->room?->name ?? 'To be assigned' }}</td>
                            </tr>
                            <tr>
                                <td style="border-top:1px solid #dbe3ea;border-bottom:1px solid #dbe3ea;padding:12px 0;font-size:12px;color:#718096;">Location</td>
                                <td align="right" style="border-top:1px solid #dbe3ea;border-bottom:1px solid #dbe3ea;padding:12px 0;font-size:12px;color:#112b49;font-weight:600;">{{ $meeting->from_location ?: '—' }} → {{ $meeting->to_location ?: '—' }}</td>
                            </tr>
                        </table>

                        <p style="margin:22px 0 0;font-size:12px;line-height:1.6;color:#718096;">Hosted by {{ $meeting->name }}@if($meeting->company_name) for {{ $meeting->company_name }}@endif. Please arrive a few minutes early.</p>
                    </td></tr>
                    <tr><td style="padding:0 32px 28px;">
                        <p style="margin:0;font-size:11px;color:#9aa7b2;">This email was sent by AykonMeet — make every minute count.</p>
                    </td></tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>