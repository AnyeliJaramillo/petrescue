<?php

namespace App\Application\Reportes\Contracts;

use App\Application\Reportes\Data\ImagenEntrada;

interface AlmacenImagenes
{
    public function guardar(ImagenEntrada $imagen): string;

    /** @param list<string> $rutas */
    public function eliminar(array $rutas): void;
}
