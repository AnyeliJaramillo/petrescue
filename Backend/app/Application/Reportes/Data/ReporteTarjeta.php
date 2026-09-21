<?php

namespace App\Application\Reportes\Data;

/** Proyección pública para búsqueda: sin contactos, dirección exacta ni coordenadas. */
final readonly class ReporteTarjeta
{
    public function __construct(
        public int $id,
        public string $tipo_reporte,
        public string $fecha_evento,
        public array $mascota,
        public ?array $ubicacion,
        public ?string $imagen_url,
    ) {}
}
