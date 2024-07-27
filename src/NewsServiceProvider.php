<?php

namespace NickKlein\News;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Routing\Registrar as Router;
use NickKlein\News\Commands\DestroyOldNewsLinksCommand;
use NickKlein\News\Commands\GenerateUserNews;

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
                __DIR__ . '/../resources/assets/' => resource_path('js/Pages/Packages/News'),
            ], 'assets');

            // Pulish python cron folder
            $this->publishes([
                __DIR__ . '/../resources/cron' => base_path('cron'),
            ], 'assets');
        }

        $this->commands([
            DestroyOldNewsLinksCommand::class,
            GenerateUserNews::class,
        ]);
    }
}
