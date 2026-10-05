@extends('layouts.app')

@section('content')
<style>
    .landing-page { min-height: calc(100vh - 220px); display: grid; grid-template-columns: minmax(0, 1fr) 260px; gap: 80px; align-items: center; max-width: 980px; margin: 0 auto; }
    .landing-page h1 { margin: 0; color: var(--navy); font: 700 clamp(52px, 8vw, 96px)/.94 'Space Grotesk', sans-serif; letter-spacing: -3px; }
    .landing-page h1 em { color: #c49700; font-style: normal; }
    .landing-copy .hero-copy { margin: 24px 0 0; }
    .join-form { margin-top: 42px; max-width: 620px; }
    .join-form label { display: block; margin-bottom: 9px; color: var(--navy); font-size: 12px; font-weight: 700; }
    .join-form > div { display: flex; }
    .join-form input { min-width: 0; flex: 1; border: 1px solid var(--line); padding: 0 17px; background: var(--white); color: var(--ink); font: 500 16px 'DM Sans', sans-serif; outline: none; }
    .join-form input:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(17,43,73,.08); }
    .join-form .button { flex: 0 0 auto; height: 52px; }
    .landing-note { align-self: end; border-left: 2px solid var(--yellow); padding: 12px 0 12px 26px; }
    .landing-note p { margin: 18px 0 0; color: var(--navy); font: 500 19px/1.35 'Space Grotesk', sans-serif; }
    @media (max-width: 700px) {
        .landing-page { min-height: auto; grid-template-columns: 1fr; gap: 44px; padding: 28px 0 20px; }
        .landing-page h1 { font-size: clamp(52px, 16vw, 76px); }
        .join-form { margin-top: 32px; }
        .join-form > div { display: grid; gap: 10px; }
        .join-form input { min-height: 52px; }
        .landing-note { align-self: start; }
    }
</style>
<section class="landing-page">
    <div class="landing-copy">
        <p class="eyebrow">WELCOME TO AYKONMEET</p>
        <h1>Join your<br><em>meeting.</em></h1>
        <p class="hero-copy">Enter the meeting code shared by your host to see the meeting details and check in when you arrive.</p>

        <form class="join-form" action="{{ route('meetings.join') }}" method="get">
            <label for="meeting_code">Meeting code</label>
            <div>
                <input id="meeting_code" name="code" placeholder="AYK-XXXXXX" autocomplete="off" required autofocus>
                <button class="button primary" type="submit">Join meeting <span>→</span></button>
            </div>
        </form>
    </div>
    <div class="landing-note">
        <span class="icon-box yellow">→</span>
        <p>One code gets you<br>where you need to be.</p>
    </div>
</section>
@endsection