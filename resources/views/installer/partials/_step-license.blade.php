<section class="step-panel active" data-step="1">
  <p>{{ config('installer.app_name', config('installer.author')) }} is licensed under a one-time, non-transferable license tied to the domain you install it on. Please review the key points below before continuing.</p>
  <div class="terms-box">
    <h4>Use of license</h4>
    <p style="margin:0 0 10px;">Each license (Regular, Everything Bundle, or Exclusive) grants rights for a single production domain unless your tier states otherwise. Regular and Bundle licenses are enforced by a purchase-code check; the Exclusive License removes this enforcement entirely.</p>
    <h4>Support &amp; refunds</h4>
    <p style="margin:0 0 10px;">Standard support covers product bugs for a limited period from purchase &mdash; it does not cover server, hosting, or environment configuration. Direct purchases may be refunded within the window stated in our Refund Policy; Envato purchases follow Envato's own refund policy.</p>
    <h4>Full documents</h4>
    <p style="margin:0;">Read the complete <a href="{{ rtrim(config('installer.website'), '/') }}/terms-of-service" target="_blank" rel="noopener">Terms of Service</a>, <a href="{{ rtrim(config('installer.website'), '/') }}/support-policy" target="_blank" rel="noopener">Support Policy</a> and <a href="{{ rtrim(config('installer.website'), '/') }}/refund-policy" target="_blank" rel="noopener">Refund Policy</a> before installing.</p>
  </div>
  <label class="agree-row">
    <input type="checkbox" id="agreeBox" onchange="document.getElementById('toStep2').disabled = !this.checked">
    I have read and agree to the Terms of Service, Support Policy and Refund Policy.
  </label>
  <div class="actions">
    <span></span>
    <button class="btn btn-primary" id="toStep2" disabled onclick="goTo(2)">Continue</button>
  </div>
</section>
