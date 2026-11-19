<?php

use Illuminate\Support\Facades\Route;
use Modules\RuasJalan\Http\Controllers\RuasController;

Route::get('/ruas-jalan', [RuasController::class, 'index'])->name('ruas-jalan.index');
Route::middleware('role:admin')->group(function () {
    Route::post('/ruas-jalan', [RuasController::class, 'store'])->name('ruas-jalan.store');
    Route::put('/ruas-jalan/{ruas}', [RuasController::class, 'update'])->name('ruas-jalan.update');
    Route::delete('/ruas-jalan/{ruas}', [RuasController::class, 'destroy'])->name('ruas-jalan.destroy');
});
