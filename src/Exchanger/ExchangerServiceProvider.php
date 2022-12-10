<?php

namespace Modules\Exchanger;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class ExchangerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerResources();
        $this->registerExchanger();
        $this->registerCommands();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootResources();
    }

    /**
     * Register exchanger class
     *
     * @return void
     */
    protected function registerExchanger()
    {
        $this->app->singleton('exchanger', function ($app) {
            return new Exchanger($app->config->get('exchanger', []));
        });
    }

    /**
     * Register commands
     *
     * @return void
     */
    protected function registerCommands()
    {
        $this->commands([
            Console\Install::class,
            Console\Update::class,
        ]);
    }

    /**
     * Publish package resources
     *
     * @return void
     */
    protected function bootResources()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/./database/migrations' => App::databasePath('migrations'),
            ], 'exchanger-migrations');
        }
    }

    /**
     * Register resources
     *
     * @return void
     */
    protected function registerResources()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/./config/exchanger.php', 'exchanger'
        );
    }
}
