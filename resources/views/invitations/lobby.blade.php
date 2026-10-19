@extends('layouts.app')

@section('title', 'Lobby · '.$meeting->meeting_topic)

@section('content')
@php
    $settings = $meeting->sessionSettings();
    $typeLabel = match (true) {
        $meeting->isHybrid() => 'Hybrid meeting',
        $meeting->is_online => 'Online meeting',
        default => 'In-person meeting',
    };
    $joinUrl = route('invitations.enter', ['attendee' => $attendee->invitation_token]);
@endphp
<div class="ivl">
    <style>
        .ivl{padding:24px 30px 48px;max-width:1240px;margin:0 auto;font-family:"DM Sans",sans-serif;color:#112b49}
        .ivl *{box-sizing:border-box}
        .ivl-head{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:20px;align-items:center;padding:26px 28px;border-radius:20px;background:radial-gradient(ellipse at 90% -20%,rgba(247,201,72,.24),transparent 55%),linear-gradient(140deg,#0b1f38,#112b49 60%,#173a63);color:#fff;box-shadow:0 20px 46px rgba(8,22,42,.25)}
        .ivl-eyebrow{font:800 10.5px "Space Grotesk",sans-serif;letter-spacing:2.6px;text-transform:uppercase;color:#f7c948}
        .ivl-head h1{margin:12px 0 0;font:600 clamp(24px,3vw,36px)/1.1 "Fraunces",Georgia,serif;color:#fff}
        .ivl-meta{display:flex;flex-wrap:wrap;gap:9px;margin-top:14px}
        .ivl-chip{display:inline-flex;align-items:center;gap:7px;padding:7px 12px;border:1px solid rgba(255,255,255,.16);border-radius:999px;background:rgba(255,255,255,.07);color:#e7eef7;font:600 11.5px "DM Sans",sans-serif}
        .ivl-chip.code{background:#f7c948;border-color:#f7c948;color:#112b49;letter-spacing:.06em}
        .ivl-join{height:52px;padding:0 28px;border:0;border-radius:13px;background:linear-gradient(135deg,#2ecc71,#239b56);color:#fff;font:700 14px "Space Grotesk",sans-serif;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:9px;box-shadow:0 12px 28px rgba(46,204,113,.36);transition:transform .16s ease}
        .ivl-join:hover{transform:translateY(-2px)}
        .ivl-grid{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(300px,1fr);gap:20px;margin-top:22px;align-items:start}
        .ivl-stage{position:relative;border-radius:18px;overflow:hidden;background:linear-gradient(145deg,#101f34,#091522 68%);min-height:420px;display:flex;align-items:center;justify-content:center;border:1px solid #203750}
        .ivl-stage video{width:100%;height:100%;min-height:420px;object-fit:cover;background:#050b12}
        .ivl-stage .mirror{transform:scaleX(-1)}
        .ivl-placeholder{position:absolute;inset:0;display:grid;place-items:center;text-align:center;padding:30px;color:#c9d6e6}
        .ivl-placeholder .ico{display:grid;place-items:center;width:72px;height:72px;margin:0 auto 16px;border-radius:22px;background:rgba(247,201,72,.16);font-size:38px}
        .ivl-placeholder h3{margin:0 0 8px;color:#fff;font:600 22px "Space Grotesk",sans-serif}
        .ivl-placeholder p{max-width:380px;margin:0 auto 18px;color:#a9bbcf;font-size:13px;line-height:1.6}
        .ivl-tag{position:absolute;bottom:14px;left:14px;padding:7px 12px;border-radius:8px;background:rgba(5,11,18,.72);color:#fff;font:700 12px "DM Sans",sans-serif}
        .ivl-controls{position:absolute;bottom:14px;left:50%;transform:translateX(-50%);display:flex;gap:10px}
        .ivl-ctrl{display:grid;place-items:center;width:52px;height:52px;border:1px solid rgba(255,255,255,.14);border-radius:50%;background:#203750;color:#fff;font-size:19px;cursor:pointer;transition:background .15s ease}
        .ivl-ctrl:hover{background:#2a4664}
        .ivl-ctrl.off{background:#c03b2d}
        .ivl-card{padding:22px 24px;border-radius:18px;background:#fff;border:1px solid #e4eaf1;box-shadow:0 10px 30px rgba(17,43,73,.05);margin-bottom:20px}
        .ivl-card h2{margin:0 0 4px;color:#112b49;font:700 17px "Space Grotesk",sans-serif}
        .ivl-card p.sub{margin:0 0 16px;color:#6b7d91;font-size:12.5px}
        .ivl-test{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:14px 0;border-bottom:1px solid #eef2f6;flex-wrap:wrap}
        .ivl-test:last-child{border-bottom:0}
        .ivl-test .label{display:flex;align-items:center;gap:11px;min-width:0}
        .ivl-test .label .ico{display:grid;place-items:center;width:38px;height:38px;flex:0 0 38px;border-radius:11px;background:#f1f5f9;color:#112b49;font-size:18px}
        .ivl-test .label strong{display:block;color:#112b49;font-size:13.5px}
        .ivl-test .label small{display:block;margin-top:2px;color:#718096;font-size:11.5px}
        .ivl-select{max-width:230px;width:100%;padding:9px 11px;border:1px solid #d5e0ea;border-radius:9px;background:#f7fafd;color:#112b49;font:12px "DM Sans",sans-serif}
        .ivl-meter{display:flex;align-items:flex-end;gap:3px;height:34px;width:120px}
        .ivl-meter i{flex:1;background:#dbe3ea;border-radius:3px;transition:height .08s linear,background .1s ease}
        .ivl-pill{padding:6px 12px;border-radius:999px;font:800 10.5px "DM Sans",sans-serif;text-transform:uppercase;letter-spacing:.3px;white-space:nowrap}
        .ivl-pill.ok{background:#ddf3e6;color:#1c6b3d}
        .ivl-pill.warn{background:#fdeecd;color:#946a00}
        .ivl-pill.bad{background:#fbe0e0;color:#a5382c}
        .ivl-btn{padding:9px 15px;border:1px solid #d5e0ea;border-radius:10px;background:#f7fafd;color:#112b49;font:700 12px "DM Sans",sans-serif;cursor:pointer}
        .ivl-btn:hover{background:#e9eff5}
        .ivl-note{padding:13px 15px;border:1px solid #cfe0f0;border-radius:12px;background:#f2f8fe;color:#2c5b8a;font-size:12px;line-height:1.55}
        .ivl-note strong{color:#112b49}
        .ivl-summary{display:grid;gap:9px}
        .ivl-summary .row{display:flex;justify-content:space-between;gap:12px;padding:9px 0;border-bottom:1px solid #eef2f6;font-size:12.5px}
        .ivl-summary .row:last-child{border-bottom:0}
        .ivl-summary .row span{color:#718096}
        .ivl-summary .row strong{color:#112b49;text-align:right}
        .ivl-join-full{display:flex;width:100%;margin-top:4px}
        @media(max-width:900px){.ivl-grid{grid-template-columns:1fr}.ivl-head{grid-template-columns:1fr}.ivl-stage,.ivl-stage video{min-height:320px}}
        @media(max-width:720px){.ivl{padding:16px 14px 40px}}
    </style>

    <header class="ivl-head">
        <div>
            <span class="ivl-eyebrow">&#9679; Pre-join lobby &middot; {{ $typeLabel }}</span>
            <h1>{{ $meeting->meeting_topic }}</h1>
            <div class="ivl-meta">
                <span class="ivl-chip code">ID {{ $meeting->meeting_code }}</span>
                <span class="ivl-chip">&#128197; {{ $meeting->start_date->format('D, M j, Y') }}</span>
                <span class="ivl-chip">&#128337; {{ $meeting->start_date->format('g:i A') }}</span>
                <span class="ivl-chip">Joining as {{ $attendee->full_name }}@if($myCompany) &middot; {{ $myCompany->name }}@endif</span>
            </div>
        </div>
        <a class="ivl-join" href="{{ $joinUrl }}">&#9654; Join Now</a>
    </header>

    <div class="ivl-grid">
        <section class="ivl-stage">
            <video id="ivlVideo" autoplay muted playsinline class="mirror"></video>
            <div class="ivl-placeholder" id="ivlPlaceholder">
                <div>
                    <div class="ico">&#127909;</div>
                    <h3>Set up your camera &amp; mic</h3>
                    <p>Allow access and pick your devices so you look and sound great before joining.</p>
                    <button class="ivl-btn" type="button" id="ivlEnable">Enable camera &amp; microphone</button>
                </div>
            </div>
            <span class="ivl-tag" id="ivlTag">Camera preview</span>
            <div class="ivl-controls">
                <button class="ivl-ctrl" id="ivlMic" type="button" title="Toggle microphone">&#127908;</button>
                <button class="ivl-ctrl" id="ivlCam" type="button" title="Toggle camera">&#128247;</button>
            </div>
        </section>

        <div>
            <section class="ivl-card">
                <h2>Device check</h2>
                <p class="sub">Choose your devices and run a quick test.</p>
                <div class="ivl-test">
                    <span class="label"><span class="ico">&#127908;</span><span><strong>Microphone</strong><small>Speak to see the level move</small></span></span>
                    <select class="ivl-select" id="ivlMicSelect" aria-label="Microphone device"><option>Default microphone</option></select>
                </div>
                <div class="ivl-test">
                    <div class="label"><span class="ico">&#127908;</span><span><strong>Input level</strong><small>Live audio meter</small></span></div>
                    <div class="ivl-meter" id="ivlMeter" aria-hidden="true"></div>
                </div>
                <div class="ivl-test">
                    <span class="label"><span class="ico">&#128266;</span><span><strong>Speakers</strong><small>Play a test tone</small></span></span>
                    <select class="ivl-select" id="ivlSpeakerSelect" aria-label="Speaker device"><option>Default speaker</option></select>
                </div>
                <div class="ivl-test">
                    <div class="label"><span class="ico">&#128266;</span><span><strong>Speaker test</strong><small>Hear a short chime</small></span></div>
                    <button class="ivl-btn" type="button" id="ivlSpeaker">Play test sound</button>
                </div>
                <div class="ivl-test">
                    <span class="label"><span class="ico">&#128247;</span><span><strong>Camera</strong><small>Preview shown on the left</small></span></span>
                    <select class="ivl-select" id="ivlCamSelect" aria-label="Camera device"><option>Default camera</option></select>
                </div>
                <div class="ivl-test">
                    <div class="label"><span class="ico">&#128247;</span><span><strong>Camera status</strong><small>Your preview</small></span></div>
                    <span class="ivl-pill warn" id="ivlCamState">Not started</span>
                </div>
            </section>

            <section class="ivl-card">
                <h2>Joining as</h2>
                <p class="sub">{{ $attendee->full_name }}@if($myCompany) &middot; {{ $myCompany->name }}@endif</p>
                <div class="ivl-summary">
                    <div class="row"><span>Meeting type</span><strong>{{ $typeLabel }}</strong></div>
                    <div class="row"><span>Scheduled</span><strong>{{ $meeting->start_date->format('M j, g:i A') }}</strong></div>
                    <div class="row"><span>Waiting room</span><strong>{{ $settings['waiting_room'] ? 'Enabled' : 'Disabled' }}</strong></div>
                    <div class="row"><span>Recording</span><strong>{{ $settings['allow_recording'] ? 'Allowed' : 'Off' }}</strong></div>
                </div>
                <a class="ivl-join ivl-join-full" href="{{ $joinUrl }}" style="margin-top:16px">&#9654; Join Now</a>
            </section>

            <div class="ivl-note"><strong>Almost there.</strong> You will join the shared room as <strong>{{ $attendee->full_name }}</strong> and be grouped under {{ $myCompany?->name ?? $attendee->companyName() }}. Your camera and mic settings carry into the meeting.</div>
        </div>
    </div>
</div>

<script>
(function () {
    var video = document.getElementById('ivlVideo');
    var placeholder = document.getElementById('ivlPlaceholder');
    var enableButton = document.getElementById('ivlEnable');
    var micButton = document.getElementById('ivlMic');
    var camButton = document.getElementById('ivlCam');
    var camState = document.getElementById('ivlCamState');
    var meter = document.getElementById('ivlMeter');
    var tag = document.getElementById('ivlTag');
    var micSelect = document.getElementById('ivlMicSelect');
    var camSelect = document.getElementById('ivlCamSelect');
    var speakerSelect = document.getElementById('ivlSpeakerSelect');
    var stream = null;
    var audioContext = null;
    var analyser = null;
    var rafId = null;
    var wantMic = @json((bool) $settings['default_mic_on']);
    var wantCam = @json((bool) $settings['default_camera_on']);
    var selectedMic = '';
    var selectedCam = '';

    for (var i = 0; i < 12; i++) { var bar = document.createElement('i'); bar.style.height = '12%'; meter.appendChild(bar); }

    function applyTracks() {
        if (!stream) return;
        stream.getAudioTracks().forEach(function (track) { track.enabled = wantMic; });
        stream.getVideoTracks().forEach(function (track) { track.enabled = wantCam; });
        micButton.classList.toggle('off', !wantMic);
        camButton.classList.toggle('off', !wantCam);
        camState.textContent = wantCam ? 'On' : 'Off';
        camState.className = 'ivl-pill ' + (wantCam ? 'ok' : 'bad');
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
            bars.forEach(function (barEl, index) {
                var value = data[index] || 0;
                var percent = Math.max(10, Math.min(100, value / 255 * 100));
                barEl.style.height = percent + '%';
                barEl.style.background = percent > 66 ? '#2ecc71' : (percent > 33 ? '#f7c948' : '#dbe3ea');
            });
            rafId = requestAnimationFrame(draw);
        }
        draw();
    }

    function stopStream() {
        if (stream) stream.getTracks().forEach(function (track) { track.stop(); });
        if (rafId) cancelAnimationFrame(rafId);
    }

    function listDevices() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) return;
        navigator.mediaDevices.enumerateDevices().then(function (devices) {
            function fill(select, kind, current) {
                var existing = select.querySelectorAll('option[data-device]');
                existing.forEach(function (option) { option.remove(); });
                devices.filter(function (d) { return d.kind === kind; }).forEach(function (d, index) {
                    var option = document.createElement('option');
                    option.value = d.deviceId;
                    option.setAttribute('data-device', '1');
                    option.textContent = d.label || (kind + ' ' + (index + 1));
                    if (d.deviceId === current) option.selected = true;
                    select.appendChild(option);
                });
            }
            fill(micSelect, 'audioinput', selectedMic);
            fill(camSelect, 'videoinput', selectedCam);
            fill(speakerSelect, 'audiooutput', '');
        });
    }

    async function start() {
        enableButton.disabled = true;
        enableButton.textContent = 'Requesting access...';
        stopStream();
        var constraints = { video: selectedCam ? { deviceId: { exact: selectedCam } } : true, audio: selectedMic ? { deviceId: { exact: selectedMic } } : true };
        try {
            stream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = stream;
            placeholder.style.display = 'none';
            audioContext = audioContext || new (window.AudioContext || window.webkitAudioContext)();
            applyTracks();
            startMeter();
            listDevices();
        } catch (error) {
            placeholder.style.display = 'grid';
            placeholder.querySelector('p').textContent = 'Camera or microphone access was blocked. You can still join, but others will not see or hear you until you allow access in your browser.';
            enableButton.disabled = false;
            enableButton.textContent = 'Try again';
        }
    }

    enableButton.addEventListener('click', start);
    if (wantMic || wantCam) start();
    listDevices();

    micButton.addEventListener('click', function () { if (!stream) return start(); wantMic = !wantMic; applyTracks(); });
    camButton.addEventListener('click', function () { if (!stream) return start(); wantCam = !wantCam; applyTracks(); });

    micSelect.addEventListener('change', function () { selectedMic = micSelect.value; if (stream) start(); });
    camSelect.addEventListener('change', function () { selectedCam = camSelect.value; if (stream) start(); });

    document.getElementById('ivlSpeaker').addEventListener('click', function () {
        try {
            var ctx = audioContext || new (window.AudioContext || window.webkitAudioContext)();
            audioContext = ctx;
            if (ctx.state === 'suspended') ctx.resume();
            var oscillator = ctx.createOscillator();
            var gain = ctx.createGain();
            oscillator.frequency.value = 440;
            gain.gain.value = 0.08;
            oscillator.connect(gain);
            var destination = ctx.createMediaStreamDestination();
            gain.connect(destination);
            var element = new Audio();
            element.srcObject = destination.stream;
            if (speakerSelect.value && typeof element.setSinkId === 'function') {
                element.setSinkId(speakerSelect.value).catch(function () {});
            }
            element.play().catch(function () {});
            oscillator.start();
            oscillator.stop(ctx.currentTime + 0.9);
        } catch (error) {
            window.alert('Could not play the test sound in this browser.');
        }
    });

    window.addEventListener('beforeunload', function () {
        stopStream();
        if (audioContext) audioContext.close();
    });
})();
</script>
@endsection
