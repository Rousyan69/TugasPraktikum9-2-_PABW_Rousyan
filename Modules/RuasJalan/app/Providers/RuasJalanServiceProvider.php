<?php

namespace Modules\RuasJalan\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RuasJalanServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('RuasJalan', 'database/migrations'));
        Route::middleware(['web', 'auth'])->group(module_path('RuasJalan', 'routes/web.php'));
    }
}
