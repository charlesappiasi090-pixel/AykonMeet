<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ?? "Aykon Meet" }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&family=Fraunces:ital,wght@0,400;0,500;0,600;1,400;1,500;1,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset("css/app.css") }}">

<style>
html:has(dialog[open]),body:has(dialog[open]){overflow:hidden!important}
dialog.meeting-dialog{position:fixed!important;inset:0;margin:auto;height:fit-content}
@media(min-width:961px){body.authed{padding-left:260px!important}}
.sidebar-fb{position:fixed;top:0;left:0;bottom:0;width:260px;background:#112b49;color:#c8d3e0;display:flex;flex-direction:column;z-index:50;overflow-y:auto;font-family:"DM Sans",sans-serif}
.sidebar-fb a{color:inherit;text-decoration:none}
.sb-brand{display:flex;align-items:center;gap:10px;padding:26px 24px 22px;color:#fff;font-family:"Space Grotesk",sans-serif;font-size:20px;font-weight:700;text-decoration:none;border-bottom:1px solid rgba(255,255,255,.08)}
.sb-mark{display:grid;place-items:center;width:34px;height:34px;background:#f7c948;color:#112b49;font-size:18px;border-radius:4px;flex:0 0 34px}
.sb-accent{color:#f7c948}
.sb-nav{padding:18px 14px;flex:1}.sb-nav ul{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:2px}
.sb-link{display:flex;align-items:center;gap:12px;padding:11px 14px;color:#aab8c9;text-decoration:none;font:600 13px "DM Sans",sans-serif;border-radius:4px;width:100%;border:0;background:transparent;cursor:pointer;text-align:left;transition:all .15s ease;font-family:inherit}
.sb-link:hover:not(:disabled){background:rgba(255,255,255,.06);color:#fff}
.sb-link.active{background:#f7c948;color:#112b49}
.sb-link:disabled{opacity:.55;cursor:not-allowed}
.sb-icon{font-size:17px;width:22px;text-align:center;flex:0 0 22px}
.sb-label{flex:1}
.sb-soon{font-size:9px;font-weight:700;background:rgba(255,255,255,.1);color:#9fb0c5;padding:2px 6px;border-radius:3px;text-transform:uppercase;letter-spacing:.5px}
.sb-footer{padding:16px 14px;border-top:1px solid rgba(255,255,255,.08);margin-top:auto}
.sb-user{display:flex;align-items:center;gap:11px;padding:8px 10px 14px}
.sb-avatar{width:36px;height:36px;background:#f7c948;color:#112b49;display:grid;place-items:center;border-radius:50%;font:700 14px "DM Sans",sans-serif;flex:0 0 36px}
.sb-user-info{min-width:0;flex:1}.sb-user-info strong{display:block;color:#fff;font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.sb-user-info small{display:block;color:#8c9cb5;font-size:11px;margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sb-logout{width:100%;padding:10px;border:1px solid rgba(255,255,255,.12);background:transparent;color:#c8d3e0;font:600 12px "DM Sans",sans-serif;border-radius:4px;cursor:pointer}.sb-logout:hover{background:rgba(255,255,255,.06);color:#fff}
.sb-toggle{display:none;position:fixed;top:max(12px,env(safe-area-inset-top));left:max(12px,env(safe-area-inset-left));z-index:60;width:44px;height:44px;background:#112b49;color:#f7c948;border:0;border-radius:4px;font-size:20px;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,.2)}
.sb-backdrop{display:none}
@media(max-width:960px){.sidebar-fb{transform:translateX(-100%);transition:transform .25s ease;box-shadow:4px 0 20px rgba(0,0,0,.2);width:min(86vw,300px)}.sidebar-fb.open{transform:translateX(0)}.sb-toggle{display:grid;place-items:center}body.authed{padding-left:0!important}body.nav-open{overflow:hidden}.sb-backdrop.on{display:block;position:fixed;inset:0;z-index:45;background:rgba(11,31,56,.55)}}
@media(min-width:961px){body.authed .page-shell{margin-left:0;max-width:none!important;padding:40px!important}}
body.authed{margin:0!important}
</style>
</head>
@php
$showAdminSidebar = auth()->user()?->isAdmin() && in_array('admin', request()->route()?->gatherMiddleware() ?? [], true);
@endphp
<body class="{{ $showAdminSidebar ? "authed " : "" }}{{ request()->routeIs('meetings.index') ? 'meetings-dashboard' : '' }}{{ request()->routeIs('landing') ? ' landing-layout' : '' }}">
<!-- Global Feedback System -->
<div id="ayk-page-loader" class="ayk-page-loader" aria-hidden="true"></div>
<div id="ayk-toast-container" class="ayk-toast-container" aria-live="polite" aria-atomic="true"></div>

@if($showAdminSidebar)
<aside class="sidebar sidebar-fb" id="sidebar">
<a class="sb-brand" href="{{ route("meetings.index") }}"><span class="sb-mark">A</span><span>Aykon<span class="sb-accent">Meet</span></span></a>
<nav class="sb-nav">
@php
$cr=request()->route()?->getName();
$nav=[
 ["route"=>"meetings.index","label"=>"Meetings","icon"=>"📅"],
 ["route"=>"admin.dashboard","label"=>"Rooms","icon"=>"🏢","anchor"=>"#workspace"],
 ["route"=>"users.index","label"=>"Users","icon"=>"👥"],
 ["route"=>"reports.index","label"=>"Reports & Analytics","icon"=>"📊"],
 ["route"=>"notifications.index","label"=>"Notifications","icon"=>"🔔"],
 ["route"=>"settings.index","label"=>"Settings","icon"=>"⚙️"],
];
@endphp
<ul>@foreach($nav as $item)
@php
if($item["label"]==="Meetings"){$act=$cr==="meetings.index"||($cr&&(\Illuminate\Support\Str::startsWith($cr,"sessions.")||\Illuminate\Support\Str::startsWith($cr,"signal.")));}
else{$act=($item["route"]??false)&&$cr===$item["route"];}
@endphp
<li>@if($item["route"]??false)
@php $href=route($item["route"]).($item["anchor"]??""); @endphp
<a href="{{ $href }}" class="sb-link {{ $act?"active":"" }}" @if($item["dialog"]??false) onclick="var d=document.getElementById('{{ $item["dialog"] }}');if(d){event.preventDefault();d.showModal();}var s=document.getElementById('sidebar');if(s)s.classList.remove('open');" @endif>
<span class="sb-icon">{{ $item["icon"] }}</span><span class="sb-label">{{ $item["label"] }}</span></a>
@endif</li>
@endforeach</ul>
</nav>
<div class="sb-footer">
<div class="sb-user"><div class="sb-avatar">{{ strtoupper(substr(auth()->user()->name??"A",0,1)) }}</div><div class="sb-user-info"><strong>{{ auth()->user()->name??"Admin" }}</strong><small>{{ auth()->user()->email??"" }}</small></div></div>
<form method="post" action="{{ route("logout") }}" style="margin:0">@csrf<button type="submit" class="sb-logout">Logout</button></form>
</div>
</aside>
<div class="sb-backdrop" id="sidebar-backdrop" hidden></div>
<button type="button" class="sb-toggle" id="sidebar-toggle" aria-label="Open menu" aria-controls="sidebar" aria-expanded="false">☰</button>
@else
<header class="topbar"><a class="brand" href="{{ route("landing") }}"><span class="brand-mark">A</span><span>Aykon<span class="brand-accent">Meet</span></span></a>
@if(auth()->check())
<nav>
@php $dashUrl = auth()->user()->isAdmin() ? route("admin.dashboard") : route("dashboard"); @endphp
<a href="{{ $dashUrl }}" class="admin-link">{{ auth()->user()->isAdmin() ? "Admin console" : "My dashboard" }}</a>
<form method="post" action="{{ route("logout") }}" style="display:inline;margin:0 0 0 14px">@csrf<button type="submit" class="admin-link" style="background:none;border:0;padding:0;cursor:pointer;font:inherit;color:inherit">Logout</button></form>
</nav>
@else
<nav><a href="{{ route("login") }}" class="admin-link">Admin login</a></nav>
@endif
</header>
@endif
<main class="page-shell">
@if(session("success"))<div class="flash success">{{ session("success") }}</div>@endif
@if(session("error"))<div class="flash error">{{ session("error") }}</div>@endif
@if($errors->any())<div class="flash error"><strong>Check the form:</strong> {{ $errors->first() }}</div>@endif
@yield("content")
</main>
<footer class="footer">AykonMeet <span>•</span> Make every minute count.</footer>
<script src="{{ asset('js/feedback.js') }}"></script>
<link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
<script>
(function(){
var t=document.getElementById("sidebar-toggle"),s=document.getElementById("sidebar"),b=document.getElementById("sidebar-backdrop");
if(!t||!s)return;
function setOpen(open){
s.classList.toggle("open",open);
if(b){b.classList.toggle("on",open);b.hidden=!open}
document.body.classList.toggle("nav-open",open);
t.setAttribute("aria-expanded",open?"true":"false");
t.setAttribute("aria-label",open?"Close menu":"Open menu");
t.textContent=open?"✕":"☰";
}
t.addEventListener("click",function(){setOpen(!s.classList.contains("open"))});
if(b)b.addEventListener("click",function(){setOpen(false)});
document.addEventListener("keydown",function(e){if(e.key==="Escape")setOpen(false)});
s.querySelectorAll("a").forEach(function(a){a.addEventListener("click",function(){if(window.innerWidth<=960)setOpen(false)})});
})();
</script>
</body></html>
