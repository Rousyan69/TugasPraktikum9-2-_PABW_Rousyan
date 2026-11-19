<?php

namespace Modules\Pendaftaran\Providers;

use Illuminate\Support\ServiceProvider;

class PendaftaranServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
        $this->mergeConfigFrom(module_path('Pendaftaran', 'config/config.php'), 'pendaftaran');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('Pendaftaran', 'database/migrations'));
    }
}
