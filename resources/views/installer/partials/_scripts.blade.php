<script>
  const ICONS = {
    check: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>',
    warn: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01"/></svg>',
    info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>',
    refresh: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 15.3-6.4L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15.3 6.4L3 16"/><path d="M3 21v-5h5"/></svg>',
    key: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="15" r="4"/><path d="M10.8 12.2 19 4M16 7l2.5 2.5M19 4l2 2"/></svg>',
    doc: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3v5h5"/><path d="M6 3h8l5 5v13H6z"/></svg>',
    life: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3.5"/><path d="m6.34 6.34 2.83 2.83M17.66 6.34l-2.83 2.83M6.34 17.66l2.83-2.83M17.66 17.66l-2.83-2.83"/></svg>',
    undo: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10h10a5 5 0 0 1 0 10H9"/><path d="M8 6 4 10l4 4"/></svg>',
    clock: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v6l4 2"/></svg>',
  };
  function hydrateIcons(){
    document.querySelectorAll('[data-icon]').forEach(el=>{
      const name = el.getAttribute('data-icon');
      if(ICONS[name]) el.innerHTML = ICONS[name];
    });
  }

  const CSRF_TOKEN = `{{ csrf_token() }}`;
  const ROUTES = {
    checkSystem: `{{ route('installer.check-system') }}`,
    autoFix: `{{ route('installer.auto-fix') }}`,
    verifyPurchase: `{{ route('installer.verify-purchase') }}`,
    checkDatabaseExists: `{{ route('installer.check-database.exists') }}`,
    checkDatabase: `{{ route('installer.check-database') }}`,
    install: `{{ route('installer.install') }}`,
  };

  function postJSON(url, payload){
    return fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': CSRF_TOKEN,
      },
      body: JSON.stringify(payload || {}),
    }).then(r => r.json());
  }
  function getJSON(url){
    return fetch(url, {headers: {'Accept': 'application/json'}}).then(r => r.json());
  }
  // allowHtml defaults to false and must be passed explicitly per call site —
  // several messages here echo text from a third-party server response
  // (verify-purchase's `msg` comes from license.xgenious.com), so this is
  // never allowed to render as HTML unless the caller is certain the string
  // is our own trusted, hardcoded controller copy.
  function showMessage(elId, text, isSuccess, allowHtml){
    const el = document.getElementById(elId);
    if(!el) return;
    if(!text){ el.className = 'form-message'; el.textContent = ''; return; }
    if(allowHtml){ el.innerHTML = text; } else { el.textContent = text; }
    el.className = 'form-message show' + (isSuccess ? ' success' : '');
  }

  const totalSteps = 6;
  const titles = {
    1:['License agreement','Terms of use'],
    2:['System readiness','PHP, permissions, .htaccess'],
    3:['Verify your purchase','Envato or {{ config('installer.author') }}'],
    4:['Database','Connect & import'],
    5:['Admin account','Your login'],
    6:['Finish','Install & launch'],
  };

  function goTo(n){
    document.querySelectorAll('.step-panel').forEach(p => p.classList.toggle('active', +p.dataset.step === n));
    document.querySelectorAll('#steplist li').forEach(li => {
      const i = +li.dataset.goto;
      li.classList.toggle('active', i===n);
      li.classList.toggle('done', i<n);
    });
    document.getElementById('stepEyebrow').textContent = `Step ${n} of ${totalSteps}`;
    document.getElementById('stepTitle').textContent = titles[n][0];
    document.getElementById('progressFill').style.width = (n/totalSteps*100)+'%';
    window.scrollTo({top:0, behavior:'smooth'});
    if(n===2){ loadSystemChecks(); }
    if(n===6){ runInstall(); }
  }
  document.querySelectorAll('#steplist li').forEach(li=>{
    li.addEventListener('click', ()=>{
      const n = +li.dataset.goto;
      if(li.classList.contains('done') || li.classList.contains('active')) goTo(n);
    });
  });

  function copyText(btn, text){
    navigator.clipboard && navigator.clipboard.writeText(text);
    const old = btn.textContent;
    btn.textContent = 'Copied';
    setTimeout(()=>btn.textContent = old, 1200);
  }

  /* ---------------- System readiness (Step 2) ---------------- */

  let systemChecksLoaded = false;
  // Conservative default: until we hear otherwise from checkSystem, treat the
  // install as remote so the database password stays required.
  let isLocalInstall = false;
  function loadSystemChecks(){
    if(systemChecksLoaded) return;
    systemChecksLoaded = true;
    getJSON(ROUTES.checkSystem).then(data => {
      isLocalInstall = !!data.is_local;
      renderChecklist(data);
    }).catch(()=>{
      document.getElementById('checkCount').textContent = 'Could not run checks';
      document.getElementById('checkSub').textContent = 'Refresh the page to try again.';
    });
  }

  /* ---------------- Required-field enforcement ---------------- */

  document.querySelectorAll('.field input').forEach(el => {
    el.addEventListener('input', () => el.classList.remove('invalid'));
  });

  // Returns the labels of any fields left empty, marking each such input
  // with the .invalid style as a side effect.
  function markMissing(fields){
    const missing = [];
    fields.forEach(([id, label]) => {
      const el = document.getElementById(id);
      const empty = !el.value.trim();
      el.classList.toggle('invalid', empty);
      if(empty) missing.push(label);
    });
    return missing;
  }

  function autoFixAndRecheck(){
    const btn = document.getElementById('recheckBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Applying fixes&hellip;';
    postJSON(ROUTES.autoFix, {}).then(data => {
      renderChecklist(data);
    }).finally(() => {
      btn.disabled = false;
      btn.innerHTML = `<span data-icon="refresh" class="btn-icon">${ICONS.refresh}</span> Auto-fix &amp; re-check`;
    });
  }

  function checkRowHtml(state, label, meta, pillOverride, fixHtml){
    const pillMap = {pass:'Pass', warn:'Attention', fail:'Security risk', host:'Host required', pending:'Unknown'};
    const iconMap = {pass:'check', warn:'warn', fail:'warn', host:'info', pending:'info'};
    const pill = pillOverride || pillMap[state] || 'Attention';
    let html = `<li><div class="check-row st-${state}"><span class="check-icon" data-icon="${iconMap[state] || 'warn'}"></span>`
      + `<div class="check-main"><div class="label">${label}</div><div class="meta">${meta}</div></div>`
      + `<span class="pill">${pill}</span></div>`;
    if(fixHtml){
      html += `<div class="fix-panel"><div class="fix-box">${fixHtml}</div></div>`;
    }
    return html + `</li>`;
  }

  function renderChecklist(data){
    const rows = [];
    let total = 0, passing = 0, fixable = 0, hostRequired = 0;
    function tally(state){
      total++;
      if(state === 'pass') passing++;
      else if(state === 'warn' || state === 'fail') fixable++;
      else if(state === 'host') hostRequired++;
    }

    // PHP version
    {
      const state = data.php_version ? 'pass' : 'host';
      tally(state);
      rows.push(checkRowHtml(state, 'PHP version',
        data.php_version ? 'Meets the minimum required version' : 'Below the minimum required version',
        null,
        state === 'host' ? 'Ask your hosting provider to switch this domain to a newer PHP version &mdash; that can\'t be changed from inside the app.' : null));
    }

    // Extensions
    {
      const extensions = data.extensions || {};
      const missing = Object.keys(extensions).filter(k => !extensions[k]);
      const state = missing.length === 0 ? 'pass' : 'host';
      tally(state);
      rows.push(checkRowHtml(state, 'Required PHP extensions',
        missing.length === 0 ? 'All required extensions are enabled' : `Missing: ${missing.join(', ')}`,
        null,
        state === 'host' ? 'Ask your hosting provider to enable the missing extension(s) &mdash; PHP extensions can\'t be installed from inside the app.' : null));
    }

    // Writable app folders
    {
      const folders = data.folders || {};
      const bad = Object.keys(folders).filter(k => !folders[k]);
      const state = bad.length === 0 ? 'pass' : 'warn';
      tally(state);
      rows.push(checkRowHtml(state, 'Writable app folders',
        bad.length === 0 ? 'storage/ and bootstrap/cache/ are writable' : `Not writable: ${bad.join(', ')}`,
        null,
        state === 'warn' ? 'Auto-fixable &mdash; corrected to 755 automatically when you click "Auto-fix &amp; re-check."' : null));
    }

    // .htaccess
    {
      const h = data.htaccess || {};
      let state, meta, fix = null;
      if(!h.exists){
        state = 'warn'; meta = 'Missing from your project root';
        fix = 'Auto-fixable &mdash; we\'ll generate it for you.';
      } else if(!h.readable){
        state = 'warn'; meta = 'Exists, but permissions aren\'t world-readable &mdash; the web server can\'t read it';
        fix = 'Auto-fixable &mdash; this file must be world-readable (644) to work.'
          + `<div class="codeline"><code>chmod 644 .htaccess</code><button type="button" class="copy-btn" onclick="copyText(this,'chmod 644 .htaccess')">Copy</button></div>`;
      } else if(!h.hardened){
        state = 'warn'; meta = 'Exists, but is missing the dotfile/core protection rules';
        fix = 'Auto-fixable &mdash; we\'ll append the missing protection rules without touching your existing ones.';
      } else {
        state = 'pass'; meta = 'Present, readable, and protecting dotfiles &amp; internal folders';
      }
      tally(state);
      rows.push(checkRowHtml(state, 'Root .htaccess file', meta, null, fix));
    }

    // assets/ permissions (only when applicable)
    if(data.assets && data.assets.applicable){
      const state = data.assets.bad_count > 0 ? 'warn' : 'pass';
      tally(state);
      rows.push(checkRowHtml(state, 'assets/ file & folder permissions',
        state === 'warn'
          ? `${data.assets.bad_count} of ${data.assets.total} items aren't readable by the web server`
          : `All ${data.assets.total} items scanned are readable`,
        null,
        state === 'warn' ? 'Auto-fixable &mdash; folders set to 755, files set to 644, at any depth. We scan whatever actually exists under assets/, not fixed folder names.' : null));
    }

    // uploads writable (only when applicable)
    if(data.uploads && data.uploads.applicable){
      const state = data.uploads.writable ? 'pass' : 'warn';
      tally(state);
      rows.push(checkRowHtml(state, 'assets/uploads/ stays writable',
        state === 'pass' ? 'Writable &mdash; new uploads will work' : 'Not writable &mdash; new uploads would fail after install',
        null,
        state === 'warn' ? 'Auto-fixable &mdash; corrected to 755.' : null));
    }

    // .env exposure (also doubles as the AllowOverride/mod_rewrite detector)
    {
      const e = data.env_exposure || {};
      const h = data.htaccess || {};
      let state, meta, fix = null;
      if(e.exposed === true){
        if(h.hardened && h.readable){
          state = 'host';
          meta = 'Your .env file is still reachable even though .htaccess is correctly configured &mdash; your host isn\'t honoring .htaccess rules';
          fix = 'We can\'t fix this one &mdash; it lives in your server\'s Apache config (AllowOverride), outside what a PHP app can touch.'
            + `<div class="codeline"><code>AllowOverride All</code><button type="button" class="copy-btn" onclick="copyText(this,'AllowOverride All')">Copy</button></div>`
            + 'Ask your host to set this, or contact us about paid installation help.';
        } else {
          state = 'fail';
          meta = 'We requested your own .env URL and it came back exposed &mdash; fixing the .htaccess row above resolves this too';
          fix = 'Auto-fixable &mdash; fixing the .htaccess row above blocks this too. Click "Auto-fix &amp; re-check" and we re-verify by requesting the URL again ourselves.';
        }
      } else if(e.exposed === false){
        state = 'pass';
        meta = 'Confirmed blocked from public access';
      } else {
        state = 'pending';
        meta = e.error ? `Couldn't verify automatically &mdash; check manually` : 'Could not be verified automatically';
      }
      if(state === 'pending'){ total++; } else { tally(state); }
      rows.push(checkRowHtml(state, '.env is protected from public access', meta, null, fix));
    }

    // HTTPS
    {
      const state = data.is_secure ? 'pass' : 'warn';
      tally(state);
      rows.push(checkRowHtml(state, 'HTTPS', data.is_secure ? 'Valid HTTPS on this domain' : 'Not currently served over HTTPS', null,
        state === 'warn' ? 'Ask your hosting provider to enable a free SSL certificate (e.g. Let\'s Encrypt) for this domain.' : null));
    }

    document.getElementById('checklist').innerHTML = rows.join('');
    document.getElementById('checkCount').textContent = `${passing} of ${total} checks passing`;
    const subParts = [];
    if(fixable > 0) subParts.push(`${fixable} fixable automatically`);
    if(hostRequired > 0) subParts.push(`${hostRequired} need${hostRequired===1?'s':''} your hosting provider`);
    document.getElementById('checkSub').textContent = subParts.length ? subParts.join(' &middot; ') : 'All checks passing';

    const layoutDetect = document.getElementById('layoutDetect');
    if(data.assets && data.assets.applicable){
      document.getElementById('layoutDetectText').innerHTML = `Detected layout: <code>assets/</code> (${data.assets.total} items &mdash; static files &amp; uploads, served directly) alongside your application folder. Nothing above assumes fixed folder names inside <code>assets/</code> &mdash; whatever's actually there gets scanned.`;
      layoutDetect.style.display = 'flex';
    } else {
      layoutDetect.style.display = 'none';
    }

    hydrateIcons();
  }

  /* ---------------- License verification (Step 3) ---------------- */

  function switchLicense(which){
    document.getElementById('segEnvato').classList.toggle('active', which==='envato');
    document.getElementById('segDirect').classList.toggle('active', which==='direct');
    document.getElementById('paneEnvato').classList.toggle('active', which==='envato');
    document.getElementById('paneDirect').classList.toggle('active', which==='direct');
    document.getElementById('licenseResult').classList.remove('show');
    document.getElementById('toStep4').disabled = true;
    showMessage('verifyMessage', '');
  }

  function verifyLicense(which){
    const btn = document.getElementById(which==='envato' ? 'verifyEnvatoBtn' : 'verifyDirectBtn');
    const old = btn.textContent;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Verifying&hellip;';
    showMessage('verifyMessage', '');

    const payload = {
      source: which,
      en_purchase_code: which === 'envato'
        ? document.getElementById('envCode').value
        : document.getElementById('dirCode').value,
    };
    if(which === 'envato'){
      payload.en_username = document.getElementById('envUser').value;
    } else {
      payload.en_email = document.getElementById('dirEmail').value;
    }

    postJSON(ROUTES.verifyPurchase, payload).then(data => {
      btn.disabled = false;
      btn.textContent = old;
      const box = document.getElementById('licenseResult');

      if(data.type !== 'success'){
        box.classList.remove('show');
        showMessage('verifyMessage', data.msg || 'Verification failed &mdash; please check your details and try again.');
        return;
      }

      const tier = data.license_tier || (which === 'envato' ? 'Regular License' : 'Everything Bundle');
      document.getElementById('licenseResultTitle').textContent = `License verified &mdash; ${tier}`;
      document.getElementById('licenseResultMeta').textContent = data.msg || 'Verification succeeded.';
      box.classList.add('show');

      // Confirm the licensed database actually landed before letting the
      // wizard move on to Database step, reusing the existing check.
      getJSON(ROUTES.checkDatabaseExists).then(dbCheck => {
        if(dbCheck.type === 'success'){
          document.getElementById('toStep4').disabled = false;
        } else {
          showMessage('verifyMessage', dbCheck.msg || 'Verification succeeded, but the installation data file is missing.');
        }
      });
    }).catch(() => {
      btn.disabled = false;
      btn.textContent = old;
      showMessage('verifyMessage', 'Could not reach the verification server. Please try again.');
    });
  }

  /* ---------------- Database (Step 4) ---------------- */

  function collectDbFields(){
    return {
      db_driver: document.getElementById('dbDriver').value,
      db_host: document.getElementById('dbHost').value,
      db_name: document.getElementById('dbName').value,
      db_username: document.getElementById('dbUsername').value,
      db_password: document.getElementById('dbPassword').value,
    };
  }

  function testDb(){
    const btn = document.getElementById('testDbBtn');
    const old = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Testing&hellip;';
    document.getElementById('dbResult').style.display = 'none';
    showMessage('dbMessage', '');

    postJSON(ROUTES.checkDatabase, collectDbFields()).then(data => {
      btn.disabled = false;
      btn.textContent = old;
      if(data.type === 'success'){
        document.getElementById('dbResult').style.display = 'inline-flex';
      } else {
        showMessage('dbMessage', data.msg || 'Could not connect to the database.');
      }
    }).catch(() => {
      btn.disabled = false;
      btn.textContent = old;
      showMessage('dbMessage', 'Could not reach the server. Please try again.');
    });
  }

  function continueFromDb(){
    const fields = [
      ['dbHost', 'Database host'],
      ['dbName', 'Database name'],
      ['dbUsername', 'Database username'],
    ];
    // Password may only be left blank for a local installation; anything
    // reachable over the network must supply one.
    if(!isLocalInstall){
      fields.push(['dbPassword', 'Database password']);
    }

    const missing = markMissing(fields);
    if(missing.length){
      showMessage('dbMessage', `Please enter: ${missing.join(', ')}.`);
      return;
    }
    showMessage('dbMessage', '');
    goTo(5);
  }

  /* ---------------- Admin account (Step 5) ---------------- */

  function continueFromAdmin(){
    const fields = [
      ['adminName', 'Full name'],
      ['adminUsername', 'Username'],
      ['adminEmail', 'Admin email'],
      ['adminPassword', 'Password'],
    ];

    const missing = markMissing(fields);
    if(missing.length){
      showMessage('adminMessage', `Please enter: ${missing.join(', ')}.`);
      return;
    }

    const emailEl = document.getElementById('adminEmail');
    if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailEl.value.trim())){
      emailEl.classList.add('invalid');
      showMessage('adminMessage', 'Please enter a valid admin email address.');
      return;
    }

    showMessage('adminMessage', '');
    goTo(6);
  }

  /* ---------------- Finish / install (Step 6) ---------------- */

  let installStarted = false;
  function runInstall(){
    if(installStarted) return;
    installStarted = true;

    const items = document.querySelectorAll('#installLog li');
    let i = 0;
    let logDone = false;
    let installResult = null;

    function stepLog(){
      if(i>0) items[i-1].classList.replace('active','done');
      if(i < items.length){
        items[i].classList.add('active');
        i++;
        setTimeout(stepLog, 420);
      } else {
        logDone = true;
        maybeFinish();
      }
    }

    function maybeFinish(){
      if(!logDone || installResult === null) return;
      document.getElementById('installLog').style.display = 'none';

      if(installResult.type !== 'success'){
        showMessage('installError', installResult.msg || 'Installation failed. Please check your details and try again.');
        return;
      }

      document.getElementById('successBlock').style.display = 'block';

      if(installResult.tenant_note){
        // Safe to render as HTML: this is our own hardcoded controller
        // string (a wildcard-subdomain doc link), never user- or
        // third-party-supplied.
        showMessage('tenantNote', installResult.tenant_note, false, true);
      }

      hydrateIcons();
    }

    const payload = Object.assign({}, collectDbFields(), {
      admin_name: document.getElementById('adminName').value,
      admin_username: document.getElementById('adminUsername').value,
      admin_email: document.getElementById('adminEmail').value,
      admin_password: document.getElementById('adminPassword').value,
    });

    stepLog();
    postJSON(ROUTES.install, payload).then(data => {
      installResult = data;
      maybeFinish();
    }).catch(() => {
      installResult = {type: 'danger', msg: 'Could not reach the server. Please try again.'};
      maybeFinish();
    });
  }

  hydrateIcons();
</script>
