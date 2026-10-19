<?php
$base = dirname(__DIR__);
$cssFile = $base.'/public/css/app.css';
$css = file_get_contents($cssFile);

// Remove old dialog overrides first
$css = preg_replace('/\/\* === Dialog polish AykonMeet.*$/s', '', $css);

$dialogCSS = '
/* ============ AYKONMEET DIALOG / MODAL POLISH ============ */
dialog{border:0!important;padding:0!important;background:transparent!important;width:min(760px,calc(100% - 32px))!important;max-height:calc(100vh - 48px)!important;overflow:visible!important}
dialog::backdrop{background:rgba(11,27,49,.75)!important;backdrop-filter:blur(8px)}
dialog .form-panel,
dialog > div,
dialog > section,
dialog > form > div{
  background:#fff!important;
  border-radius:20px!important;
  box-shadow:0 30px 80px rgba(0,0,0,.35),0 0 0 1px rgba(255,255,255,.1)!important;
  padding:36px 40px!important;
  position:relative;
  overflow:hidden;
}
/* If dialog has a hero/colored section */
dialog .dialog-hero,
dialog > form > .dialog-hero{
  background:linear-gradient(130deg,#0c203a 0%,#112b49 40%,#1a3d66 100%);
  margin:-36px -40px 28px;
  padding:32px 40px 48px;
  position:relative;
  overflow:hidden;
}
dialog .dialog-hero::before{content:"";position:absolute;top:-60px;right:-60px;width:240px;height:240px;background:radial-gradient(circle,rgba(247,201,72,.2),transparent 70%);border-radius:50%}
dialog .dialog-hero::after{content:"";position:absolute;bottom:-40px;left:30%;width:180px;height:180px;background:radial-gradient(circle,rgba(247,201,72,.08),transparent 70%);border-radius:50%}
dialog .dialog-hero > *{position:relative;z-index:2}
dialog .dialog-hero .icon-box,
dialog .panel-title .icon-box{
  width:42px;height:42px;border-radius:10px!important;
  background:#f7c948!important;color:#112b49!important;
  font-size:20px!important;box-shadow:0 6px 16px rgba(247,201,72,.25)!important;
  flex:0 0 42px;
}
dialog .dialog-hero h2,
dialog .dialog-hero h3,
dialog .panel-title h2,
dialog .panel-title h3{
  color:#fff!important;font-family:"Space Grotesk",sans-serif!important;
  font-weight:700!important;font-size:26px!important;letter-spacing:-.5px!important;
  margin:0 0 6px!important;
}
dialog .dialog-hero p,
dialog .panel-title p{color:rgba(255,255,255,.7)!important;font-size:14px!important;margin:0!important;line-height:1.5}
dialog .dialog-close{
  position:absolute!important;top:16px!important;right:16px!important;z-index:10;
  width:36px!important;height:36px!important;border-radius:50%!important;
  background:rgba(255,255,255,.1)!important;border:1px solid rgba(255,255,255,.2)!important;
  color:#fff!important;font-size:20px!important;line-height:1!important;
  display:grid!important;place-items:center!important;transition:all .15s!important;
}
dialog .dialog-close:hover{background:#f7c948!important;color:#112b49!important;border-color:#f7c948!important;transform:rotate(90deg)}
/* When not in a hero, close button is dark */
dialog .form-panel:not(:has(.dialog-hero)) .dialog-close,
dialog > div:not(:has(.dialog-hero)) .dialog-close{
  background:#f7f9fb!important;color:#718096!important;border-color:#e4e9ef!important;
}
dialog .form-panel:not(:has(.dialog-hero)) .dialog-close:hover,
dialog > div:not(:has(.dialog-hero)) .dialog-close:hover{
  background:#fff1f1!important;color:#c0392b!important;border-color:#fbd5d5!important;transform:rotate(90deg)
}
/* Form labels */
dialog label,
dialog .form-grid label{color:#112b49!important;font-size:12px!important;font-weight:700!important;letter-spacing:.3px}
dialog input,dialog select{
  border:1.5px solid #dde3ea!important;border-radius:10px!important;padding:12px 14px!important;
  font:500 14px "DM Sans",sans-serif!important;background:#fbfcfd!important;color:#172437!important;
  transition:border-color .2s,box-shadow .2s!important;
}
dialog input:focus,dialog select:focus{border-color:#112b49!important;background:#fff!important;box-shadow:0 0 0 3px rgba(17,43,73,.08)!important}
dialog input::placeholder{color:#9fadb8}
/* Buttons in dialogs */
dialog .button,
dialog .dialog-actions button{
  border-radius:10px!important;font-weight:700!important;min-height:46px!important;
  padding:0 24px!important;font-size:14px!important;transition:all .2s!important;
}
dialog .button.primary,
dialog .dialog-actions .button.primary,
dialog .dialog-actions button:not(.secondary){
  background:#112b49!important;color:#f7c948!important;box-shadow:0 4px 14px rgba(17,43,73,.2)!important;
}
dialog .button.primary:hover,
dialog .dialog-actions button:not(.secondary):hover{
  background:#0a1c33!important;transform:translateY(-1px);box-shadow:0 8px 20px rgba(17,43,73,.25)!important;
}
dialog .button.secondary,
dialog .dialog-actions .button.secondary{
  background:#f7f9fb!important;color:#5a6b7d!important;border:1.5px solid #e4e9ef!important;
}
dialog .button.secondary:hover{background:#eef2f5!important;color:#112b49!important}
dialog .dialog-actions{margin-top:28px!important;padding-top:20px!important;border-top:1px solid #f0f4f8}
/* === Meeting Type Chooser (the "Choose meeting type" page) === */
.type-chooser,
.meeting-type-selector{padding:20px 0 4px}
.type-option,.meeting-type-option{
  display:flex!important;align-items:center!important;gap:16px!important;
  padding:18px 20px!important;border:1.5px solid #e4e9ef!important;border-radius:12px!important;
  margin-bottom:12px!important;background:#fbfcfd!important;cursor:pointer!important;
  transition:all .2s!important;text-decoration:none!important;color:inherit!important;
}
.type-option:hover,.meeting-type-option:hover{
  border-color:#f7c948!important;background:#fffdf5!important;
  transform:translateX(4px);box-shadow:0 6px 16px rgba(247,201,72,.15)!important;
}
.type-icon{
  width:48px;height:48px;border-radius:12px;display:grid;place-items:center;
  background:#112b49;color:#f7c948;font-size:22px;flex:0 0 48px;
}
.type-icon.online{background:linear-gradient(135deg,#267344,#3d9e64)}
.type-icon.inperson{background:linear-gradient(135deg,#112b49,#2a5580)}
.type-icon.hybrid{background:linear-gradient(135deg,#8a6a00,#f7c948);color:#112b49}
.type-info{flex:1}
.type-info strong{display:block;color:#112b49!important;font:700 15px "DM Sans",sans-serif}
.type-info small{display:block;color:#718096;font-size:12px;margin-top:3px;font-weight:500}
.type-arrow{color:#c4cdd6;font-size:20px;transition:transform .2s,color .2s}
.type-option:hover .type-arrow{color:#112b49;transform:translateX(4px)}
/* Eyebrow label inside dialog body (when no hero) */
dialog .form-panel .eyebrow,
dialog > div .eyebrow{color:#b18800!important;margin-bottom:18px!important;font-weight:700}
/* Fix panel title when no hero */
dialog .form-panel:not(:has(.dialog-hero)) .panel-title,
dialog > div:not(:has(.dialog-hero)) .panel-title{margin-bottom:24px}
dialog .form-panel:not(:has(.dialog-hero)) .panel-title h3{color:#112b49!important}
dialog .form-panel:not(:has(.dialog-hero)) .panel-title p{color:#718096!important}
';

file_put_contents($cssFile, $css.$dialogCSS);

// Now inject a meeting type picker step into the new-meeting-dialog IF it doesn't have one already
$index = file_get_contents($base.'/resources/views/meetings/index.blade.php');
if ($index && strpos($index, 'type-chooser') === false) {
    // Add a type-selector step (initially hidden) and wrap the form in two steps
    // First, before the form-grid, add a radio-style type picker shown by default
    $oldFormGrid = '<div class="form-grid">';
    $typeChooser = '<div class="type-chooser" id="meeting-type-chooser">
<div style="display:flex;align-items:center;gap:14px;margin-bottom:20px">
<div style="width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#267344,#3d9e64);display:grid;place-items:center;color:#fff;font-size:22px;flex:0 0 42px">🎥</div>
<div><h3 style="margin:0;color:#112b49;font:700 18px Space Grotesk,sans-serif">Choose meeting type</h3><p style="margin:4px 0 0;color:#718096;font-size:12px">Select how people will attend.</p></div>
</div>
<label class="type-option" data-type="online" onclick="selectType(\'online\')"><span class="type-icon online">🎥</span><span class="type-info"><strong>Online meeting</strong><small>Meet in a video call</small></span><span class="type-arrow">→</span></label>
<label class="type-option" data-type="inperson" onclick="selectType(\'inperson\')"><span class="type-icon inperson">🏢</span><span class="type-info"><strong>In-person meeting</strong><small>Meet in a physical room</small></span><span class="type-arrow">→</span></label>
<label class="type-option" data-type="hybrid" onclick="selectType(\'hybrid\')"><span class="type-icon hybrid">👥</span><span class="type-info"><strong>Hybrid meeting</strong><small>Meet in a room and online</small></span><span class="type-arrow">→</span></label>
<input type="hidden" name="meeting_type" id="meeting-type-input" value="inperson">
</div>
<div class="form-grid" id="meeting-form-fields" style="display:none">';
    $index = str_replace($oldFormGrid, $typeChooser, $index);

    // Close the hidden fields div before dialog-actions (insert a </div>)
    $oldActions = '<div class="dialog-actions"><button class="button secondary" type="button" onclick="document.getElementById(\'new-meeting-dialog\').close()">Cancel</button><button class="button primary" type="submit">Create meeting <span>→</span></button></div>';
    $newActions = '</div><div class="dialog-actions"><button class="button secondary" type="button" id="type-back-btn" style="display:none" onclick="goBackToTypes()">← Back</button><button class="button secondary" type="button" onclick="document.getElementById(\'new-meeting-dialog\').close()">Cancel</button><button class="button primary" type="submit" id="create-btn" style="display:none">Create meeting <span>→</span></button></div>
<script>function selectType(t){document.getElementById("meeting-type-input").value=t;var online=t==="online"||t==="hybrid";var room=document.querySelector("select[name=meeting_room_id]");var roomReq=document.getElementById("room-req");room.required=!online;roomReq.style.visibility=online?"hidden":"visible";var cb=document.getElementById("is-online-cb");cb.checked=online;document.getElementById("meeting-type-chooser").style.display="none";document.getElementById("meeting-form-fields").style.display="grid";document.getElementById("type-back-btn").style.display="inline-flex";document.getElementById("create-btn").style.display="inline-flex"}function goBackToTypes(){document.getElementById("meeting-type-chooser").style.display="block";document.getElementById("meeting-form-fields").style.display="none";document.getElementById("type-back-btn").style.display="none";document.getElementById("create-btn").style.display="none"}document.getElementById("new-meeting-dialog").addEventListener("close",function(){goBackToTypes()});</script>';
    $index = str_replace($oldActions, $newActions, $index);

    file_put_contents($base.'/resources/views/meetings/index.blade.php', $index);
}

foreach (array_merge(glob($base.'/storage/framework/views/*.php'), glob($base.'/bootstrap/cache/*.php')) as $f) {
    @unlink($f);
}
?>
<!DOCTYPE html><html><body style="font-family:Segoe UI,Arial;padding:30px;background:#f7f9fb;color:#112b49">
<h1 style="border-bottom:3px solid #f7c948;padding-bottom:8px">Dialogs + Meeting Type chooser styled ✅</h1>
<ul>
<li style='font-family:Consolas,monospace;font-size:13px'>All dialogs: white rounded 20px cards with deep shadows, blurred backdrop</li>
<li style='font-family:Consolas,monospace;font-size:13px'>Dialog headers: navy gradient hero with yellow logo mark + glowing orbs</li>
<li style='font-family:Consolas,monospace;font-size:13px'>Close button (X): white circle on dark, rotates on hover</li>
<li style='font-family:Consolas,monospace;font-size:13px'>Inputs: 10px rounded, focus glow, proper padding</li>
<li style='font-family:Consolas,monospace;font-size:13px'>Buttons: navy primary with yellow text + shadow lift on hover</li>
<li style='font-family:Consolas,monospace;font-size:13px'>NEW: Added a branded "Choose meeting type" step before the form (Online/In-person/Hybrid) with colored icons, hover slide effect, yellow arrow</li>
<li style='font-family:Consolas,monospace;font-size:13px'>Type icons: green = online, navy = in-person, yellow = hybrid</li>
<li style='font-family:Consolas,monospace;font-size:13px'>Back button appears after choosing a type</li>
<li style='font-family:Consolas,monospace;font-size:13px'>CACHE CLEARED</li>
</ul>
<p style="background:#fff8e1;padding:12px;border-left:3px solid #f7c948;margin-top:20px">Ctrl+Shift+R the dashboard, click "+ New meeting" — you'll first see a beautiful type chooser with 3 options, then click one to reach the form.</p>
<p style="margin-top:20px"><a href="/admin" style="display:inline-block;background:#112b49;color:#f7c948;padding:14px 26px;text-decoration:none;font-weight:700;border-radius:6px">Test "+ New meeting" →</a></p>
</body></html>