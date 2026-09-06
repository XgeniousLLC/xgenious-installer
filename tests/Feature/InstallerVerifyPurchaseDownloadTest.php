<?php

namespace Xgenious\Installer\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Xgenious\Installer\Http\Controllers\InstallerController;
use Xgenious\Installer\Tests\TestCase;

/**
 * Covers the SQL-dump-download branch of verifyPurchase(): the license
 * server streams database.sql / database_pgsql.sql directly as the real
 * success path (see InstallerControllerTest for the JSON-body branch).
 */
class InstallerVerifyPurchaseDownloadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance('request', Request::create('https://example.test/install'));
        Config::set('installer.product_key', 'test-key');
    }

    protected function tearDown(): void
    {
        Storage::disk('local')->delete(['database.sql', 'database_pgsql.sql']);
        parent::tearDown();
    }

    private function verify(array $overrides = [])
    {
        $controller = new InstallerController();
        $request = Request::create('/install/verify-purchase', 'POST', array_merge([
            'source' => 'direct',
            'en_email' => 'buyer@example.com',
            'en_purchase_code' => 'XGENIOUS-000001',
        ], $overrides));

        return $controller->verifyPurchase($request)->getData(true);
    }

    /** @test */
    public function a_sql_attachment_with_no_database_type_header_defaults_to_mysql()
    {
        Http::fake([
            'license.xgenious.com/*' => Http::response('-- dump --', 200, [
                'Content-Type' => 'application/sql',
                'Content-Disposition' => 'attachment; filename="database.sql"',
            ]),
        ]);

        $data = $this->verify();

        $this->assertSame('success', $data['type']);
        $this->assertTrue(Storage::disk('local')->exists('database.sql'));
        $this->assertFalse(Storage::disk('local')->exists('database_pgsql.sql'));
    }

    /** @test */
    public function a_database_type_of_postgresql_is_stored_under_the_pgsql_filename()
    {
        Http::fake([
            'license.xgenious.com/*' => Http::response('-- dump --', 200, [
                'Content-Type' => 'application/sql',
                'Content-Disposition' => 'attachment; filename="database_pgsql.sql"',
                'Database-Type' => 'postgresql',
            ]),
        ]);

        $data = $this->verify();

        $this->assertSame('success', $data['type']);
        $this->assertTrue(Storage::disk('local')->exists('database_pgsql.sql'));
        $this->assertFalse(Storage::disk('local')->exists('database.sql'));
    }

    /** @test */
    public function an_empty_body_with_attachment_headers_still_succeeds_and_writes_an_empty_file()
    {
        Http::fake([
            'license.xgenious.com/*' => Http::response('', 200, [
                'Content-Type' => 'application/sql',
                'Content-Disposition' => 'attachment; filename="database.sql"',
            ]),
        ]);

        $data = $this->verify();

        $this->assertSame('success', $data['type']);
        $this->assertTrue(Storage::disk('local')->exists('database.sql'));
        $this->assertSame('', Storage::disk('local')->get('database.sql'));
    }

    /** @test */
    public function content_disposition_attachment_alone_is_enough_without_a_matching_content_type()
    {
        Http::fake([
            'license.xgenious.com/*' => Http::response('-- dump --', 200, [
                'Content-Disposition' => 'attachment; filename="database.sql"',
            ]),
        ]);

        $data = $this->verify();

        $this->assertSame('success', $data['type']);
        $this->assertTrue(Storage::disk('local')->exists('database.sql'));
    }

    /** @test */
    public function a_malformed_non_json_response_body_does_not_crash_the_request()
    {
        Http::fake([
            'license.xgenious.com/*' => Http::response('not json at all {{{', 200, [
                'Content-Type' => 'text/plain',
            ]),
        ]);

        $data = $this->verify();

        $this->assertSame('danger', $data['type']);
        $this->assertNotEmpty($data['msg']);
        $this->assertNull($data['license_tier']);
    }

    /** @test */
    public function stale_dump_files_from_a_previous_attempt_are_cleaned_up_even_when_this_attempt_fails()
    {
        Storage::disk('local')->put('database.sql', 'old mysql dump');
        Storage::disk('local')->put('database_pgsql.sql', 'old pgsql dump');

        Http::fake([
            'license.xgenious.com/*' => Http::response(['verify' => false, 'msg' => 'invalid purchase code'], 200),
        ]);

        $data = $this->verify();

        $this->assertSame('danger', $data['type']);
        $this->assertFalse(Storage::disk('local')->exists('database.sql'));
        $this->assertFalse(Storage::disk('local')->exists('database_pgsql.sql'));
    }

    /** @test */
    public function stale_dump_files_are_cleaned_up_even_when_the_outbound_request_itself_throws()
    {
        Storage::disk('local')->put('database.sql', 'old mysql dump');

        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('could not connect');
        });

        $data = $this->verify();

        $this->assertSame('danger', $data['type']);
        $this->assertFalse(Storage::disk('local')->exists('database.sql'));
    }

    /** @test */
    public function verify_purchase_rejects_an_invalid_source_value()
    {
        $controller = new InstallerController();
        $request = Request::create('/install/verify-purchase', 'POST', [
            'source' => 'reseller',
            'en_purchase_code' => 'XGENIOUS-000001',
        ]);

        $data = $controller->verifyPurchase($request)->getData(true);

        $this->assertSame('danger', $data['type']);
    }

    /** @test */
    public function verify_purchase_rejects_a_malformed_email_even_for_the_direct_source()
    {
        $controller = new InstallerController();
        $request = Request::create('/install/verify-purchase', 'POST', [
            'source' => 'direct',
            'en_email' => 'not-an-email',
            'en_purchase_code' => 'XGENIOUS-000001',
        ]);

        $data = $controller->verifyPurchase($request)->getData(true);

        $this->assertSame('danger', $data['type']);
    }
}
