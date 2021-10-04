<?php

namespace Karamvirs\HereTrigger;

use Illuminate\Support\ServiceProvider;

class HereTriggerServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'karamvirs');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'karamvirs');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        $this->publishes([
            __DIR__.'/../config/here-trigger.php' => config_path('here-trigger.php'),
            __DIR__.'/Jobs/HereTriggerProcessor.php' => $this->app->basePath('app/Jobs/HereTriggerProcessor.php'),
            __DIR__.'/Helpers/HereTriggerHelper.php' => $this->app->basePath('app/Helpers/HereTriggerHelper.php'),
        ]);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/here-trigger.php', 'here-trigger');

        // Register the service the package provides.
        $this->app->singleton('here-trigger', function ($app) {
            return new HereTrigger;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['here-trigger'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/here-trigger.php' => config_path('here-trigger.php'),
        ], 'here-trigger.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/karamvirs'),
        ], 'here-trigger.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/karamvirs'),
        ], 'here-trigger.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/karamvirs'),
        ], 'here-trigger.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
