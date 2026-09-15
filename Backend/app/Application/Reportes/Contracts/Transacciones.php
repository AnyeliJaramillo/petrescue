<?php

namespace App\Application\Reportes\Contracts;

interface Transacciones
{
    public function ejecutar(callable $operacion): mixed;
}
