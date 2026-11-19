<?php

namespace Modules\LaporanJalan\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class LaporanJalanServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('LaporanJalan', 'database/migrations'));
        Route::middleware(['web', 'auth'])->group(module_path('LaporanJalan', 'routes/web.php'));
    }
}
