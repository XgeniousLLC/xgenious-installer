<?php

namespace Xgenious\Installer\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Xgenious\Installer\Support\DatabaseLayout;
use Xgenious\Installer\Tests\TestCase;

class FakeLayoutHandler
{
    /** @var list<array{0: string, 1: array}> */
    public static array $calls = [];

    /** @var array{ok: bool, message: string} */
    public static array $result = ['ok' => true, 'message' => 'layout applied'];

    public function apply(string $layout, array $db): array
    {
        self::$calls[] = [$layout, $db];

        return self::$result;
    }
}

class DatabaseLayoutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('installer.multi_tenant', true);
        Config::set('installer.database_layouts', [
            'per_tenant' => 'Multi-database',
            'pooled' => 'Three shared databases',
        ]);
        Config::set('installer.database_layout', 'per_tenant');
        Config::set('installer.install_callback', FakeLayoutHandler::class);

        FakeLayoutHandler::$calls = [];
        FakeLayoutHandler::$result = ['ok' => true, 'message' => 'layout applied'];
    }

    /** @test */
    public function it_resolves_the_submitted_layout()
    {
        $this->assertSame('pooled', DatabaseLayout::resolve('pooled'));
    }

    /** @test */
    public function it_falls_back_to_the_configured_default_for_an_unknown_layout()
    {
        Config::set('installer.database_layout', 'pooled');
        $this->assertSame('pooled', DatabaseLayout::resolve('not-a-layout'));

        Config::set('installer.database_layout', 'not-a-layout');
        $this->assertSame('per_tenant', DatabaseLayout::resolve(null));
    }

    /** @test */
    public function it_calls_the_product_callback_with_the_layout_and_database()
    {
        $result = DatabaseLayout::apply('pooled', [
            'driver' => 'pgsql',
            'host' => '127.0.0.1',
            'name' => 'app',
            'username' => 'app',
            'password' => 'secret',
        ]);

        $this->assertTrue($result['ok']);
        $this->assertSame('layout applied', $result['message']);
        $this->assertCount(1, FakeLayoutHandler::$calls);
        $this->assertSame('pooled', FakeLayoutHandler::$calls[0][0]);
        $this->assertSame('app', FakeLayoutHandler::$calls[0][1]['name']);
    }

    /** @test */
    public function it_is_a_noop_when_no_layouts_are_configured()
    {
        Config::set('installer.database_layouts', []);

        $result = DatabaseLayout::apply('pooled', ['name' => 'app']);

        $this->assertTrue($result['ok']);
        $this->assertSame('', $result['message']);
        $this->assertSame([], FakeLayoutHandler::$calls);
    }

    /** @test */
    public function it_is_a_noop_when_the_callback_class_does_not_exist()
    {
        Config::set('installer.install_callback', 'App\\Missing\\Handler');

        $result = DatabaseLayout::apply('pooled', ['name' => 'app']);

        $this->assertTrue($result['ok']);
        $this->assertSame('', $result['message']);
    }

    /** @test */
    public function it_propagates_callback_failures_instead_of_throwing()
    {
        FakeLayoutHandler::$result = ['ok' => false, 'message' => 'could not create database'];

        $result = DatabaseLayout::apply('pooled', ['name' => 'app']);

        $this->assertFalse($result['ok']);
        $this->assertSame('could not create database', $result['message']);
    }
}
