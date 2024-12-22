<?php

namespace AbdAlrzaq\Roles;

use AbdAlrzaq\Roles\Middleware\CheckRole;
use Illuminate\Support\ServiceProvider;

class RolesServiceProvider extends ServiceProvider
{
    public function register()
    {
// Register config, commands, or services
        $this->mergeConfigFrom(__DIR__ . '/../config/roles.php', 'roles');
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/roles.php');

        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/roles.php' => config_path('roles.php'),
        ], 'config');
        $this->publishes([

            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'migrations');
        if ($this->app->runningInConsole()) {
            $this->commands([
                \AbdAlrzaq\Roles\Console\Commands\AssignRoleCommand::class,
                \AbdAlrzaq\Roles\Console\Commands\RemoveRoleCommand::class,
            ]);
        }




        $this->registerMiddleware();
    }

    protected function registerMiddleware()
    {
        // Access the Router and register the middleware
        $router = $this->app['router'];
        $router->aliasMiddleware('role', CheckRole::class);
    }

}
