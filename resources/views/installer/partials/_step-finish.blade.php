<section class="step-panel" data-step="6">
  <ul class="install-log" id="installLog">
    <li data-i="0"><span class="dot"></span> Writing configuration (.env)</li>
    <li data-i="1"><span class="dot"></span> Generating .htaccess</li>
    <li data-i="2"><span class="dot"></span> Creating database schema</li>
    <li data-i="3"><span class="dot"></span> Importing your licensed data</li>
    <li data-i="4"><span class="dot"></span> Creating admin account</li>
    <li data-i="5"><span class="dot"></span> Clearing caches</li>
  </ul>

  <div class="form-message" id="installError"></div>

  <div id="successBlock" style="display:none;">
    <div class="success-hero">
      <div class="success-badge"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6 9 17l-5-5"/></svg></div>
      <h2 style="font-size:20px;">{{ config('installer.app_name', config('installer.author')) }} is installed</h2>
      <p>Your admin login was sent to your inbox as a backup. You can sign in right away.</p>
    </div>

    <div class="form-message" id="tenantNote" style="text-align:left;"></div>

    <div class="next-links">
      <a href="{{ url('/') }}"><div class="nl-t"><span class="nl-icon" data-icon="key"></span>Go to admin panel</div><div class="nl-d">Sign in with the account you just created</div></a>
      <a href="{{ rtrim(config('installer.website'), '/') }}/support-policy" target="_blank" rel="noopener"><div class="nl-t"><span class="nl-icon" data-icon="life"></span>Get support</div><div class="nl-d">See what's covered and how to reach us</div></a>
    </div>
  </div>
</section>
