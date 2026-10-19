@extends('layouts.app')

@section('content')
@php
    $isOnline = $meeting->is_online;
    $typeLabel = match ($meeting->meeting_type) {
        'online' => $meeting->isMultiCompany() ? 'Multi-company online meeting' : 'Online meeting',
        'hybrid' => 'Hybrid meeting',
        default => 'In-person meeting',
    };
@endphp
<div class="lby">
    <style>
        .lby{padding:24px 30px 48px;max-width:1240px;margin:0 auto}
        .lby-back{display:inline-flex;align-items:center;gap:7px;margin-bottom:16px;color:#6b7d91;font:700 12.5px "DM Sans",sans-serif;text-decoration:none}
        .lby-back:hover{color:var(--navy)}
        .lby-head{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:20px;align-items:center;padding:24px 28px;border-radius:20px;background:linear-gradient(140deg,#0b1f38,#112b49 60%,#173a63);color:#fff;box-shadow:0 20px 46px rgba(8,22,42,.25)}
        .lby-head h1{margin:12px 0 0;font:600 clamp(24px,3vw,36px)/1.1 "Fraunces",Georgia,serif;color:#fff}
        .lby-eyebrow{font:800 10.5px "Space Grotesk",sans-serif;letter-spacing:2.6px;text-transform:uppercase;color:#f7c948}
        .lby-meta{display:flex;flex-wrap:wrap;gap:9px;margin-top:14px}
        .lby-chip{display:inline-flex;align-items:center;gap:7px;padding:7px 12px;border:1px solid rgba(255,255,255,.16);border-radius:999px;background:rgba(255,255,255,.07);color:#e7eef7;font:600 11.5px "DM Sans",sans-serif}
        .lby-chip.code{background:#f7c948;border-color:#f7c948;color:#112b49;letter-spacing:.06em}
        .lby-enter{height:50px;padding:0 26px;border:0;border-radius:13px;background:linear-gradient(135deg,#2ecc71,#239b56);color:#fff;font:700 14px "Space Grotesk",sans-serif;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:9px;box-shadow:0 10px 26px rgba(46,204,113,.35);transition:transform .16s ease}
        .lby-enter:hover{transform:translateY(-2px)}
        .lby-grid{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(300px,1fr);gap:20px;margin-top:22px;align-items:start}
        .lby-stage{position:relative;border-radius:18px;overflow:hidden;background:linear-gradient(145deg,#101f34,#091522 68%);min-height:420px;display:flex;align-items:center;justify-content:center;border:1px solid #203750}
        .lby-stage video{width:100%;height:100%;min-height:420px;object-fit:cover;background:#050b12}
        .lby-stage .mirror{transform:scaleX(-1)}
        .lby-placeholder{position:absolute;inset:0;display:grid;place-items:center;text-align:center;padding:30px;color:#c9d6e6}
        .lby-placeholder .ico{display:grid;place-items:center;width:72px;height:72px;margin:0 auto 16px;border-radius:22px;background:rgba(247,201,72,.16);font-size:38px}
        .lby-placeholder h3{margin:0 0 8px;color:#fff;font:600 22px "Space Grotesk",sans-serif}
        .lby-placeholder p{max-width:380px;margin:0 auto 18px;color:#a9bbcf;font-size:13px;line-height:1.6}
        .lby-preview-tag{position:absolute;bottom:14px;left:14px;padding:7px 12px;border-radius:8px;background:rgba(5,11,18,.72);color:#fff;font:700 12px "DM Sans",sans-serif}
        .lby-controls{position:absolute;bottom:14px;left:50%;transform:translateX(-50%);display:flex;gap:10px}
        .lby-ctrl{display:grid;place-items:center;width:52px;height:52px;border:1px solid rgba(255,255,255,.14);border-radius:50%;background:#203750;color:#fff;font-size:19px;cursor:pointer;transition:background .15s ease}
        .lby-ctrl:hover{background:#2a4664}
        .lby-ctrl.off{background:#c03b2d}
        .lby-card{padding:22px 24px;border-radius:18px;background:#fff;border:1px solid #e4eaf1;box-shadow:0 10px 30px rgba(17,43,73,.05);margin-bottom:20px}
        .lby-card h2{margin:0 0 4px;color:#112b49;font:700 17px "Space Grotesk",sans-serif}
        .lby-card p.sub{margin:0 0 16px;color:#6b7d91;font-size:12.5px}
        .lby-test{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:13px 0;border-bottom:1px solid #eef2f6}
        .lby-test:last-child{border-bottom:0}
        .lby-test .label{display:flex;align-items:center;gap:11px;min-width:0}
        .lby-test .label .ico{display:grid;place-items:center;width:38px;height:38px;flex:0 0 38px;border-radius:11px;background:#f1f5f9;color:#112b49;font-size:18px}
        .lby-test .label strong{display:block;color:#112b49;font-size:13.5px}
        .lby-test .label small{display:block;margin-top:2px;color:#718096;font-size:11.5px}
        .lby-meter{display:flex;align-items:flex-end;gap:3px;height:34px;width:120px}
        .lby-meter i{flex:1;background:#dbe3ea;border-radius:3px;transition:height .08s linear,background .1s ease}
        .lby-pill{padding:6px 12px;border-radius:999px;font:800 10.5px "DM Sans",sans-serif;text-transform:uppercase;letter-spacing:.3px;white-space:nowrap}
        .lby-pill.ok{background:#ddf3e6;color:#1c6b3d}
        .lby-pill.warn{background:#fdeecd;color:#946a00}
        .lby-pill.bad{background:#fbe0e0;color:#a5382c}
        .lby-test-btn{padding:9px 15px;border:1px solid #d5e0ea;border-radius:10px;background:#f7fafd;color:#112b49;font:700 12px "DM Sans",sans-serif;cursor:pointer}
        .lby-test-btn:hover{background:#e9eff5}
        .lby-note{padding:13px 15px;border:1px solid #ead58b;border-radius:12px;background:#fff9e5;color:#6b5500;font-size:12px;line-height:1.55}
        .lby-note strong{color:#112b49}
        .lby-summary{display:grid;gap:9px}
        .lby-summary .row{display:flex;justify-content:space-between;gap:12px;padding:9px 0;border-bottom:1px solid #eef2f6;font-size:12.5px}
        .lby-summary .row:last-child{border-bottom:0}
        .lby-summary .row span{color:#718096}
        .lby-summary .row strong{color:#112b49;text-align:right}
        @media(max-width:900px){.lby-grid{grid-template-columns:1fr}.lby-head{grid-template-columns:1fr}.lby-stage,.lby-stage video{min-height:320px}}
        @media(max-width:720px){.lby{padding:16px 14px 40px}}
    </style>

    <a href="{{ route('meetings.details', $meeting) }}" class="lby-back">&larr; Back to meeting details</a>

    <header class="lby-head">
        <div>
            <span class="lby-eyebrow">&#9679; Pre-meeting lobby &middot; {{ $typeLabel }}</span>
            <h1>{{ $meeting->meeting_topic }}</h1>
            <div class="lby-meta">
                <span class="lby-chip code">ID {{ $meeting->meeting_code }}</span>
                <span class="lby-chip">&#128197; {{ $meeting->start_date->format('D, M j, Y') }}</span>
                <span class="lby-chip">&#128337; {{ $meeting->start_date->format('g:i A') }}</span>
                <span class="lby-chip">Host: {{ $meeting->name }}</span>
            </div>
        </div>
        <a class="lby-enter" href="{{ $joinUrl }}">
            @if($meeting->canStart()) &#9654; Enter Meeting Room @else &#9654; Open Room @endif
        </a>
    </header>

    <div class="lby-grid">
        <section class="lby-stage">
            <video id="lbyVideo" autoplay muted playsinline class="mirror"></video>
            <div class="lby-placeholder" id="lbyPlaceholder">
                <div>
                    <div class="ico">&#127909;</div>
                    <h3>Test your camera &amp; microphone</h3>
                    <p>Allow access so you can check how you look and sound before entering the meeting room.</p>
                    <button class="lby-test-btn" type="button" id="lbyEnable">Enable camera &amp; microphone</button>
                </div>
            </div>
            <span class="lby-preview-tag" id="lbyTag">Camera preview</span>
            <div class="lby-controls">
                <button class="lby-ctrl" id="lbyMic" type="button" title="Toggle microphone">&#127908;</button>
                <button class="lby-ctrl" id="lbyCam" type="button" title="Toggle camera">&#128247;</button>
            </div>
        </section>

        <div>
            <section class="lby-card">
                <h2>Device check</h2>
                <p class="sub">Make sure everything works before you join.</p>
                <div class="lby-test">
                    <span class="label"><span class="ico">&#127908;</span><span><strong>Microphone</strong><small>Speak to see the level move</small></span></span>
                    <div class="lby-meter" id="lbyMeter" aria-hidden="true"></div>
                </div>
                <div class="lby-test">
                    <span class="label"><span class="ico">&#128266;</span><span><strong>Speakers</strong><small>Play a test tone</small></span></span>
                    <button class="lby-test-btn" type="button" id="lbySpeaker">Play test sound</button>
                </div>
                <div class="lby-test">
                    <span class="label"><span class="ico">&#128247;</span><span><strong>Camera</strong><small>Preview shown on the left</small></span></span>
                    <span class="lby-pill warn" id="lbyCamState">Not started</span>
                </div>
            </section>

            <section class="lby-card">
                <h2>Joining as</h2>
                <p class="sub">{{ $meeting->name }}</p>
                <div class="lby-summary">
                    <div class="row"><span>Meeting type</span><strong>{{ $typeLabel }}</strong></div>
                    <div class="row"><span>Scheduled</span><strong>{{ $meeting->start_date->format('M j, g:i A') }}</strong></div>
                    <div class="row"><span>Waiting room</span><strong>{{ $settings['waiting_room'] ? 'Enabled' : 'Disabled' }}</strong></div>
                    <div class="row"><span>Recording</span><strong>{{ $settings['allow_recording'] ? 'Allowed' : 'Off' }}</strong></div>
                </div>
            </section>

            <div class="lby-note"><strong>You are in charge.</strong> When you are ready, enter the room. For online meetings the host connects automatically; invited participants verify their email to join the same shared session.</div>
        </div>
    </div>
</div>

<script>
(function () {
    var video = document.getElementById('lbyVideo');
    var placeholder = document.getElementById('lbyPlaceholder');
    var enableButton = document.getElementById('lbyEnable');
    var micButton = document.getElementById('lbyMic');
    var camButton = document.getElementById('lbyCam');
    var camState = document.getElementById('lbyCamState');
    var meter = document.getElementById('lbyMeter');
    var tag = document.getElementById('lbyTag');
    var stream = null;
    var audioContext = null;
    var analyser = null;
    var rafId = null;
    var wantMic = @json((bool) $settings['default_mic_on']);
    var wantCam = @json((bool) $settings['default_camera_on']);

    for (var i = 0; i < 12; i++) { var bar = document.createElement('i'); bar.style.height = '12%'; meter.appendChild(bar); }

    function applyTracks() {
        if (!stream) return;
        stream.getAudioTracks().forEach(function (track) { track.enabled = wantMic; });
        stream.getVideoTracks().forEach(function (track) { track.enabled = wantCam; });
        micButton.classList.toggle('off', !wantMic);
        camButton.classList.toggle('off', !wantCam);
        camState.textContent = wantCam ? 'On' : 'Off';
        camState.className = 'lby-pill ' + (wantCam ? 'ok' : 'bad');
        tag.textContent = wantCam ? 'Camera preview' : 'Camera off';
    }

    function startMeter() {
        if (!audioContext || !stream || !stream.getAudioTracks().length) return;
        analyser = audioContext.createAnalyser();
        analyser.fftSize = 32;
        audioContext.createMediaStreamSource(stream).connect(analyser);
        var data = new Uint8Array(analyser.frequencyBinCount);
        var bars = meter.querySelectorAll('i');
        function draw() {
            analyser.getByteFrequencyData(data);
            bars.forEach(function (bar, index) {
                var value = data[index] || 0;
                var percent = Math.max(10, Math.min(100, value / 255 * 100));
                bar.style.height = percent + '%';
                bar.style.background = percent > 66 ? '#2ecc71' : (percent > 33 ? '#f7c948' : '#dbe3ea');
            });
            rafId = requestAnimationFrame(draw);
        }
        draw();
    }

    async function start() {
        enableButton.disabled = true;
        enableButton.textContent = 'Requesting access...';
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            video.srcObject = stream;
            placeholder.style.display = 'none';
            audioContext = new (window.AudioContext || window.webkitAudioContext)();
            applyTracks();
            startMeter();
        } catch (error) {
            placeholder.querySelector('p').textContent = 'Camera or microphone access was blocked. You can still enter the meeting, but others will not see or hear you until you allow access in your browser.';
            enableButton.disabled = false;
            enableButton.textContent = 'Try again';
        }
    }

    enableButton.addEventListener('click', start);
    if (wantMic || wantCam) start();

    micButton.addEventListener('click', function () { if (!stream) return start(); wantMic = !wantMic; applyTracks(); });
    camButton.addEventListener('click', function () { if (!stream) return start(); wantCam = !wantCam; applyTracks(); });

    document.getElementById('lbySpeaker').addEventListener('click', function () {
        try {
            var ctx = audioContext || new (window.AudioContext || window.webkitAudioContext)();
            audioContext = ctx;
            if (ctx.state === 'suspended') ctx.resume();
            var oscillator = ctx.createOscillator();
            var gain = ctx.createGain();
            oscillator.frequency.value = 440;
            gain.gain.value = 0.08;
            oscillator.connect(gain).connect(ctx.destination);
            oscillator.start();
            oscillator.stop(ctx.currentTime + 0.9);
        } catch (error) {
            window.alert('Could not play the test sound in this browser.');
        }
    });

    window.addEventListener('beforeunload', function () {
        if (rafId) cancelAnimationFrame(rafId);
        if (stream) stream.getTracks().forEach(function (track) { track.stop(); });
        if (audioContext) audioContext.close();
    });
})();
</script>
@endsection
