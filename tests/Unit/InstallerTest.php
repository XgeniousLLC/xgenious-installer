<?php

namespace Xgenious\Installer\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Xgenious\Installer\Tests\TestCase;
use Xgenious\Installer\Http\Controllers\InstallerController;
use Xgenious\Installer\Helpers\InstallationHelper;

class InstallerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    /** @test */
    public function it_loads_routes_when_env_file_does_not_exist()
    {
        File::delete(base_path('.env'));
        
        $this->assertTrue(Route::has('installer.index'));
        $this->assertTrue(Route::has('installer.verify-purchase'));
        $this->assertTrue(Route::has('installer.check-database'));
        $this->assertTrue(Route::has('installer.install'));
    }

    /** @test */
    public function it_does_not_load_routes_when_env_file_exists()
    {
        File::put(base_path('.env'), 'APP_NAME=Laravel');
        
        $this->assertFalse(Route::has('installer.index'));
        $this->assertFalse(Route::has('installer.verify-purchase'));
        $this->assertFalse(Route::has('installer.check-database'));
        $this->assertFalse(Route::has('installer.install'));
    }

    /** @test */
    public function it_can_access_installer_index_page()
    {
        $this->get(route('installer.index'))
            ->assertStatus(200)
            ->assertViewIs('installer::installer.index');
    }

    /** @test */
    public function it_can_verify_purchase()
    {
        $this->mock(InstallationHelper::class, function ($mock) {
            $mock->shouldReceive('verifyPurchase')->once()->andReturn(true);
        });

        $response = $this->postJson(route('installer.verify-purchase'), ['purchase_code' => 'valid_code']);
        
        $response->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Purchase verified']);
    }

    /** @test */
    public function it_can_check_database_connection()
    {
        $this->mock(InstallationHelper::class, function ($mock) {
            $mock->shouldReceive('checkDatabaseConnection')->once()->andReturn(true);
        });

        $response = $this->postJson(route('installer.check-database'), [
            'database_host' => 'localhost',
            'database_name' => 'test_db',
            'database_username' => 'root',
            'database_password' => '',
        ]);
        
        $response->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Database connection successful']);
    }

    /** @test */
    public function it_can_install_the_application()
    {
        $this->mock(InstallationHelper::class, function ($mock) {
            $mock->shouldReceive('createEnvFile')->once()->andReturn(true);
            $mock->shouldReceive('updateDatabaseConfig')->once()->andReturn(true);
            $mock->shouldReceive('migrateAndSeedDatabase')->once()->andReturn(true);
        });

        $response = $this->postJson(route('installer.install'), [
            'app_name' => 'Test App',
            'app_env' => 'production',
            'app_debug' => 'false',
            'app_url' => 'http://localhost',
            'database_host' => 'localhost',
            'database_name' => 'test_db',
            'database_username' => 'root',
            'database_password' => '',
        ]);
        
        $response->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Installation completed successfully']);
    }

    /** @test */
    public function it_returns_error_on_invalid_purchase_code()
    {
        $this->mock(InstallationHelper::class, function ($mock) {
            $mock->shouldReceive('verifyPurchase')->once()->andReturn(false);
        });

        $response = $this->postJson(route('installer.verify-purchase'), ['purchase_code' => 'invalid_code']);
        
        $response->assertStatus(400)
            ->assertJson(['success' => false, 'message' => 'Invalid purchase code']);
    }

    /** @test */
    public function it_returns_error_on_database_connection_failure()
    {
        $this->mock(InstallationHelper::class, function ($mock) {
            $mock->shouldReceive('checkDatabaseConnection')->once()->andReturn(false);
        });

        $response = $this->postJson(route('installer.check-database'), [
            'database_host' => 'invalid_host',
            'database_name' => 'invalid_db',
            'database_username' => 'invalid_user',
            'database_password' => 'invalid_pass',
        ]);
        
        $response->assertStatus(400)
            ->assertJson(['success' => false, 'message' => 'Database connection failed']);
    }
}