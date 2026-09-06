<?php

namespace Xgenious\Installer\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Xgenious\Installer\Helpers\InstallationHelper;
use Xgenious\Installer\Http\Controllers\InstallerController;
use Xgenious\Installer\Http\Middleware\InstallerMiddleware;
use Xgenious\Installer\Tests\TestCase;

class InstallerControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance('request', Request::create('https://example.test/install'));
    }

    protected function tearDown(): void
    {
        $htaccess = base_path('../.htaccess');
        if (File::exists($htaccess)) {
            @chmod($htaccess, 0644);
            File::delete($htaccess);
        }

        parent::tearDown();
    }

    /** @test */
    public function verify_purchase_requires_a_username_for_the_envato_source()
    {
        $controller = new InstallerController();
        $request = Request::create('/install/verify-purchase', 'POST', [
            'source' => 'envato',
            'en_purchase_code' => 'abc-123',
        ]);

        $response = $controller->verifyPurchase($request);
        $data = $response->getData(true);

        $this->assertSame('danger', $data['type']);
    }

    /** @test */
    public function verify_purchase_does_not_require_a_username_for_the_direct_source()
    {
        Config::set('installer.product_key', 'test-key');
        Http::fake([
            'license.xgenious.com/*' => Http::response(['verify' => true, 'msg' => 'ok'], 200),
        ]);

        $controller = new InstallerController();
        $request = Request::create('/install/verify-purchase', 'POST', [
            'source' => 'direct',
            'en_email' => 'buyer@example.com',
            'en_purchase_code' => 'XGENIOUS-000001',
        ]);

        $response = $controller->verifyPurchase($request);
        $data = $response->getData(true);

        $this->assertSame('success', $data['type']);
    }

    /** @test */
    public function verify_purchase_forwards_the_source_to_the_license_server()
    {
        Config::set('installer.product_key', 'test-key');
        Http::fake([
            'license.xgenious.com/*' => Http::response(['verify' => true, 'msg' => 'ok'], 200),
        ]);

        $controller = new InstallerController();
        $request = Request::create('/install/verify-purchase', 'POST', [
            'source' => 'direct',
            'en_email' => 'buyer@example.com',
            'en_purchase_code' => 'XGENIOUS-000001',
        ]);
        $controller->verifyPurchase($request);

        Http::assertSent(function ($request) {
            return $request['source'] === 'direct';
        });
    }

    /** @test */
    public function verify_purchase_surfaces_optional_license_tier_from_the_json_response()
    {
        Config::set('installer.product_key', 'test-key');
        Http::fake([
            'license.xgenious.com/*' => Http::response([
                'verify' => true,
                'msg' => 'ok',
                'license_tier' => 'Exclusive',
            ], 200),
        ]);

        $controller = new InstallerController();
        $request = Request::create('/install/verify-purchase', 'POST', [
            'source' => 'direct',
            'en_email' => 'buyer@example.com',
            'en_purchase_code' => 'XGENIOUS-000001',
        ]);
        $data = $controller->verifyPurchase($request)->getData(true);

        $this->assertSame('Exclusive', $data['license_tier']);
    }

    /** @test */
    public function verify_purchase_does_not_error_when_license_tier_is_absent()
    {
        Config::set('installer.product_key', 'test-key');
        Http::fake([
            'license.xgenious.com/*' => Http::response(['verify' => true, 'msg' => 'ok'], 200),
        ]);

        $controller = new InstallerController();
        $request = Request::create('/install/verify-purchase', 'POST', [
            'source' => 'envato',
            'en_username' => 'buyer',
            'en_purchase_code' => 'abcd-1234',
        ]);
        $data = $controller->verifyPurchase($request)->getData(true);

        $this->assertSame('success', $data['type']);
        $this->assertNull($data['license_tier']);
    }

    /** @test */
    public function verify_purchase_backfills_username_for_direct_purchases_since_the_license_server_still_requires_it()
    {
        // license.xgenious.com's own validator currently requires en_username
        // unconditionally, even though it never checks it for XGENIOUS-prefixed
        // codes. The direct-purchase UI intentionally doesn't ask for one, so
        // the controller must backfill something non-empty itself.
        Config::set('installer.product_key', 'test-key');
        Http::fake([
            'license.xgenious.com/*' => Http::response(['verify' => true, 'msg' => 'ok'], 200),
        ]);

        $controller = new InstallerController();
        $request = Request::create('/install/verify-purchase', 'POST', [
            'source' => 'direct',
            'en_email' => 'buyer@example.com',
            'en_purchase_code' => 'XGENIOUS-000001',
        ]);
        $controller->verifyPurchase($request);

        Http::assertSent(function ($request) {
            return !empty($request['en_username']);
        });
    }

    /** @test */
    public function verify_purchase_reads_license_tier_from_the_real_success_response_headers()
    {
        // The real InstallationVerifyController never returns a JSON body on
        // success — it streams the SQL file directly, with tier info (if any)
        // carried as the X-Variant-Name response header. This is display-only:
        // nothing here is persisted by the installer (see xilancer's own
        // "General Settings > Check Update" feature for that).
        Config::set('installer.product_key', 'test-key');
        Http::fake([
            'license.xgenious.com/*' => Http::response('-- sql dump --', 200, [
                'Content-Type' => 'application/sql',
                'Content-Disposition' => 'attachment; filename="database.sql"',
                'X-Variant-Name' => 'Everything Bundle',
            ]),
        ]);

        $controller = new InstallerController();
        $request = Request::create('/install/verify-purchase', 'POST', [
            'source' => 'direct',
            'en_email' => 'buyer@example.com',
            'en_purchase_code' => 'XGENIOUS-000001',
        ]);
        $data = $controller->verifyPurchase($request)->getData(true);

        $this->assertSame('success', $data['type']);
        $this->assertSame('Everything Bundle', $data['license_tier']);
    }

    /** @test */
    public function check_system_returns_the_expected_shape()
    {
        $controller = new InstallerController();
        $data = $controller->checkSystem()->getData(true);

        foreach (['php_version', 'extensions', 'folders', 'htaccess', 'assets', 'uploads', 'env_exposure', 'database_file', 'is_local', 'is_secure'] as $key) {
            $this->assertArrayHasKey($key, $data);
        }
        $this->assertIsBool($data['php_version']);
        $this->assertIsArray($data['htaccess']);
        $this->assertArrayHasKey('exists', $data['htaccess']);
    }

    /** @test */
    public function auto_fix_moves_a_broken_htaccess_to_passing_and_is_idempotent()
    {
        $path = base_path('../.htaccess');
        File::put($path, 'stale content');
        chmod($path, 0600);

        $controller = new InstallerController();

        $first = $controller->autoFix()->getData(true);
        $this->assertTrue($first['htaccess']['exists']);
        $this->assertTrue($first['htaccess']['readable']);
        $this->assertTrue($first['htaccess']['hardened']);

        $second = $controller->autoFix()->getData(true);
        $this->assertTrue($second['htaccess']['readable']);
        $this->assertTrue($second['htaccess']['hardened']);
    }

    /** @test */
    public function middleware_redirects_unlisted_routes_when_the_installer_is_needed()
    {
        $this->withInstallerNeeded(function () {
            $middleware = new InstallerMiddleware();
            $request = Request::create('/some/other/route');

            $response = $middleware->handle($request, function () {
                return 'next-called';
            });

            $this->assertSame(302, $response->getStatusCode());
            $this->assertStringContainsString('/install', $response->headers->get('Location'));
        });
    }

    /** @test */
    public function middleware_allows_the_new_readiness_routes_through_when_the_installer_is_needed()
    {
        $this->withInstallerNeeded(function () {
            $middleware = new InstallerMiddleware();

            foreach (['install/check-system', 'install/auto-fix'] as $path) {
                $request = Request::create('/' . $path);
                $response = $middleware->handle($request, function () {
                    return 'next-called';
                });

                $this->assertSame('next-called', $response);
            }
        });
    }

    /**
     * Forces InstallationHelper::isInstallerNeeded() to true for the duration of
     * the callback by writing a temporary .env without DB_CONNECTION, matching
     * a fresh, not-yet-installed application — then cleans it up either way.
     */
    private function withInstallerNeeded(callable $callback)
    {
        $envPath = base_path('.env');
        $existed = File::exists($envPath);
        $original = $existed ? File::get($envPath) : null;

        File::put($envPath, "APP_NAME=Test\n");

        try {
            $callback();
        } finally {
            if ($existed) {
                File::put($envPath, $original);
            } else {
                File::delete($envPath);
            }
        }
    }
}
