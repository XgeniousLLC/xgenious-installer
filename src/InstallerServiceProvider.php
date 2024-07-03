<?php

namespace Xgenious\Installer;

use Illuminate\Support\ServiceProvider;

class InstallerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (!file_exists(base_path('.env'))) {
            $this->loadRoutesFrom(__DIR__.'/routes/web.php');
            $this->loadViewsFrom(__DIR__.'/resources/views', 'installer');
            $this->publishes([
                __DIR__.'/Config/installer.php' => config_path('installer.php'),
            ], 'config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/Config/installer.php', 'installer'
        );
    }
}