<aside class="rail">
  <div class="brand">
    <div class="brand-mark">{{ strtoupper(substr(config('installer.author', 'X'), 0, 2)) }}</div>
    <div class="brand-text">
      <div class="name">{{ config('installer.author') }}</div>
      <div class="sub">Setup &amp; installation</div>
    </div>
  </div>

  <ul class="steplist" id="steplist">
    <li class="active" data-goto="1"><span class="step-num">1</span><span class="step-copy"><span class="t">License agreement</span><span class="d">Terms of use</span></span></li>
    <li data-goto="2"><span class="step-num">2</span><span class="step-copy"><span class="t">System readiness</span><span class="d">PHP, permissions, .htaccess</span></span></li>
    <li data-goto="3"><span class="step-num">3</span><span class="step-copy"><span class="t">Verify your purchase</span><span class="d">Envato or {{ config('installer.author') }}</span></span></li>
    <li data-goto="4"><span class="step-num">4</span><span class="step-copy"><span class="t">Database</span><span class="d">Connect &amp; import</span></span></li>
    <li data-goto="5"><span class="step-num">5</span><span class="step-copy"><span class="t">Admin account</span><span class="d">Your login</span></span></li>
    <li data-goto="6"><span class="step-num">6</span><span class="step-copy"><span class="t">Finish</span><span class="d">Install &amp; launch</span></span></li>
  </ul>

  <div class="rail-card">
    <div class="rail-title">Before you buy support</div>
    <a href="{{ rtrim(config('installer.website'), '/') }}/terms-of-service" target="_blank" rel="noopener">Terms of Service &#8599;</a>
    <a href="{{ rtrim(config('installer.website'), '/') }}/support-policy" target="_blank" rel="noopener">Support Policy &#8599;</a>
    <a href="{{ rtrim(config('installer.website'), '/') }}/refund-policy" target="_blank" rel="noopener">Refund Policy &#8599;</a>
    <div class="env-badge">
      <span data-icon="clock" style="width:14px; height:14px; display:inline-flex;"></span>
      Standard support replies in 2&ndash;5 business days, Sun&ndash;Thu
    </div>
  </div>
</aside>
