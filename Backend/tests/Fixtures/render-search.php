<?php

// Fixture de presentación para DOM/navegador. No consulta ni modifica la base real.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['session.driver' => 'array']);
$app->make('session')->start();
$app->make('view')->share('errors', new Illuminate\Support\ViewErrorBag());
$tipo = ($argv[1] ?? '') === 'encontrada' ? 'encontrada' : 'perdida';
$empty = ($argv[1] ?? '') === 'vacio';
$cards = $empty ? [] : [
    new App\Application\Reportes\Data\ReporteTarjeta(1, $tipo, '2026-01-01', ['nombre' => 'Luna', 'especie' => 'perro', 'color_principal' => 'negro', 'tamano' => 'mediano'], ['barrio' => 'Centro', 'ciudad' => 'Pasto'], null),
    new App\Application\Reportes\Data\ReporteTarjeta(2, $tipo, '2026-01-02', ['nombre' => null, 'especie' => 'perro', 'color_principal' => 'negro', 'tamano' => 'mediano'], ['ciudad' => 'Pasto'], 'https://example.com/mascota.png'),
];
$filtros = ['tipo_reporte' => $tipo, 'especie' => 'perro'];
echo view('busquedas.index', [
    'reportes' => new Illuminate\Pagination\LengthAwarePaginator($cards, $empty ? 0 : 14, 12, 1, ['path' => '/buscar', 'query' => $filtros]),
    'filtros' => $filtros,
    'opciones' => ['especie' => ['gato', 'perro'], 'color_principal' => ['blanco', 'negro'], 'tamano' => ['mediano', 'pequeño'], 'sexo' => ['hembra', 'macho'], 'raza' => ['labrador']],
])->render();
