<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Admin Login — AykonMeet</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,400;0,9..144,500;1,9..144,400&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;overflow-x:hidden}
body{font-family:"DM Sans",sans-serif;background:#f7f9fb;color:#112b49}
.lf{width:100%;height:100vh;min-height:100vh;background:#fff;overflow:hidden;display:grid;grid-template-columns:1fr 1fr}
.brand-side{background:linear-gradient(150deg,#0b1f38 0%,#112b49 45%,#1a3d66 100%);padding:48px 52px;display:flex;flex-direction:column;justify-content:space-between;position:relative;overflow:hidden;color:#fff}
.brand-side::before{content:"";position:absolute;top:-130px;right:-130px;width:400px;height:400px;background:radial-gradient(circle,rgba(247,201,72,.22),transparent 70%);border-radius:50%}
.brand-side::after{content:"";position:absolute;bottom:-140px;left:-110px;width:340px;height:340px;background:radial-gradient(circle,rgba(247,201,72,.12),transparent 70%);border-radius:50%}
.brand-grid{position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.045) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.045) 1px,transparent 1px);background-size:52px 52px;mask-image:radial-gradient(ellipse at 75% 55%,black 5%,transparent 78%);z-index:1;pointer-events:none}
.brand-top{display:flex;align-items:center;gap:12px;color:#fff;font-family:"Space Grotesk",sans-serif;font-size:22px;font-weight:700;position:relative;z-index:2;text-decoration:none}
.bm{display:grid;place-items:center;width:42px;height:42px;background:#f7c948;color:#112b49;border-radius:8px;font-size:22px}
.brand-mid{position:relative;z-index:2}
.kicker{display:inline-flex;align-items:center;gap:9px;font-family:"Space Grotesk",sans-serif;font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:#f7c948;margin-bottom:22px}
.kicker .dot{width:7px;height:7px;border-radius:50%;background:#f7c948;box-shadow:0 0 0 4px rgba(247,201,72,.25)}
.brand-mid h1{font-family:"Fraunces",serif;font-weight:400;font-size:clamp(38px,4vw,54px);line-height:1.02;color:#fff;margin-bottom:20px;letter-spacing:-1.2px}
.brand-mid h1 em{font-style:italic;color:#f7c948;font-weight:500}
.brand-mid p{color:rgba(255,255,255,.75);font-size:15px;line-height:1.7;max-width:370px}
.preview{position:relative;z-index:2;margin-top:32px;width:100%;max-width:400px;background:rgba(255,255,255,.09);border:1px solid rgba(255,255,255,.16);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border-radius:18px;padding:15px 17px;box-shadow:0 22px 46px rgba(0,0,0,.35);animation:floaty 6s ease-in-out infinite}
@keyframes floaty{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
.pv-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:13px}
.pv-title{font-family:"Space Grotesk",sans-serif;font-size:14px;font-weight:700;color:#fff}
.pv-live{display:inline-flex;align-items:center;gap:6px;background:rgba(247,201,72,.14);border:1px solid rgba(247,201,72,.4);color:#f7c948;font-size:9px;font-weight:700;letter-spacing:1px;text-transform:uppercase;padding:4px 9px;border-radius:20px}
.pv-live::before{content:"";width:6px;height:6px;border-radius:50%;background:#f7c948;animation:blink 1.6s ease-in-out infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.pv-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:13px}
.pv-stat{background:#fff;border-radius:10px;padding:8px 6px;text-align:center}
.pv-stat b{display:block;font-family:"Space Grotesk",sans-serif;font-size:17px;color:#112b49}
.pv-stat.gold{background:#f7c948}
.pv-stat small{display:block;font-size:8px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:#6b7a8d;margin-top:2px}
.pv-bars{display:flex;align-items:flex-end;gap:6px;height:48px;padding:0 2px}
.pv-bars i{flex:1;border-radius:3px 3px 0 0;background:linear-gradient(to top,rgba(255,255,255,.22),rgba(255,255,255,.5))}
.pv-bars i.hi{background:linear-gradient(to top,#f7c948,#ffd766)}
.pv-check{position:absolute;right:-16px;bottom:-18px;display:flex;align-items:center;gap:9px;background:#fff;border-radius:12px;padding:9px 13px;box-shadow:0 16px 32px rgba(0,0,0,.32);animation:floaty 5s ease-in-out infinite .8s}
.pv-check .dotg{width:9px;height:9px;border-radius:50%;background:#4ade80;box-shadow:0 0 0 4px rgba(74,222,128,.25);flex-shrink:0}
.pv-check strong{display:block;font-size:11px;color:#112b49;white-space:nowrap}
.pv-check small{display:block;font-size:9px;color:#6b7a8d;white-space:nowrap}
.brand-foot{position:relative;z-index:2;font-size:12px;color:rgba(255,255,255,.5);letter-spacing:.4px}
.form-side{padding:56px 52px;display:flex;flex-direction:column;justify-content:center;overflow-y:auto}
.form-side .back{color:#6b7a8d;font-size:13px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-bottom:26px;font-weight:500}.form-side .back:hover{color:#112b49}
.form-side h2{font-family:"Fraunces",serif;font-weight:500;font-size:34px;color:#112b49;margin-bottom:6px;letter-spacing:-.6px}
.form-side .sub{color:#6b7a8d;font-size:14px;margin-bottom:28px;line-height:1.5}
.err-banner{background:#fdecea;border:1px solid #e8b4ac;border-left:4px solid #c03b2d;color:#a5382c;padding:13px 16px;border-radius:8px;font-size:13px;font-weight:600;margin-bottom:20px}
.err-banner strong{display:block;font-family:"Space Grotesk",sans-serif;margin-bottom:3px}
.field{margin-bottom:18px}
.field label{display:block;color:#112b49;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px}
.field input[type=email],.field input[type=password],.field input[type=text]{width:100%;height:50px;border:1.5px solid #dde3ea;border-radius:10px;padding:0 16px;font:500 15px "DM Sans",sans-serif;color:#112b49;background:#f7f9fb;outline:none;transition:all .2s}
.field input::placeholder{color:#9aa8b8}
.field input:focus{border-color:#112b49;background:#fff;box-shadow:0 0 0 4px rgba(247,201,72,.28)}
.pw-wrap{position:relative}
.pw-wrap input{padding-right:74px!important}
.pw-toggle{position:absolute;right:8px;top:50%;transform:translateY(-50%);border:0;background:transparent;color:#112b49;font:700 11px "DM Sans",sans-serif;letter-spacing:.5px;text-transform:uppercase;cursor:pointer;padding:9px 11px;border-radius:8px;transition:background .15s}
.pw-toggle:hover{background:#f7f0d8}
.row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:2px 0 22px}
.remember{display:flex;align-items:center;gap:9px;color:#4a5a6e;font-size:13px;font-weight:500;cursor:pointer}
.remember input{width:17px;height:17px;accent-color:#112b49;cursor:pointer}
.forgot{color:#112b49;font-size:13px;font-weight:600;text-decoration:none}
.forgot:hover{color:#b18800}
.submit{width:100%;height:54px;border:0;border-radius:10px;background:linear-gradient(135deg,#112b49,#1a3d66);color:#f7c948;font:700 15px "DM Sans",sans-serif;letter-spacing:.3px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:10px;transition:all .2s;box-shadow:0 10px 26px rgba(17,43,73,.28)}
.submit:hover{transform:translateY(-2px);box-shadow:0 14px 32px rgba(17,43,73,.34)}
.submit span{font-size:18px;transition:transform .15s}.submit:hover span{transform:translateX(4px)}
.alt{margin-top:24px;text-align:center;font-size:13px;color:#6b7a8d}
.alt a{color:#112b49;font-weight:700;text-decoration:none}.alt a:hover{color:#b18800}
@media(max-width:860px){html,body{height:auto}.lf{height:auto;min-height:100vh;grid-template-columns:1fr}.brand-side{padding:36px 28px;min-height:300px}.brand-mid h1{font-size:38px}.form-side{padding:36px 28px}}
</style></head><body>
<div class="lf">
<div class="brand-side">
<div class="brand-grid" aria-hidden="true"></div>
<a class="brand-top" href="{{ route('landing') }}"><span class="bm">A</span><span>Aykon<span style="color:#f7c948">Meet</span></span></a>
<div class="brand-mid">
<span class="kicker"><span class="dot"></span>Admin portal</span>
<h1>Sign in to run your <em>meetings.</em></h1>
<p>Manage rooms, check in attendees, launch online sessions, and view live analytics — all from one console.</p>
<div class="preview" aria-hidden="true">
<div class="pv-head"><span class="pv-title">Dashboard</span><span class="pv-live">Live</span></div>
<div class="pv-stats">
<div class="pv-stat"><b>3</b><small>Live now</small></div>
<div class="pv-stat gold"><b>12</b><small>Today</small></div>
<div class="pv-stat"><b>47</b><small>Checked in</small></div>
</div>
<div class="pv-bars"><i style="height:40%"></i><i style="height:70%"></i><i style="height:52%"></i><i class="hi" style="height:92%"></i><i style="height:46%"></i><i class="hi" style="height:78%"></i><i style="height:60%"></i><i style="height:35%"></i></div>
<div class="pv-check"><span class="dotg"></span><div><strong>New check-in</strong><small>Ama Osei just arrived</small></div></div>
</div>
</div>
<div class="brand-foot">© {{ date('Y') }} AykonMeet · Make every minute count.</div>
</div>
<div class="form-side">
<a class="back" href="{{ route('landing') }}">← Back to home</a>
<h2>Welcome back</h2>
<p class="sub">Enter your admin credentials to continue.</p>
@if($errors->any())<div class="err-banner"><strong>Couldn't sign you in</strong>{{ $errors->first() }}</div>@endif
<form method="post" action="{{ route('login.submit') }}">@csrf
<div class="field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="admin@aykonmeet.com"></div>
<div class="field"><label for="password">Password</label><div class="pw-wrap"><input id="password" type="password" name="password" required placeholder="Enter your password"><button class="pw-toggle" type="button" aria-label="Show password" aria-pressed="false">Show</button></div></div>
<div class="row">
<label class="remember"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
</div>
<button class="submit" type="submit">Sign in as admin <span>→</span></button>
</form>
<div class="alt">Meeting participant? <a href="{{ route('landing') }}">Join with a meeting code</a></div>
</div>
</div>
<script>(function(){var b=document.querySelector('.pw-toggle'),i=document.getElementById('password');if(b&&i){b.addEventListener('click',function(){var v=i.type==='text';i.type=v?'password':'text';b.textContent=v?'Show':'Hide';b.setAttribute('aria-pressed',String(!v));});}})();</script>
</body></html>
