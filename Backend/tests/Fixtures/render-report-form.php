<?php

// Renderizado real de Blade para las pruebas DOM de Frontend, sin consultas ni escrituras.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['session.driver' => 'array']);
$app->make('session')->start();
$app->make('view')->share('errors', new Illuminate\Support\ViewErrorBag());
$view = ($argv[1] ?? '') === 'encontrada' ? 'encontradas' : 'perdidas';
echo view('reportes.'.$view.'.create')->render();
