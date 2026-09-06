<section class="step-panel" data-step="2">
  <p>We check your server before touching anything. Whatever we can repair ourselves, we do &mdash; you'll only see manual steps for the handful of things that require access we don't have.</p>

  <div class="layout-detect" id="layoutDetect" style="display:none;">
    <span data-icon="info" style="flex:none; width:15px; height:15px; margin-top:2px; color:var(--info);"></span>
    <span id="layoutDetectText"></span>
  </div>

  <div class="check-summary">
    <div>
      <span class="count" id="checkCount">Checking your server&hellip;</span>
      <div class="check-sub" id="checkSub"></div>
    </div>
    <button class="btn btn-primary btn-sm" id="recheckBtn" onclick="autoFixAndRecheck()"><span data-icon="refresh" class="btn-icon"></span> Auto-fix &amp; re-check</button>
  </div>

  <ul class="checklist" id="checklist"></ul>

  <p class="footnote" style="margin-top:14px;">"Host required" items sit outside standard support per our <a href="{{ rtrim(config('installer.website'), '/') }}/support-policy" target="_blank" rel="noopener">Support Policy</a> &mdash; your hosting provider's support team can usually change this in under a minute.</p>

  <div class="actions">
    <button class="btn btn-ghost" onclick="goTo(1)">Back</button>
    <button class="btn btn-primary" id="toStep3" onclick="goTo(3)">Continue</button>
  </div>
</section>
