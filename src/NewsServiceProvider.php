<?php

namespace NickKlein\News;

use Illuminate\Support\ServiceProvider;
use NickKlein\News\Commands\DestroyOldNewsLinksCommand;
use NickKlein\News\Commands\GenerateUserNews;
use NickKlein\News\Commands\RunSeederCommand;

class NewsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        $this->loadRoutesFrom(__DIR__ . '/Routes/auth.php');

        // Register migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');

        // Publish 
        //
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../spider-robot/' => base_path('cron'),
            ], 'assets');

            $this->publishes([
                __DIR__ . '/../resources/js' => resource_path('js/Pages/News'),
            ], 'assets');
        }

        $this->commands([
            DestroyOldNewsLinksCommand::class,
            GenerateUserNews::class,
            RunSeederCommand::class
        ]);
    }
}
