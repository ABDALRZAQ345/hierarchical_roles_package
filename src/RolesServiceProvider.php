<?php
namespace AbdAlrzaq\Roles;
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
// Publish configuration
$this->publishes([
__DIR__ . '/../config/roles.php' => config_path('roles.php'),
], 'config');
    $this->publishes([

        __DIR__ . '/../database/migrations/' => database_path('migrations'),
    ], 'migrations');
 
// Load migrations
$this->loadRoutesFrom(__DIR__ . '/../routes/roles.php');

}
}
