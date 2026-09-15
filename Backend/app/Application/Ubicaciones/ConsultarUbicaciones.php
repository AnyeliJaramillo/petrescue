<?php

namespace App\Application\Ubicaciones;

use App\Application\Ubicaciones\Contracts\Geocodificador;
use App\Application\Ubicaciones\Data\Direccion;

final class ConsultarUbicaciones
{
    public function __construct(private Geocodificador $geocodificador) {}

    /** @return list<Direccion> */
    public function buscar(string $direccion, string $barrio, string $ciudad, string $departamento): array
    {
        $partes = array_filter(array_map('trim', [$direccion, $barrio, $ciudad, $departamento]));

        return $this->geocodificador->buscar(implode(', ', $partes).', Colombia');
    }

    public function invertir(float $latitud, float $longitud): ?Direccion
    {
        return $this->geocodificador->invertir($latitud, $longitud);
    }
}
