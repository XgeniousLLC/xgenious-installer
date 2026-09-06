<style>
  :root{
    --bg:#F5F6F1;
    --surface:#FFFFFF;
    --surface-2:#ECEFE5;
    --border:#DCE1D3;
    --ink:#1E2318;
    --ink-muted:#5B6355;
    --ink-faint:#8B937E;
    --accent:#4C7A1E;
    --accent-strong:#3C6116;
    --accent-soft:#E4EEDA;
    --accent-ink:#20330C;
    --success:#2E7D46;
    --success-soft:#E1F3E3;
    --success-ink:#173C22;
    --warning:#96631B;
    --warning-soft:#FBEBD2;
    --warning-ink:#4A3009;
    --danger:#B23B24;
    --danger-soft:#FBE2DC;
    --danger-ink:#5C1D10;
    --info:#3E6E8C;
    --info-soft:#DEEAF0;
    --info-ink:#1D3C4E;
    --code-bg:#1B1F15;
    --code-ink:#DCE6CF;
    --code-accent:#9ED26A;
    --shadow:0 1px 2px rgba(30,35,24,0.04), 0 12px 32px -16px rgba(30,35,24,0.18);
    --focus:#4C7A1E;
  }
  @media (prefers-color-scheme: dark){
    :root:not([data-theme="light"]){
      --bg:#14170F;
      --surface:#1B2015;
      --surface-2:#22271A;
      --border:#333B29;
      --ink:#EDF0E5;
      --ink-muted:#AAB29C;
      --ink-faint:#78806B;
      --accent:#8FC94A;
      --accent-strong:#A8DA63;
      --accent-soft:#26331A;
      --accent-ink:#DCF0BE;
      --success:#6FCB8B;
      --success-soft:#1C3324;
      --success-ink:#C4EED0;
      --warning:#E3B15C;
      --warning-soft:#3A2E13;
      --warning-ink:#F5DCA9;
      --danger:#E28268;
      --danger-soft:#3A2019;
      --danger-ink:#F6C8B9;
      --info:#7FB6D6;
      --info-soft:#1B2E38;
      --info-ink:#CFE7F5;
      --code-bg:#0F110B;
      --code-ink:#DCE6CF;
      --code-accent:#9ED26A;
      --shadow:0 1px 2px rgba(0,0,0,0.3), 0 20px 40px -20px rgba(0,0,0,0.6);
      --focus:#A8DA63;
    }
  }
  :root[data-theme="dark"]{
    --bg:#14170F;
    --surface:#1B2015;
    --surface-2:#22271A;
    --border:#333B29;
    --ink:#EDF0E5;
    --ink-muted:#AAB29C;
    --ink-faint:#78806B;
    --accent:#8FC94A;
    --accent-strong:#A8DA63;
    --accent-soft:#26331A;
    --accent-ink:#DCF0BE;
    --success:#6FCB8B;
    --success-soft:#1C3324;
    --success-ink:#C4EED0;
    --warning:#E3B15C;
    --warning-soft:#3A2E13;
    --warning-ink:#F5DCA9;
    --danger:#E28268;
    --danger-soft:#3A2019;
    --danger-ink:#F6C8B9;
    --code-bg:#0F110B;
    --code-ink:#DCE6CF;
    --code-accent:#9ED26A;
    --shadow:0 1px 2px rgba(0,0,0,0.3), 0 20px 40px -20px rgba(0,0,0,0.6);
    --focus:#A8DA63;
  }

  *{box-sizing:border-box;}
  html,body{margin:0;padding:0;}
  body{
    background:var(--bg);
    color:var(--ink);
    font-family:'IBM Plex Sans', ui-sans-serif, system-ui, sans-serif;
    font-size:15px;
    line-height:1.55;
    -webkit-font-smoothing:antialiased;
  }
  h1,h2,h3,h4{
    font-family:'Sora', ui-sans-serif, system-ui, sans-serif;
    font-weight:600;
    color:var(--ink);
    margin:0 0 6px;
    text-wrap:balance;
  }
  p{margin:0 0 12px;color:var(--ink-muted);}
  a{color:var(--accent-strong);}
  code, .mono, input.mono{font-family:'IBM Plex Mono', ui-monospace, SFMono-Regular, Menlo, monospace;}
  button{font-family:inherit;}
  *:focus-visible{outline:2px solid var(--focus); outline-offset:2px;}

  .shell{
    max-width:1180px;
    margin:0 auto;
    padding:32px 24px 64px;
    display:grid;
    grid-template-columns:272px 1fr;
    gap:28px;
    align-items:start;
  }
  @media (max-width: 880px){
    .shell{grid-template-columns:1fr; padding:20px 14px 48px;}
  }

  /* ---------- Sidebar ---------- */
  .rail{
    position:sticky;
    top:24px;
    display:flex;
    flex-direction:column;
    gap:20px;
  }
  .brand{
    display:flex;
    align-items:center;
    gap:10px;
    padding:2px 2px 4px;
  }
  .brand-mark{
    width:34px;height:34px;border-radius:9px;
    background:linear-gradient(155deg, var(--accent) 0%, var(--accent-strong) 100%);
    display:flex;align-items:center;justify-content:center;
    color:#fff; font-family:'Sora',sans-serif; font-weight:700; font-size:14px; letter-spacing:.02em;
    flex:none;
    box-shadow:var(--shadow);
  }
  .brand-text .name{font-family:'Sora',sans-serif; font-weight:600; font-size:15px; color:var(--ink); text-transform:capitalize;}
  .brand-text .sub{font-size:12px; color:var(--ink-faint);}

  .steplist{
    list-style:none; margin:0; padding:0;
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:14px;
    padding:6px;
    box-shadow:var(--shadow);
  }
  .steplist li{
    display:flex; align-items:flex-start; gap:11px;
    padding:11px 12px;
    border-radius:10px;
    cursor:pointer;
    position:relative;
  }
  .steplist li + li{margin-top:1px;}
  .steplist li:hover{background:var(--surface-2);}
  .step-num{
    flex:none; width:24px;height:24px; border-radius:50%;
    background:var(--surface-2); color:var(--ink-faint);
    display:flex; align-items:center; justify-content:center;
    font-family:'IBM Plex Mono',monospace; font-size:11.5px;
    border:1px solid var(--border);
    margin-top:1px;
  }
  .steplist li.done .step-num{background:var(--success-soft); color:var(--success); border-color:transparent;}
  .steplist li.active .step-num{background:var(--accent); color:#fff; border-color:transparent;}
  .step-copy .t{font-size:13.5px; font-weight:600; color:var(--ink-muted);}
  .steplist li.active .step-copy .t{color:var(--ink);}
  .steplist li.done .step-copy .t{color:var(--ink);}
  .step-copy .d{font-size:11.5px; color:var(--ink-faint); margin-top:1px;}

  .rail-card{
    background:var(--surface-2);
    border:1px solid var(--border);
    border-radius:14px;
    padding:14px 15px;
    font-size:12.5px;
    color:var(--ink-muted);
  }
  .rail-card .rail-title{font-size:11.5px; text-transform:uppercase; letter-spacing:.06em; color:var(--ink-faint); font-weight:600; margin-bottom:8px;}
  .rail-card a{display:block; color:var(--ink-muted); text-decoration:none; padding:3px 0;}
  .rail-card a:hover{color:var(--accent-strong);}
  .rail-card .env-badge{
    display:inline-flex; align-items:center; gap:6px;
    font-size:11.5px; color:var(--ink-faint); margin-top:10px; padding-top:10px; border-top:1px dashed var(--border);
  }

  /* ---------- Main panel ---------- */
  .panel{
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:18px;
    box-shadow:var(--shadow);
    overflow:hidden;
  }
  .panel-top{
    padding:20px 28px;
    border-bottom:1px solid var(--border);
    display:flex; align-items:center; justify-content:space-between; gap:16px;
  }
  .progress-track{height:6px; background:var(--surface-2); border-radius:99px; overflow:hidden; margin-top:12px;}
  .progress-fill{height:100%; background:linear-gradient(90deg, var(--accent), var(--accent-strong)); border-radius:99px; transition:width .35s ease;}
  .eyebrow{font-size:11.5px; text-transform:uppercase; letter-spacing:.08em; color:var(--ink-faint); font-weight:600;}
  .panel-body{padding:30px 32px 28px;}
  @media (max-width:600px){.panel-body{padding:22px 18px;} .panel-top{padding:18px 18px;}}

  .step-panel{display:none;}
  .step-panel.active{display:block; animation:rise .28s ease;}
  @keyframes rise{from{opacity:0; transform:translateY(6px);} to{opacity:1; transform:translateY(0);}}
  @media (prefers-reduced-motion: reduce){ .step-panel.active{animation:none;} }

  .field{margin-bottom:16px;}
  .field label{display:block; font-size:13px; font-weight:600; color:var(--ink); margin-bottom:6px;}
  .field .hint{font-size:12px; color:var(--ink-faint); margin-top:5px;}
  .field input, .field select{
    width:100%; padding:10px 12px; border-radius:9px;
    border:1px solid var(--border); background:var(--bg); color:var(--ink);
    font-size:14px;
  }
  .field input:focus, .field select:focus{border-color:var(--accent);}
  .field-row{display:grid; grid-template-columns:1fr 1fr; gap:14px;}
  @media (max-width:560px){.field-row{grid-template-columns:1fr;}}

  .btn{
    display:inline-flex; align-items:center; gap:8px;
    padding:10px 18px; border-radius:9px; border:1px solid transparent;
    font-size:13.5px; font-weight:600; cursor:pointer; white-space:nowrap;
  }
  .btn-primary{background:var(--accent); color:#fff;}
  .btn-primary:hover{background:var(--accent-strong);}
  .btn-primary:disabled{opacity:.5; cursor:not-allowed;}
  .btn-ghost{background:transparent; color:var(--ink-muted); border-color:var(--border);}
  .btn-ghost:hover{color:var(--ink); border-color:var(--ink-faint);}
  .btn-sm{padding:6px 12px; font-size:12.5px; border-radius:7px;}
  .actions{display:flex; justify-content:space-between; align-items:center; margin-top:26px; padding-top:20px; border-top:1px solid var(--border);}
  .spinner{width:14px;height:14px;border-radius:50%;border:2px solid rgba(255,255,255,.4); border-top-color:#fff; animation:spin .7s linear infinite;}
  @keyframes spin{to{transform:rotate(360deg);}}

  /* ---------- Checklist (requirements) ---------- */
  .check-summary{
    display:flex; align-items:center; justify-content:space-between;
    background:var(--surface-2); border:1px solid var(--border); border-radius:12px;
    padding:12px 16px; margin-bottom:16px;
  }
  .check-summary .count{font-family:'Sora',sans-serif; font-weight:600; font-size:14px;}
  .check-sub{font-size:12px; color:var(--ink-faint); margin-top:2px;}
  .btn-icon{display:inline-flex; width:14px; height:14px;}
  .btn-icon svg{width:100%; height:100%;}
  .checklist{list-style:none; margin:0 0 8px; padding:0; border:1px solid var(--border); border-radius:12px; overflow:hidden;}
  .checklist:empty{display:none;}
  .checklist > li{border-top:1px solid var(--border);}
  .checklist > li:first-child{border-top:none;}
  .check-row{display:flex; align-items:center; gap:12px; padding:13px 16px; background:var(--surface);}
  .check-icon{flex:none; width:22px;height:22px;border-radius:50%; display:flex; align-items:center; justify-content:center;}
  .check-icon svg{width:13px;height:13px;}
  .st-pass .check-icon{background:var(--success-soft); color:var(--success);}
  .st-warn .check-icon{background:var(--warning-soft); color:var(--warning);}
  .st-fail .check-icon{background:var(--danger-soft); color:var(--danger);}
  .st-pending .check-icon{background:var(--surface-2); color:var(--ink-faint);}
  .st-host .check-icon{background:var(--info-soft); color:var(--info);}
  .check-main{flex:1; min-width:0;}
  .check-main .label{font-size:13.5px; font-weight:600;}
  .check-main .meta{font-size:12px; color:var(--ink-faint); margin-top:1px;}
  .pill{font-size:11px; font-weight:600; padding:3px 9px; border-radius:99px; flex:none;}
  .st-pass .pill{background:var(--success-soft); color:var(--success-ink);}
  .st-warn .pill{background:var(--warning-soft); color:var(--warning-ink);}
  .st-fail .pill{background:var(--danger-soft); color:var(--danger-ink);}
  .st-pending .pill{background:var(--surface-2); color:var(--ink-faint);}
  .st-host .pill{background:var(--info-soft); color:var(--info-ink);}
  .fix-panel{
    padding:0 16px 15px 50px; background:var(--surface); font-size:13px; color:var(--ink-muted);
  }
  .fix-panel .fix-box{background:var(--warning-soft); border:1px solid transparent; border-radius:10px; padding:12px 14px;}
  .st-fail .fix-panel .fix-box{background:var(--danger-soft);}
  .st-host .fix-panel .fix-box{background:var(--info-soft);}
  .fix-box .fix-title{font-weight:600; color:var(--ink); font-size:12.5px; margin-bottom:6px;}
  .codeline{
    display:flex; align-items:center; justify-content:space-between; gap:10px;
    background:var(--code-bg); color:var(--code-ink);
    border-radius:8px; padding:9px 11px; margin-top:8px;
    font-size:12.5px; overflow-x:auto;
  }
  .codeline code{color:var(--code-accent); white-space:pre;}
  .copy-btn{
    flex:none; background:rgba(255,255,255,.08); color:var(--code-ink); border:1px solid rgba(255,255,255,.15);
    border-radius:6px; padding:4px 9px; font-size:11px; cursor:pointer;
  }
  .copy-btn:hover{background:rgba(255,255,255,.16);}

  /* ---------- License step ---------- */
  .seg{
    display:flex; gap:6px; background:var(--surface-2); padding:5px; border-radius:11px; margin-bottom:20px;
  }
  .seg button{
    flex:1; padding:10px 10px; border-radius:8px; border:none; background:transparent;
    color:var(--ink-muted); font-weight:600; font-size:13px; cursor:pointer;
  }
  .seg button.active{background:var(--surface); color:var(--ink); box-shadow:var(--shadow);}
  .license-pane{display:none;}
  .license-pane.active{display:block;}
  .callout{
    display:flex; gap:11px; padding:12px 14px; border-radius:11px; background:var(--accent-soft); margin-bottom:18px; font-size:13px; color:var(--accent-ink);
  }
  .callout svg{flex:none; margin-top:2px;}
  .callout a{color:inherit; text-decoration:underline;}
  .env-notice{
    display:flex; gap:11px; padding:12px 14px; border-radius:11px; background:var(--warning-soft); margin-bottom:18px; font-size:13px; color:var(--warning-ink);
  }
  .env-notice strong{color:inherit;}
  .layout-detect{
    display:flex; gap:10px; padding:11px 14px; border-radius:11px; background:var(--info-soft); margin-bottom:16px; font-size:12.5px; color:var(--info-ink); line-height:1.5;
  }
  .layout-detect code{background:rgba(0,0,0,.06); padding:1px 5px; border-radius:5px;}
  :root[data-theme="dark"] .layout-detect code{background:rgba(255,255,255,.08);}
  @media (prefers-color-scheme: dark){ :root:not([data-theme="light"]) .layout-detect code{background:rgba(255,255,255,.08);} }
  .license-result{
    display:none; align-items:center; gap:12px; margin-top:16px; padding:13px 15px;
    background:var(--success-soft); border-radius:11px; color:var(--success-ink); font-size:13.5px;
  }
  .license-result.show{display:flex;}
  .license-result strong{display:block; font-size:14px;}
  .form-message{
    display:none; margin-top:14px; padding:12px 14px; border-radius:11px; background:var(--danger-soft); color:var(--danger-ink); font-size:13px;
  }
  .form-message.show{display:block;}
  .form-message.success{background:var(--success-soft); color:var(--success-ink);}

  /* ---------- Finish ---------- */
  .install-log{border:1px solid var(--border); border-radius:12px; overflow:hidden; margin:18px 0;}
  .install-log li{list-style:none; display:flex; align-items:center; gap:11px; padding:11px 16px; font-size:13px; border-top:1px solid var(--border);}
  .install-log li:first-child{border-top:none;}
  .install-log .dot{width:8px;height:8px;border-radius:50%;background:var(--border); flex:none;}
  .install-log li.done .dot{background:var(--success);}
  .install-log li.active .dot{background:var(--accent); animation:pulse 1s infinite;}
  @keyframes pulse{0%,100%{opacity:1;}50%{opacity:.35;}}
  .install-log li.done{color:var(--ink-muted);}
  .success-hero{text-align:center; padding:12px 0 8px;}
  .success-badge{
    width:56px;height:56px;border-radius:50%;background:var(--success-soft); color:var(--success);
    display:flex;align-items:center;justify-content:center;margin:0 auto 16px;
  }
  .next-links{display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:20px;}
  @media (max-width:560px){.next-links{grid-template-columns:1fr;}}
  .next-links a{
    display:block; padding:14px 15px; border:1px solid var(--border); border-radius:12px;
    text-decoration:none; color:var(--ink); font-size:13px;
  }
  .next-links a:hover{border-color:var(--accent);}
  .next-links .nl-t{font-weight:600; display:flex; align-items:center; gap:8px; margin-bottom:3px;}
  .nl-icon{flex:none; width:16px; height:16px; color:var(--accent-strong);}
  .nl-icon svg{width:100%; height:100%;}
  .next-links .nl-d{color:var(--ink-faint); font-size:12px;}

  .footnote{margin-top:22px; font-size:11.5px; color:var(--ink-faint); text-align:center;}
  .footnote a{color:var(--ink-faint); text-decoration:underline;}

  .terms-box{
    max-height:230px; overflow-y:auto; border:1px solid var(--border); border-radius:12px;
    padding:16px 18px; background:var(--surface-2); font-size:13px; color:var(--ink-muted); margin-bottom:16px;
  }
  .terms-box h4{font-size:13px; margin:14px 0 4px;}
  .terms-box h4:first-child{margin-top:0;}
  .agree-row{display:flex; align-items:flex-start; gap:10px; font-size:13px; color:var(--ink); cursor:pointer;}
  .agree-row input{margin-top:3px;}
</style>
