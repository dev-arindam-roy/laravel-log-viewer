<?php

namespace CreativeSyntax\LogViewer;

use Illuminate\Support\ServiceProvider;

class CreativeSyntaxLogViewer extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        $this->loadViewsFrom(__DIR__ . '/resources/views', 'logviewer');

        $this->mergeConfigFrom(
            __DIR__ . '/config/log-viewer.php', 'logviewer'
        );

        $this->publishes([
            __DIR__ . '/config/log-viewer.php' => config_path('log-viewer.php')
        ]);

        //php artisan vendor:publish --provider="CreativeSyntax\LogViewer\CreativeSyntaxLogViewer" --force
    }
}