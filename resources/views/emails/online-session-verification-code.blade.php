<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your AykonMeet verification code</title>
</head>
<body style="margin:0;background:#f7f9fb;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#172437;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f7f9fb;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="background:#fff;border:1px solid #dbe3ea;border-top:4px solid #f7c948;">
                    <tr>
                        <td style="padding:28px 32px;">
                            <div style="font-weight:700;font-size:20px;color:#112b49;">Aykon<span style="color:#b18800;">Meet</span></div>
                            <p style="margin:24px 0 6px;font-size:11px;font-weight:700;letter-spacing:1.5px;color:#b18800;">EMAIL VERIFICATION</p>
                            <h1 style="margin:0 0 16px;font-size:24px;color:#112b49;">{{ $meeting->meeting_topic }}</h1>
                            <p style="font-size:14px;line-height:1.6;color:#718096;">Hello {{ $attendee->full_name }}, enter this code to verify your email and confirm your attendance at this meeting, whether you join online or in person.</p>
                            <p style="margin:24px 0;padding:20px;border-radius:8px;background:#112b49;text-align:center;font-family:'Courier New',monospace;font-size:30px;font-weight:700;letter-spacing:8px;color:#fff;">{{ $code }}</p>
                            <p style="font-size:13px;line-height:1.6;color:#718096;">This code expires in 10 minutes and can be used once. If you did not request it, you can ignore this email.</p>
                            <p style="margin:24px 0 0;font-size:11px;color:#9aa7b2;">Meeting code: {{ $meeting->meeting_code }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
