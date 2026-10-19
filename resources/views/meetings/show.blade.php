@extends('layouts.app')

@section('content')
<style>
    .meeting-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(135px, 1fr)); gap: 12px; margin-top: 18px; }
    #hybridRsvpActions[hidden] { display: none !important; }
    #hybridRsvpActions[hidden] { display: none !important; }
    .meeting-actions form, .meeting-actions .button { width: 100%; }
    .meeting-actions .button { min-height: 54px; margin-top: 0; display: inline-flex; align-items: center; justify-content: center; gap: 9px; padding: 10px 14px; border-radius: 7px; text-decoration: none; white-space: nowrap; font-size: 13px; font-weight: 700; transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease; }
    .meeting-actions .button:hover { transform: translateY(-2px); box-shadow: 0 5px 14px rgba(17,43,73,.12); }
    .meeting-actions .button:focus-visible { outline: 3px solid rgba(247,201,72,.7); outline-offset: 2px; }
    .meeting-actions > a.button { grid-column: auto; }
    @media (max-width: 620px) { .meeting-actions { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; } .meeting-actions .button { min-height: 50px; padding: 8px; font-size: 12px; } }
    @media (max-width: 380px) { .meeting-actions { grid-template-columns: 1fr; } }
    .attendee-row small { display: block; margin-top: 5px; font-weight: 700; letter-spacing: .01em; }
    .status-dot.cancelled { background: #c0392b; }
    .cancelled-banner { margin-top: 22px; background: #fdf3f2; border: 1px solid #e8b4ac; color: #a5382c; padding: 14px 16px; font-size: 13px; font-weight: 700; }
    .notes-panel textarea { width: 100%; min-height: 130px; border: 1px solid var(--line); padding: 12px 14px; font: inherit; color: var(--ink); outline: none; resize: vertical; }
    .notes-panel textarea:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(17,43,73,.08); }
    .notes-panel-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
    .public-online-card { max-width: 1027px; margin: 20px auto; padding: 28px; border: 1px solid var(--line); border-left: 4px solid #e05858; background: linear-gradient(90deg,#fffafa 0%,#fff 45%); }
    .public-online-card.scheduled { border-left-color: var(--navy); background: var(--white); }
    .public-online-card-inner { display: grid; grid-template-columns: 100px minmax(0,1fr) auto; gap: 24px; align-items: center; }
    .public-online-time { text-align: center; }
    .public-online-time strong { display: block; color: #c03b2d; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
    .public-online-card.scheduled .public-online-time strong { color: #b18800; }
    .public-online-time span { display: block; margin-top: 5px; color: var(--navy); font: 700 18px "Space Grotesk",sans-serif; }
    .public-online-details h2 { margin: 0 0 7px; color: var(--navy); font: 700 22px "Space Grotesk",sans-serif; }
    .public-online-details p { margin: 0; color: var(--muted); font-size: 13px; }
    .public-online-meta { display: flex; flex-wrap: wrap; gap: 14px; align-items: center; margin-top: 12px; color: var(--muted); font-size: 12px; font-weight: 600; }
    .public-online-badge { padding: 4px 9px; border-radius: 3px; background: #e3f4e8; color: #267344; font-size: 10px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; }
    .public-online-join { display: inline-flex; justify-content: center; padding: 12px 18px; border-radius: 4px; background: #e05858; color: #fff; font-size: 12px; font-weight: 700; text-decoration: none; white-space: nowrap; }
    .public-online-card.scheduled .public-online-join { background: var(--navy); }
    .public-online-join:hover { filter: brightness(.95); }
    .public-online-note { max-width: 1027px; margin: 14px auto; color: var(--muted); font-size: 12px; line-height: 1.6; }
    @media(max-width:700px) { .public-online-card { padding: 20px; } .public-online-card-inner { grid-template-columns: 1fr; gap: 16px; } .public-online-time { text-align: left; } .public-online-join { width: 100%; } }
    .hybrid-page { max-width: 1120px; margin: 0 auto; padding: 8px 0 34px; }
    .hybrid-topline { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 18px; }
    .hybrid-label { display: inline-flex; align-items: center; gap: 8px; padding: 7px 11px; border: 1px solid rgba(247,201,72,.6); border-radius: 999px; background: #112b49; color: #f7c948; font-size: 10px; font-weight: 800; letter-spacing: .09em; text-transform: uppercase; }
    .hybrid-code { color: var(--muted); font-size: 12px; font-weight: 700; letter-spacing: .06em; }
    .hybrid-hero { padding: 34px; border-radius: 18px; background: radial-gradient(circle at 88% 8%,rgba(247,201,72,.2),transparent 30%),linear-gradient(125deg,#112b49,#1a3b60 62%,#274866); color: #fff; box-shadow: 0 18px 50px rgba(17,43,73,.18); }
    .hybrid-hero h1 { margin: 8px 0 9px; color: #fff; font: 700 clamp(28px,4vw,42px)/1.12 "Space Grotesk",sans-serif; letter-spacing: -.7px; }
    .hybrid-hero > p { margin: 0; color: #c4d0df; font-size: 14px; }
    .hybrid-live { display: inline-flex; align-items: center; gap: 7px; color: #f7c948; font-size: 11px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
    .hybrid-live-dot { width: 8px; height: 8px; border-radius: 50%; background: #f7c948; box-shadow: 0 0 0 4px rgba(247,201,72,.16); }
    .hybrid-choices { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 16px; margin-top: 18px; }
    .hybrid-choice { display: flex; min-width: 0; flex-direction: column; padding: 23px; border: 1px solid var(--line); border-radius: 14px; background: #fff; box-shadow: 0 8px 24px rgba(17,43,73,.05); }
    .hybrid-choice:has(input[name="attendance_mode"]:checked) { outline: 2px solid var(--navy); outline-offset: 2px; }
    .hybrid-choice.online { border-top: 3px solid var(--navy); }
    .hybrid-choice.in-person { border-top: 3px solid var(--yellow); }
    .hybrid-choice-icon { display: grid; place-items: center; width: 42px; height: 42px; margin-bottom: 14px; border-radius: 12px; background: #eaf0f6; color: var(--navy); font-size: 20px; }
    .hybrid-choice.in-person .hybrid-choice-icon { background: #fff6d9; color: #8a6600; }
    .hybrid-choice h2 { margin: 0 0 7px; color: var(--navy); font: 700 19px "Space Grotesk",sans-serif; }
    .hybrid-choice > p { margin: 0 0 15px; color: var(--muted); font-size: 13px; line-height: 1.55; }
    .hybrid-location { flex: 1; margin: 1px 0 18px; padding: 13px; border-radius: 9px; background: #f6f8fb; color: #34465d; font-size: 12px; line-height: 1.7; }
    .hybrid-location strong { display: block; margin-bottom: 3px; color: var(--navy); font-size: 12px; }
    .hybrid-action { display: inline-flex; min-height: 46px; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 11px 16px; border: 1px solid var(--navy); border-radius: 8px; background: var(--navy); color: #fff; font: 700 13px "DM Sans",sans-serif; text-decoration: none; cursor: pointer; transition: transform .15s ease,filter .15s ease,background-color .15s ease,color .15s ease; }
    .hybrid-action[hidden] { display: none !important; }
    .hybrid-action[hidden] { display: none !important; }
    .hybrid-action:hover { background: var(--yellow); border-color: var(--yellow); color: var(--navy); transform: translateY(-1px); }
    .hybrid-action:focus-visible { outline: 3px solid rgba(247,201,72,.58); outline-offset: 2px; }
    .hybrid-action:disabled { opacity: .6; cursor: wait; transform: none; }
    .hybrid-radio { display: flex; align-items: center; gap: 9px; margin-top: auto; padding: 12px 13px; border: 1px solid var(--line); border-radius: 8px; color: var(--navy); font-size: 13px; font-weight: 700; cursor: pointer; }
    .hybrid-radio input { width: 17px; height: 17px; accent-color: var(--navy); }
    .hybrid-presence { margin-top: 18px; padding: 22px; border: 1px solid var(--line); border-radius: 14px; background: #fff; }
    .hybrid-presence-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 15px; }
    .hybrid-presence-head h2 { margin: 0; color: var(--navy); font: 700 18px "Space Grotesk",sans-serif; }
    .hybrid-people { display: grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); gap: 9px; }
    .hybrid-person { display: flex; align-items: center; gap: 10px; min-width: 0; padding: 11px 12px; border: 1px solid #edf0f4; border-radius: 9px; background: #fafbfd; }
    .hybrid-person-avatar { display: grid; flex: 0 0 34px; place-items: center; width: 34px; height: 34px; border-radius: 50%; background: #eaf0f6; color: var(--navy); font-size: 12px; font-weight: 800; }
    .hybrid-person.in_person .hybrid-person-avatar { background: #fff6d9; color: #8a6600; }
    .hybrid-person-name { min-width: 0; flex: 1; overflow: hidden; color: #24364d; font-size: 12px; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
    .hybrid-person-mode { color: var(--muted); font-size: 10px; white-space: nowrap; }
    .hybrid-empty { grid-column: 1/-1; padding: 20px; border: 1px dashed var(--line); border-radius: 9px; color: var(--muted); font-size: 12px; text-align: center; }
    .hybrid-checkin { display: grid; gap: 10px; margin-top: 13px; padding: 14px; border-radius: 9px; background: #f2f6fa; }
    .hybrid-checkin[hidden] { display: none; }
    .hybrid-checkin input { width: 100%; min-width: 0; padding: 11px 12px; border: 1px solid #c9d5e2; border-radius: 7px; background: #fff; font: 13px "DM Sans",sans-serif; }
    .hybrid-checkin input:focus { outline: 3px solid rgba(247,201,72,.25); border-color: var(--yellow); }
    .hybrid-checkin-status { min-height: 16px; color: var(--navy); font-size: 11px; line-height: 1.5; }
    .hybrid-organizer-groups { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: 14px; margin: 18px 0 24px; }
    .hybrid-organizer-group { min-width: 0; padding: 18px; border: 1px solid var(--line); border-radius: 12px; background: #fff; }
    .hybrid-organizer-group h3 { margin: 0 0 13px; color: var(--navy); font: 700 15px "Space Grotesk",sans-serif; }
    .hybrid-organizer-group ul { display: grid; gap: 8px; margin: 0; padding: 0; list-style: none; }
    .hybrid-organizer-group li,.hybrid-organizer-empty { padding: 9px 10px; border-radius: 7px; background: #f6f8fb; color: #34465d; font-size: 12px; }
    .hybrid-organizer-meta { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px; color: var(--muted); font-size: 11px; }
    .hybrid-share-link { display: grid; grid-template-columns: minmax(0,1fr) auto; gap: 10px; align-items: end; margin: 18px 0; padding: 18px; border: 1px solid #d8e0e8; border-top: 3px solid var(--yellow); border-radius: 12px; background: #fff; }
    .hybrid-share-link label { min-width: 0; color: var(--navy); font-size: 12px; font-weight: 700; }
    .hybrid-share-link input { display: block; width: 100%; min-width: 0; margin-top: 7px; padding: 11px 12px; border: 1px solid #c9d5e2; border-radius: 7px; background: #f5f7f9; color: var(--navy); font: 12px "DM Sans",sans-serif; }
    .hybrid-share-link button { min-height: 40px; padding: 9px 15px; border: 1px solid var(--navy); border-radius: 7px; background: var(--navy); color: #fff; font: 700 12px "DM Sans",sans-serif; cursor: pointer; }
    .hybrid-share-link button:hover { border-color: var(--yellow); background: var(--yellow); color: var(--navy); }
.hybrid-share-link a.meeting-share-anchor { display: block; min-width: 0; margin-top: 7px; padding: 11px 12px; border: 1px solid #c9d5e2; border-radius: 7px; background: #f5f7f9; color: var(--navy); font: 12px "DM Sans",sans-serif; text-decoration: underline; word-break: break-all; }
.hybrid-share-link a.meeting-share-anchor:hover { color: #0d5c9c; }
    .hybrid-share-status { grid-column: 1/-1; min-height: 15px; color: var(--navy); font-size: 11px; }
    @media(max-width:560px) { .hybrid-share-link { grid-template-columns: 1fr; } }
    @media(max-width:800px) { .hybrid-organizer-groups{grid-template-columns:1fr} }
    .hybrid-back { color: #d7dfeb; font-size: 12px; font-weight: 600; text-decoration: none; }
    @media(max-width:700px) { .hybrid-hero{padding:25px 20px}.hybrid-choices{grid-template-columns:1fr}.hybrid-presence{padding:17px}.hybrid-topline{align-items:flex-start;flex-direction:column} }
</style>
@if($meeting->isHybrid() && (! auth()->check() || (! auth()->user()->isAdmin() && auth()->id() !== $meeting->host_user_id)))
@php
    $hybridUrls = [
        'join' => \Illuminate\Support\Facades\URL::signedRoute('sessions.join', ['meeting' => $meeting]),
        'send_code' => \Illuminate\Support\Facades\URL::signedRoute('sessions.participants.send-code', ['meeting' => $meeting]),
        'rsvp' => route('meetings.hybrid-rsvp', $meeting),
    ];
    $hybridLocation = $meeting->physical_location
        ?: $meeting->room?->location
        ?: $meeting->room?->name
        ?: trim(implode(' · ', array_filter([$meeting->from_location, $meeting->to_location])));
    $hybridMapUrl = $hybridLocation
        ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($hybridLocation)
        : null;
@endphp
<div class="hybrid-page" id="hybridMeeting" data-urls="{{ json_encode($hybridUrls) }}" data-csrf="{{ csrf_token() }}">
    <div class="hybrid-topline">
        <a class="hybrid-back" href="{{ route('landing') }}">← Back to meeting code</a>
        <span class="hybrid-label">✦ Hybrid session</span>
    </div>
    <section class="hybrid-hero">
        <span class="hybrid-live"><span class="hybrid-live-dot"></span>{{ $meeting->status === 'in_progress' ? 'Session live' : ucfirst($meeting->status) }}</span>
        <h1>{{ $meeting->meeting_topic }}</h1>
        <p>Hosted by {{ $meeting->name }} · {{ $meeting->start_date->format('D, M j · g:i A') }}</p>
    </section>
    <aside class="hybrid-presence"><div class="hybrid-presence-head"><h2>Hybrid Meeting Information</h2></div><p class="muted">Attend in person or online. Verify the email address on your invitation; online participants enter the emailed code in the meeting room, while in-person participants confirm their RSVP here. Attendance choices can be changed until the meeting starts.</p></aside>
    <section class="hybrid-choices" aria-label="Choose how to attend">
        <article class="hybrid-choice online">
            <span class="hybrid-choice-icon" aria-hidden="true">◉</span>
            <h2>Attend Online</h2>
            <p>Choose online attendance to join the meeting through AykonMeet’s video call.</p>
            <div class="hybrid-location"><strong>Online Meeting</strong>Verify your invited email, then enter the emailed code in the meeting room to join.</div>
            <label class="hybrid-radio"><input type="radio" name="attendance_mode" value="online" form="hybridRsvp" required> Choose online</label>
        </article>
        <article class="hybrid-choice in-person">
            <span class="hybrid-choice-icon" aria-hidden="true">⌖</span>
            <h2>Attend In Person</h2>
            <p>Meet the group at the physical location. Verify your invited email to confirm your choice.</p>
            <div class="hybrid-location">
                <strong>Physical Location</strong>
                {{ $hybridLocation ?: $meeting->room?->name ?: 'Location to be confirmed by the host.' }}
                @if($meeting->room?->name)<br>{{ $meeting->room->name }}@if($meeting->room->number), Room {{ $meeting->room->number }}@endif @if($meeting->room->floor), Floor {{ $meeting->room->floor }}@endif @endif
            </div>
            <label class="hybrid-radio"><input type="radio" name="attendance_mode" value="in_person" form="hybridRsvp" required> Choose in person</label>
        </article>
    </section>
    <section class="hybrid-presence">
        <div class="hybrid-presence-head"><h2>Confirm your attendance choice</h2></div>
        @if($meeting->status === 'scheduled' && $meeting->started_at === null)
        <form id="hybridRsvp" class="hybrid-checkin">
            <input type="email" id="hybridEmail" maxlength="255" autocomplete="email" placeholder="Email from your invitation" aria-label="Email from your invitation" required>
            <button class="hybrid-action" id="sendHybridCode" type="button">Email me a verification code</button>
            <input id="hybridCode" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="6-digit verification code" aria-label="6-digit verification code" hidden>
            <button class="hybrid-action hybrid-resend-code" id="resendHybridCode" type="button" hidden>Resend code</button>
            <button class="hybrid-action" id="confirmHybridRsvp" type="submit">Confirm RSVP</button>
            <span class="hybrid-checkin-status" id="hybridRsvpStatus" role="status" aria-live="polite"></span>
        </form>
        @else
        <p class="muted">Attendance choices are closed because this meeting has started or ended.</p>
        @endif
        <div id="hybridRsvpActions" class="meeting-actions">
            <a class="hybrid-action" id="joinHybridOnline" href="{{ $hybridUrls['join'] }}">▶ Join Online</a>
            @if($hybridMapUrl)<a class="hybrid-action" id="viewHybridLocation" href="{{ $hybridMapUrl }}" target="_blank" rel="noopener">⌖ View Location</a>@endif
            <button class="hybrid-action" id="changeHybridRsvp" type="button">Change attendance choice</button>
        </div>
    </section>
</div>
<script>
(function(){
    const root=document.getElementById("hybridMeeting");
    if(!root)return;
    const urls=JSON.parse(root.dataset.urls),csrf=root.dataset.csrf;
    const form=document.getElementById("hybridRsvp"),email=document.getElementById("hybridEmail"),code=document.getElementById("hybridCode"),status=document.getElementById("hybridRsvpStatus");
    const resendCode=document.getElementById("resendHybridCode");
    const joinOnline=document.getElementById("joinHybridOnline");
    const verifiedEmailKey="hybridVerifiedEmail:{{ $meeting->id }}",codeSentAtKey=verifiedEmailKey+":sentAt";
    const selectedAttendanceMode=()=>root.querySelector('input[name="attendance_mode"]:checked')?.value;
    root.querySelectorAll('input[name="attendance_mode"]').forEach(radio=>{
        radio.addEventListener("change",()=>{
            code.required=radio.value==="in_person"&&!code.hidden;
            document.getElementById("sendHybridCode").textContent=radio.value==="online"
                ? "Continue to Online Meeting"
                : "Email me a verification code";
            status.textContent=radio.value==="online"
                ? "Online selected. Continue to the meeting link, then verify your email in the room to receive a code."
                : "In-person selected. Verify your email and confirm your RSVP.";
        });
    });
    async function post(url,payload){
        const response=await fetch(url,{method:"POST",headers:{"Content-Type":"application/json","Accept":"application/json","X-CSRF-TOKEN":csrf},body:JSON.stringify(payload)});
        const data=await response.json();
        if(!response.ok)throw new Error(data.message||"Could not complete that request.");
        return data;
    }
    document.getElementById("sendHybridCode").addEventListener("click",async(event)=>{
        const button=event.currentTarget;
        if(!form.reportValidity())return;
        const choice=selectedAttendanceMode();
        if(!choice){status.textContent="Choose whether to attend online or in person first.";return}
        if(choice==="online"){
            sessionStorage.setItem(verifiedEmailKey,email.value.trim());
            sessionStorage.removeItem(codeSentAtKey);
            window.location.assign(joinOnline.href);
            return;
        }
        button.disabled=true;
        try{
            const result=await post(urls.send_code,{email:email.value.trim()});
            email.readOnly=true;
            sessionStorage.setItem(verifiedEmailKey,email.value.trim());
            sessionStorage.setItem(codeSentAtKey,String(Date.now()));
            resendCode.hidden=false;
            if(choice==="online"){
                status.textContent=result.message+" Check your inbox. Enter the code in the meeting room after selecting Join Online.";
            }else{
                code.hidden=false;code.required=true;code.focus();
                status.textContent=result.message+" Enter the code to confirm your in-person attendance.";
            }
        }
        catch(error){status.textContent=error.message}
        finally{button.disabled=false}
    });
    resendCode.addEventListener("click",async()=>{
        if(!email.reportValidity())return;
        resendCode.disabled=true;
        try{
            const result=await post(urls.send_code,{email:email.value.trim()});
            sessionStorage.setItem(verifiedEmailKey,email.value.trim());
            sessionStorage.setItem(codeSentAtKey,String(Date.now()));
            status.textContent=result.message+" Check your inbox for the new code.";
        }catch(error){status.textContent=error.message}
        finally{resendCode.disabled=false}
    });
    joinOnline.addEventListener("click",async event=>{
        event.preventDefault();
        if(selectedAttendanceMode()!=="online"){status.textContent="Choose Attend Online before joining the video meeting.";return}
        if(!email.reportValidity())return;
        joinOnline.setAttribute("aria-disabled","true");
        try{
            const emailAddress=email.value.trim();
            email.readOnly=true;
            sessionStorage.setItem(verifiedEmailKey,emailAddress);
            sessionStorage.removeItem(codeSentAtKey);
            window.location.assign(joinOnline.href);
        }catch(error){status.textContent=error.message}
        finally{joinOnline.removeAttribute("aria-disabled")}
    });
    form.addEventListener("submit",async(event)=>{
        event.preventDefault();
        const choice=selectedAttendanceMode();
        if(!choice){status.textContent="Choose whether to attend online or in person first.";return}
        if(!form.reportValidity())return;
        if(choice!=="in_person"){status.textContent="Use Join Online to attend the video meeting.";return}
        if(code.hidden){status.textContent="Verify your email first to confirm in-person attendance.";return}
        const button=document.getElementById("confirmHybridRsvp");button.disabled=true;
        try{
            const result=await post(urls.rsvp,{email:email.value.trim(),verification_code:code.value.trim(),attendance_mode:selectedAttendanceMode()});
            status.textContent=result.message;
            code.value="";code.hidden=true;code.required=false;document.getElementById("sendHybridCode").hidden=false;
        }catch(error){status.textContent=error.message}
        finally{button.disabled=false}
    });
    document.getElementById("changeHybridRsvp").addEventListener("click",()=>{
        sessionStorage.removeItem(verifiedEmailKey);sessionStorage.removeItem(codeSentAtKey);
        document.getElementById("sendHybridCode").hidden=false;
        resendCode.hidden=true;
        form.querySelectorAll('input[name="attendance_mode"]').forEach(radio=>radio.checked=false);
        email.readOnly=false;email.value="";code.value="";code.hidden=true;code.required=false;status.textContent="Choose an attendance method and verify your email again.";
        email.focus();
    });
})();
</script>
@elseif($meeting->is_online && auth()->guest())
@php
    $sessionJoinUrl = \Illuminate\Support\Facades\URL::signedRoute('sessions.join', ['meeting' => $meeting]);
@endphp
<a class="back-link" href="{{ route('landing') }}">← Back to meeting code</a>
<article class="public-online-card {{ $meeting->status === 'in_progress' ? 'live' : 'scheduled' }}">
    <div class="public-online-card-inner">
        <div class="public-online-time">
            <strong>{{ $meeting->status === 'in_progress' ? 'Live now' : 'Online session' }}</strong>
            <span>{{ $meeting->status === 'in_progress' ? '●' : $meeting->start_date->format('g:i A') }}</span>
        </div>
        <div class="public-online-details">
            <h2>{{ $meeting->meeting_topic }}</h2>
            <p>Hosted by {{ $meeting->name }}</p>
            <div class="public-online-meta">
                <span class="public-online-badge">AykonMeet</span>
                <span>👥 {{ $meeting->attendees->count() }} invited</span>
                <span>{{ $meeting->start_date->format('D, M j') }}</span>
            </div>
        </div>
        @if(in_array($meeting->status, ['scheduled', 'in_progress'], true))
        <a class="public-online-join" href="{{ $sessionJoinUrl }}">▶ {{ $meeting->status === 'in_progress' ? 'Join now' : 'Open room' }}</a>
        @else
        <span class="public-online-join" aria-disabled="true">Session {{ $meeting->status === 'completed' ? 'ended' : 'unavailable' }}</span>
        @endif
    </div>
</article>
<p class="public-online-note">Join the online session and verify the email address from your invitation. A one-time code will be emailed to you to confirm your attendance.</p>
@else
<div class="meeting-page-head">
    <div>
        <a class="back-link" href="{{ route('meetings.index') }}">← Back to console</a>
        <p class="eyebrow">MEETING ACCESS / {{ $meeting->meeting_code }}</p>
        <h1>{{ $meeting->meeting_topic }}</h1>
        <p class="hero-copy">Hosted by {{ $meeting->name }}
            @if($meeting->company_name)
            for {{ $meeting->company_name }}
            @endif
        </p>
    </div>
    <div class="big-code"><span>MEETING CODE</span><strong>{{ $meeting->meeting_code }}</strong></div>
</div>
@if($meeting->is_online && !$meeting->isHybrid() && auth()->check() && $meeting->host_user_id === auth()->id())
@php($onlineMeetingShareUrl = \Illuminate\Support\Facades\URL::signedRoute('sessions.join', ['meeting' => $meeting]))
<section class="hybrid-share-link" aria-labelledby="onlineMeetingShareTitle">
    <label id="onlineMeetingShareTitle">Shareable meeting link — participants verify their invited email to join
        <a id="onlineMeetingShareUrl" class="meeting-share-anchor" href="{{ $onlineMeetingShareUrl }}" target="_blank" rel="noopener">{{ $onlineMeetingShareUrl }}</a>
    </label>
    <button id="copyOnlineMeetingShareUrl" type="button">Copy meeting link</button>
    <span id="onlineMeetingShareStatus" class="hybrid-share-status" role="status" aria-live="polite"></span>
</section>
<script>
    document.getElementById('copyOnlineMeetingShareUrl').addEventListener('click', async function () {
        const link = document.getElementById('onlineMeetingShareUrl');
        const status = document.getElementById('onlineMeetingShareStatus');
        try {
            await navigator.clipboard.writeText(link.href);
            status.textContent = 'Meeting link copied. You can now share it with participants.';
        } catch (error) {
            link.focus();
            link.select();
            status.textContent = 'Copy is unavailable in this browser. Select and copy the meeting link above.';
        }
    });
</script>
@endif
@if($hybridAttendance && session('hybrid_share_url'))
<section class="hybrid-share-link" aria-labelledby="hybridShareTitle">
    <label id="hybridShareTitle" for="hybridShareUrl">Share this hybrid meeting with participants
        <input id="hybridShareUrl" type="url" readonly value="{{ session('hybrid_share_url') }}">
    </label>
    <button id="copyHybridShareUrl" type="button">Copy meeting link</button>
    <span id="hybridShareStatus" class="hybrid-share-status" role="status" aria-live="polite"></span>
</section>
<script>
    document.getElementById('copyHybridShareUrl').addEventListener('click', async function () {
        const link = document.getElementById('hybridShareUrl');
        const status = document.getElementById('hybridShareStatus');
        try {
            await navigator.clipboard.writeText(link.value);
            status.textContent = 'Meeting link copied. You can now share it with participants.';
        } catch (error) {
            link.focus();
            link.select();
            status.textContent = 'Copy is unavailable in this browser. Select and copy the meeting link above.';
        }
    });
</script>
@endif
@if($hybridAttendance)
<div class="hybrid-organizer-groups" id="hybridOrganizerAttendance" data-url="{{ route('meetings.hybrid-attendance', $meeting) }}">
    @foreach([
        'online' => 'Online Participants',
        'in_person' => 'In-Person Participants',
        'not_responded' => 'Not Responded',
    ] as $groupKey => $groupTitle)
    <section class="hybrid-organizer-group" aria-live="polite">
        <h3>{{ $groupTitle }} <span id="hybrid-{{ $groupKey }}-count">{{ $hybridAttendance[$groupKey.'_count'] }}</span></h3>
        <ul id="hybrid-{{ $groupKey }}-list">
            @forelse($hybridAttendance[$groupKey] as $person)
            <li>{{ $person['name'] }}</li>
            @empty
            <li class="hybrid-organizer-empty">No participants</li>
            @endforelse
        </ul>
    </section>
    @endforeach
</div>
<script>
(function(){
    const root=document.getElementById("hybridOrganizerAttendance");
    if(!root)return;
    const groups={online:"online",in_person:"in_person",not_responded:"not_responded"};
    function render(data){
        Object.entries(groups).forEach(([key,field])=>{
            document.getElementById("hybrid-"+key+"-count").textContent=data[field+"_count"];
            const list=document.getElementById("hybrid-"+key+"-list");
            list.replaceChildren();
            const entries=data[field];
            if(!entries.length){const empty=document.createElement("li");empty.className="hybrid-organizer-empty";empty.textContent="No participants";list.appendChild(empty);return}
            entries.forEach(person=>{const item=document.createElement("li");item.textContent=person.name;list.appendChild(item)});
        });
    }
    window.setInterval(async()=>{
        try{const response=await fetch(root.dataset.url,{headers:{"Accept":"application/json"}});if(!response.ok)throw new Error("Attendance list could not be refreshed.");render(await response.json())}
        catch(error){console.error(error)}
    },5000);
})();
</script>
@endif
<div class="meeting-detail-grid">
    <section class="panel status-panel">
        <div class="status-line">
            <span class="status-dot {{ $meeting->status }}"></span>
            <span>{{ str_replace('_', ' ', ucfirst($meeting->status)) }}</span>
        </div>
        <div class="timer" id="timer" data-started="{{ $meeting->started_at?->timestamp ?? '' }}" data-ended="{{ $meeting->ended_at?->timestamp ?? '' }}">00:00:00</div>
        <p class="muted">Meeting duration</p>
        @if(auth()->check() && $meeting->status === 'scheduled')
        <div class="meeting-actions">
            <form method="post" action="{{ route('meetings.start', $meeting) }}">
                @csrf
                <button class="button primary" type="submit" @if(!(((int)($meeting->min_attendees??0))===0 || ($meeting->attendees->count() >= (int)($meeting->min_attendees??0)))) disabled title="Need {{ (int)($meeting->min_attendees??0) }} attendees, currently {{ $meeting->attendees->count() }}" @endif>Start meeting <span>▶</span></button>
            </form>
        </div>
        @endif
        @if(auth()->check() && $meeting->status === 'in_progress')
        <div class="meeting-actions">
            <form method="post" action="{{ route('meetings.end', $meeting) }}">
                @csrf
                <button class="button secondary" type="submit">End meeting <span>■</span></button>
            </form>
        </div>
        @endif
        @guest
            @if(in_array($meeting->status, ['scheduled', 'in_progress'], true))
            <div class="meeting-actions">
                <a class="button primary" href="{{ route('login', ['meeting' => $meeting->meeting_code]) }}">
                    {{ $meeting->status === 'scheduled' ? 'Start meeting' : 'End meeting' }} <span>{{ $meeting->status === 'scheduled' ? '▶' : '■' }}</span>
                </a>
            </div>
            @endif
        @endguest
        @if(auth()->user()?->isAdmin())
        <div class="meeting-actions">
            @if($meeting->is_online)
            <a class="button secondary" href="{{ route('sessions.report', $meeting) }}">Session report <span>▤</span></a>
            @endif
            @if($meeting->isCancelled())
                <form method="post" action="{{ route('meetings.reopen', $meeting) }}">
                    @csrf
                    <button class="button secondary" type="submit">Reopen meeting <span>↻</span></button>
                </form>
            @endif
            <a class="button secondary" href="{{ route('meetings.badges', $meeting) }}">Badges <span>▦</span></a>
        </div>
        @if(!$meeting->isCancelled() && $meeting->status !== 'completed')
            <div class="meeting-actions">
                <a class="button secondary" href="{{ route('kiosk.show', $meeting->meeting_code) }}">Kiosk <span>📷</span></a>
            </div>
        @endif
        @if(!$meeting->isCancelled())
            <div class="meeting-actions">
                @if($meeting->status === 'completed' && $meeting->attendees->count() > 0)<a class="button secondary" href="{{ route('meetings.pdf', $meeting) }}">Session report <span>↓</span></a>@elseif($meeting->status === 'completed' && $meeting->attendees->count() === 0)<button class="button secondary" type="button" style="opacity:.4;cursor:not-allowed" title="No attendees to report on" disabled>Session report <span>↓</span></button>@else<button class="button secondary" type="button" style="opacity:.4;cursor:not-allowed" title="Available after the meeting ends" disabled>Session report <span>🔒</span></button>@endif
            </div>
        @endif
        @endif
        @if($canViewSessionReport && !auth()->user()?->isAdmin())
        <div class="meeting-actions">
            <a class="button secondary" href="{{ route('sessions.report', $meeting) }}">Session report &amp; recordings <span>▤</span></a>
        </div>
        @endif
        @if($meeting->isCancelled())
        <div class="cancelled-banner">
            This meeting was cancelled
            @if($meeting->cancelled_at)
            on {{ $meeting->cancelled_at->format('D, d M Y · H:i') }}
            @endif
            . Attendees have been notified.
        </div>
        @endif
        <div class="detail-list">
            <div><span>When</span><strong>{{ $meeting->start_date->format('D, d M Y · H:i') }}</strong></div>
            <div><span>Room</span><strong>{{ $meeting->room?->name ?? 'Not assigned' }}</strong></div>
            <div><span>Attendees</span><strong>{{ $meeting->attendees->whereNotNull('checked_in_at')->count() }}/{{ $meeting->attendees->count() }} present@if((int)($meeting->min_attendees??0)>0) · min {{ (int)($meeting->min_attendees??0) }} required@endif</strong></div>
        </div>
    </section>
    <section class="panel attendance-panel">
        <div class="panel-title">
            <span class="icon-box yellow">✓</span>
            <div>
                <h3>Check in attendees</h3>
                <p>Enter an attendee code to mark them present.</p>
            </div>
        </div>
        <form class="checkin-form" method="post" action="{{ route('attendees.check-in', $meeting) }}">
            @csrf
            <input name="attendee_code" placeholder="AT-XXXXXX" value="{{ old('attendee_code') }}" required @disabled($meeting->isCancelled() || $meeting->status === 'completed')>
            <button class="button secondary" type="submit" @disabled($meeting->isCancelled() || $meeting->status === 'completed')>Mark present</button>
        </form>
        <div class="attendee-list">
            <div class="list-heading">
                <h3>Guest list</h3>
                <span>{{ $meeting->attendees->whereNotNull('checked_in_at')->count() }}/{{ $meeting->attendees->count() }} present</span>
            </div>
            @forelse($meeting->attendees as $attendee)
                <div class="attendee-row">
                    <div class="avatar">{{ strtoupper(substr($attendee->full_name, 0, 1)) }}</div>
                    <div>
                        <strong>{{ $attendee->full_name }}</strong>
                        <small>{{ $attendee->attendee_code }} · {{ $attendee->email }}</small>
                    </div>
                    @if($attendee->checked_in_at)
                        <span class="{{ $attendee->checked_out_at ? 'pending' : 'present' }}">{{ $attendee->checked_out_at ? 'Departed' : 'Present' }}</span>
                    @else
                        <span class="pending">Expected</span>
                    @endif
                </div>
            @empty
                <p class="muted">No attendees added yet.</p>
            @endforelse
        </div>
        <form class="import-form" method="post" action="{{ route('attendees.import', $meeting) }}" enctype="multipart/form-data">
            @csrf
            <label class="file-label">Upload attendee file (.csv · .xlsx · .xls) <input type="file" name="attendees_file" accept=".csv,.xlsx,.xls" required></label>
            <button class="text-button" type="submit">Import list →</button>
            <a class="text-button" href="{{ route('attendees.template') }}">Download template ↓</a>
        </form>
    </section>
</div>
@auth
<section class="panel notes-panel" style="margin-top:20px;">
    <div class="panel-title">
        <span class="icon-box yellow">✎</span>
        <div>
            <h3>Minutes & notes</h3>
            <p>Save your minutes to open the latest meeting report as a PDF.</p>
        </div>
    </div>
    <form method="post" action="{{ route('meetings.notes', $meeting) }}">
        @csrf
        <textarea name="notes" placeholder="Action items, decisions, follow-ups...">{{ old('notes', $meeting->notes) }}</textarea>
        <p style="text-align:right;margin:10px 0 0;"><button class="button secondary" type="submit">Save &amp; view PDF <span>↓</span></button></p>
    </form>
</section>
@endauth
@if(session('unknown_attendee'))
<div class="modal-backdrop" id="unknown-modal">
    <div class="modal panel">
        <button class="modal-close" onclick="document.getElementById('unknown-modal').remove()">×</button>
        <p class="eyebrow">NEW ARRIVAL</p>
        <h2>We couldn't find that code.</h2>
        <p class="muted">No attendee matches <strong>{{ session('unknown_attendee_code', old('attendee_code')) }}</strong>. Add them and mark present now.</p>
        <form method="post" action="{{ route('attendees.store', $meeting) }}">
            @csrf
            <label>Full name<input name="full_name" value="{{ old('full_name') }}" required @error('full_name') class="invalid" @enderror></label>
            @error('full_name')<small class="field-error">{{ $message }}</small>@enderror
            <label>Email<input name="email" type="email" value="{{ old('email') }}" required @error('email') class="invalid" @enderror></label>
            @error('email')<small class="field-error">{{ $message }}</small>@enderror
            <label>Phone number<input name="phone" value="{{ old('phone') }}" required @error('phone') class="invalid" @enderror></label>
            @error('phone')<small class="field-error">{{ $message }}</small>@enderror
            <button class="button primary" type="submit">Add and mark present</button>
        </form>
    </div>
</div>
@endif
<script>const timer=document.getElementById('timer');if(timer){const started=Number(timer.dataset.started);const ended=Number(timer.dataset.ended);function tick(){if(!started)return;const seconds=Math.max(0,(ended||Math.floor(Date.now()/1000))-started);const h=String(Math.floor(seconds/3600)).padStart(2,'0');const m=String(Math.floor(seconds%3600/60)).padStart(2,'0');const s=String(seconds%60).padStart(2,'0');timer.textContent=`${h}:${m}:${s}`}tick();setInterval(tick,1000)}</script>
@endif
@endsection