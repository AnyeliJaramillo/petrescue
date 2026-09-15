<?php

namespace App\Application\Reportes\Data;

final readonly class NuevoReporte
{
    /**
     * Datos previamente validados por el adaptador de entrada.
     *
     * @param  array<string, mixed>  $mascota
     * @param  array<string, mixed>  $contactoYEvento
     * @param  array<string, mixed>  $ubicacion
     * @param  list<ImagenEntrada>  $imagenes
     */
    public function __construct(
        public array $mascota,
        public array $contactoYEvento,
        public array $ubicacion,
        public array $imagenes = [],
    ) {}
}
