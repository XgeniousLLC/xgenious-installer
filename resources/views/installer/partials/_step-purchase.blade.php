@php
  $isLocalInstall = \Xgenious\Installer\Helpers\InstallationHelper::is_local_request(request());
@endphp
<section class="step-panel" data-step="3">
  <p>Tell us where you bought {{ config('installer.app_name', config('installer.author')) }}. We'll verify it and pull down your database automatically &mdash; no license key to copy or paste.</p>

  @if($isLocalInstall)
  <div class="env-notice" id="envNotice">
    <span data-icon="warn" style="flex:none; width:16px; height:16px; margin-top:2px;"></span>
    <div>
      <strong>Local/development environment detected (<span class="mono">{{ request()->getHost() }}</span>).</strong>
      <span> Verifying here is free to repeat and won't be recorded against your license. Domain-locking only happens the first time you verify on your live production domain &mdash; testing locally first is completely safe. This is detected automatically from your server address and can't be changed from this screen.</span>
    </div>
  </div>
  @endif

  <div class="seg">
    <button type="button" class="active" id="segEnvato" onclick="switchLicense('envato')">Bought on Envato</button>
    <button type="button" id="segDirect" onclick="switchLicense('direct')">Bought on {{ parse_url(config('installer.website'), PHP_URL_HOST) ?: config('installer.website') }}</button>
  </div>

  <!-- Envato pane -->
  <div class="license-pane active" id="paneEnvato">
    <div class="callout">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
      <span>Find this on your <a href="https://codecanyon.net/downloads" target="_blank" rel="noopener">Envato Downloads</a> page &rarr; License certificate.</span>
    </div>
    <div class="field">
      <label for="envUser">Envato username</label>
      <input type="text" id="envUser" placeholder="e.g. john_doe">
    </div>
    <div class="field">
      <label for="envCode">Purchase code</label>
      <input type="text" class="mono" id="envCode" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
      <div class="hint">36-character code from your Envato purchase, format above.</div>
    </div>
    <button type="button" class="btn btn-primary" id="verifyEnvatoBtn" onclick="verifyLicense('envato')">Verify with Envato</button>
  </div>

  <!-- Direct pane -->
  <div class="license-pane" id="paneDirect">
    <div class="callout">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
      <span>Your purchase code is on the order receipt we emailed you, and under <a href="{{ rtrim(config('installer.website'), '/') }}/my-account" target="_blank" rel="noopener">My Account &rarr; Orders</a>. No separate license key needed &mdash; we look it up for you.</span>
    </div>
    <div class="field">
      <label for="dirEmail">Order email</label>
      <input type="email" id="dirEmail" placeholder="you@company.com">
    </div>
    <div class="field">
      <label for="dirCode">Purchase code</label>
      <input type="text" class="mono" id="dirCode" placeholder="XGENIOUS-XXXXXXXX">
      <div class="hint">Sent by email at checkout and shown on your invoice. This also tells us your license tier &mdash; Regular, Bundle or Exclusive &mdash; so you never have to declare it yourself.</div>
    </div>
    <button type="button" class="btn btn-primary" id="verifyDirectBtn" onclick="verifyLicense('direct')">Verify with {{ config('installer.author') }}</button>
  </div>

  <div class="license-result" id="licenseResult">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
    <div>
      <strong id="licenseResultTitle"></strong>
      <span id="licenseResultMeta"></span>
    </div>
  </div>

  <div class="form-message" id="verifyMessage"></div>

  <div class="actions">
    <button class="btn btn-ghost" onclick="goTo(2)">Back</button>
    <button class="btn btn-primary" id="toStep4" onclick="goTo(4)" disabled>Continue</button>
  </div>
</section>
