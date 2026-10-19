<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your AykonMeet invitation</title>
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
                        <p style="margin:0 0 22px;font-size:14px;line-height:1.6;color:#718096;">Hello {{ $attendee->full_name }}, {{ $meeting->name }} has invited you to this meeting. Click the button below to join the meeting directly.</p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#112b49;border-radius:8px;margin-bottom:22px;">
                            <tr><td align="center" style="padding:24px 20px;">
                                <p style="margin:0 0 14px;font-size:13px;line-height:1.5;color:#c8d6e6;">Join the meeting now</p>
                                <a href="{{ $joinUrl }}" style="display:inline-block;padding:14px 26px;background:#f7c948;color:#112b49;text-decoration:none;font-size:14px;font-weight:700;border-radius:6px;">Join Meeting</a>
                            </td></tr>
                        </table>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f7f9fb;border:1px dashed #cfdce8;border-radius:8px;">
                            <tr><td align="center" style="padding:16px;">
                                <p style="margin:0 0 6px;font-size:10px;font-weight:700;letter-spacing:2px;color:#718096;">YOUR ATTENDEE CODE</p>
                                <p style="margin:0;font-family:'Courier New',monospace;font-size:20px;font-weight:700;color:#112b49;letter-spacing:2px;">{{ $attendee->attendee_code }}</p>
                            </td></tr>
                        </table>

                        @if($rsvpUrl)
                        <p style="margin:22px 0 8px;font-size:14px;font-weight:700;color:#112b49;">Prefer to RSVP directly?</p>
                        <p style="margin:0 0 10px;font-size:12px;line-height:1.6;color:#718096;">Open the meeting page, choose Attend In Person or Attend Online, and confirm your RSVP with the code sent to your email.</p>
                        <p style="margin:0 0 22px;"><a href="{{ $rsvpUrl }}" style="display:inline-block;padding:12px 20px;background:#f7c948;color:#112b49;text-decoration:none;font-size:13px;font-weight:700;border-radius:4px;">Choose attendance method</a></p>
                        @endif

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:22px;">
                            <tr>
                                <td style="border-top:1px solid #dbe3ea;padding:12px 0;font-size:12px;color:#718096;">Organizer</td>
                                <td align="right" style="border-top:1px solid #dbe3ea;padding:12px 0;font-size:12px;color:#112b49;font-weight:600;">{{ $meeting->name }}@if($meeting->company_name) · {{ $meeting->company_name }}@endif</td>
                            </tr>
                            <tr>
                                <td style="border-top:1px solid #dbe3ea;padding:12px 0;font-size:12px;color:#718096;">Meeting code</td>
                                <td align="right" style="border-top:1px solid #dbe3ea;padding:12px 0;font-size:12px;color:#112b49;font-weight:600;">{{ $meeting->meeting_code }}</td>
                            </tr>
                            <tr>
                                <td style="border-top:1px solid #dbe3ea;padding:12px 0;font-size:12px;color:#718096;">Date & time</td>
                                <td align="right" style="border-top:1px solid #dbe3ea;padding:12px 0;font-size:12px;color:#112b49;font-weight:600;">{{ $meeting->start_date->format('D, d M Y · H:i') }} – {{ $meeting->end_date->format('H:i') }}</td>
                            </tr>
                            <tr>
                                <td style="border-top:1px solid #dbe3ea;padding:12px 0;font-size:12px;color:#718096;">Duration</td>
                                <td align="right" style="border-top:1px solid #dbe3ea;padding:12px 0;font-size:12px;color:#112b49;font-weight:600;">{{ $durationLabel }}</td>
                            </tr>
                            <tr>
                                <td style="border-top:1px solid #dbe3ea;padding:12px 0;font-size:12px;color:#718096;">Invited company</td>
                                <td align="right" style="border-top:1px solid #dbe3ea;padding:12px 0;font-size:12px;color:#112b49;font-weight:600;">{{ $attendee->companyName() }}</td>
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

                        @if(filled($meeting->description))
                        <div style="margin-top:22px;padding:16px;background:#f7f9fb;border-left:3px solid #f7c948;">
                            <p style="margin:0 0 6px;font-size:11px;font-weight:700;letter-spacing:1.5px;color:#b18800;">ABOUT THIS MEETING</p>
                            <p style="margin:0;font-size:13px;line-height:1.6;color:#4a5568;">{{ $meeting->description }}</p>
                        </div>
                        @endif

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