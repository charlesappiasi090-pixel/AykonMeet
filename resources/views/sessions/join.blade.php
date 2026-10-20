@extends("layouts.app")
@section("content")
<script>document.body.classList.add("meeting-fullscreen")</script>
<style>
.session-page{max-width:1380px;margin:0 auto;padding:8px 0 28px}
.back-link-inline{display:inline-flex;align-items:center;gap:6px;margin-bottom:16px;color:#65758a;font-size:13px;font-weight:600;text-decoration:none}
.back-link-inline:hover{color:var(--navy)}
.session-head{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:24px;align-items:center;margin-bottom:18px;padding:22px 26px;background:#fff;border:1px solid var(--line);border-radius:12px;box-shadow:0 6px 24px rgba(17,43,73,.05)}
.session-head h1{margin:0;color:var(--navy);font:700 27px/1.2 "Space Grotesk",sans-serif}
.session-head p{margin:6px 0 0;color:#65758a;font-size:13px}
.provider-pill{display:inline-flex;align-items:center;gap:6px;margin-top:10px;padding:6px 10px;border-radius:999px;background:#e9f6ed;color:#267344;font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase}
.video-stage{display:flex;flex-direction:column;align-items:stretch;justify-content:flex-start;gap:16px;min-height:0;padding:22px;background:linear-gradient(145deg,#10243b,#0b1a2e 65%);border:1px solid #203750;border-radius:14px;box-shadow:0 16px 42px rgba(11,26,46,.16);color:#d8e2ee}
.recording-indicator{display:none;align-self:flex-start;padding:8px 12px;border:1px solid rgba(255,255,255,.12);border-radius:999px;background:#8d211d;color:#fff;font-size:12px;font-weight:700}
.recording-indicator.on{display:block}
.camera-off-msg{width:min(100%,580px);margin:auto;padding:32px 28px;border:1px solid rgba(255,255,255,.1);border-radius:14px;background:rgba(255,255,255,.055);text-align:center}
.camera-off-msg .big{display:grid;place-items:center;width:66px;height:66px;margin:0 auto 16px;border-radius:20px;background:rgba(247,201,72,.14);font-size:34px}
.camera-off-msg h2{margin:0 0 8px;color:#fff;font:600 24px "Space Grotesk",sans-serif}
.camera-off-msg p{max-width:430px;margin:0 auto 20px;color:#aebdce;font-size:13px;line-height:1.6}
.camera-off-msg input{display:block;width:min(100%,360px);margin:0 auto 12px;padding:12px 14px;border:1px solid #465e78;border-radius:7px;background:#0b1a2e;color:#fff;font:13px "DM Sans",sans-serif}
.camera-off-msg input:focus{outline:3px solid rgba(247,201,72,.22);border-color:var(--yellow)}
#startCamera{padding:12px 22px;border:0;border-radius:7px;background:#2f8750;color:#fff;font:700 13px "DM Sans",sans-serif;cursor:pointer;transition:background .15s ease,transform .15s ease}
#startCamera:hover{background:#267344;transform:translateY(-1px)}
#startCamera:disabled{opacity:.55;cursor:not-allowed;transform:none}
.media-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,320px),1fr));gap:14px;width:100%;max-width:none}
.media-tile{position:relative;overflow:hidden;min-height:220px;aspect-ratio:16/9;border:1px solid rgba(255,255,255,.12);border-radius:11px;background:#050b12;box-shadow:0 8px 24px rgba(0,0,0,.2)}
.media-tile video{display:block;width:100%;height:100%;min-height:220px;max-height:560px;object-fit:cover}
.media-tile span{position:absolute;bottom:12px;left:12px;padding:6px 9px;border:1px solid rgba(255,255,255,.12);border-radius:6px;background:rgba(5,11,18,.72);color:#fff;font-size:12px;font-weight:600}
.controls-bar{display:none;align-items:center;justify-content:center;flex-wrap:wrap;gap:8px;margin:0;padding:10px 12px;border:1px solid rgba(255,255,255,.12);border-radius:14px;background:rgba(8,18,32,.78)}
.controls-bar.on{display:flex}
.ctrl{display:inline-flex;flex:0 0 auto;flex-direction:column;align-items:center;justify-content:center;gap:4px;width:68px;height:58px;margin:0;padding:6px 4px;border:1px solid rgba(255,255,255,.12);border-radius:12px;background:#203750;color:#fff;font-size:18px;line-height:1;cursor:pointer;transition:background .15s ease,transform .15s ease,border-color .15s ease}
.ctrl small{display:block;max-width:100%;overflow:hidden;color:inherit;font:700 10px/1 "DM Sans",sans-serif;letter-spacing:.01em;text-overflow:ellipsis;white-space:nowrap}
.ctrl:hover{background:#2a4664;transform:translateY(-1px)}
.ctrl:focus-visible,.session-tools button:focus-visible,.chat-form button:focus-visible{outline:3px solid rgba(247,201,72,.5);outline-offset:2px}
.ctrl.off{border-color:#c03b2d;background:#c03b2d;color:#fff}
.ctrl.active{border-color:var(--yellow);background:var(--yellow);color:var(--navy)}
.ctrl.leave{width:max-content;min-width:0;margin-left:6px;padding:6px 16px;border-radius:12px;background:#bf4141;font-size:18px;font-weight:700}
.ctrl.leave small{max-width:none}
.ctrl.leave:hover{background:#a4332c;color:#fff}
.participant-panel{display:none;width:100%;margin:0}
.participant-panel.on{display:block}
.participant-panel h3,.chat-panel h3{margin:0 0 13px;color:#fff;font:600 16px "Space Grotesk",sans-serif}
.participant-list{display:grid;gap:8px}
.participant-row{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:12px 13px;border:1px solid rgba(255,255,255,.08);border-radius:8px;background:#172e47}
.participant-row button{padding:8px 11px;border:0;border-radius:6px;background:var(--yellow);color:#112b49;font:700 11px "DM Sans",sans-serif;cursor:pointer}
.participant-status{color:#d2deeb;font-size:12px}
.company-group{margin-bottom:8px;border:1px solid rgba(255,255,255,.08);border-radius:9px;overflow:hidden;background:rgba(255,255,255,.03)}
.company-group-head{display:flex;align-items:center;gap:9px;width:100%;padding:10px 12px;border:0;border-bottom:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.05);color:#fff;font:700 12px "DM Sans",sans-serif;cursor:pointer;text-align:left}
.company-group-head:hover{background:rgba(255,255,255,.09)}
.company-group-logo{display:grid;place-items:center;width:26px;height:26px;flex:0 0 auto;border-radius:6px;background:var(--yellow);color:var(--navy);font:700 12px "Space Grotesk",sans-serif;overflow:hidden}
.company-group-logo img{width:100%;height:100%;object-fit:cover}
.company-group-name{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.company-group-count{padding:2px 8px;border-radius:999px;background:rgba(247,201,72,.18);color:#f7c948;font-size:10px}
.company-group-caret{color:#9fb0c3;font-size:11px;transition:transform .15s ease}
.company-group.collapsed .company-group-caret{transform:rotate(-90deg)}
.company-group-list{display:grid;gap:8px;padding:9px}
.company-group.collapsed .company-group-list{display:none}
.company-group-list .participant-row{background:#172e47}
.hybrid-room-page .company-group{border-color:#e7edf4;background:#f7f9fc}
.hybrid-room-page .company-group-head{border-bottom-color:#e7edf4;background:#eef3f8;color:var(--navy)}
.hybrid-room-page .company-group-head:hover{background:#e4ecf4}
.hybrid-room-page .company-group-name{color:var(--navy)}
.hybrid-room-page .company-group-list .participant-row{background:#f7f9fc}
.hybrid-room-page .company-group-caret{color:#64788e}

/* Company Selector Bar */
.company-selector{display:flex;gap:8px;padding:12px;border-bottom:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.03);border-radius:8px 8px 0 0;margin:-12px -12px 12px}
.company-selector-btn{display:flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid rgba(255,255,255,.1);border-radius:8px;background:rgba(255,255,255,.05);color:#fff;font:600 12px "DM Sans",sans-serif;cursor:pointer;transition:all .15s ease;white-space:nowrap}
.company-selector-btn:hover{border-color:rgba(247,201,72,.5);background:rgba(247,201,72,.15)}
.company-selector-btn.active{border-color:var(--yellow);background:rgba(247,201,72,.25);color:var(--yellow)}
.company-selector-btn.active .company-selector-logo{border-color:var(--yellow);background:var(--yellow);color:var(--navy)}
.company-selector-logo{display:grid;place-items:center;width:28px;height:28px;flex:0 0 auto;border-radius:6px;background:var(--yellow);color:var(--navy);font:700 12px "Space Grotesk",sans-serif;overflow:hidden;border:2px solid transparent}
.company-selector-logo img{width:100%;height:100%;object-fit:cover}
.company-selector-name{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.company-selector-count{padding:2px 8px;border-radius:999px;background:rgba(247,201,72,.18);color:#f7c948;font-size:10px}
.company-selector-all{border-color:rgba(247,201,72,.5);background:rgba(247,201,72,.1)}
.company-selector-all.active{border-color:var(--yellow);background:rgba(247,201,72,.25);color:var(--yellow)}
.hybrid-room-page .company-selector{border-color:#e7edf4;background:#f7f9fc;border-radius:8px 8px 0 0}
.hybrid-room-page .company-selector-btn{border-color:#e7edf4;background:#fff;color:var(--navy)}
.hybrid-room-page .company-selector-btn:hover{border-color:#f7c948;background:#fff9e8}
.hybrid-room-page .company-selector-btn.active{border-color:var(--yellow);background:#fff9e8;color:var(--navy)}
.hybrid-room-page .company-selector-logo{border-color:transparent;background:var(--navy);color:var(--yellow)}
.hybrid-room-page .company-selector-btn.active .company-selector-logo{border-color:var(--yellow);background:var(--yellow);color:var(--navy)}
.hybrid-room-page .company-selector-all{border-color:#f7c948;background:#fff9e8}
.hybrid-room-page .company-selector-all.active{border-color:var(--yellow);background:#fff9e8;color:var(--navy)}

.session-status{min-height:18px;margin:0;color:#ffd66b;font-size:12px;text-align:center}
.session-tools{display:flex;justify-content:center;flex-wrap:wrap;gap:9px;margin:0;padding:12px;border:1px solid rgba(255,255,255,.1);border-radius:11px;background:rgba(255,255,255,.055)}
.session-tools button,.chat-form button{padding:10px 14px;border:0;border-radius:7px;background:#294561;color:#fff;font:700 12px "DM Sans",sans-serif;cursor:pointer;transition:background .15s ease}
.session-tools button:hover,.chat-form button:hover{background:#365a7c}
.session-tools button.active{background:#c03b2d}
.session-tools button:disabled{opacity:.55;cursor:not-allowed}
.transcript-controls{display:none;align-items:center}
.transcript-controls.on{display:flex}
.transcript-controls label{display:flex;align-items:center;gap:8px;color:#d8e2ee;font-size:12px}
.transcript-controls select{padding:9px 11px;border:1px solid #465e78;border-radius:7px;background:#10243b;color:#fff}
.transcript-consent,.session-consent{max-width:960px;margin:0 auto;color:#9fb0c3;font-size:11px;line-height:1.6;text-align:center}
.room-columns{display:none;grid-template-columns:minmax(0,1.4fr) minmax(280px,1fr);gap:16px;width:100%;max-width:none;margin:0}
.room-columns.on{display:grid}
.chat-panel{min-width:0;padding:16px;border:1px solid rgba(255,255,255,.1);border-radius:11px;background:rgba(255,255,255,.055)}
.chat-messages{display:flex;height:230px;flex-direction:column;gap:9px;overflow:auto;padding:4px}
.chat-message{padding:10px;border:1px solid rgba(255,255,255,.06);border-radius:8px;background:#10243b;overflow-wrap:anywhere}
.chat-message strong{display:block;margin-bottom:4px;color:#ffd66b;font-size:11px}
.chat-message span{color:#fff;font-size:12px;white-space:pre-wrap}
.chat-form{display:flex;gap:8px;margin-top:10px}
.chat-form input{flex:1;min-width:0;padding:10px 12px;border:1px solid #465e78;border-radius:7px;background:#0b1a2e;color:#fff}
.chat-form input:focus{outline:3px solid rgba(247,201,72,.2);border-color:var(--yellow)}
@media(max-width:800px){.session-head{grid-template-columns:1fr;padding:20px}.video-stage{gap:13px;padding:14px}.room-columns{grid-template-columns:1fr}.session-page{padding-top:0}}
@media(max-width:520px){.session-head h1{font-size:23px}.camera-off-msg{padding:25px 16px}.camera-off-msg h2{font-size:21px}.media-grid{grid-template-columns:1fr}.media-tile,.media-tile video{min-height:190px}.chat-messages{height:170px}.ctrl{width:58px;height:54px}.ctrl.leave{width:max-content;margin-left:0}}
.online-share-link{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:10px;align-items:end;margin:0 0 16px;padding:18px;border:1px solid #d8e0e8;border-left:4px solid var(--yellow);border-radius:12px;background:#fff}
.online-share-field{min-width:0}
.online-share-link label{min-width:0;color:var(--navy);font-size:13px;font-weight:700}
.online-share-link label span{display:block;margin-top:3px;color:#718096;font-size:11px;font-weight:500}
.online-share-link input{display:block;width:100%;min-width:0;margin-top:8px;padding:11px 12px;border:1px solid #c9d5e2;border-radius:7px;background:#f5f7f9;color:var(--navy);font:12px "DM Sans",sans-serif}
.online-share-url{display:block;min-width:0;margin-top:8px;padding:11px 12px;border:1px solid #c9d5e2;border-radius:7px;background:#f5f7f9;color:var(--navy);font:12px "DM Sans",sans-serif;text-decoration:underline;word-break:break-all}
.online-share-url:hover{color:#0d5c9c}
.online-share-link button{min-height:42px;padding:9px 16px;border:1px solid var(--navy);border-radius:7px;background:var(--navy);color:#fff;font:700 12px "DM Sans",sans-serif;cursor:pointer}
.online-share-link button:hover{border-color:var(--yellow);background:var(--yellow);color:var(--navy)}
.online-share-status{grid-column:1/-1;min-height:15px;color:var(--navy);font-size:11px}
@media(max-width:560px){.online-share-link{grid-template-columns:1fr}}
body.meeting-fullscreen{padding-left:0!important;overflow:hidden;background:#070f1c}
body.meeting-fullscreen .sidebar-fb,body.meeting-fullscreen .sb-toggle,body.meeting-fullscreen .topbar,body.meeting-fullscreen .footer{display:none!important}
body.meeting-fullscreen .page-shell{max-width:none!important;height:100dvh;margin:0!important;padding:0!important;overflow:hidden}
body.meeting-fullscreen .session-page:not(.hybrid-room-page){position:relative;display:flex;flex-direction:column;max-width:none;height:100dvh;margin:0;padding:0;background:#070f1c}
body.meeting-fullscreen .session-page:not(.hybrid-room-page) .video-stage{position:relative;flex:1;min-height:0;border:0;border-radius:0;box-shadow:none;background:#070f1c}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .back-link-inline,
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .online-share-link,
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .transcript-consent,
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .session-consent{display:none}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .session-head{position:absolute;top:0;right:352px;left:0;z-index:4;margin:0;padding:16px 22px 28px;border:0;border-radius:0;background:linear-gradient(180deg,rgba(7,15,28,.78),transparent);box-shadow:none;pointer-events:none}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .session-head h1{color:#fff;font-size:20px}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .session-head .eyebrow,
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .session-head p,
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .provider-pill{display:none}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .video-stage{display:grid;grid-template-columns:minmax(0,1fr) 340px;grid-template-rows:minmax(0,1fr);gap:12px;padding:12px}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .media-grid{grid-column:1;grid-row:1;height:100%;min-height:0;align-content:stretch;grid-template-columns:minmax(0,1fr);grid-auto-rows:minmax(0,1fr);padding-bottom:86px}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .media-grid:has(>.media-tile:nth-child(2)){grid-template-columns:repeat(2,minmax(0,1fr))}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .media-grid:has(>.media-tile:nth-child(5)){grid-template-columns:repeat(3,minmax(0,1fr))}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .media-grid:has(>.media-tile:nth-child(10)){grid-template-columns:repeat(4,minmax(0,1fr))}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .media-grid:has(>.media-tile:nth-child(17)){grid-template-columns:repeat(5,minmax(0,1fr))}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .media-grid:has(>.media-tile:nth-child(3):last-child){grid-template-columns:repeat(4,minmax(0,1fr))}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .media-grid:has(>.media-tile:nth-child(3):last-child)>.media-tile:nth-child(1){grid-column:1/3}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .media-grid:has(>.media-tile:nth-child(3):last-child)>.media-tile:nth-child(2){grid-column:3/5}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .media-grid:has(>.media-tile:nth-child(3):last-child)>.media-tile:nth-child(3){grid-column:2/4}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .media-tile,
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .media-tile video{height:100%;min-height:0;max-height:none;aspect-ratio:auto}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .controls-bar{position:absolute;bottom:22px;left:calc(12px + (100% - 376px) / 2);z-index:5;width:max-content;max-width:calc(100% - 400px);transform:translateX(-50%);background:rgba(8,16,30,.88)}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .transcript-controls{position:absolute;bottom:96px;left:calc(12px + (100% - 376px) / 2);z-index:5;transform:translateX(-50%)}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .session-status{position:absolute;right:364px;bottom:104px;left:24px;z-index:5;min-height:0;text-align:left}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .recording-indicator.on{position:absolute;top:18px;left:18px;z-index:6}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .room-columns,
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .room-columns.on{display:flex;grid-column:2;grid-row:1;flex-direction:column;gap:10px;min-height:0;overflow:hidden}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .participant-panel.on,
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .room-columns .chat-panel:not(.doc-panel){display:flex;flex:1;flex-direction:column;min-height:0;overflow:hidden}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .participant-list,
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .chat-messages{flex:1;height:auto;min-height:0;overflow:auto}
body.meeting-fullscreen .session-page:not(.hybrid-room-page) .doc-panel{display:none}
body.meeting-fullscreen .session-page:not(.hybrid-room-page) .doc-panel.on{display:flex;flex:1;min-height:0}
@media(max-width:900px){
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .video-stage{grid-template-columns:1fr;grid-template-rows:minmax(0,1fr) minmax(180px,32dvh);padding:8px}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .session-head{right:0}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .controls-bar{left:50%;max-width:calc(100% - 16px)}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .transcript-controls{left:50%}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .session-status{right:12px}
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .room-columns,
body.meeting-fullscreen .session-page:not(.hybrid-room-page):has(.controls-bar.on) .room-columns.on{grid-column:1;grid-row:2}
}
@if($isHybrid)
.hybrid-room-page{max-width:1440px;padding-top:8px}
.hybrid-room-page .session-head{grid-template-columns:minmax(0,1fr) auto;margin-bottom:14px;padding:18px 22px}
.hybrid-room-header-badge{display:inline-flex;align-items:center;gap:7px;margin-bottom:8px;padding:6px 10px;border:1px solid rgba(247,201,72,.65);border-radius:999px;background:#112b49;color:#f7c948;font-size:10px;font-weight:800;letter-spacing:.08em}
.hybrid-room-heading-meta{display:flex;flex-wrap:wrap;gap:9px 18px;margin-top:9px;color:#61748d;font-size:12px}
.hybrid-room-heading-meta span{display:inline-flex;align-items:center;gap:6px}
.hybrid-connection{display:inline-flex;align-items:center;gap:8px;padding:9px 12px;border:1px solid #d9e4ef;border-radius:999px;background:#f7f9fc;color:#6b7d91;font-size:11px;font-weight:700;white-space:nowrap}
.hybrid-connection::before{content:"";width:8px;height:8px;border-radius:50%;background:#94a3b8}
.hybrid-connection.connected{border-color:#ead58b;background:#fff8df;color:#725500}
.hybrid-connection.connected::before{background:#d6a500;box-shadow:0 0 0 3px rgba(247,201,72,.2)}
.hybrid-connection.connecting{border-color:#f3e2b7;background:#fff9e8;color:#896700}
.hybrid-connection.connecting::before{background:#e0ad24}
.hybrid-room-layout{display:grid;grid-template-columns:minmax(0,1fr) 310px;gap:14px;align-items:stretch}
.hybrid-room-main{min-width:0}
.hybrid-room-page .video-stage{min-height:640px;height:100%;padding:16px;background:linear-gradient(145deg,#101f34,#091522 68%);border-radius:13px}
.hybrid-room-page .media-grid{flex:1;align-content:center;grid-template-columns:repeat(auto-fit,minmax(min(100%,340px),1fr))}
.hybrid-room-page .media-tile,.hybrid-room-page .media-tile video{min-height:250px}
.hybrid-room-page .camera-off-msg{max-width:560px}
.hybrid-room-page #startCamera{border:1px solid var(--yellow);background:var(--yellow);color:var(--navy)}
.hybrid-room-page #startCamera:hover{background:#ffda69}
.hybrid-room-page .camera-off-msg button:not(#startCamera):hover{border-color:var(--yellow);background:#294561}
.hybrid-room-page .ctrl:not(.leave):hover,.hybrid-room-page .ctrl:not(.leave).active{border-color:var(--yellow);background:var(--yellow);color:var(--navy)}
.hybrid-room-page .camera-off-msg button:not(#startCamera){margin:0 4px 10px;padding:10px 14px;border:1px solid #465e78;border-radius:7px;background:#203750;color:#fff;font:700 12px "DM Sans",sans-serif;cursor:pointer}
.hybrid-room-page .controls-bar{gap:8px;padding:10px}
.hybrid-room-page .ctrl{width:58px;height:58px;flex-direction:column;gap:3px;border-radius:12px;font-size:18px}
.hybrid-room-page .ctrl small{font-size:9px;font-weight:700}
.hybrid-room-page .ctrl.leave{width:max-content;min-width:0;padding:6px 16px;border-radius:12px;background:#bf4141}
.hybrid-room-sidebar{display:flex;flex-direction:column;gap:12px;min-width:0}
.hybrid-room-info,.hybrid-room-page .participant-panel,.hybrid-room-page .chat-panel{padding:16px;border:1px solid #e1e8f0;border-radius:12px;background:#fff;color:var(--ink)}
.hybrid-room-info h2,.hybrid-room-page .participant-panel h3,.hybrid-room-page .chat-panel h3{margin:0 0 12px;color:var(--navy);font:700 15px "Space Grotesk",sans-serif}
.hybrid-room-counts{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.hybrid-room-count{padding:11px;border:1px solid #e6edf5;border-radius:9px;background:#f8fbff}
.hybrid-room-count span{display:block;color:#61748d;font-size:10px}
.hybrid-room-count strong{display:block;margin-top:5px;color:var(--navy);font:700 22px "Space Grotesk",sans-serif}
.hybrid-room-location{margin-top:10px;padding:11px;border-radius:8px;background:#f4f7fb;color:#52677f;font-size:11px;line-height:1.5}
.hybrid-room-location strong{display:block;margin-bottom:3px;color:var(--navy);font-size:10px}
.hybrid-room-page .room-columns{display:none;margin:0}
.hybrid-room-page .room-columns.on{display:block}
.hybrid-room-page .participant-panel{display:none;width:100%;margin:0}
.hybrid-room-page .participant-panel.on{display:block}
.hybrid-room-page .participant-row{border-color:#e7edf4;background:#f7f9fc}
.hybrid-room-page .participant-status{color:#31465e}
.hybrid-room-page .participant-panel h3{display:flex;align-items:center;justify-content:space-between}
.hybrid-room-page .participant-panel h3 span{color:#64788e;font:600 10px "DM Sans",sans-serif}
.hybrid-room-page .chat-panel{display:none;min-width:0}
.hybrid-room-page .chat-panel.on{display:block}
.hybrid-room-page .chat-message{border-color:#e7edf4;background:#f7f9fc}
.hybrid-room-page .chat-message strong{color:#315ab1}
.hybrid-room-page .chat-message span{color:#21354d}
.hybrid-room-page .chat-form input{border-color:#dbe3ea;background:#fff;color:var(--ink)}
.hybrid-room-page .session-status{text-align:left;color:#a7c3dd}
.hybrid-room-page .transcript-consent,.hybrid-room-page .session-consent{color:#6b7d91;font-size:10px;text-align:left}
.doc-panel{display:none;flex-direction:column;min-width:0;padding:16px;border:1px solid rgba(255,255,255,.1);border-radius:11px;background:rgba(255,255,255,.055)}
        .doc-panel.on{display:flex}
        .doc-toolbar{display:flex;gap:8px;margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid rgba(255,255,255,.08)}
        .doc-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:8px 12px;border:1px solid rgba(255,255,255,.1);border-radius:7px;background:#294561;color:#fff;font:700 11px "DM Sans",sans-serif;cursor:pointer;transition:background .15s ease}
        .doc-btn:hover{background:#365a7c}
        .doc-list{display:flex;flex-direction:column;gap:8px;overflow:auto;max-height:200px}
        .doc-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border:1px solid rgba(255,255,255,.06);border-radius:8px;background:#10243b;overflow-wrap:anywhere}
        .doc-item .doc-icon{display:grid;place-items:center;width:36px;height:36px;border-radius:6px;background:rgba(247,201,72,.16);color:#f7c948;font-size:16px;flex:0 0 36px}
        .doc-item .doc-info{flex:1;min-width:0}
        .doc-item .doc-name{display:block;color:#fff;font:600 12px "DM Sans",sans-serif;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .doc-item .doc-meta{display:block;margin-top:2px;color:#aebdce;font-size:10.5px}
        .doc-item .doc-actions{display:flex;gap:6px;flex-wrap:wrap}
        .doc-item .doc-action{padding:5px 9px;border:1px solid rgba(255,255,255,.1);border-radius:6px;background:rgba(255,255,255,.05);color:#fff;font:700 10px "DM Sans",sans-serif;cursor:pointer}
        .doc-item .doc-action:hover{background:rgba(255,255,255,.1)}
        .doc-item .doc-action.primary{background:#f7c948;border-color:#f7c948;color:#112b49}
        .doc-item .doc-action.danger{background:rgba(192,59,45,.16);border-color:rgba(255,120,110,.4);color:#ffc9c3}
        .hybrid-room-page .doc-panel{border-color:#e1e8f0;background:#f8fafc}
        .hybrid-room-page .doc-item{border-color:#e7edf4;background:#fff;color:var(--ink)}
        .hybrid-room-page .doc-item .doc-name{color:var(--navy)}
        .hybrid-room-page .doc-item .doc-meta{color:#64788e}
        .hybrid-room-page .doc-item .doc-action{border-color:#dbe3ea;background:#f7f9fc;color:var(--navy)}
        .hybrid-room-page .doc-item .doc-action:hover{background:#eef2f6}
        .hybrid-room-page .doc-item .doc-action.primary{background:#f7c948;border-color:#f7c948;color:#112b49}
.hybrid-room-page .doc-item .doc-action.danger{background:#fdecea;border-color:#f0c9c9;color:#a5382c}
        .hybrid-room-page .doc-btn{border-color:#dbe3ea;background:#fff;color:var(--navy)}
        .hybrid-room-page .doc-btn:hover{background:#f1f5f9}
        .doc-extras{display:flex;gap:4px;margin-top:4px;flex-wrap:wrap}
        .doc-badge{display:inline-flex;align-items:center;gap:4px;padding:2px 6px;border-radius:4px;font-size:9px;font-weight:700;white-space:nowrap}
        .doc-badge.expired{background:#fdecea;color:#a5382c}
        .doc-badge.expires{background:#fff4dd;color:#946a00}
        .doc-badge.retention{background:#e8f6ed;color:#267344}
        .doc-badge.e2e{background:#e8edf2;color:var(--navy)}
        .hybrid-room-page .doc-badge.expired{background:#fdecea;color:#a5382c}
        .hybrid-room-page .doc-badge.expires{background:#fff4dd;color:#946a00}
        .hybrid-room-page .doc-badge.retention{background:#e8f6ed;color:#267344}
        .hybrid-room-page .doc-badge.e2e{background:#e8edf2;color:var(--navy)}
@media(max-width:900px){.hybrid-room-layout{grid-template-columns:1fr}.hybrid-room-page .session-head{grid-template-columns:1fr;align-items:flex-start}.hybrid-connection{white-space:normal}.hybrid-room-page .video-stage{min-height:0}.hybrid-room-page .controls-bar{position:sticky;bottom:0;z-index:4}.company-selector{flex-wrap:wrap}}
@media(max-width:520px){.hybrid-room-page{padding-top:0}.hybrid-room-page .ctrl{width:52px;height:50px}.hybrid-room-page .media-tile,.hybrid-room-page .media-tile video{min-height:180px}.hybrid-room-counts{grid-template-columns:1fr 1fr}}
    @endif
</style>
<style>
.waiting-overlay{position:fixed;inset:0;z-index:60;display:grid;place-items:center;padding:24px;background:linear-gradient(150deg,rgba(9,20,36,.94),rgba(11,26,46,.97))}
.waiting-overlay[hidden]{display:none}
.waiting-card{width:min(440px,100%);padding:34px 30px;border:1px solid rgba(255,255,255,.12);border-radius:16px;background:#10243b;color:#d8e2ee;text-align:center;box-shadow:0 30px 80px rgba(0,0,0,.4)}
.waiting-card h2{margin:0 0 10px;color:#fff;font:600 23px "Space Grotesk",sans-serif}
.waiting-card p{margin:0;color:#a9bacd;font-size:13px;line-height:1.6}
.waiting-card .waiting-hint{margin-top:14px;padding:10px 12px;border-radius:9px;background:rgba(247,201,72,.12);color:#f7c948;font-size:12px}
.waiting-spinner{width:46px;height:46px;margin:0 auto 18px;border:4px solid rgba(247,201,72,.22);border-top-color:#f7c948;border-radius:50%;animation:waiting-spin 1s linear infinite}
@keyframes waiting-spin{to{transform:rotate(360deg)}}
.host-waiting-panel{position:fixed;top:16px;right:16px;z-index:55;width:min(340px,calc(100vw - 32px));max-height:calc(100vh - 32px);display:flex;flex-direction:column;border:1px solid #dbe3ea;border-radius:14px;background:#fff;box-shadow:0 20px 55px rgba(11,26,46,.28);overflow:hidden}
.host-waiting-panel[hidden]{display:none}
.host-waiting-panel header{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 16px;background:#112b49;color:#fff}
.host-waiting-panel header h3{margin:0;font:700 14px "Space Grotesk",sans-serif;color:#fff}
.host-waiting-panel header span{padding:3px 9px;border-radius:999px;background:#f7c948;color:#112b49;font:800 11px "DM Sans",sans-serif}
.host-waiting-list{flex:1;overflow:auto;padding:10px;display:grid;gap:8px}
.host-waiting-row{display:flex;flex-wrap:wrap;align-items:center;gap:8px;padding:10px 11px;border:1px solid #e7eef5;border-radius:10px;background:#f7fafd}
.host-waiting-row .waiting-who{flex:1;min-width:110px}
.host-waiting-row .waiting-who strong{display:block;color:#112b49;font-size:12.5px}
.host-waiting-row .waiting-who small{display:block;color:#718096;font-size:10.5px}
.host-waiting-row .waiting-actions{display:flex;flex-wrap:wrap;gap:6px;width:100%}
.host-waiting-row button,.host-waiting-panel .admit-all{padding:7px 10px;border:0;border-radius:7px;font:700 11px "DM Sans",sans-serif;cursor:pointer}
.host-waiting-row button.admit{background:#2f8750;color:#fff}
.host-waiting-row button.reject{background:#f0e3e1;color:#a5382c}
.host-waiting-row button.company{background:#eef3f8;color:#31517a}
.host-waiting-panel .admit-all{margin:10px;padding:11px;background:#f7c948;color:#112b49;font-size:12px}
.host-waiting-empty{padding:18px;color:#718096;font-size:12px;text-align:center}
.participant-row .cohost-tag{margin-left:6px;padding:2px 6px;border-radius:6px;background:rgba(247,201,72,.2);color:#f7c948;font-size:9.5px;font-weight:800;text-transform:uppercase}
.participant-row .row-actions{display:flex;flex-wrap:wrap;gap:6px}
.participant-row button.remove{background:#f0e3e1;color:#a5382c}
.participant-row button.role{background:#294561}
</style>
<div class="waiting-overlay" id="waitingOverlay" hidden>
<div class="waiting-card" style="width:min(500px,100%);">
<div class="waiting-video-preview-wrapper" style="position:relative;width:100%;height:190px;border-radius:12px;overflow:hidden;background:#050b12;border:1.5px solid #203750;margin-bottom:16px;">
    <video id="waitingVideoPreview" autoplay muted playsinline style="width:100%;height:100%;object-fit:cover;transform:scaleX(-1);display:none;"></video>
    <div id="waitingVideoPlaceholder" style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:#94a3b8;font-size:13px;gap:8px;">
        <span style="font-size:32px;">📷</span>
        <span>Camera &amp; Microphone Self-Check Active</span>
    </div>
    <div style="position:absolute;bottom:8px;left:8px;padding:3px 8px;border-radius:6px;background:rgba(5,11,18,0.75);color:#f7c948;font-size:11px;font-weight:700;">
        Waiting Room Live Preview
    </div>
</div>
<div class="waiting-spinner"></div>
<h2 style="margin:0 0 8px;color:#fff;font:600 22px 'Space Grotesk',sans-serif">Waiting for host admission</h2>
<p id="waitingMessage" style="margin:0 0 10px;color:#cbd5e1;font-size:13.5px;line-height:1.5">You are in the waiting room. The host has been notified of your arrival and will admit you shortly.</p>
<p class="waiting-hint" id="waitingHint" hidden></p>
</div>
</div>
<aside class="host-waiting-panel" id="hostWaitingPanel" hidden>
<header><h3>Waiting room</h3><span id="waitingRoomCount">0</span></header>
<div class="host-waiting-list" id="waitingRoomList"></div>
<button class="admit-all" id="admitAllBtn" type="button">Admit everyone</button>
</aside>
@if($isHybrid)
<div class="session-page hybrid-room-page">
    <a href="{{ route('meetings.show', $meeting->meeting_code) }}" class="back-link-inline">← Back to hybrid meeting</a>
    <header class="session-head hybrid-room-header">
        <div>
            <span class="hybrid-room-header-badge">◉ &nbsp; HYBRID SESSION</span>
            <h1>{{ $meeting->meeting_topic }}</h1>
            <div class="hybrid-room-heading-meta">
                <span>▣ {{ $meeting->start_date->format('D, M j, Y') }}</span>
                <span>◷ {{ $meeting->start_date->format('g:i A') }} – {{ $meeting->end_date->format('g:i A') }}</span>
                <span>Hosted by {{ $meeting->name }}</span>
            </div>
        </div>
        <span class="hybrid-connection" id="hybridConnectionStatus" role="status">Not connected</span>
    </header>
    <div class="hybrid-room-layout">
        <main class="hybrid-room-main">
            <div class="video-stage" id="sessionRoom" data-urls="{{ json_encode($urls) }}" data-is-host="{{ $isHost ? 'true' : 'false' }}" data-hybrid="true" data-multi-company="{{ $meeting->is_multi_company ? 'true' : 'false' }}" data-hybrid-summary="{{ json_encode($hybridSummary) }}" data-meeting-id="{{ $meeting->id }}" data-direct-join="{{ $directJoin ? json_encode($directJoin) : '' }}">
                <div class="recording-indicator" id="recordingIndicator">● Recording is active</div>
                <div class="media-grid" id="mediaGrid" style="display:none"><article class="media-tile" id="localTile"><video id="localVideo" autoplay muted playsinline></video><span id="localName">You</span></article></div>
                <div class="camera-off-msg" id="cameraOffMsg"><div class="big">🎥</div><h2>Join the online session</h2><p>Enter the verification code sent to your invited email. Your camera and microphone will connect to the live hybrid meeting.</p><input id="participantIdentity" type="email" maxlength="255" value="{{ $isHost ? auth()->user()->email : '' }}" placeholder="Email address" aria-label="Email address" autocomplete="email" required><button id="sendVerificationCode" type="button" @if($isHost) hidden @endif>Send verification code</button><input id="verificationCode" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="6-digit verification code" aria-label="6-digit verification code" hidden><button id="resendVerificationCode" type="button" hidden>Resend code</button><button id="changeEmail" type="button" hidden>Use another email</button><button id="startCamera" type="button" @if(! $isHost) hidden @endif>▶ Join Meeting</button></div>
                <div class="controls-bar" id="controlsBar">
                    <button class="ctrl" id="micBtn" type="button" aria-label="Mute microphone" title="Mute microphone">🎤<small>Mute</small></button>
                    <button class="ctrl" id="camBtn" type="button" aria-label="Turn camera off" title="Turn camera off">📷<small>Camera</small></button>
                    <button class="ctrl" id="screenBtn" type="button" aria-label="Share screen" title="Share screen">▣<small>Share</small></button>
                    <button class="ctrl" id="docBtn" type="button" aria-label="Documents" title="Shared Documents">📄<small>Docs</small></button>
                    <button class="ctrl" id="participantsToggle" type="button" aria-label="Show participants" title="Show participants" aria-pressed="true">♟<small>People</small></button>
                    <button class="ctrl" id="chatToggle" type="button" aria-label="Open chat" title="Open chat" aria-pressed="false">▤<small>Chat</small></button>
                    <button class="ctrl" id="handBtn" type="button" aria-label="Raise hand" title="Raise hand">✋<small>Hand</small></button>
                    <button class="ctrl" id="recordBtn" type="button" aria-label="Start recording" hidden>●<small>Record</small></button>
                    <button class="ctrl leave" id="leaveBtn" type="button" aria-label="Leave meeting">↗<small>Leave Meeting</small></button>
                </div>
                <div class="session-status" id="sessionStatus" role="status" aria-live="polite"></div>
                <p class="session-consent">Recording controls are available to permitted participants. Screen sharing is broadcast to other participants.</p>
            </div>
        </main>
        <aside class="hybrid-room-sidebar">
            <section class="hybrid-room-info">
                <h2>Participants</h2>
                <div class="hybrid-room-counts">
                    <div class="hybrid-room-count"><span>Online Participants</span><strong id="hybridOnlineParticipantsCount">0</strong></div>
                    <div class="hybrid-room-count"><span>In-Person Participants</span><strong id="hybridInPersonParticipantsCount">{{ $meeting->attendees()->where('attendance_mode', 'in_person')->whereNotNull('checked_in_at')->whereNull('checked_out_at')->count() }}</strong></div>
                </div>
                <div class="hybrid-room-location"><strong>Physical Meeting Location</strong>{{ $hybridLocation ?: 'Location not provided' }}</div>
            </section>
            <div class="room-columns hybrid-room-panels" id="roomColumns">
                <section class="participant-panel on" id="participantPanel">
                    <h3>Online Participants <span id="hybridOnlineParticipantLabel">0 connected</span></h3>
                    <div class="company-selector" id="companySelector" role="tablist" aria-label="Filter participants by company"></div>
                    <div class="participant-list" id="participantList"></div>
                </section>
                <section class="chat-panel" id="hybridChatPanel">
                    <h3>Meeting Chat</h3>
                    <div class="chat-messages" id="chatMessages" aria-live="polite"></div>
                    <form class="chat-form" id="chatForm"><input id="chatInput" maxlength="2000" placeholder="Write a message…" aria-label="Chat message"><button type="submit">Send</button></form>
                </section>
                <section class="chat-panel doc-panel" id="docPanel">
                    <h3>Shared Documents</h3>
                    <div class="doc-toolbar">
                        <button type="button" id="docUploadBtn" class="doc-btn" aria-label="Upload document">📤 Upload</button>
                        <input type="file" id="docFileInput" accept=".pdf,.docx,.pptx,.xlsx,.txt,.png,.jpg,.jpeg" hidden>
                    </div>
                    <div class="doc-list" id="docList"></div>
                </section>
            </div>
        </aside>
    </div>
</div>
@else
<div class="session-page">
<a href="{{ auth()->check() ? route('sessions.index') : route('landing') }}" class="back-link-inline">← {{ auth()->check() ? 'Back to sessions' : 'Back to AykonMeet' }}</a>
<div class="session-head"><div><p class="eyebrow" style="margin:0 0 6px">ONLINE SESSION</p><h1>{{ $meeting->meeting_topic }}</h1><p>Hosted by {{ $meeting->name }}</p><span class="provider-pill">🎥 AykonMeet WebRTC</span></div></div>
@if($isHost)
@php($shareUrl = \Illuminate\Support\Facades\URL::signedRoute('sessions.join', ['meeting' => $meeting]))
<section class="online-share-link" aria-labelledby="onlineShareTitle">
<div class="online-share-field">
<label id="onlineShareTitle">Shareable meeting link<span>Send this autogenerated link to your participants. Each guest verifies their invited email to join.</span></label>
<a id="onlineShareUrl" class="online-share-url" href="{{ $shareUrl }}" target="_blank" rel="noopener">{{ $shareUrl }}</a>
</div>
<button id="copyOnlineShareUrl" type="button">Copy link</button>
<span id="onlineShareStatus" class="online-share-status" role="status" aria-live="polite"></span>
</section>
@endif
<div class="video-stage" id="sessionRoom" data-urls="{{ json_encode($urls) }}" data-is-host="{{ $isHost ? 'true' : 'false' }}" data-multi-company="{{ $meeting->is_multi_company ? 'true' : 'false' }}" data-direct-join="{{ $directJoin ? json_encode($directJoin) : '' }}">
<div class="recording-indicator" id="recordingIndicator">● Recording is active</div>
<div class="media-grid" id="mediaGrid" style="display:none"><article class="media-tile" id="localTile"><video id="localVideo" autoplay muted playsinline></video><span id="localName">You</span></article></div>
<div class="camera-off-msg" id="cameraOffMsg">
@if($isHost)
<div class="big">🎥</div><h2>Join the online session</h2><p>Starting the room as the host. Your camera and microphone will connect to the live meeting.</p><input id="participantIdentity" type="email" maxlength="255" value="{{ auth()->user()->email }}" placeholder="Email address" aria-label="Email address" autocomplete="email" required><button id="startCamera" type="button">▶ Join Meeting</button>
@else
<div class="big">🎥</div><h2>Joining the meeting…</h2><p>Enter your name so the host can see who is waiting to join.</p><input id="guestDisplayName" type="text" maxlength="80" value="{{ auth()->user()->name ?? '' }}" placeholder="Your name" aria-label="Your name" autocomplete="name" required><button id="startCamera" type="button">▶ Enter meeting</button>
@endif
</div>
<div class="controls-bar" id="controlsBar"><button class="ctrl" id="micBtn" type="button" aria-label="Toggle microphone">🎤<small>Mute</small></button><button class="ctrl" id="camBtn" type="button" aria-label="Toggle camera">📷<small>Camera</small></button><button class="ctrl" id="screenBtn" type="button" aria-label="Share screen">▣<small>Share</small></button><button class="ctrl" id="docBtn" type="button" aria-label="Documents" title="Shared Documents">📄<small>Docs</small></button><button class="ctrl" id="handBtn" type="button" aria-label="Raise hand">✋<small>Hand</small></button><button class="ctrl" id="recordBtn" type="button" aria-label="Start recording" hidden>●<small>Record</small></button><button class="ctrl leave" id="leaveBtn" type="button" aria-label="Leave meeting">↗<small>Leave Meeting</small></button></div>
<div class="room-columns" id="roomColumns">
        <section class="participant-panel" id="participantPanel"><h3>Participants</h3><div class="company-selector" id="companySelector" role="tablist" aria-label="Filter participants by company"></div><div class="participant-list" id="participantList"></div></section>
        <section class="chat-panel"><h3>Live chat</h3><div class="chat-messages" id="chatMessages" aria-live="polite"></div><form class="chat-form" id="chatForm"><input id="chatInput" maxlength="2000" placeholder="Write a message…" aria-label="Chat message"><button type="submit">Send</button></form></section>
        <section class="chat-panel doc-panel" id="docPanel">
            <h3>Shared Documents</h3>
            <div class="doc-toolbar">
                <button type="button" id="docUploadBtn" class="doc-btn" aria-label="Upload document">📤 Upload</button>
                <input type="file" id="docFileInput" accept=".pdf,.docx,.pptx,.xlsx,.txt,.png,.jpg,.jpeg" hidden>
            </div>
            <div class="doc-list" id="docList"></div>
        </section>
    </div>
<div class="session-status" id="sessionStatus" role="status" aria-live="polite"></div>
<p class="session-consent">Recording controls are available to permitted participants. When a recording starts, a recording indicator is shown in this room. Screen sharing is broadcast to other participants.</p>
</div></div>
@endif
<script>
const room=document.getElementById("sessionRoom"),urls=JSON.parse(room.dataset.urls),isHost=room.dataset.isHost==="true",isHybridRoom=room.dataset.hybrid==="true",isMultiCompanyRoom=room.dataset.multiCompany==="true",lv=document.getElementById("localVideo"),sc=document.getElementById("startCamera"),sendCodeButton=document.getElementById("sendVerificationCode"),resendCodeButton=document.getElementById("resendVerificationCode"),codeInput=document.getElementById("verificationCode"),changeEmailButton=document.getElementById("changeEmail"),co=document.getElementById("cameraOffMsg"),cb=document.getElementById("controlsBar"),mb=document.getElementById("micBtn"),camb=document.getElementById("camBtn"),screenBtn=document.getElementById("screenBtn"),handBtn=document.getElementById("handBtn"),recordBtn=document.getElementById("recordBtn"),lb=document.getElementById("leaveBtn"),identityInput=document.getElementById("participantIdentity"),guestNameInput=document.getElementById("guestDisplayName"),participantList=document.getElementById("participantList"),participantPanel=document.getElementById("participantPanel"),chatForm=document.getElementById("chatForm"),chatInput=document.getElementById("chatInput"),chatMessages=document.getElementById("chatMessages"),mediaGrid=document.getElementById("mediaGrid"),recordingIndicator=document.getElementById("recordingIndicator"),status=document.getElementById("sessionStatus"),csrf=document.querySelector('meta[name="csrf-token"]').content,hybridConnection=document.getElementById("hybridConnectionStatus"),hybridChatPanel=document.getElementById("hybridChatPanel"),hybridParticipantsToggle=document.getElementById("participantsToggle"),hybridChatToggle=document.getElementById("chatToggle"),docPanel=document.getElementById("docPanel"),docUploadBtn=document.getElementById("docUploadBtn"),docFileInput=document.getElementById("docFileInput"),docList=document.getElementById("docList");let ls=null,peerId=null,peerToken=null,pollTimer=null,hybridSummaryTimer=null,mutedByHost=false,localMuted=false,handRaised=false,signalCursor=0,chatCursor=0,screenTrack=null,recording=null,roomPermissions={},isCoHost=false;const peers=new Map(),remoteTiles=new Map(),pendingCandidates=new Map(),earlySignals=new Map();let roomNotice="";
const ALL_COHOST_PERMISSIONS=["mute_participants","manage_waiting_room","remove_participants","promote_co_hosts"];
const configuredCoHostPermissions=@json($meeting->coHostPermissions());
const waitingOverlay=document.getElementById("waitingOverlay"),waitingMessage=document.getElementById("waitingMessage"),waitingHint=document.getElementById("waitingHint"),hostWaitingPanel=document.getElementById("hostWaitingPanel"),waitingRoomList=document.getElementById("waitingRoomList"),waitingRoomCount=document.getElementById("waitingRoomCount"),admitAllBtn=document.getElementById("admitAllBtn");
let waitingTimer=null,lastWaitingCount=-1;

// Company selector state
let selectedCompanyId = null;
let allParticipantsCache = [];
let companySelectorCache = null;
const hybridEmailStorageKey="hybridVerifiedEmail:"+room.dataset.meetingId;
const hybridCodeSentAtKey=hybridEmailStorageKey+":sentAt";
if(isHybridRoom&&!isHost){
const verifiedEmail=sessionStorage.getItem(hybridEmailStorageKey);
if(verifiedEmail){identityInput.value=verifiedEmail;status.textContent="You opened this meeting from its invite link. Verify your email here to receive a code, then enter it to join."}
}
const transcriptBtn=document.getElementById("transcriptBtn"),transcriptControls=document.getElementById("transcriptControls"),transcriptLanguage=document.getElementById("transcriptLanguage");let speechRecognition=null,transcribing=false;
async function post(url,data){const response=await fetch(url,{method:"POST",headers:{"Content-Type":"application/json","Accept":"application/json","X-CSRF-TOKEN":csrf},body:JSON.stringify(data)});if(!response.ok){let message="The room could not complete that action ("+response.status+").";let body=null;try{body=await response.json();message=body.message||message}catch(error){console.error(error)}const error=new Error(message);error.status=response.status;error.body=body;throw error}return response.json()}
function authPayload(extra){return Object.assign({peer_id:peerId,token:peerToken},extra)}
function stopLiveTranscription(){transcribing=false;if(speechRecognition){speechRecognition.onend=null;speechRecognition.stop();speechRecognition=null}if(transcriptBtn){transcriptBtn.textContent="Start live transcript";transcriptBtn.classList.remove("active")}if(transcriptLanguage)transcriptLanguage.disabled=false}
function startLiveTranscription(){const Recognition=window.SpeechRecognition||window.webkitSpeechRecognition;if(!Recognition){status.textContent="Live transcription is not supported by this browser.";return}if(!peerId){status.textContent="Join the session before starting transcription.";return}speechRecognition=new Recognition();speechRecognition.continuous=true;speechRecognition.interimResults=false;speechRecognition.lang=transcriptLanguage.value;speechRecognition.onresult=event=>{for(let index=event.resultIndex;index<event.results.length;index++){const result=event.results[index];if(!result.isFinal)continue;const text=result[0].transcript.trim();if(!text)continue;post(urls.transcript_store,authPayload({text})).then(()=>{status.textContent="Transcript saved."}).catch(error=>{status.textContent="Transcript could not be saved: "+error.message})}};speechRecognition.onerror=event=>{if(event.error==="no-speech")return;status.textContent="Speech recognition stopped: "+event.error;stopLiveTranscription()};speechRecognition.onend=()=>{if(transcribing&&peerId){try{speechRecognition.start()}catch(error){status.textContent="Speech recognition could not restart: "+error.message;stopLiveTranscription()}}};try{speechRecognition.start();transcribing=true;transcriptBtn.textContent="Stop live transcript";transcriptBtn.classList.add("active");transcriptLanguage.disabled=true;status.textContent="Live transcription started."}catch(error){status.textContent="Speech recognition could not start: "+error.message;stopLiveTranscription()}}
transcriptBtn?.addEventListener("click",()=>{if(transcribing)stopLiveTranscription();else startLiveTranscription()});
lb.addEventListener("click",stopLiveTranscription,{capture:true});
window.addEventListener("beforeunload",stopLiveTranscription);
window.addEventListener("pagehide",()=>{if(!peerId||!peerToken)return;const body=JSON.stringify({peer_id:peerId,token:peerToken});fetch(urls.leave,{method:"POST",headers:{"Content-Type":"application/json","Accept":"application/json","X-CSRF-TOKEN":csrf},body,keepalive:true}).catch(()=>{})});
function setHybridConnectionState(state,label){if(!hybridConnection)return;hybridConnection.classList.remove("connected","connecting");if(state==="connected")hybridConnection.classList.add("connected");if(state==="connecting")hybridConnection.classList.add("connecting");hybridConnection.textContent=label}
async function refreshHybridSummary(){if(!isHybridRoom)return;try{const response=await fetch(urls.hybrid_summary,{headers:{"Accept":"application/json"}});if(!response.ok)throw new Error("Hybrid participant totals could not be refreshed.");const summary=await response.json();document.getElementById("hybridOnlineParticipantsCount").textContent=summary.online_count;document.getElementById("hybridInPersonParticipantsCount").textContent=summary.in_person_count;if(!peerId)document.getElementById("hybridOnlineParticipantLabel").textContent=summary.online_count+" connected"}catch(error){console.error(error);if(!peerId)status.textContent=error.message}}
if(isHybridRoom){refreshHybridSummary();hybridSummaryTimer=window.setInterval(refreshHybridSummary,5000);hybridParticipantsToggle.addEventListener("click",()=>{participantPanel.classList.add("on");hybridChatPanel.classList.remove("on");docPanel.classList.remove("on");hybridParticipantsToggle.classList.add("active");hybridChatToggle.classList.remove("active");docUploadBtn.classList.remove("active");hybridParticipantsToggle.setAttribute("aria-pressed","true");hybridChatToggle.setAttribute("aria-pressed","false");docUploadBtn.setAttribute("aria-pressed","false")});hybridChatToggle.addEventListener("click",()=>{hybridChatPanel.classList.add("on");participantPanel.classList.remove("on");docPanel.classList.remove("on");hybridChatToggle.classList.add("active");hybridParticipantsToggle.classList.remove("active");docUploadBtn.classList.remove("active");hybridChatToggle.setAttribute("aria-pressed","true");hybridParticipantsToggle.setAttribute("aria-pressed","false");docUploadBtn.setAttribute("aria-pressed","false")});docUploadBtn.addEventListener("click",()=>{docPanel.classList.add("on");participantPanel.classList.remove("on");hybridChatPanel.classList.remove("on");docUploadBtn.classList.add("active");hybridParticipantsToggle.classList.remove("active");hybridChatToggle.classList.remove("active");docUploadBtn.setAttribute("aria-pressed","true");hybridParticipantsToggle.setAttribute("aria-pressed","false");hybridChatToggle.setAttribute("aria-pressed","false");loadDocuments()})}docBtn.addEventListener("click",()=>{docPanel.classList.toggle("on");docBtn.classList.toggle("active",docPanel.classList.contains("on"));if(docPanel.classList.contains("on")){participantPanel.classList.remove("on");hybridChatPanel?.classList.remove("on");hybridParticipantsToggle?.classList.remove("active");hybridChatToggle?.classList.remove("active");docUploadBtn?.classList.remove("active");loadDocuments()}});
async function sendSignal(toPeer,type,payload){await post(urls.signal_send,authPayload({to_peer:toPeer,type,payload}))}
function setCtrlLabel(button,icon,label,aria){if(!button)return;let caption=button.querySelector("small");if(!caption){button.textContent="";caption=document.createElement("small");button.append(document.createTextNode(icon),caption)}else if(!button.firstChild||button.firstChild.nodeType!==Node.TEXT_NODE){button.insertBefore(document.createTextNode(icon),caption)}else{button.firstChild.nodeValue=icon}caption.textContent=label;if(aria){button.setAttribute("aria-label",aria);button.title=aria}}
function applyMicState(){if(ls)ls.getAudioTracks().forEach(track=>track.enabled=!mutedByHost&&!localMuted);mb.disabled=mutedByHost||roomPermissions.unmute===false;const muted=mutedByHost||localMuted;setCtrlLabel(mb,muted?"🔇":"🎤",muted?"Unmute":"Mute",muted?"Unmute microphone":"Mute microphone");mb.classList.toggle("off",muted)}
async function setRemoteMute(targetPeerId,muted,button){button.disabled=true;try{await post(urls.mute,Object.assign({peer_id:targetPeerId,muted},actorPayload()));status.textContent=muted?"Participant muted.":"Participant unmuted."}catch(error){status.textContent=error.message}finally{button.disabled=false}}
function buildParticipantRow(participant){const row=document.createElement("div"),label=document.createElement("span");row.className="participant-row";label.className="participant-status";label.textContent=participant.display_name+(participant.is_host?" · Host":"")+(participant.is_co_host?" · Co-host":"")+(participant.is_muted_by_host?" · Muted":"")+(participant.hand_raised?" · ✋ Hand raised":"");if(participant.is_co_host){const tag=document.createElement("span");tag.className="cohost-tag";tag.textContent="Co-host";label.appendChild(tag)}row.appendChild(label);const actions=document.createElement("span");actions.className="row-actions";if(!participant.is_host){if(canControl("mute_participants")){const button=document.createElement("button");button.type="button";button.textContent=participant.is_muted_by_host?"Unmute":"Mute";button.addEventListener("click",()=>setRemoteMute(participant.peer_id,!participant.is_muted_by_host,button));actions.appendChild(button)}if(canControl("promote_co_hosts")){const roleButton=document.createElement("button");roleButton.type="button";roleButton.className="role";roleButton.textContent=participant.is_co_host?"Remove co-host":"Make co-host";roleButton.addEventListener("click",()=>setCoHost(participant.peer_id,!participant.is_co_host,roleButton));actions.appendChild(roleButton)}if(canControl("remove_participants")){const removeButton=document.createElement("button");removeButton.type="button";removeButton.className="remove";removeButton.textContent="Remove";removeButton.addEventListener("click",()=>removeParticipant(participant.peer_id,removeButton));actions.appendChild(removeButton)}}if(actions.childElementCount)row.appendChild(actions);return row}
function showParticipants(participants) {
    // Cache all participants for filtering
    allParticipantsCache = participants;
    
    participantList.replaceChildren();
    
    // Add "You" row for hybrid rooms
    if (isHybridRoom && peerId) {
        const self = document.createElement("div");
        const label = document.createElement("span");
        self.className = "participant-row";
        label.className = "participant-status";
        label.textContent = "You · " + (isHost ? "Host" : "Online");
        self.appendChild(label);
        participantList.appendChild(self);
    }

    // Build company selector if multi-company room
    if (isMultiCompanyRoom) {
        buildCompanySelector(participants);
        
        // Filter participants by selected company
        const filteredParticipants = selectedCompanyId === null 
            ? participants 
            : participants.filter(p => {
                const pid = p.company_id;
                return selectedCompanyId === 'all' ? true : 
                       (pid === selectedCompanyId || (selectedCompanyId === 'guests' && !pid));
            });
        
        // Group filtered participants by company
        const groups = new Map();
        filteredParticipants.forEach(participant => {
            const key = participant.company_id != null 
                ? ("id:" + participant.company_id) 
                : ("name:" + (participant.company_name || "Guests"));
            if (!groups.has(key)) {
                groups.set(key, {
                    name: participant.company_name || "Guests",
                    logo: participant.company_logo || null,
                    id: participant.company_id,
                    members: []
                });
            }
            groups.get(key).members.push(participant);
        });

        // Render company groups
        groups.forEach(group => {
            const wrap = document.createElement("section");
            wrap.className = "company-group";
            
            const head = document.createElement("button");
            head.type = "button";
            head.className = "company-group-head";
            head.dataset.companyId = group.id || 'guests';
            
            const logo = document.createElement("span");
            logo.className = "company-group-logo";
            if (group.logo) {
                const img = document.createElement("img");
                img.src = group.logo;
                img.alt = "";
                logo.appendChild(img);
            } else {
                logo.textContent = group.name.charAt(0).toUpperCase();
            }
            
            const name = document.createElement("span");
            name.className = "company-group-name";
            name.textContent = group.name;
            
            const count = document.createElement("span");
            count.className = "company-group-count";
            count.textContent = group.members.length + (group.members.length === 1 ? " member" : " members");
            
            const caret = document.createElement("span");
            caret.className = "company-group-caret";
            caret.textContent = "▾";
            
            head.append(logo, name, count, caret);
            head.addEventListener("click", () => wrap.classList.toggle("collapsed"));
            
            const list = document.createElement("div");
            list.className = "company-group-list";
            group.members.forEach(participant => list.appendChild(buildParticipantRow(participant)));
            
            wrap.append(head, list);
            participantList.appendChild(wrap);
        });
        
        // Update company selector active state
        updateCompanySelectorActive();
    } else {
        participants.forEach(participant => participantList.appendChild(buildParticipantRow(participant)));
    }
    
    participantPanel.classList.add("on");if(isHybridRoom){const count=participants.length+(peerId?1:0);document.getElementById("hybridOnlineParticipantsCount").textContent=count;document.getElementById("hybridOnlineParticipantLabel").textContent=count+" connected"}const connected=new Set(participants.map(person=>person.peer_id));for(const [remotePeer,pc] of peers){if(!connected.has(remotePeer)){pc.close();peers.delete(remotePeer);remoteTiles.get(remotePeer)?.remove();remoteTiles.delete(remotePeer)}}participants.forEach(connectToPeer)}
function addRemoteTile(remotePeer,name,track,stream){let tile=remoteTiles.get(remotePeer);if(!tile){tile=document.createElement("article");tile.className="media-tile";const video=document.createElement("video"),label=document.createElement("span");video.autoplay=true;video.playsInline=true;video.muted=false;label.textContent=name;tile.append(video,label);mediaGrid.appendChild(tile);remoteTiles.set(remotePeer,tile)}const video=tile.querySelector("video");let media=video.srcObject instanceof MediaStream?video.srcObject:null;if(!media){media=stream instanceof MediaStream?stream:new MediaStream();video.srcObject=media}if(track&&![...media.getTracks()].some(item=>item.id===track.id))media.addTrack(track);video.play().catch(()=>{status.textContent="Click the meeting once to hear the other participants."});tile.querySelector("span").textContent=name;if(recording?.addAudio)recording.addAudio(media)}
function descriptionPayload(description){const sdp=String(description.sdp||"").replace(/\r\n/g,"\n").replace(/\r/g,"\n").replace(/\n/g,"\r\n");return {type:description.type,sdp:sdp.endsWith("\r\n")?sdp:sdp+"\r\n"}}
function addLocalTracks(pc){const audio=ls.getAudioTracks()[0],video=ls.getVideoTracks()[0];if(audio)pc.addTrack(audio,ls);if(video)pc.addTrack(video,ls)}
async function answerWithLocalTracks(pc){const audio=ls.getAudioTracks()[0],video=ls.getVideoTracks()[0];const kinds=[];for(const line of (pc.remoteDescription?.sdp||"").split("\n")){if(line.startsWith("m=audio"))kinds.push("audio");else if(line.startsWith("m=video"))kinds.push("video")}const transceivers=pc.getTransceivers();for(let index=0;index<transceivers.length;index++){const transceiver=transceivers[index];const kind=(transceiver.receiver.track&&transceiver.receiver.track.kind)||kinds[index];if(kind==="audio"&&audio)await transceiver.sender.replaceTrack(audio);else if(kind==="video"&&video)await transceiver.sender.replaceTrack(video);if(kind==="audio"||kind==="video")transceiver.direction="sendrecv"}const answer=await pc.createAnswer();await pc.setLocalDescription(answer)}
async function flushPendingCandidates(pc,remotePeer){const queued=pendingCandidates.get(remotePeer)||[];pendingCandidates.set(remotePeer,[]);for(const candidate of queued)await pc.addIceCandidate(candidate)}
function connectToPeer(person){if(!ls||peers.has(person.peer_id))return;const pc=new RTCPeerConnection({iceServers:[{urls:"stun:stun.l.google.com:19302"},{urls:"stun:stun1.l.google.com:19302"}]});peers.set(person.peer_id,pc);pendingCandidates.set(person.peer_id,[]);pc.onicecandidate=event=>{if(event.candidate)sendSignal(person.peer_id,"ice-candidate",event.candidate.toJSON()).catch(error=>{roomNotice=error.message;status.textContent=roomNotice})};pc.ontrack=event=>addRemoteTile(person.peer_id,person.display_name,event.track,event.streams&&event.streams[0]);pc.onconnectionstatechange=()=>{if(pc.connectionState==="connected"){roomNotice="";status.textContent="";return}if(pc.connectionState==="closed"){peers.delete(person.peer_id);remoteTiles.get(person.peer_id)?.remove();remoteTiles.delete(person.peer_id);return}if(pc.connectionState!=="failed")return;roomNotice="This network is blocking the direct video connection to "+person.display_name+", so their camera and microphone cannot come through.";status.textContent=roomNotice};const shouldOffer=peerId.localeCompare(person.peer_id)<0;if(shouldOffer){addLocalTracks(pc);pc.createOffer().then(offer=>pc.setLocalDescription(offer)).then(()=>sendSignal(person.peer_id,"offer",descriptionPayload(pc.localDescription))).catch(error=>{roomNotice="Could not connect to "+person.display_name+": "+error.message;status.textContent=roomNotice})}const queued=earlySignals.get(person.peer_id)||[];earlySignals.delete(person.peer_id);if(queued.length)Promise.resolve().then(async()=>{for(const signal of queued)await receiveSignal(signal)})}
async function receiveSignal(signal){const pc=peers.get(signal.from_peer);if(!pc){const queued=earlySignals.get(signal.from_peer)||[];queued.push(signal);earlySignals.set(signal.from_peer,queued);return}try{if(signal.type==="offer"){if(pc.signalingState!=="stable")await pc.setLocalDescription({type:"rollback"});await pc.setRemoteDescription(descriptionPayload(signal.payload));await answerWithLocalTracks(pc);await sendSignal(signal.from_peer,"answer",descriptionPayload(pc.localDescription));await flushPendingCandidates(pc,signal.from_peer)}else if(signal.type==="answer"){await pc.setRemoteDescription(descriptionPayload(signal.payload));await flushPendingCandidates(pc,signal.from_peer)}else if(signal.type==="ice-candidate"){const candidate=new RTCIceCandidate(signal.payload);if(pc.remoteDescription)await pc.addIceCandidate(candidate);else pendingCandidates.get(signal.from_peer)?.push(candidate)}}catch(error){console.error(error);roomNotice="Connection signaling failed: "+error.message;status.textContent=roomNotice}}
function addChatMessage(message){if(message.id<=chatCursor)return;chatCursor=message.id;const row=document.createElement("div"),name=document.createElement("strong"),text=document.createElement("span");row.className="chat-message";name.textContent=message.participant?.display_name||"Participant";text.textContent=message.message;row.append(name,text);chatMessages.appendChild(row);chatMessages.scrollTop=chatMessages.scrollHeight}
let sessionEnding=false;async function heartbeat(){if(!peerId)return;try{const data=await post(urls.heartbeat,authPayload({after_signal_id:signalCursor,after_chat_id:chatCursor}));if(data.permissions){roomPermissions=data.permissions;isCoHost=!!data.is_co_host;applyPermissions()}mutedByHost=data.is_muted;applyMicState();showParticipants(data.participants);updateWaitingRoom(data.waiting_count||0);for(const signal of data.signals){signalCursor=Math.max(signalCursor,signal.id);await receiveSignal(signal)}data.messages.forEach(addChatMessage);recordingIndicator.classList.toggle("on",data.recordings.length>0);recordingIndicator.textContent=data.recordings.length?"● Recording: "+data.recordings.map(item=>item.participant.display_name).join(", "):"";status.textContent=data.is_muted?"The host has muted your microphone.":roomNotice}catch(error){status.textContent=error.message;if(error.status===403&&error.body&&error.body.status==="removed"&&!sessionEnding){sessionEnding=true;await teardownRoom();status.textContent=error.message;return}if(error.status===410&&!sessionEnding){sessionEnding=true;await teardownRoom();status.textContent="This online session has ended."}}}
if(sendCodeButton)sendCodeButton.addEventListener("click",async()=>{sendCodeButton.disabled=true;try{const email=identityInput.value.trim();if(!email)throw new Error("Enter the email address from your invitation.");const result=await post(urls.send_code,{email});identityInput.readOnly=true;sendCodeButton.hidden=true;codeInput.hidden=false;codeInput.required=true;changeEmailButton.hidden=false;if(isHybridRoom){resendCodeButton.hidden=false;sc.hidden=false;sc.textContent="▶ Join Meeting";sessionStorage.setItem(hybridEmailStorageKey,email);sessionStorage.setItem(hybridCodeSentAtKey,String(Date.now()))}status.textContent=result.message;codeInput.focus()}catch(error){status.textContent=error.message}finally{sendCodeButton.disabled=false}});
if(isHybridRoom)resendCodeButton.addEventListener("click",async()=>{resendCodeButton.disabled=true;try{const email=identityInput.value.trim();if(!email)throw new Error("Enter the email address from your invitation.");const result=await post(urls.send_code,{email});sessionStorage.setItem(hybridEmailStorageKey,email);sessionStorage.setItem(hybridCodeSentAtKey,String(Date.now()));status.textContent=result.message+" Check your inbox for the new code."}catch(error){status.textContent=error.message}finally{resendCodeButton.disabled=false}});
if(changeEmailButton)changeEmailButton.addEventListener("click",()=>{sessionStorage.removeItem(hybridEmailStorageKey);sessionStorage.removeItem(hybridCodeSentAtKey);identityInput.readOnly=false;identityInput.value="";codeInput.value="";codeInput.required=false;codeInput.hidden=true;resendCodeButton.hidden=true;changeEmailButton.hidden=true;sc.hidden=true;sendCodeButton.hidden=false;identityInput.focus()});
async function enterRoom(data){peerId=data.peer_id;peerToken=data.token;roomPermissions=data.permissions||{};isCoHost=!!data.is_co_host;hideWaiting();recordBtn.hidden=!data.can_record;ls=await navigator.mediaDevices.getUserMedia({video:true,audio:true});lv.srcObject=ls;lv.muted=true;lv.play().catch(()=>{});document.getElementById("localName").textContent="You";if(!room.dataset.playbackBound){room.dataset.playbackBound="1";room.addEventListener("pointerdown",()=>{room.querySelectorAll("video").forEach(video=>{if(video.srcObject)video.play().catch(()=>{})})})}co.style.display="none";cb.classList.add("on");transcriptControls?.classList.add("on");mediaGrid.style.display="grid";document.getElementById("roomColumns").classList.add("on");mutedByHost=data.is_muted;applyPermissions();showParticipants(data.participants||[]);updateWaitingRoom(data.waiting_count||0);setHybridConnectionState("connected","Connected");pollTimer=window.setInterval(heartbeat,1500);await heartbeat()}
const directJoinData=room.dataset.directJoin?JSON.parse(room.dataset.directJoin):null;
sc.addEventListener("click",async()=>{sc.disabled=true;setHybridConnectionState("connecting","Connecting");try{if(directJoinData){if(directJoinData.waiting){beginWaiting(directJoinData)}else{await enterRoom(directJoinData)}}else if(guestNameInput){const displayName=guestNameInput.value.trim();if(!displayName)throw new Error("Enter your name so the host knows who is joining.");const data=await post(urls.guest_join,{display_name:displayName});if(data.waiting){beginWaiting(data)}else{await enterRoom(data)}}else{const email=identityInput.value.trim();if(!email)throw new Error("Enter your email address.");if(!isHost&&!codeInput.value.trim())throw new Error("Enter the verification code sent to your email.");const data=await post(urls.join,{email,verification_code:isHost?undefined:codeInput.value.trim()});sessionStorage.removeItem(hybridEmailStorageKey);sessionStorage.removeItem(hybridCodeSentAtKey);if(data.waiting){beginWaiting(data)}else{await enterRoom(data)}}}catch(error){if(peerId){try{await post(urls.leave,authPayload({}))}catch(leaveError){console.error(leaveError)}peerId=null}if(ls){ls.getTracks().forEach(track=>track.stop());ls=null}setHybridConnectionState("","Not connected");status.textContent=error.name==="NotAllowedError"?"Camera or microphone access was denied.":error.message;sc.disabled=false}});
if(directJoinData)sc.click();
mb.addEventListener("click",()=>{if(mutedByHost)return;localMuted=!localMuted;applyMicState()});
camb.addEventListener("click",()=>{if(!ls)return;const track=ls.getVideoTracks()[0];if(!track)return;track.enabled=!track.enabled;camb.classList.toggle("off",!track.enabled);setCtrlLabel(camb,track.enabled?"📷":"🚫",track.enabled?"Camera":"Off",track.enabled?"Turn camera off":"Turn camera on")});
screenBtn.addEventListener("click",async()=>{if(!ls)return;try{if(screenTrack){await stopScreenShare();return}await post(urls.screen_share_check,authPayload({}));const display=await navigator.mediaDevices.getDisplayMedia({video:true});screenTrack=display.getVideoTracks()[0];for(const pc of peers.values()){const sender=pc.getSenders().find(item=>item.track?.kind==="video");if(sender)await sender.replaceTrack(screenTrack)}lv.srcObject=new MediaStream([screenTrack,...ls.getAudioTracks()]);screenBtn.classList.add("active");setCtrlLabel(screenBtn,"▣","Stop","Stop sharing");screenTrack.onended=()=>stopScreenShare()}catch(error){status.textContent="Screen sharing failed: "+error.message}});
async function stopScreenShare(){if(!screenTrack)return;screenTrack.onended=null;screenTrack.stop();screenTrack=null;screenBtn.classList.remove("active");setCtrlLabel(screenBtn,"▣","Share","Share screen");lv.srcObject=ls;const camera=ls?.getVideoTracks()[0];for(const pc of peers.values()){const sender=pc.getSenders().find(item=>item.track?.kind==="video");if(sender&&camera)await sender.replaceTrack(camera)}}
handBtn.addEventListener("click",async()=>{if(!peerId)return;handBtn.disabled=true;try{handRaised=!handRaised;await post(urls.hand,authPayload({raised:handRaised}));handBtn.classList.toggle("active",handRaised);setCtrlLabel(handBtn,"✋",handRaised?"Lower":"Hand",handRaised?"Lower hand":"Raise hand")}catch(error){handRaised=!handRaised;status.textContent=error.message}finally{handBtn.disabled=false}});
chatForm.addEventListener("submit",async event=>{event.preventDefault();const message=chatInput.value.trim();if(!message||!peerId)return;try{await post(urls.chat_send,authPayload({message}));chatInput.value="";await heartbeat()}catch(error){status.textContent=error.message}});
function drawFrame(ctx,canvas){const tiles=[...mediaGrid.querySelectorAll("video")].filter(video=>video.readyState>=2);ctx.fillStyle="#0b1a2e";ctx.fillRect(0,0,canvas.width,canvas.height);const cols=Math.ceil(Math.sqrt(Math.max(1,tiles.length))),rows=Math.ceil(tiles.length/cols),width=canvas.width/cols,height=canvas.height/rows;tiles.forEach((video,index)=>{const x=(index%cols)*width,y=Math.floor(index/cols)*height;ctx.drawImage(video,x,y,width,height);ctx.fillStyle="rgba(0,0,0,.6)";ctx.fillRect(x,y+height-34,width,34);ctx.fillStyle="#fff";ctx.font="20px sans-serif";ctx.fillText(video.closest(".media-tile").querySelector("span").textContent,x+12,y+height-11)})}
async function startRecording(){if(!window.MediaRecorder||!HTMLCanvasElement.prototype.captureStream)throw new Error("Recording is not supported by this browser.");const data=await post(urls.recording_start,authPayload({}));const canvas=document.createElement("canvas");canvas.width=1280;canvas.height=720;const ctx=canvas.getContext("2d");let frame;const render=()=>{drawFrame(ctx,canvas);frame=requestAnimationFrame(render)};render();const output=canvas.captureStream(15),audioContext=new AudioContext(),audioDestination=audioContext.createMediaStreamDestination(),connected=new WeakSet();const addAudio=stream=>{if(connected.has(stream))return;connected.add(stream);const audio=stream.getAudioTracks();if(audio.length)audioContext.createMediaStreamSource(new MediaStream(audio)).connect(audioDestination)};addAudio(ls);for(const tile of remoteTiles.values())if(tile.querySelector("video").srcObject)addAudio(tile.querySelector("video").srcObject);const allTracks=[...output.getVideoTracks(),...audioDestination.stream.getAudioTracks()];if(allTracks.length<2)throw new Error("No session audio/video tracks are available to record.");const stream=new MediaStream(allTracks),mime=MediaRecorder.isTypeSupported("video/webm;codecs=vp8,opus")?"video/webm;codecs=vp8,opus":"video/webm",recorder=new MediaRecorder(stream,{mimeType:mime}),chunks=[];recording={id:data.recording_id,recorder,chunks,canvas,frame,audioContext,stream,startedAt:Date.now(),addAudio};recorder.ondataavailable=event=>{if(event.data.size)chunks.push(event.data)};recorder.start(1000);setCtrlLabel(recordBtn,"■","Stop","Stop recording");recordBtn.classList.add("active");recordingIndicator.classList.add("on");recordingIndicator.textContent="● Recording: You"}
function stopRecording(){if(!recording)return Promise.resolve();const current=recording;recording=null;return new Promise((resolve,reject)=>{current.recorder.onstop=async()=>{cancelAnimationFrame(current.frame);current.stream.getTracks().forEach(track=>track.stop());await current.audioContext.close();const blob=new Blob(current.chunks,{type:current.recorder.mimeType||"video/webm"});const form=new FormData();form.append("peer_id",peerId);form.append("token",peerToken);form.append("recording_id",current.id);form.append("duration_seconds",String(Math.max(1,Math.floor((Date.now()-current.startedAt)/1000))));form.append("recording",blob,"session-recording.webm");try{const response=await fetch(urls.recording_finish,{method:"POST",headers:{"Accept":"application/json","X-CSRF-TOKEN":csrf},body:form});if(!response.ok)throw new Error((await response.json()).message||"Recording upload failed ("+response.status+").");status.textContent="Recording saved under this session.";resolve()}catch(error){status.textContent=error.message;reject(error)}finally{current.canvas.width=1;current.canvas.height=1}};current.recorder.stop();setCtrlLabel(recordBtn,"●","Record","Start recording");recordBtn.classList.remove("active")})}
recordBtn.addEventListener("click",async()=>{recordBtn.disabled=true;try{if(recording)await stopRecording();else await startRecording()}catch(error){status.textContent=error.message}finally{recordBtn.disabled=false}});
const copyOnlineShareButton=document.getElementById("copyOnlineShareUrl");
if(copyOnlineShareButton){copyOnlineShareButton.addEventListener("click",async()=>{const link=document.getElementById("onlineShareUrl"),onlineShareStatus=document.getElementById("onlineShareStatus");try{await navigator.clipboard.writeText(link.href);onlineShareStatus.textContent="Meeting link copied."}catch(error){onlineShareStatus.textContent="Copy is unavailable in this browser. Select and copy the link above."}window.setTimeout(()=>{onlineShareStatus.textContent=""},2500)})}
lb.addEventListener("click",async()=>{if(pollTimer)window.clearInterval(pollTimer);try{if(recording)await stopRecording();if(screenTrack)await stopScreenShare();if(peerId)await post(urls.leave,authPayload({}))}catch(error){status.textContent=error.message}for(const pc of peers.values())pc.close();peers.clear();if(ls){ls.getTracks().forEach(track=>track.stop());ls=null}peerId=null;peerToken=null;lv.srcObject=null;mediaGrid.style.display="none";cb.classList.remove("on");participantPanel.classList.remove("on");hybridChatPanel?.classList.remove("on");document.getElementById("roomColumns").classList.remove("on");chatMessages.replaceChildren();co.style.display="";sc.disabled=false;if(isHybridRoom){setHybridConnectionState("","Not connected");hybridParticipantsToggle.classList.add("active");hybridChatToggle.classList.remove("active");hybridParticipantsToggle.setAttribute("aria-pressed","true");hybridChatToggle.setAttribute("aria-pressed","false")}if(!isHost){if(identityInput)identityInput.readOnly=false;if(codeInput){codeInput.value="";codeInput.hidden=true}if(changeEmailButton)changeEmailButton.hidden=true;sc.hidden=true;if(sendCodeButton)sendCodeButton.hidden=false}recordingIndicator.classList.remove("on");screenBtn.classList.remove("active");handBtn.classList.remove("active");setCtrlLabel(handBtn,"✋","Hand","Raise hand");setCtrlLabel(camb,"📷","Camera","Turn camera off");setCtrlLabel(mb,"🎤","Mute","Mute microphone");if(isHybridRoom)refreshHybridSummary()});
function actorPayload(){return {actor_peer_id:peerId,actor_token:peerToken}}
function canControl(capability){return (isHost||isCoHost)&&!!roomPermissions[capability]}
function applyPermissions(){
    if(!roomPermissions||Object.keys(roomPermissions).length===0)return;
    const setControl=(el,allowed,message)=>{if(!el)return;el.disabled=!allowed;el.title=allowed?el.title:message;el.classList.toggle("permission-blocked",!allowed)};
    setControl(mb,roomPermissions.unmute!==false,"The host has disabled self-unmute.");
    setControl(camb,roomPermissions.camera!==false,"The host has disabled participant cameras.");
    setControl(screenBtn,roomPermissions.screen_share!==false,"The host has disabled screen sharing.");
    setControl(handBtn,roomPermissions.hand!==false,"The host has disabled raised hands.");
    const chatSendButton=chatForm?chatForm.querySelector('button[type="submit"]'):null;const chatAllowed=roomPermissions.chat!==false;if(chatInput){chatInput.disabled=!chatAllowed;chatInput.placeholder=chatAllowed?"Write a message…":"Chat is disabled by the host"}if(chatSendButton)chatSendButton.disabled=!chatAllowed}
function showWaiting(messageText,hintText){waitingOverlay.hidden=false;waitingMessage.textContent=messageText||"You are in the waiting room. The host has been notified of your arrival and will admit you shortly.";if(hintText){waitingHint.hidden=false;waitingHint.textContent=hintText}else{waitingHint.hidden=true}const wVid=document.getElementById("waitingVideoPreview");const wPlc=document.getElementById("waitingVideoPlaceholder");if(wVid && typeof localStream!=="undefined" && localStream && localStream.getVideoTracks().length>0){wVid.srcObject=localStream;wVid.style.display="block";if(wPlc) wPlc.style.display="none";wVid.play().catch(function(){});}setHybridConnectionState("connecting","Waiting for host")}

async function loadDocuments(){
    if(!docList)return;
    try{
        const response=await fetch(urls.documents_list,{headers:{"Accept":"application/json"}});
        if(!response.ok)throw new Error("Failed to load documents");
        const data=await response.json();
        renderDocuments(data.documents);
    }catch(error){
        console.error(error);
        docList.innerHTML='<div style="padding:12px;color:#aebdce;font-size:12px">Failed to load documents: '+error.message+'</div>';
    }
}

function renderDocuments(documents){
    if(!docList)return;
    docList.innerHTML="";
    if(!documents.length){
        docList.innerHTML='<div style="padding:12px;color:#aebdce;font-size:12px;text-align:center">No documents shared yet</div>';
        return;
    }
    documents.forEach(doc=>{
        const item=document.createElement("div");
        item.className="doc-item";
        const icon=getFileIcon(doc.extension);
        const size=formatFileSize(doc.file_size);
        const metaParts=[doc.uploader_name,doc.uploader_company,size].filter(Boolean);
        const canPreview=doc.allow_preview;
        const canDownload=doc.allow_download;
        const canManage=doc.can_manage;
        
        // Build expiration/retention indicator
        let extras = [];
        if (doc.expires_at) {
            const expiresDate = new Date(doc.expires_at);
            const now = new Date();
            if (expiresDate < now) {
                extras.push('<span class="doc-badge expired">⏰ Expired</span>');
            } else {
                extras.push('<span class="doc-badge expires">⏰ Expires ' + expiresDate.toLocaleDateString() + '</span>');
            }
        }
        if (doc.retention_policy && doc.retention_policy !== 'keep') {
            const policyLabels = {
                'revoke_on_end': '📋 Revoke on meeting end',
                'delete_on_end': '🗑 Delete on meeting end'
            };
            extras.push('<span class="doc-badge retention">' + (policyLabels[doc.retention_policy] || doc.retention_policy) + '</span>');
        }
        if (doc.encrypted_e2e) {
            extras.push('<span class="doc-badge e2e">🔒 E2E Encrypted</span>');
        }
        const extrasHtml = extras.length ? '<div class="doc-extras">' + extras.join('') + '</div>' : '';

        item.innerHTML=`
            <span class="doc-icon">${icon}</span>
            <div class="doc-info">
                <span class="doc-name">${escapeHtml(doc.original_name)}</span>
                <span class="doc-meta">${escapeHtml(metaParts.join(" · "))}</span>
                ${extrasHtml}
            </div>
            <div class="doc-actions">
                ${canPreview?'<button class="doc-action primary" data-action="preview" data-url="'+escapeHtml(doc.preview_url)+'">👁 Preview</button>':""}
                ${canDownload?'<button class="doc-action" data-action="download" data-url="'+escapeHtml(doc.download_url)+'">⬇ Download</button>':""}
                ${canManage?'<button class="doc-action" data-action="share" data-id="'+doc.id+'">🔗 Share</button><button class="doc-action danger" data-action="revoke" data-id="'+doc.id+'">🗑 Revoke</button>':""}
            </div>
        `;
        docList.appendChild(item);
    });
    docList.querySelectorAll(".doc-action").forEach(btn=>{
        btn.addEventListener("click",async()=>{
            const action=btn.dataset.action;
            if(action==="preview"){
                window.open(btn.dataset.url,"_blank");
            }else if(action==="download"){
                window.open(btn.dataset.url,"_blank");
            }else if(action==="share"){
                await updateDocumentShare(btn.dataset.id);
            }else if(action==="revoke"){
                await revokeDocument(btn.dataset.id);
            }
        });
    });
}

function escapeHtml(text){
    const div=document.createElement("div");
    div.textContent=text;
    return div.innerHTML;
}

function getFileIcon(ext){
    switch(ext){
        case "pdf":return "📄";
        case "docx":return "📝";
        case "pptx":return "📊";
        case "xlsx":return "📈";
        case "txt":return "📃";
        case "png":
        case "jpg":
        case "jpeg":return "🖼";
        default:return "📎";
    }
}

function formatFileSize(bytes){
    if(bytes<1024)return bytes+" B";
    if(bytes<1024*1024)return (bytes/1024).toFixed(1)+" KB";
    return (bytes/(1024*1024)).toFixed(1)+" MB";
}

async function updateDocumentShare(documentId){
    const doc=docList.querySelector('.doc-item [data-id="'+documentId+'"]')?.closest('.doc-item');
    if(!doc)return;
    const originalName=doc.querySelector(".doc-name")?.textContent||"Document";
    const newScope=prompt("Share with:\n1 - All participants\n2 - Selected companies\n3 - Selected individuals\n\nEnter 1, 2, or 3:", "1");
    if(!newScope)return;
    const scopeMap={"1":"all","2":"companies","3":"individuals"};
    const shareScope=scopeMap[newScope]||"all";
    let companies=[],participants=[];
    if(shareScope==="companies"){
        const input=prompt("Enter company IDs (comma-separated):");
        if(input)companies=input.split(",").map(s=>parseInt(s.trim())).filter(n=>!isNaN(n));
    }else if(shareScope==="individuals"){
        const input=prompt("Enter participant IDs (comma-separated):");
        if(input)participants=input.split(",").map(s=>parseInt(s.trim())).filter(n=>!isNaN(n));
    }
    
    // Optional: expiration date
    const expiresAt = prompt("Expiration date (YYYY-MM-DD) or leave blank for no expiration:");
    // Optional: retention policy
    const retentionPolicy = prompt("Retention policy:\n1 - Keep after meeting ends\n2 - Revoke access when meeting ends\n3 - Delete file when meeting ends\n\nEnter 1, 2, or 3 (default: 1):", "1");
    const retentionMap = {"1": "keep", "2": "revoke_on_end", "3": "delete_on_end"};
    
    try{
        await post(buildDocUrl(urls.documents_update_base,documentId),{
            share_scope:shareScope,
            shared_with_companies:companies,
            shared_with_participants:participants,
            allow_download:true,
            allow_preview:true,
            expires_at: expiresAt && expiresAt.trim() ? expiresAt.trim() : null,
            retention_policy: retentionMap[retentionPolicy] || "keep",
        });
        status.textContent="Document sharing updated";
        await loadDocuments();
    }catch(error){
        status.textContent="Failed to update sharing: "+error.message;
    }
}

async function revokeDocument(documentId){
    if(!confirm("Revoke this document? This will remove it from the meeting and delete the file."))return;
    try{
        await post(buildDocUrl(urls.documents_revoke_base,documentId),{});
        status.textContent="Document revoked";
        await loadDocuments();
    }catch(error){
        status.textContent="Failed to revoke: "+error.message;
    }
}

docUploadBtn.addEventListener("click",()=>docFileInput.click());
docFileInput.addEventListener("change",async()=>{
    const file=docFileInput.files[0];
    if(!file)return;
    docUploadBtn.disabled=true;
    docUploadBtn.textContent="⏳ Uploading...";
    try{
        const form=new FormData();
        form.append("file",file);
        form.append("share_scope","all");
        form.append("allow_download","true");
        form.append("allow_preview","true");
        
        // Optional: expiration date
        const expiresAt = prompt("Expiration date (YYYY-MM-DD) or leave blank for no expiration:");
        if (expiresAt && expiresAt.trim()) {
            form.append("expires_at", expiresAt.trim());
        }
        
        // Optional: retention policy
        const retentionPolicy = prompt("Retention policy:\n1 - Keep after meeting ends\n2 - Revoke access when meeting ends\n3 - Delete file when meeting ends\n\nEnter 1, 2, or 3 (default: 1):", "1");
        const retentionMap = {"1": "keep", "2": "revoke_on_end", "3": "delete_on_end"};
        form.append("retention_policy", retentionMap[retentionPolicy] || "keep");
        
        // Optional: E2E encryption (placeholder for future implementation)
        // form.append("encrypted_e2e", "false");
        
        const response=await fetch(urls.documents_upload,{method:"POST",headers:{"Accept":"application/json","X-CSRF-TOKEN":csrf},body:form});
        if(!response.ok){
            const err=await response.json();
            throw new Error(err.message||"Upload failed");
        }
        const data=await response.json();
        status.textContent="Document uploaded: "+data.document.original_name;
        docFileInput.value="";
        await loadDocuments();
    }catch(error){
        status.textContent="Upload failed: "+error.message;
    }finally{
        docUploadBtn.disabled=false;
        docUploadBtn.textContent="📤 Upload";
    }
});

// Screen sharing with backend integration
screenBtn.addEventListener("click",async()=>{
    if(!ls)return;
    try{
        if(screenTrack){
            await stopScreenShare();
            return;
        }
        await post(urls.screen_share_check,authPayload({}));
        const display=await navigator.mediaDevices.getDisplayMedia({video:{cursor:"always"},audio:true});
        screenTrack=display.getVideoTracks()[0];
        for(const pc of peers.values()){
            const sender=pc.getSenders().find(item=>item.track?.kind==="video");
            if(sender)await sender.replaceTrack(screenTrack);
        }
        lv.srcObject=new MediaStream([screenTrack,...ls.getAudioTracks()]);
        screenBtn.classList.add("active");
        setCtrlLabel(screenBtn,"▣","Stop","Stop sharing");
        screenTrack.onended=()=>stopScreenShare();
        // Notify backend
        await post(urls.screen_share_start,authPayload({share_type:"screen"}));
    }catch(error){
        status.textContent="Screen sharing failed: "+error.message;
    }
});

async function stopScreenShare(){
    if(!screenTrack)return;
    screenTrack.onended=null;
    screenTrack.stop();
    screenTrack=null;
    screenBtn.classList.remove("active");
    setCtrlLabel(screenBtn,"▣","Share","Share screen");
    lv.srcObject=ls;
    const camera=ls?.getVideoTracks()[0];
    for(const pc of peers.values()){
        const sender=pc.getSenders().find(item=>item.track?.kind==="video");
        if(sender&&camera)await sender.replaceTrack(camera);
    }
    // Notify backend
    await post(urls.screen_share_stop,authPayload({}));
}

// Handle screen share signals
const originalReceiveSignal=receiveSignal;
receiveSignal=async function(signal){
    if(signal.type==="screen-share-update"){
        if(signal.payload.action==="started"){
            status.textContent=signal.payload.sharer_name+" started sharing their screen ("+signal.payload.share_type+")";
            // Could add visual indicator for who is sharing
        }else if(signal.payload.action==="stopped"){
            status.textContent=signal.payload.sharer_name+" stopped sharing their screen";
        }
        return;
    }
    if(signal.type==="document-update"){
        if(["uploaded","share_updated","revoked"].includes(signal.payload.action)){
            await loadDocuments();
            status.textContent="Documents updated: "+signal.payload.original_name;
        }
        return;
    }
    await originalReceiveSignal(signal);
};

// Initial document load when entering room
const originalEnterRoom=enterRoom;
enterRoom=async function(data){
    await originalEnterRoom(data);
    await loadDocuments();
};

// Check screen share status on heartbeat
const originalHeartbeat=heartbeat;
heartbeat=async function(){
    await originalHeartbeat();
    // Could periodically check screen share status if needed
};
function hideWaiting(){if(waitingTimer){window.clearInterval(waitingTimer);waitingTimer=null}const wVid=document.getElementById("waitingVideoPreview");if(wVid){wVid.srcObject=null;wVid.style.display="none";}waitingOverlay.hidden=true}
function beginWaiting(payload){peerId=payload.peer_id;peerToken=payload.token;roomPermissions=payload.permissions||{};isCoHost=!!payload.is_co_host;showWaiting(null,payload.waiting_room_message);status.textContent="You are in the waiting room. Waiting for the host to admit you.";if(waitingTimer)window.clearInterval(waitingTimer);waitingTimer=window.setInterval(async()=>{try{const data=await post(urls.status,authPayload({}));if(data.status==="waiting"){if(data.waiting_room_message)showWaiting(null,data.waiting_room_message);return}hideWaiting();await enterRoom(data)}catch(error){if(error.status===403){hideWaiting();status.textContent=error.message;sc.disabled=false;setHybridConnectionState("","Not connected");return}status.textContent=error.message}},2500)}
async function updateWaitingRoom(count){if(!canControl("manage_waiting_room")){hostWaitingPanel.hidden=true;return}if(count<=0){hostWaitingPanel.hidden=true;waitingRoomList.replaceChildren();waitingRoomCount.textContent="0";lastWaitingCount=0;return}hostWaitingPanel.hidden=false;waitingRoomCount.textContent=String(count);if(count!==lastWaitingCount){lastWaitingCount=count;await refreshWaitingRoom()}}
async function refreshWaitingRoom(){if(!canControl("manage_waiting_room"))return;try{const data=await post(urls.waiting_room,actorPayload());renderWaitingRoom(data.waiting||[])}catch(error){status.textContent=error.message}}
function renderWaitingRoom(list){waitingRoomList.replaceChildren();waitingRoomCount.textContent=String(list.length);admitAllBtn.hidden=list.length===0;if(list.length===0){const empty=document.createElement("div");empty.className="host-waiting-empty";empty.textContent="No one is waiting right now.";waitingRoomList.appendChild(empty);return}list.forEach(person=>{const row=document.createElement("div"),who=document.createElement("span"),name=document.createElement("strong"),company=document.createElement("small"),actions=document.createElement("span");row.className="host-waiting-row";who.className="waiting-who";name.textContent=person.display_name;company.textContent=person.company_name||"Guests";who.append(name,company);actions.className="waiting-actions";const admitButton=document.createElement("button");admitButton.type="button";admitButton.className="admit";admitButton.textContent="Admit";admitButton.addEventListener("click",()=>admitWaiting(person.peer_id,admitButton));actions.appendChild(admitButton);const rejectButton=document.createElement("button");rejectButton.type="button";rejectButton.className="reject";rejectButton.textContent="Reject";rejectButton.addEventListener("click",()=>rejectWaiting(person.peer_id,rejectButton));actions.appendChild(rejectButton);if(person.company_id!=null){const companyButton=document.createElement("button");companyButton.type="button";companyButton.className="company";companyButton.textContent="Admit "+company.textContent;companyButton.addEventListener("click",()=>admitCompanyWaiting(person.company_id,companyButton));actions.appendChild(companyButton)}row.append(who,actions);waitingRoomList.appendChild(row)})}
async function admitWaiting(peerIdToAdmit,button){button.disabled=true;try{await post(urls.admit,Object.assign({peer_id:peerIdToAdmit},actorPayload()));status.textContent="Participant admitted.";lastWaitingCount=-1;await refreshWaitingRoom()}catch(error){status.textContent=error.message;button.disabled=false}}
async function rejectWaiting(peerIdToReject,button){button.disabled=true;try{await post(urls.reject,Object.assign({peer_id:peerIdToReject},actorPayload()));status.textContent="Participant rejected.";lastWaitingCount=-1;await refreshWaitingRoom()}catch(error){status.textContent=error.message;button.disabled=false}}
async function admitCompanyWaiting(companyId,button){button.disabled=true;try{const result=await post(urls.admit_company,Object.assign({company_id:companyId},actorPayload()));status.textContent=result.admitted+" participant(s) admitted.";lastWaitingCount=-1;await refreshWaitingRoom()}catch(error){status.textContent=error.message;button.disabled=false}}
async function admitAllWaiting(){admitAllBtn.disabled=true;try{const result=await post(urls.admit_all,actorPayload());status.textContent=result.admitted+" participant(s) admitted.";lastWaitingCount=-1;await refreshWaitingRoom()}catch(error){status.textContent=error.message}finally{admitAllBtn.disabled=false}}
async function setCoHost(peerIdToChange,promote,button){button.disabled=true;try{const permissions=promote?(configuredCoHostPermissions.length?configuredCoHostPermissions:ALL_COHOST_PERMISSIONS):[];await post(urls.co_host,Object.assign({peer_id:peerIdToChange,is_co_host:promote,permissions},actorPayload()));status.textContent=promote?"Participant is now a co-host.":"Co-host removed."}catch(error){status.textContent=error.message}finally{button.disabled=false}}
async function removeParticipant(peerIdToRemove,button){button.disabled=true;try{await post(urls.remove,Object.assign({peer_id:peerIdToRemove},actorPayload()));status.textContent="Participant removed."}catch(error){status.textContent=error.message;button.disabled=false}}
async function teardownRoom(){if(pollTimer)window.clearInterval(pollTimer);hideWaiting();if(pollTimer)window.clearInterval(pollTimer);stopLiveTranscription();try{if(recording)await stopRecording();if(screenTrack)await stopScreenShare()}catch(error){console.error(error)}for(const pc of peers.values())pc.close();peers.clear();if(ls){ls.getTracks().forEach(track=>track.stop());ls=null}lv.srcObject=null;cb.classList.remove("on");transcriptControls?.classList.remove("on");mediaGrid.style.display="none";participantPanel.classList.remove("on");document.getElementById("roomColumns").classList.remove("on");hostWaitingPanel.hidden=true;sc.disabled=true;peerId=null;peerToken=null;recordingIndicator.classList.remove("on");sessionEnding=true}
if(admitAllBtn)admitAllBtn.addEventListener("click",admitAllWaiting);
</script>
@endsection