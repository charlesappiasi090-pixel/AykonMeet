<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0,viewport-fit=cover">
<meta http-equiv="Cache-Control" content="no-cache,no-store">
<title>AykonMeet — Make every minute count</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,400;0,9..144,500;1,9..144,400;1,9..144,400&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%}
body{font-family:"DM Sans",sans-serif;background:#0c1f36;color:#fff}
.page{display:flex;flex-direction:column;min-height:100vh;background:#0c1f36;position:relative;overflow:hidden}
.page::before{content:"";position:absolute;top:-30%;left:-10%;width:60%;height:80%;background:radial-gradient(ellipse,rgba(247,201,72,.12) 0%,transparent 60%);pointer-events:none}
.page::after{content:"";position:absolute;bottom:-20%;right:-10%;width:50%;height:60%;background:radial-gradient(ellipse,rgba(247,201,72,.08) 0%,transparent 60%);pointer-events:none}
nav{position:relative;z-index:10;display:flex;align-items:center;justify-content:space-between;padding:20px 56px;background:rgba(17,43,73,.8);border-bottom:1px solid rgba(255,255,255,.08);backdrop-filter:blur(10px)}
.logo{display:flex;align-items:center;gap:12px;text-decoration:none;color:#fff;font-family:"Space Grotesk",sans-serif;font-size:22px;font-weight:700;letter-spacing:-.4px}
.logo-mark{display:grid;place-items:center;width:40px;height:40px;background:#f7c948;color:#112b49;border-radius:8px;font-size:20px;font-weight:700}
.admin-btn{color:rgba(255,255,255,.8);text-decoration:none;font-size:13px;font-weight:600;padding:10px 20px;border:1px solid rgba(255,255,255,.15);border-radius:8px;background:rgba(255,255,255,.05);transition:all .15s}
.admin-btn:hover{background:#f7c948;color:#112b49;border-color:#f7c948}
.hero{position:relative;z-index:5;display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center;padding:80px 72px;max-width:1280px;margin:0 auto;width:100%}
.hero-copy{max-width:520px}
.eyebrow{display:inline-flex;align-items:center;gap:8px;color:#f7c948;font-size:11px;font-weight:800;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:24px}
.eyebrow::before{content:"";width:8px;height:8px;background:#f7c948;border-radius:2px}
h1{font-family:"Fraunces",serif;font-weight:400;font-size:clamp(48px,5.5vw,72px);line-height:1.05;letter-spacing:-2px;margin-bottom:24px;color:#fff}
h1 em{font-style:italic;color:#f7c948;font-weight:500}
.hero-sub{color:rgba(255,255,255,.75);font-size:18px;line-height:1.7;margin-bottom:36px;max-width:440px;font-weight:400}
.join-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:28px;box-shadow:0 4px 32px rgba(0,0,0,.2);backdrop-filter:blur(10px)}
.join-card label{display:block;color:#f7c948;font-size:12px;font-weight:700;margin-bottom:10px;letter-spacing:.3px}
.join-row{display:flex;gap:10px}
.join-input{flex:1;min-width:0;height:54px;border:1px solid rgba(255,255,255,.15);border-radius:10px;padding:0 18px;background:rgba(255,255,255,.08);color:#fff;font:500 15px "DM Sans",sans-serif;outline:none;transition:border .15s,box-shadow .15s}
.join-input::placeholder{color:rgba(255,255,255,.4)}
.join-input:focus{border-color:#f7c948;box-shadow:0 0 0 3px rgba(247,201,72,.15)}
.join-submit{flex:0 0 auto;height:54px;padding:0 28px;border:0;border-radius:10px;background:#f7c948;color:#112b49;font:600 14px "DM Sans",sans-serif;cursor:pointer;transition:background .15s,transform .1s}
.join-submit:hover{background:#f5bf20}
.join-submit:active{transform:scale(.99)}
.hero-visual{position:relative;display:flex;flex-direction:column;gap:16px}
.feature-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;flex:1}
.feature-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:14px;padding:24px;transition:transform .2s,box-shadow .2s,border-color .2s;backdrop-filter:blur(10px)}
.feature-card:hover{transform:translateY(-4px);box-shadow:0 12px 40px rgba(0,0,0,.3);border-color:rgba(247,201,72,.3)}
.feature-icon{display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;border-radius:10px;margin-bottom:14px}
.feature-icon svg{width:22px;height:22px}
.feature-card:nth-child(1) .feature-icon{background:#f7c948}
.feature-card:nth-child(1) .feature-icon svg{stroke:#112b49}
.feature-card:nth-child(2) .feature-icon{background:#112b49;border:1px solid rgba(247,201,72,.3)}
.feature-card:nth-child(2) .feature-icon svg{stroke:#f7c948}
.feature-card:nth-child(3) .feature-icon{background:#f7c948}
.feature-card:nth-child(3) .feature-icon svg{stroke:#112b49}
.feature-card:nth-child(4) .feature-icon{background:#112b49;border:1px solid rgba(247,201,72,.3)}
.feature-card:nth-child(4) .feature-icon svg{stroke:#f7c948}
.feature-card h3{font-family:"Space Grotesk",sans-serif;font-size:15px;font-weight:700;color:#fff;margin-bottom:6px}
.feature-card p{color:rgba(255,255,255,.65);font-size:13px;line-height:1.55}
.preview-card{background:#112b49;border:1px solid rgba(247,201,72,.2);border-radius:16px;padding:28px;color:#fff;position:relative;overflow:hidden}
.preview-card::before{content:"";position:absolute;top:-50%;right:-20%;width:60%;height:120%;background:radial-gradient(ellipse,rgba(247,201,72,.15) 0%,transparent 60%);pointer-events:none}
.preview-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;position:relative;z-index:1}
.preview-title{font-family:"Space Grotesk",sans-serif;font-size:16px;font-weight:700;color:#fff}
.preview-live{display:inline-flex;align-items:center;gap:6px;background:rgba(247,201,72,.15);color:#f7c948;padding:4px 10px;border-radius:20px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.6px}
.preview-live::before{content:"";width:6px;height:6px;background:#f7c948;border-radius:50%;animation:pulse 1.5s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
.preview-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;position:relative;z-index:1}
.pstat{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:14px;text-align:center}
.pstat .n{font-family:"Space Grotesk",sans-serif;font-size:24px;font-weight:700;color:#f7c948}.pstat .l{font-size:10px;color:rgba(255,255,255,.5);font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-top:3px}
.preview-chart{height:64px;display:flex;align-items:end;gap:5px;padding:0 4px;position:relative;z-index:1}
.pbar{flex:1;background:rgba(255,255,255,.1);border-radius:3px 3px 0 0;transition:height .3s ease}
.pbar:nth-child(1){height:35%}.pbar:nth-child(2){height:65%}.pbar:nth-child(3){background:#f7c948;height:85%}.pbar:nth-child(4){height:45%}.pbar:nth-child(5){height:70%}.pbar:nth-child(6){background:#f7c948;height:90%}.pbar:nth-child(7){height:55%}
footer{position:relative;z-index:5;padding:32px 56px;display:flex;justify-content:space-between;align-items:center;color:rgba(255,255,255,.5);font-size:12px;font-weight:500;border-top:1px solid rgba(255,255,255,.08);background:rgba(17,43,73,.8);backdrop-filter:blur(10px)}
footer a{color:inherit;text-decoration:none;transition:color .15s}
footer a:hover{color:#f7c948}
@media(max-width:960px){nav{padding:16px 24px}.hero{grid-template-columns:1fr;padding:48px 24px;gap:40px}.hero-copy,.hero-sub{max-width:none}.feature-grid{grid-template-columns:1fr 1fr}.preview-card{order:-1}footer{padding:24px;flex-direction:column;gap:12px;text-align:center}}
@media(max-width:640px){nav{padding:max(12px,env(safe-area-inset-top)) 16px 12px}.logo{font-size:18px;gap:8px}.logo-mark{width:34px;height:34px;font-size:16px}.admin-btn{padding:10px 12px}.hero{padding:28px 16px 36px;gap:22px}h1{font-size:clamp(34px,10vw,46px);letter-spacing:-1px}.hero-sub{font-size:16px;margin-bottom:22px}.join-card{padding:18px}.join-row{flex-direction:column}.join-submit{width:100%}.feature-grid{grid-template-columns:1fr}.feature-card{padding:18px}.preview-card{padding:18px}.preview-stats{grid-template-columns:1fr 1fr 1fr}footer{padding:20px 16px max(20px,env(safe-area-inset-bottom))}}
</style></head><body>
<div class="page">
<nav>
<a class="logo" href="/"><span class="logo-mark">A</span><span>Aykon<span class="logo-accent">Meet</span></span></a>
<a class="admin-btn" href="{{ route('login') }}">Admin login →</a>
</nav>
<main class="hero">
<div class="hero-copy">
<span class="eyebrow">All-in-one meetings</span>
<h1>Book rooms, check in guests,<br>and host video calls<em>.</em></h1>
<p class="hero-sub">The meeting platform built for teams that value every minute. No downloads, no friction — just one code to join.</p>
<div class="join-card">
<label for="mc">Enter meeting code</label>
<form class="join-row" action="{{ route('meetings.join') }}" method="get">
<input class="join-input" id="mc" name="code" placeholder="AYK-XXXXXX" autocomplete="off" required autofocus>
<button class="join-submit" type="submit">Join meeting</button>
</form>
</div>
</div>
<div class="hero-visual">
<div class="feature-grid">
<article class="feature-card">
<div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
<h3>Room booking</h3>
<p>Reserve spaces instantly with real-time availability.</p>
</article>
<article class="feature-card">
<div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
<h3>Guest check-in</h3>
<p>QR codes and one-tap arrival tracking.</p>
</article>
<article class="feature-card">
<div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg></div>
<h3>Video calls</h3>
<p>WebRTC rooms with screen share & recording.</p>
</article>
<article class="feature-card">
<div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="6" x2="12" y2="12"/><line x1="12" y1="12" x2="16" y2="14"/></svg></div>
<h3>Scheduling</h3>
<p>Recurring meetings with exceptions & reminders.</p>
</article>
</div>
<article class="preview-card">
<div class="preview-header"><span class="preview-title">Dashboard</span><span class="preview-live">Live</span></div>
<div class="preview-stats">
<div class="pstat"><div class="n">3</div><div class="l">Live meetings</div></div>
<div class="pstat"><div class="n">12</div><div class="l">Meetings today</div></div>
<div class="pstat"><div class="n">47</div><div class="l">Attendees in</div></div>
</div>
<div class="preview-chart" aria-label="Meeting activity">
<div class="pbar"></div><div class="pbar"></div><div class="pbar"></div><div class="pbar"></div><div class="pbar"></div><div class="pbar"></div><div class="pbar"></div>
</div>
</article>
</div>
</main>
<footer>
<span>AykonMeet · Make every minute count</span>
<div style="display:flex;gap:16px">
<a href="#">Privacy</a>
<a href="#">Terms</a>
<a href="#">Contact</a>
</div>
</footer>
</div></body></html>