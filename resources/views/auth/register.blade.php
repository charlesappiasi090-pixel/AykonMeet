<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Create Account — AykonMeet</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;1,9..144,400&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;overflow-x:hidden}
body{font-family:"DM Sans",sans-serif;background:#f7f9fb;color:#112b49;display:flex;align-items:center;justify-content:center;padding:24px}
.lf{width:100%;max-width:1100px;min-height:620px;background:#fff;border-radius:20px;box-shadow:0 30px 80px rgba(17,43,73,.12);overflow:hidden;display:grid;grid-template-columns:1fr 1fr}
.brand-side{background:linear-gradient(150deg,#112b49,#1a3d66 40%,#23507e);padding:48px 52px;display:flex;flex-direction:column;justify-content:space-between;position:relative;overflow:hidden}
.brand-side::before{content:"";position:absolute;top:-120px;right:-120px;width:380px;height:380px;background:radial-gradient(circle,rgba(247,201,72,.2),transparent 70%);border-radius:50%}
.brand-top{display:flex;align-items:center;gap:12px;color:#fff;font-family:"Space Grotesk",sans-serif;font-size:22px;font-weight:700;position:relative;z-index:2;text-decoration:none}
.bm{display:grid;place-items:center;width:42px;height:42px;background:#f7c948;color:#112b49;border-radius:8px;font-size:22px}
.brand-mid{position:relative;z-index:2}.brand-mid h1{font-family:"Fraunces",serif;font-weight:400;font-size:52px;line-height:1;color:#fff;margin-bottom:20px;letter-spacing:-1px}.brand-mid h1 em{font-style:italic;color:#f7c948;font-weight:500}
.brand-mid p{color:rgba(255,255,255,.75);font-size:15px;line-height:1.7;max-width:360px}
.form-side{padding:56px 52px;display:flex;flex-direction:column;justify-content:center;overflow-y:auto}
.form-side .back{color:#6b7a8d;font-size:13px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-bottom:24px;font-weight:500}.form-side .back:hover{color:#112b49}
.form-side h2{font-family:"Space Grotesk",sans-serif;font-size:30px;font-weight:700;color:#112b49;margin:0 0 8px;letter-spacing:-.5px}.form-side .sub{color:#6b7a8d;font-size:14px;margin-bottom:28px;line-height:1.5}
.field{margin-bottom:18px}.field label{display:block;color:#112b49;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;margin-bottom:8px}
.field input{width:100%;height:46px;border:1.5px solid #dde3ea;border-radius:8px;padding:0 16px;font:500 14px "DM Sans",sans-serif;color:#112b49;background:#f7f9fb;outline:none;transition:all .2s}.field input:focus{border-color:#112b49;background:#fff;box-shadow:0 0 0 3px rgba(17,43,73,.08)}
.btn-primary{width:100%;height:52px;border:0;border-radius:8px;background:#112b49;color:#f7c948;font:700 14px "DM Sans",sans-serif;cursor:pointer;margin-top:8px}.btn-primary:hover{transform:translateY(-1px);box-shadow:0 8px 20px rgba(17,43,73,.2)}
.err-banner{background:#fdecea;border:1px solid #e8b4ac;color:#a5382c;padding:12px 16px;border-radius:6px;font-size:13px;font-weight:600;margin-bottom:20px}
@media(max-width:800px){html,body{height:auto;padding:0}.lf{border-radius:0;max-width:100%;min-height:100vh;grid-template-columns:1fr}.brand-side{padding:36px 28px;min-height:280px}.brand-mid h1{font-size:38px}.form-side{padding:32px 28px}}
</style></head><body>
<div class="lf">
<div class="brand-side">
<a class="brand-top" href="/"><span class="bm">A</span><span>Aykon<span style="color:#f7c948">Meet</span></span></a>
<div class="brand-mid"><h1>Create your<br><em>account.</em></h1><p>Join AykonMeet to start scheduling and managing meetings right away.</p></div>
</div>
<div class="form-side">
<a class="back" href="{{ route("login") }}">← Back to sign in</a>
<h2>Create an account</h2>
<p class="sub">Fill in your details to get started.</p>
@if($errors->any())<div class="err-banner">{{ $errors->first() }}</div>@endif
<form method="post" action="{{ route("register.submit") }}">@csrf
<div class="field"><label>Name</label><input name="name" value="{{ old("name") }}" required autofocus></div>
<div class="field"><label>Email</label><input type="email" name="email" value="{{ old("email") }}" required></div>
<div class="field"><label>Password</label><input type="password" name="password" required></div>
<div class="field"><label>Confirm password</label><input type="password" name="password_confirmation" required></div>
<button class="btn-primary" type="submit">Create account <span>→</span></button>
</form></div></div></body></html>