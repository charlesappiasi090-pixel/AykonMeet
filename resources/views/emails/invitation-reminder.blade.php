<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invitation reminder</title>
</head>
<body style="margin:0;background:#f7f9fb;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#172437;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f7f9fb;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border:1px solid #dbe3ea;border-top:4px solid #f7c948;">
                    <tr><td style="padding:28px 32px 8px;">
                        <div style="font-weight:700;font-size:20px;color:#112b49;">Aykon<span style="color:#b18800;">Meet</span></div>
                    </td></tr>
                    <tr><td style="padding:12px 32px 28px;">
                        <p style="margin:0 0 6px;font-size:11px;font-weight:700;letter-spacing:1.5px;color:#b18800;">{{ $isReminder ? 'INVITATION REMINDER' : 'MEETING INVITATION' }}</p>
                        <h1 style="margin:0 0 18px;font-size:26px;line-height:1.2;color:#112b49;">{{ $meeting->meeting_topic }}</h1>
                        <p style="margin:0 0 22px;font-size:14px;line-height:1.6;color:#718096;">Hello {{ $attendee->full_name }}, {{ $meeting->name }} is looking forward to having you at this meeting. Please confirm whether you can attend.</p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#112b49;border-radius:8px;margin-bottom:22px;">
                            <tr><td align="center" style="padding:24px 20px;">
                                <p style="margin:0 0 14px;font-size:13px;color:#c8d6e6;">{{ $meeting->start_date->format('l, d M Y \a\t H:i') }}</p>
                                <a href="{{ $attendee->invitationUrl() }}" style="display:inline-block;padding:14px 26px;background:#f7c948;color:#112b49;text-decoration:none;font-size:14px;font-weight:700;border-radius:6px;">View invitation &amp; respond</a>
                            </td></tr>
                        </table>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="border-top:1px solid #dbe3ea;padding:12px 0;font-size:12px;color:#718096;">Meeting code</td>
                                <td align="right" style="border-top:1px solid #dbe3ea;padding:12px 0;font-size:12px;color:#112b49;font-weight:600;">{{ $meeting->meeting_code }}</td>
                            </tr>
                            <tr>
                                <td style="border-top:1px solid #dbe3ea;border-bottom:1px solid #dbe3ea;padding:12px 0;font-size:12px;color:#718096;">Your attendee code</td>
                                <td align="right" style="border-top:1px solid #dbe3ea;border-bottom:1px solid #dbe3ea;padding:12px 0;font-family:'Courier New',monospace;font-size:13px;color:#112b49;font-weight:700;">{{ $attendee->attendee_code }}</td>
                            </tr>
                        </table>

                        <p style="margin:22px 0 0;font-size:11px;color:#9aa7b2;">This email was sent by AykonMeet — make every minute count.</p>
                    </td></tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
