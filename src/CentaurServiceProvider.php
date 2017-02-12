<?php

namespace Centaur;

use ReflectionClass;
use Illuminate\Routing\Router;
use Centaur\Console\CentaurSpruce;
use Centaur\Console\CentaurScaffold;
use Centaur\Console\CentaurPublisher;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class CentaurServiceProvider extends ServiceProvider
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
    public function boot(Router $router)
    {
        // Determine the package file path
        $centaurFilename = with(new ReflectionClass('Centaur\CentaurServiceProvider'))->getFileName();
        $centaurPath = dirname($centaurFilename);

        // Register the route middleware
        $router->aliasMiddleware('sentinel.guest', \Centaur\Middleware\SentinelGuest::class);
        $router->aliasMiddleware('sentinel.auth', \Centaur\Middleware\SentinelAuthenticate::class);
        $router->aliasMiddleware('sentinel.role', \Centaur\Middleware\SentinelUserInRole::class);
        $router->aliasMiddleware('sentinel.access', \Centaur\Middleware\SentinelUserHasAccess::class);

        // Register Artisan Commands
        $this->registerArtisanCommands();

        // Establish Views Namespace
        if (is_dir(base_path() . '/resources/views/centaur')) {
            // The package views have been published - use those views.
            $this->loadViewsFrom(base_path() . '/resources/views/centaur', 'Centaur');
        } else {
            // The package views have not been published. Use the defaults.
            $this->loadViewsFrom($centaurPath . '/../views', 'Centaur');
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Register the Sentinel Service Provider
        $this->app->register('Cartalyst\Sentinel\Laravel\SentinelServiceProvider');

        // Load the Sentry and Hashid Facade Aliases
        $loader = AliasLoader::getInstance();
        $loader->alias('Activation', 'Cartalyst\Sentinel\Laravel\Facades\Activation');
        $loader->alias('Reminder', 'Cartalyst\Sentinel\Laravel\Facades\Reminder');
        $loader->alias('Sentinel', 'Cartalyst\Sentinel\Laravel\Facades\Sentinel');
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('auth', 'sentry');
    }
    /**
     * Register the Artisan Commands
     */
    private function registerArtisanCommands()
    {
        // Register the Scaffold command
        $this->app->singleton('centaur.scaffold', function($app) {
            return new CentaurScaffold(
                $app->make('files')
            );
        });
        $this->commands('centaur.scaffold');

        // Register the Spruce command
        $this->app->singleton('centaur.spruce', function($app) {
            return new CentaurSpruce(
                $app->make('files')
            );
        });
        $this->commands('centaur.spruce');
    }
}
