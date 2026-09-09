<aside class="rail">
  <div class="brand">
    <div class="brand-mark" aria-hidden="true">
      <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Xgenious">
        <path d="M7 14.5C7 12.2 8.9 10.2 11.2 10.2H44.8L67.6 48.4L44.8 86.2H11.2C8.9 86.2 7 84.3 7 82V77.8L31.2 48.4L7 18.8V14.5Z" fill="white"/>
        <path d="M56.8 22.8C55.6 20.6 57.2 18 59.7 18H90.3C92.8 18 94.4 20.6 93.2 22.8L75.8 53L56.8 22.8Z" fill="white"/>
        <path d="M75.8 62.8L93.2 93C94.4 95.2 92.8 97.8 90.3 97.8H59.7C57.2 97.8 55.6 95.2 56.8 93L75.8 62.8Z" fill="white"/>
      </svg>
    </div>
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
