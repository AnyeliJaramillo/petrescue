<?php

namespace App\Application\Ubicaciones\Data;

final readonly class Direccion
{
    public function __construct(
        public string $direccion,
        public string $barrio,
        public string $ciudad,
        public string $departamento,
        public string $etiqueta,
        public float $latitud,
        public float $longitud,
    ) {}
}
