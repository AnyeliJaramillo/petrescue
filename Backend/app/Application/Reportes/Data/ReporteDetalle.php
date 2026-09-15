<?php

namespace App\Application\Reportes\Data;

/** Datos de lectura: no contienen modelos, relaciones ni consultas diferidas. */
final readonly class ReporteDetalle
{
    public function __construct(
        public int $id,
        public string $tipo_reporte,
        public string $titulo,
        public string $estado,
        public string $fecha_evento,
        public ?string $descripcion,
        public string $responsable_nombre,
        public string $responsable_telefono,
        public ?string $responsable_correo,
        public ?array $mascota,
        public ?array $ubicacion,
        public array $imagenes,
    ) {}
}
