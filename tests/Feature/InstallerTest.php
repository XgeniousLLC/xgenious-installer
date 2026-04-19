<?php

namespace Xgenious\Installer\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Xgenious\Installer\Tests\TestCase;
use Xgenious\Installer\Helpers\InstallationHelper;

class InstallerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Configure the package for testing
        Config::set('installer.app_name', 'TestApp');
        Config::set('installer.php_version', '8.3');
        Config::set('installer.extensions', ['BCMath', 'Ctype', 'JSON', 'Mbstring', 'OpenSSL', 'PDO', 'Tokenizer', 'XML', 'cURL', 'fileinfo']);
    }

    /** @test */
    public function it_can_check_php_version()
    {
        $this->assertTrue(InstallationHelper::php_version());
    }

    /** @test */
    public function it_can_check_extensions()
    {
        $extensions = InstallationHelper::extensions();
        $this->assertIsArray($extensions);
        $this->assertNotEmpty($extensions);
    }

    /** @test */
    public function it_can_get_required_folders()
    {
        $folders = InstallationHelper::folders();
        $this->assertIsArray($folders);
        $this->assertContains('bootstrap/cache/', $folders);
        $this->assertContains('storage/', $folders);
    }

    /** @test */
    public function it_can_check_if_installer_is_needed()
    {
        // In test environment, this should return true since DB_CONNECTION is not set
        $result = InstallationHelper::isInstallerNeeded();
        $this->assertIsBool($result);
    }

    /** @test */
    public function it_can_check_database_connection()
    {
        // This will fail with invalid credentials, but tests the method exists
        $result = InstallationHelper::check_database_connection('mysql', 'invalid_host', 'invalid_db', 'user', 'pass');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertFalse($result['status']);
    }

    /** @test */
    public function it_can_check_extension()
    {
        $this->assertTrue(InstallationHelper::extension_check('JSON'));
        $this->assertFalse(InstallationHelper::extension_check('nonexistent_extension'));
    }

    /** @test */
    public function it_can_check_multi_tenant()
    {
        Config::set('installer.multi_tenant', false);
        $this->assertFalse(InstallationHelper::is_multi_tenant());

        Config::set('installer.multi_tenant', true);
        $this->assertTrue(InstallationHelper::is_multi_tenant());
    }
}
