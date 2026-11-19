<?php

use Illuminate\Support\Facades\Route;
use Modules\LaporanJalan\Http\Controllers\LaporanController;

Route::get('/laporan-jalan', [LaporanController::class, 'index'])->name('laporan-jalan.index');
Route::get('/laporan-jalan/data', [LaporanController::class, 'data'])->name('laporan-jalan.data');
Route::get('/laporan-jalan/{laporan}/foto', [LaporanController::class, 'foto'])->name('laporan-jalan.foto');
Route::post('/laporan-jalan', [LaporanController::class, 'store'])->name('laporan-jalan.store');
Route::put('/laporan-jalan/{laporan}', [LaporanController::class, 'update'])->name('laporan-jalan.update');
Route::delete('/laporan-jalan/{laporan}', [LaporanController::class, 'destroy'])->name('laporan-jalan.destroy');
