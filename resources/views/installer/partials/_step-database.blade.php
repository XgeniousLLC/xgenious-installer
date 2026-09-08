<section class="step-panel" data-step="4">
  <p>Enter the database {{ config('installer.app_name', config('installer.author')) }} should use. We'll test the connection before importing anything.</p>
  <input type="hidden" id="dbDriver" value="{{ config('installer.database_type', 'mysql') }}">
  <div class="field-row">
    <div class="field"><label for="dbHost">Database host</label><input type="text" id="dbHost" value="127.0.0.1"></div>
    <div class="field"><label for="dbPort">Database port</label><input type="text" id="dbPort" value="{{ config('installer.database_type') === 'pgsql' ? 5432 : 3306 }}"></div>
  </div>
  <div class="field-row">
    <div class="field"><label for="dbName">Database name</label><input type="text" id="dbName" placeholder="database_name"></div>
    <div class="field"><label for="dbUsername">Database username</label><input type="text" id="dbUsername" placeholder="database_user"></div>
  </div>
  <div class="field"><label for="dbPassword">Database password</label><input type="password" id="dbPassword" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"></div>
  <button type="button" class="btn btn-ghost" id="testDbBtn" onclick="testDb()">Test connection</button>
  <span id="dbResult" style="margin-left:10px; font-size:13px; color:var(--success); display:none; align-items:center; gap:5px;"><span data-icon="check" style="width:13px; height:13px; display:inline-flex;"></span>Connected successfully</span>
  <div class="form-message" id="dbMessage"></div>
  <div class="actions">
    <button class="btn btn-ghost" onclick="goTo(3)">Back</button>
    <button class="btn btn-primary" onclick="continueFromDb()">Continue</button>
  </div>
</section>
