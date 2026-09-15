<?php

namespace App\Infrastructure\Persistence;

use App\Application\Reportes\Contracts\Transacciones;
use Illuminate\Support\Facades\DB;

final class LaravelTransacciones implements Transacciones
{
    public function ejecutar(callable $operacion): mixed
    {
        return DB::transaction($operacion);
    }
}
