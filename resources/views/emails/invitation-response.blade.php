<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invitation response</title>
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
                        <p style="margin:0 0 6px;font-size:11px;font-weight:700;letter-spacing:1.5px;color:#b18800;">INVITATION RESPONSE</p>
                        <h1 style="margin:0 0 18px;font-size:24px;line-height:1.2;color:#112b49;">{{ $meeting->meeting_topic }}</h1>
                        @php($accepted = $response === 'accepted')
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{{ $accepted ? '#eefaf1' : '#fdf3f3' }};border:1px solid {{ $accepted ? '#bfe6cc' : '#f0c9c9' }};border-radius:8px;margin-bottom:22px;">
                            <tr><td style="padding:18px 20px;">
                                <p style="margin:0;font-size:15px;font-weight:700;color:{{ $accepted ? '#1c6b3d' : '#8f2222' }};">
                                    {{ $attendee->full_name }} has {{ $accepted ? 'accepted' : 'declined' }} the invitation.
                                </p>
                                <p style="margin:6px 0 0;font-size:12.5px;color:#718096;">
                                    {{ $attendee->email }}@if($attendee->companyName() !== 'Guests') · {{ $attendee->companyName() }}@endif
                                </p>
                            </td></tr>
                        </table>

                        <p style="margin:0 0 18px;font-size:13px;line-height:1.6;color:#718096;">The invitation status has been updated on your dashboard.</p>
                        <p style="margin:0;"><a href="{{ route('meetings.details', $meeting) }}" style="display:inline-block;padding:12px 20px;background:#f7c948;color:#112b49;text-decoration:none;font-size:13px;font-weight:700;border-radius:6px;">Open meeting dashboard</a></p>
                        <p style="margin:22px 0 0;font-size:11px;color:#9aa7b2;">This email was sent by AykonMeet — make every minute count.</p>
                    </td></tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
