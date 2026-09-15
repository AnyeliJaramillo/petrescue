<?php

namespace App\Application\Ubicaciones\Contracts;

use App\Application\Ubicaciones\Data\Direccion;

interface Geocodificador
{
    /** @return list<Direccion> */
    public function buscar(string $consulta): array;

    public function invertir(float $latitud, float $longitud): ?Direccion;
}
