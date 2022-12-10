<?php

namespace Modules\Multipay;

use Illuminate\Support\ServiceProvider;

class MultipayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerResources();
        $this->registerMultipay();
    }

    /**
     * Register multipay class
     *
     * @return void
     */
    protected function registerMultipay()
    {
        $this->app->singleton('multipay', function ($app) {
            return new Multipay($app->config->get('multipay', []));
        });
    }

    /**
     * Register resources
     *
     * @return void
     */
    protected function registerResources()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/./config/multipay.php', 'multipay'
        );
    }
}
