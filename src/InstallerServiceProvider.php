<?php

namespace Xgenious\Installer;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Xgenious\Installer\Commands\RemoveMiddlewareCommand;
use Xgenious\Installer\Helpers\InstallationHelper;
use Illuminate\Support\Facades\URL;

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

<<<<<<< HEAD
        if ($this->app->runningInConsole()) {
            $this->commands([
                RemoveMiddlewareCommand::class,
            ]);
        }

=======
        // Check if the application is using HTTPS and force SSL if true
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            URL::forceScheme('https');
        }
>>>>>>> b277035d70e739d50e5e3fef1e7391324328794f
    }


    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/installer.php', 'installer'
        );
    }


}
