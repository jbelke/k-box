<?php

namespace KBox\Documents\Providers;

use Illuminate\Support\ServiceProvider;

use KBox\Documents\Services\ThumbnailsService;

/**
 * Register the {@see ThumbnailService}
 */
class ThumbnailsServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ThumbnailsService::class, function ($app) {
            return new ThumbnailsService();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['KBox\Documents\Services\ThumbnailsService', 'thumbnails'];
    }
}
