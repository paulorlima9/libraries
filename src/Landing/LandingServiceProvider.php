<?php

namespace Modules\Landing;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Landing\Http\Controllers\IndexController;

class LandingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootPublishers();
        $this->registerViews();
        $this->registerRoutes();
    }

    /**
     * Boot publishers
     *
     * @return void
     */
    protected function bootPublishers()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/./resources/stubs/index.blade.php' => App::resourcePath('views/vendor/landing/index.blade.php'),
                __DIR__.'/./resources/stubs/gitignore.stub' => App::resourcePath('views/vendor/landing/.gitignore'),
            ], 'landing-page');

            $this->publishes([
                __DIR__.'/./resources/public' => App::basePath('public/vendor/landing'),
            ], 'landing-page');
        }
    }

    /**
     * Register Routes
     *
     * @return void
     */
    protected function registerRoutes()
    {
        if (View::exists('landing::index')) {
            Route::middleware(['web', 'guest'])->group(function () {
                Route::get('/landing/{any?}', [IndexController::class, 'view'])->where('any', '.*');
                Route::get('/', [IndexController::class, 'view'])->name('landing');
            });
        }
    }

    /**
     * Register landing page views
     *
     * @return void
     */
    protected function registerViews()
    {
        $this->loadViewsFrom(__DIR__ . '/./resources/views', 'landing');
    }
}