<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReportePerdidaController;
use App\Http\Controllers\ReporteEncontradaController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/reportes-perdidos/crear', [ReportePerdidaController::class, 'create'])
    ->name('reportes-perdidos.create');

Route::post('/reportes-perdidos', [ReportePerdidaController::class, 'store'])
    ->name('reportes-perdidos.store');

Route::get('/reportes-encontrados/crear', [ReporteEncontradaController::class, 'create'])
    ->name('reportes-encontrados.create');

Route::post('/reportes-encontrados', [ReporteEncontradaController::class, 'store'])
    ->name('reportes-encontrados.store');