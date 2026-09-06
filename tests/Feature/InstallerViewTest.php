<?php

namespace Xgenious\Installer\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Xgenious\Installer\Tests\TestCase;

class InstallerViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance('request', Request::create('https://example.test/install'));
        Config::set('installer.author', 'xgenious');
        Config::set('installer.website', 'https://xgenious.com');
        Config::set('installer.app_name', 'TestApp');

        // The 'installer::' view namespace and the named routes the scripts
        // partial calls route() for are both only registered by the service
        // provider when isInstallerNeeded() was true at boot time (already
        // past by the time a test runs). Register both directly here so
        // these tests can verify rendering regardless of that condition.
        $this->app['view']->addNamespace('installer', __DIR__ . '/../../resources/views');
        require __DIR__ . '/../../routes/web.php';
    }

    /** @test */
    public function the_installer_view_renders_without_error()
    {
        $html = view('installer::installer.index')->render();

        $this->assertStringContainsString('License agreement', $html);
        $this->assertStringContainsString('System readiness', $html);
        $this->assertStringContainsString('id="checklist"', $html);
        $this->assertStringContainsString('id="steplist"', $html);
        $this->assertStringContainsString('function verifyLicense', $html);
    }

    /** @test */
    public function the_local_environment_notice_only_renders_for_local_hosts()
    {
        $this->app->instance('request', Request::create('https://mystore.com/install'));
        $html = view('installer::installer.index')->render();
        $this->assertStringNotContainsString('Local/development environment detected', $html);

        $this->app->instance('request', Request::create('https://myapp.test/install'));
        $html = view('installer::installer.index')->render();
        $this->assertStringContainsString('Local/development environment detected', $html);
    }
}
