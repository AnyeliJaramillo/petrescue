<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReporteEncontradaController;
use App\Http\Controllers\ReportePerdidaController;
use App\Http\Controllers\UbicacionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('throttle:geocoding')->prefix('ubicaciones')->name('ubicaciones.')->group(function () {
    Route::post('/buscar', [UbicacionController::class, 'buscar'])->name('buscar');
    Route::post('/invertir', [UbicacionController::class, 'invertir'])->name('invertir');
});

Route::get('/reportes-perdidos', [ReportePerdidaController::class, 'index'])
    ->name('reportes-perdidos.index');

Route::get('/reportes-perdidos/crear', [ReportePerdidaController::class, 'create'])
    ->name('reportes-perdidos.create');

Route::post('/reportes-perdidos', [ReportePerdidaController::class, 'store'])
    ->name('reportes-perdidos.store');

Route::get('/reportes-perdidos/{id}', [ReportePerdidaController::class, 'show'])
    ->whereNumber('id')->name('reportes-perdidos.show');

Route::get('/reportes-encontrados', [ReporteEncontradaController::class, 'index'])
    ->name('reportes-encontrados.index');

Route::get('/reportes-encontrados/crear', [ReporteEncontradaController::class, 'create'])
    ->name('reportes-encontrados.create');

Route::post('/reportes-encontrados', [ReporteEncontradaController::class, 'store'])
    ->name('reportes-encontrados.store');

Route::get('/reportes-encontrados/{id}', [ReporteEncontradaController::class, 'show'])
    ->whereNumber('id')->name('reportes-encontrados.show');
