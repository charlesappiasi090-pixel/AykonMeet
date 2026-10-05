<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk · {{ $meeting->meeting_topic }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #0e2036; color: #fff; min-height: 100vh; display: flex; flex-direction: column; }
        header { padding: 30px 40px 0; display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; }
        .eyebrow { font-size: 11px; font-weight: 800; letter-spacing: 2.5px; color: #f7c948; margin-bottom: 8px; }
        header h1 { font-size: clamp(26px, 4vw, 44px); line-height: 1.05; color: #fff; }
        header p { color: #9fb2c4; font-size: 14px; margin-top: 8px; }
        .code-chip { background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.18); padding: 12px 18px; border-radius: 8px; font-size: 13px; white-space: nowrap; }
        .code-chip strong { font-family: 'Courier New', monospace; letter-spacing: 1px; color: #f7c948; display: block; margin-top: 3px; font-size: 18px; }
        main { flex: 1; display: grid; grid-template-columns: 1.1fr .9fr; gap: 30px; padding: 44px 40px; }
        .panel { background: #132b48; border: 1px solid rgba(255,255,255,.1); border-radius: 14px; padding: 34px; }
        .stats { display: flex; gap: 14px; margin-bottom: 26px; }
        .stat { flex: 1; background: rgba(255,255,255,.05); border-radius: 10px; padding: 18px 16px; text-align: center; }
        .stat strong { display: block; font-size: 46px; line-height: 1; color: #f7c948; }
        .stat span { font-size: 11px; color: #9fb2c4; text-transform: uppercase; letter-spacing: 1px; }
        .checkin-form { display: flex; gap: 10px; margin-bottom: 18px; }
        .checkin-form input { flex: 1; border: 0; border-radius: 10px; padding: 18px 20px; font: 600 22px 'Courier New', monospace; letter-spacing: 3px; color: #0e2036; outline: none; text-transform: uppercase; }
        .checkin-form input:focus { box-shadow: 0 0 0 3px rgba(247,201,72,.5); }
        .checkin-form button { border: 0; border-radius: 10px; background: #f7c948; color: #0e2036; font: 800 15px 'Segoe UI', sans-serif; padding: 0 26px; cursor: pointer; }
        .checkin-form button:hover { background: #ffdd6b; }
        .msg { border-radius: 10px; padding: 18px 20px; font-size: 16px; font-weight: 700; text-align: center; }
        .msg.success { background: #173d2c; border: 1px solid #2e7d50; color: #7fe0a8; }
        .msg.error { background: #40201c; border: 1px solid #c0392b; color: #ff9d93; }
        .list-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; color: #9fb2c4; font-size: 12px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
        .attendee-list { max-height: 460px; overflow-y: auto; }
        .attendee { display: flex; align-items: center; gap: 12px; padding: 11px 6px; border-bottom: 1px solid rgba(255,255,255,.07); }
        .attendee .avatar { width: 34px; height: 34px; border-radius: 50%; background: rgba(247,201,72,.15); color: #f7c948; display: grid; place-items: center; font-weight: 800; }
        .attendee .name { flex: 1; font-size: 14px; }
        .attendee .state { font-size: 10px; font-weight: 800; padding: 5px 9px; border-radius: 20px; text-transform: uppercase; letter-spacing: .5px; }
        .attendee .state.in { background: rgba(67,184,121,.18); color: #67d69e; }
        .attendee .state.out { background: rgba(247,201,72,.15); color: #f7c948; }
        .attendee .state.expected { background: rgba(255,255,255,.08); color: #9fb2c4; }
        .foot { padding: 0 40px 28px; color: #5b7186; font-size: 12px; }
        @media (max-width: 860px) { main { grid-template-columns: 1fr; padding: 24px; } header { flex-direction: column; padding: 24px 24px 0; } .foot { padding: 0 24px 24px; } }
    </style>
</head>
<body>
    <header>
        <div>
            <p class="eyebrow">SELF CHECK-IN</p>
            <h1>{{ $meeting->meeting_topic }}</h1>
            <p>Scan the QR code on your badge, or type your attendee code below.</p>
        </div>
        <div class="code-chip">MEETING CODE<strong>{{ $meeting->meeting_code }}</strong></div>
    </header>
    <main>
        <div class="panel">
            <div class="stats">
                <div class="stat"><strong>{{ $meeting->attendees->count() }}</strong><span>Invited</span></div>
                <div class="stat"><strong>{{ $meeting->attendees->whereNotNull('checked_in_at')->count() }}</strong><span>Checked in</span></div>
                <div class="stat"><strong>{{ $meeting->attendees->count() - $meeting->attendees->whereNotNull('checked_in_at')->count() }}</strong><span>Expected</span></div>
            </div>
            <form class="checkin-form" method="post" action="{{ route('kiosk.check-in', $meeting) }}" id="checkin-form">
                @csrf
                <input name="attendee_code" id="attendee_code" placeholder="AT-XXXXXX" autocomplete="off" autofocus required>
                <button type="submit">Check in</button>
            </form>
            @if(session('kiosk_success'))<div class="msg success">Welcome, {{ session('kiosk_success') }} — you're checked in.</div>@endif
            @if(session('kiosk_error'))<div class="msg error">We couldn't find code {{ session('kiosk_error') }}. Try again or find the front desk.</div>@endif
        </div>
        <div class="panel">
            <div class="list-head"><span>Guest list</span><span>{{ $meeting->attendees->whereNotNull('checked_in_at')->count() }}/{{ $meeting->attendees->count() }} present</span></div>
            <div class="attendee-list">
                @forelse($meeting->attendees as $attendee)
                    <div class="attendee">
                        <div class="avatar">{{ strtoupper(substr($attendee->full_name, 0, 1)) }}</div>
                        <div class="name">{{ $attendee->full_name }}</div>
                        @if($attendee->checked_in_at && $attendee->checked_out_at)
                            <span class="state out">Departed</span>
                        @elseif($attendee->checked_in_at)
                            <span class="state in">Present</span>
                        @else
                            <span class="state expected">Expected</span>
                        @endif
                    </div>
                @empty
                    <p style="color:#9fb2c4;text-align:center;padding:30px 0;">No attendees registered yet.</p>
                @endforelse
            </div>
        </div>
    </main>
    <div class="foot">AykonMeet kiosk — scan, tap, done.</div>
    <script>
        (function () {
            var code = new URLSearchParams(window.location.search).get('code');
            if (code) {
                var input = document.getElementById('attendee_code');
                input.value = code;
                document.getElementById('checkin-form').submit();
            }
        })();
    </script>
</body>
</html>