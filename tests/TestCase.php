<?php

namespace Xgenious\Installer\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Xgenious\Installer\InstallerServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            InstallerServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Additional setup
    }
}