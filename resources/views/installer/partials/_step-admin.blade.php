<section class="step-panel" data-step="5">
  <p>This becomes your super-admin login once installation finishes.</p>
  <div class="field-row">
    <div class="field"><label for="adminName">Full name</label><input type="text" id="adminName" placeholder="Jane Cooper"></div>
    <div class="field"><label for="adminUsername">Username</label><input type="text" id="adminUsername" value="super_admin"></div>
  </div>
  <div class="field-row">
    <div class="field"><label for="adminEmail">Admin email</label><input type="email" id="adminEmail" placeholder="jane@yourcompany.com"></div>
    <div class="field"><label for="adminPassword">Password</label><input type="password" id="adminPassword" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"></div>
  </div>
  <div class="form-message" id="adminMessage"></div>
  <div class="actions">
    <button class="btn btn-ghost" onclick="goTo(4)">Back</button>
    <button class="btn btn-primary" id="installNowBtn" onclick="continueFromAdmin()">Install now</button>
  </div>
</section>
