<?php

namespace Modules\UserManagement\Providers;

use Illuminate\Support\ServiceProvider;

class UserManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
        $this->mergeConfigFrom(module_path('UserManagement', 'config/config.php'), 'usermanagement');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('UserManagement', 'database/migrations'));
    }
}
