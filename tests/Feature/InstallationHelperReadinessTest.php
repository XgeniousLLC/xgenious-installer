<?php

namespace Xgenious\Installer\Tests\Feature;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Xgenious\Installer\Helpers\InstallationHelper;
use Xgenious\Installer\Tests\TestCase;

class InstallationHelperReadinessTest extends TestCase
{
    protected function tearDown(): void
    {
        $htaccess = base_path('../.htaccess');
        if (File::exists($htaccess)) {
            @chmod($htaccess, 0644);
            File::delete($htaccess);
        }

        $assets = base_path('../test-assets');
        if (File::isDirectory($assets)) {
            @chmod($assets, 0755);
            File::deleteDirectory($assets);
        }

        Config::set('installer.assets_dir', 'assets');

        parent::tearDown();
    }

    /** @test */
    public function htaccess_status_reports_missing_file()
    {
        $status = InstallationHelper::htaccess_status();

        $this->assertFalse($status['exists']);
        $this->assertFalse($status['readable']);
        $this->assertFalse($status['hardened']);
    }

    /** @test */
    public function htaccess_status_flags_unreadable_permissions()
    {
        $path = base_path('../.htaccess');
        File::put($path, 'RewriteRule ^core/ - [F,L]');
        chmod($path, 0600);

        $status = InstallationHelper::htaccess_status();

        $this->assertTrue($status['exists']);
        $this->assertFalse($status['readable']);
    }

    /** @test */
    public function htaccess_status_flags_stale_unhardened_content()
    {
        $path = base_path('../.htaccess');
        File::put($path, "RewriteEngine On\nRewriteRule ^ index.php [L]");
        chmod($path, 0644);

        $status = InstallationHelper::htaccess_status();

        $this->assertTrue($status['exists']);
        $this->assertTrue($status['readable']);
        $this->assertFalse($status['hardened']);
    }

    /** @test */
    public function htaccess_status_passes_for_real_shipped_sample()
    {
        $path = base_path('../.htaccess');
        File::copy(__DIR__ . '/../../htaccess-sample.txt', $path);
        chmod($path, 0644);

        $status = InstallationHelper::htaccess_status();

        $this->assertTrue($status['exists']);
        $this->assertTrue($status['readable']);
        $this->assertTrue($status['hardened']);
    }

    /** @test */
    public function fix_htaccess_corrects_permissions_without_deleting_custom_rules()
    {
        $path = base_path('../.htaccess');
        File::put($path, "# My custom rule\nRewriteRule ^core/ - [F,L]\nRewriteRule (^|/)\\. - [F]");
        chmod($path, 0600);

        $status = InstallationHelper::fix_htaccess();

        $this->assertTrue($status['exists']);
        $this->assertTrue($status['readable']);
        $this->assertTrue($status['hardened']);
        $this->assertStringContainsString('My custom rule', File::get($path));
    }

    /** @test */
    public function fix_htaccess_generates_file_when_missing()
    {
        $this->assertFalse(File::exists(base_path('../.htaccess')));

        $status = InstallationHelper::fix_htaccess();

        $this->assertTrue($status['exists']);
    }

    /** @test */
    public function assets_permission_status_is_not_applicable_when_directory_absent()
    {
        Config::set('installer.assets_dir', 'this-does-not-exist');

        $status = InstallationHelper::assets_permission_status();
        $this->assertFalse($status['applicable']);
        $this->assertSame(0, $status['bad_count']);

        $fixed = InstallationHelper::fix_assets_permissions();
        $this->assertFalse($fixed['applicable']);
    }

    /** @test */
    public function assets_permission_status_finds_and_fixes_bad_permissions_at_any_depth()
    {
        Config::set('installer.assets_dir', 'test-assets');
        $root = base_path('../test-assets');
        File::makeDirectory($root . '/js', 0755, true);
        File::put($root . '/js/app.js', 'console.log(1);');
        chmod($root . '/js/app.js', 0600);
        chmod($root . '/js', 0700);

        $status = InstallationHelper::assets_permission_status();
        $this->assertTrue($status['applicable']);
        $this->assertGreaterThan(0, $status['bad_count']);

        $fixed = InstallationHelper::fix_assets_permissions();
        $this->assertSame(0, $fixed['bad_count']);
    }

    /** @test */
    public function uploads_permission_status_and_fix()
    {
        Config::set('installer.assets_dir', 'test-assets');
        $uploads = base_path('../test-assets/uploads');
        File::makeDirectory($uploads, 0755, true);
        chmod($uploads, 0555); // read + execute only — no write bit for anyone, including the owner

        $status = InstallationHelper::uploads_permission_status();
        $this->assertTrue($status['applicable']);
        $this->assertFalse($status['writable']);

        $fixed = InstallationHelper::fix_uploads_permission();
        $this->assertTrue($fixed['writable']);
    }

    /** @test */
    public function uploads_permission_status_is_not_applicable_without_assets_dir()
    {
        Config::set('installer.assets_dir', 'this-does-not-exist');

        $status = InstallationHelper::uploads_permission_status();

        $this->assertFalse($status['applicable']);
    }

    /** @test */
    public function env_exposure_status_detects_a_leaking_env_file()
    {
        Http::fake([
            '*' => Http::response("APP_NAME=Test\nAPP_KEY=base64:abc\nDB_PASSWORD=secret", 200),
        ]);

        $status = InstallationHelper::env_exposure_status();

        $this->assertTrue($status['exposed']);
        $this->assertNull($status['error']);
    }

    /** @test */
    public function env_exposure_status_treats_a_blocked_response_as_safe()
    {
        Http::fake([
            '*' => Http::response('Forbidden', 403),
        ]);

        $status = InstallationHelper::env_exposure_status();

        $this->assertFalse($status['exposed']);
    }

    /** @test */
    public function env_exposure_status_ignores_200_responses_that_dont_look_like_env_content()
    {
        Http::fake([
            '*' => Http::response('<html>not found</html>', 200),
        ]);

        $status = InstallationHelper::env_exposure_status();

        $this->assertFalse($status['exposed']);
    }

    /** @test */
    public function env_exposure_status_fails_safe_on_connection_error()
    {
        Http::fake(function () {
            throw new ConnectionException('could not connect');
        });

        $status = InstallationHelper::env_exposure_status();

        $this->assertNull($status['exposed']);
        $this->assertNotNull($status['error']);
    }

    /** @test */
    public function is_local_host_recognizes_local_and_private_addresses()
    {
        $this->assertTrue(InstallationHelper::is_local_host('localhost'));
        $this->assertTrue(InstallationHelper::is_local_host('127.0.0.1'));
        $this->assertTrue(InstallationHelper::is_local_host('myapp.test'));
        $this->assertTrue(InstallationHelper::is_local_host('myapp.local'));
        $this->assertTrue(InstallationHelper::is_local_host('192.168.1.50'));
        $this->assertTrue(InstallationHelper::is_local_host('10.0.0.5'));
        $this->assertTrue(InstallationHelper::is_local_host('172.16.0.5'));
        $this->assertFalse(InstallationHelper::is_local_host('example.com'));
        $this->assertFalse(InstallationHelper::is_local_host('172.32.0.5'));
    }

    /** @test */
    public function is_local_request_reads_the_request_host()
    {
        $local = Request::create('https://myapp.test/install');
        $this->assertTrue(InstallationHelper::is_local_request($local));

        $production = Request::create('https://mystore.com/install');
        $this->assertFalse(InstallationHelper::is_local_request($production));
    }
}
