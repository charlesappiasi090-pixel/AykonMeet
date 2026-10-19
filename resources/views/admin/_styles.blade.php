<style>
/* ============ AykonMeet admin page kit ============ */
.admin-hero{position:relative;overflow:hidden;border-radius:22px;background:radial-gradient(ellipse at 88% -10%,rgba(247,201,72,.22),transparent 45%),linear-gradient(135deg,#0b1f38 0%,#112b49 52%,#1a3d66 100%);color:#fff;padding:42px 46px;margin-bottom:36px;box-shadow:0 24px 60px rgba(17,43,73,.25);display:grid;grid-template-columns:minmax(0,1.3fr) minmax(0,1fr);gap:44px;align-items:center}
.admin-hero.solo{grid-template-columns:minmax(0,1fr)}
.admin-hero::before{content:"";position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.045) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.045) 1px,transparent 1px);background-size:46px 46px;mask-image:radial-gradient(ellipse at 85% 45%,black 5%,transparent 78%);-webkit-mask-image:radial-gradient(ellipse at 85% 45%,black 5%,transparent 78%);pointer-events:none}
.admin-kicker{position:relative;z-index:2;display:inline-flex;align-items:center;gap:9px;font:700 11px/1 'Space Grotesk',sans-serif;letter-spacing:3px;text-transform:uppercase;color:#f7c948;margin:0 0 16px}
.admin-kicker .dot{width:7px;height:7px;border-radius:50%;background:#f7c948;box-shadow:0 0 0 4px rgba(247,201,72,.25)}
.admin-hero h1{position:relative;z-index:2;margin:0 0 12px;color:#fff;font:500 clamp(30px,3vw,44px)/1.05 'Fraunces',Georgia,serif;letter-spacing:-1.2px}
.admin-hero h1 em{font-style:italic;color:#f7c948}
.admin-sub{position:relative;z-index:2;margin:0;max-width:540px;color:rgba(255,255,255,.74);font-size:14.5px;line-height:1.65}
.admin-actions{position:relative;z-index:2;display:flex;flex-wrap:wrap;gap:11px;margin-top:26px}
.admin-btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;height:48px;padding:0 24px;border:0;border-radius:12px;font:700 13.5px 'DM Sans',sans-serif;cursor:pointer;text-decoration:none;transition:transform .18s ease,box-shadow .18s ease,background .18s ease}
.admin-btn i{font-style:normal;font-size:16px;transition:transform .15s}
.admin-btn:hover i{transform:translateX(4px)}
.admin-btn-gold{background:#f7c948;color:#112b49;box-shadow:0 12px 30px rgba(247,201,72,.3)}
.admin-btn-gold:hover{background:#ffd766;transform:translateY(-2px);box-shadow:0 16px 36px rgba(247,201,72,.4)}
.admin-btn-ghost{background:rgba(255,255,255,.08);color:#fff;border:1.5px solid rgba(255,255,255,.26)}
.admin-btn-ghost:hover{background:rgba(255,255,255,.16);transform:translateY(-2px)}
.admin-tiles{position:relative;z-index:2;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.admin-tile{background:rgba(255,255,255,.09);border:1px solid rgba(255,255,255,.16);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border-radius:16px;padding:16px 18px;min-width:0}
.admin-tile b{display:block;margin-bottom:7px;color:#f7c948;font:700 clamp(24px,2vw,32px)/1 'Space Grotesk',sans-serif;overflow-wrap:anywhere}
.admin-tile b.text{font-size:16px;line-height:1.25}
.admin-tile span{display:block;color:rgba(255,255,255,.72);font-size:10px;font-weight:800;letter-spacing:1.1px;text-transform:uppercase}
.admin-tile small{display:block;margin-top:4px;color:rgba(255,255,255,.5);font-size:11px;overflow-wrap:anywhere}
.admin-tile.gold{background:linear-gradient(135deg,rgba(247,201,72,.95),rgba(247,201,72,.8));border-color:rgba(247,201,72,.6)}
.admin-tile.gold b,.admin-tile.gold span{color:#112b49}
.admin-tile.gold small{color:rgba(17,43,73,.7)}
.admin-section{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;padding-bottom:14px;border-bottom:2px solid #eef2f6;position:relative;margin:0 0 22px}
.admin-section::after{content:"";position:absolute;left:0;bottom:-2px;width:60px;height:3px;background:#f7c948;border-radius:3px}
.admin-section .admin-eyebrow{margin:0 0 6px;color:#c49700;font-size:11px;font-weight:800;letter-spacing:2px;text-transform:uppercase}
.admin-section h2{margin:0;color:var(--navy);font:700 26px/1.15 'Space Grotesk',sans-serif;letter-spacing:-.5px}
.admin-chip{flex:none;background:#fff9e7;border:1px solid #f3e2ae;color:#8a6600;padding:7px 14px;border-radius:999px;font-size:12px;font-weight:800;letter-spacing:.3px}
.admin-page-panel{background:var(--white);border:1px solid #e4eaf1;border-radius:18px;padding:26px;margin-bottom:22px;box-shadow:0 10px 30px rgba(17,43,73,.05)}
.admin-panel-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:18px}
.admin-panel-head h2{margin:0;color:var(--navy);font:600 19px 'Space Grotesk',sans-serif;letter-spacing:-.2px}
.admin-panel-head p{margin:6px 0 0;color:var(--muted);font-size:13px;line-height:1.6;max-width:660px}
.admin-page-table{width:100%;border-collapse:collapse;text-align:left}
.admin-page-table th{padding:11px 12px;background:var(--paper);border-bottom:1px solid var(--line);color:var(--muted);font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;white-space:nowrap}
.admin-page-table td{padding:14px 12px;border-bottom:1px solid #eef2f6;color:var(--ink);font-size:13.5px;vertical-align:middle}
.admin-page-table tbody tr{transition:background .15s ease}
.admin-page-table tbody tr:hover{background:#fbfcfe}
.admin-page-table tr:last-child td{border-bottom:0}
.admin-page-table strong{color:var(--navy)}
.admin-page-table small{display:block;margin-top:3px;color:var(--muted);font-size:12px}
.admin-pill{display:inline-flex;align-items:center;gap:6px;padding:5px 11px;border-radius:999px;font-size:10.5px;font-weight:800;letter-spacing:.5px;text-transform:uppercase;white-space:nowrap}
.admin-pill.ok{background:#e8f6ec;color:#267344}
.admin-pill.warn{background:#fff4dd;color:#946a00}
.admin-pill.off{background:#eef2f5;color:#5b6b7d}
.admin-pill.live{background:#e3f4e8;color:#267344;box-shadow:0 0 0 3px rgba(46,204,113,.15)}
.admin-act{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:36px;padding:0 15px;border:1.5px solid var(--line);border-radius:10px;background:#fff;color:var(--navy);font:700 12px 'DM Sans',sans-serif;text-decoration:none;cursor:pointer;transition:transform .16s ease,background .16s ease,border-color .16s ease;white-space:nowrap}
.admin-act:hover{border-color:var(--navy);background:var(--paper);transform:translateY(-1px)}
.admin-act.gold{background:#f7c948;border-color:#f7c948;color:#112b49}
.admin-act.gold:hover{background:#ffd766;border-color:#ffd766}
.admin-act.navy{background:var(--navy);border-color:var(--navy);color:#f7c948}
.admin-act.navy:hover{background:#1a3d66}
.admin-page-panel .button,.admin-page-head .button,.admin-page-actions .button{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:42px;margin-top:0;padding:0 20px;border:0;border-radius:10px;font:700 13px 'DM Sans',sans-serif;text-decoration:none;cursor:pointer;transition:transform .16s ease,box-shadow .16s ease}
.admin-page-panel .button.primary,.admin-page-head .button.primary{background:linear-gradient(135deg,#112b49,#1a3d66);color:#f7c948;box-shadow:0 8px 18px rgba(17,43,73,.2)}
.admin-page-panel .button.primary:hover{transform:translateY(-1px)}
.admin-page-panel .button.secondary,.admin-page-head .button.secondary{background:#f7c948;color:#112b49;box-shadow:0 8px 18px rgba(247,201,72,.28)}
.admin-page-panel .button.secondary:hover{background:#ffd766;transform:translateY(-1px)}
.admin-form{display:grid;gap:16px;max-width:660px}
.admin-form label{display:block;color:var(--navy);font:700 11px 'Space Grotesk',sans-serif;letter-spacing:.07em;text-transform:uppercase}
.admin-form select,.admin-form input,.admin-form textarea,.admin-page-panel>form select,.admin-page-panel>form input{display:block;width:100%;margin-top:8px;min-height:46px;padding:11px 13px;border:1.5px solid #dde5ee;border-radius:10px;background:#fff;color:var(--ink);font:400 14px 'DM Sans',sans-serif;outline:none;transition:border-color .15s ease,box-shadow .15s ease}
.admin-form :focus,.admin-page-panel>form :focus{border-color:var(--navy);box-shadow:0 0 0 4px rgba(247,201,72,.28)}
.admin-page-table select{min-height:38px;padding:8px 10px;border:1.5px solid #dde5ee;border-radius:9px;background:#fff;color:var(--ink);font:600 13px 'DM Sans',sans-serif;outline:none}
.admin-page-table select:focus{border-color:var(--navy);box-shadow:0 0 0 3px rgba(247,201,72,.26)}
.admin-page-list{display:grid;gap:10px}
.admin-page-item{display:flex;justify-content:space-between;align-items:center;gap:20px;padding:16px 18px;background:var(--white);border:1px solid var(--line);border-radius:14px;transition:border-color .16s ease,box-shadow .16s ease,transform .16s ease}
.admin-page-item:hover{border-color:#f7c948;box-shadow:0 12px 26px rgba(17,43,73,.08);transform:translateY(-1px)}
.admin-page-item .admin-item-icon{flex:none;display:grid;place-items:center;width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#112b49,#1a3d66);color:#f7c948;font-size:17px}
.admin-page-item .admin-item-body{flex:1;min-width:0}
.admin-page-item strong{display:block;color:var(--navy);font-size:14px}
.admin-page-item small{display:block;margin-top:5px;color:var(--muted);font-size:12.5px}
.admin-page-empty{display:block;padding:36px 24px;text-align:center;color:var(--muted);background:var(--paper);border:1.5px dashed #d5dde7;border-radius:14px;font-size:13.5px;line-height:1.6}
.admin-page-note{padding:16px 18px;border-left:3px solid var(--yellow);border-radius:0 12px 12px 0;background:#fff9e7;color:var(--ink);font-size:13px;line-height:1.65;margin-bottom:22px}
.admin-page-list-link{flex:none;display:inline-flex;align-items:center;gap:7px;color:var(--navy);font:700 12.5px 'DM Sans',sans-serif;text-decoration:none;white-space:nowrap;transition:transform .16s ease}
.admin-page-list-link:hover{transform:translateX(3px)}
.admin-page-bars{display:grid;gap:13px}
.admin-page-bar{display:grid;grid-template-columns:minmax(90px,150px) 1fr 42px;gap:14px;align-items:center;color:var(--ink);font-size:12.5px}
.admin-page-bar strong{text-align:right;color:var(--navy);font:700 13px 'Space Grotesk',sans-serif}
.admin-page-bar-track{height:10px;background:var(--paper);border:1px solid var(--line);border-radius:999px;overflow:hidden}
.admin-page-bar-fill{height:100%;min-width:4px;border-radius:999px;background:linear-gradient(90deg,#f7c948,#f0b420);box-shadow:0 2px 6px rgba(247,201,72,.45)}
.admin-page-actions{display:flex;gap:10px;flex-wrap:wrap}
.admin-page-head{display:flex;justify-content:space-between;align-items:end;gap:24px;margin-bottom:36px}
.admin-page-head h1{margin:0;color:var(--navy);font:700 42px/1.08 "Space Grotesk",sans-serif;letter-spacing:-.7px}
.admin-page-head p:last-child{margin:10px 0 0;color:var(--muted);font-size:14px;line-height:1.6}
.admin-page-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:32px}
.admin-page-link{color:var(--navy);font-weight:700;text-decoration:none}
.admin-page-link:hover{text-decoration:underline}
@media(max-width:980px){.admin-hero{grid-template-columns:1fr;padding:34px 30px;gap:30px}}
@media(max-width:800px){.admin-page-head{align-items:flex-start;flex-direction:column}.admin-page-head h1{font-size:36px}.admin-page-stats{grid-template-columns:1fr 1fr}.admin-page-panel{padding:20px;overflow-x:auto}.admin-page-item{align-items:flex-start;flex-direction:column}.admin-page-bar{grid-template-columns:85px 1fr 34px;gap:8px}.admin-section{flex-direction:column;align-items:flex-start}}
@media(max-width:640px){.admin-hero{padding:28px 22px}.admin-tiles{grid-template-columns:1fr 1fr}}
</style>
