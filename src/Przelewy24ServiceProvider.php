<?php

namespace Adams\Przelewy24;

use Illuminate\Support\ServiceProvider;

class Przelewy24ServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutes();

        $this->app->bind('przelewy24', function () {
            return new Przelewy24();
        });
        $this->app->bind('przelewy24.api', function () {
            return new Przelewy24Api();
        });
    }

    /**
     * Load routes to default payment controller.
     * 
     * @return void
     */
    protected function loadRoutes()
    {
        if (config('przelewy24.package_routes', false)) {
            return;
        }

        $this->loadRoutesFrom(
            $this->routesFilePath()
        );
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            $this->configFilePath(),
            'przelewy24'
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            Przelewy24::class
        ];
    }

    /**
     * Get module config file path.
     * 
     * @return string
     */
    protected function configFilePath()
    {
        return realpath(__DIR__ . '/../config/przelewy24.php');
    }

    /**
     * Get module routes path.
     * 
     * @return string
     */
    protected function routesFilePath()
    {
        return realpath(__DIR__ . '/../routes/przelewy24.php');
    }
}
