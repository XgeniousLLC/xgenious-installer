<?php

namespace Xgenious\Installer;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Xgenious\Installer\Helpers\InstallationHelper;

class InstallerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (InstallationHelper::isInstallerNeeded()) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
            $this->loadViewsFrom(__DIR__.'/../resources/views', 'installer');
        }

        $this->publishes([
            __DIR__.'/../config/installer.php' => config_path('installer.php'),
        ], 'config');

    }


    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/installer.php', 'installer'
        );
    }


}
