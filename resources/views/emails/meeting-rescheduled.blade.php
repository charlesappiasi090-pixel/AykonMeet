<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Meeting rescheduled</title>
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
                        <p style="margin:0 0 6px;font-size:11px;font-weight:700;letter-spacing:1.5px;color:#b18800;">MEETING RESCHEDULED</p>
                        <h1 style="margin:0 0 18px;font-size:26px;line-height:1.2;color:#112b49;">{{ $meeting->meeting_topic }}</h1>
                        <p style="margin:0 0 22px;font-size:14px;line-height:1.6;color:#718096;">Hello {{ $attendee->full_name }}, the organizer has changed the time for this meeting. The updated schedule is below.</p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#112b49;border-radius:8px;margin-bottom:22px;">
                            <tr><td align="center" style="padding:24px 20px;">
                                <p style="margin:0 0 6px;font-size:11px;letter-spacing:2px;color:#9fb4cc;">NEW DATE &amp; TIME</p>
                                <p style="margin:0 0 14px;font-size:18px;font-weight:700;color:#f7c948;">{{ $meeting->start_date->format('l, d M Y \a\t H:i') }} – {{ $meeting->end_date->format('H:i') }}</p>
                                <a href="{{ $attendee->invitationUrl() }}" style="display:inline-block;padding:12px 22px;background:#f7c948;color:#112b49;text-decoration:none;font-size:13px;font-weight:700;border-radius:6px;">View invitation</a>
                            </td></tr>
                        </table>

                        <p style="margin:0;font-size:12px;line-height:1.6;color:#718096;">If you can no longer attend, please open the invitation and decline so the organizer can plan accordingly.</p>
                        <p style="margin:18px 0 0;font-size:11px;color:#9aa7b2;">This email was sent by AykonMeet — make every minute count.</p>
                    </td></tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
