<?php

namespace App\Application\Reportes\Data;

use App\Domain\Reportes\TipoReporte;

final readonly class FiltrosReportes
{
    public function __construct(
        public ?TipoReporte $tipo = null,
        public ?string $especie = null,
        public ?string $color_principal = null,
        public ?string $tamano = null,
        public ?string $sexo = null,
        public ?string $raza = null,
        public ?string $ubicacion = null,
    ) {}

    /** @return array<string, string> */
    public function caracteristicas(): array
    {
        return array_filter([
            'especie' => $this->especie,
            'color_principal' => $this->color_principal,
            'tamano' => $this->tamano,
            'sexo' => $this->sexo,
            'raza' => $this->raza,
        ], fn ($valor) => $valor !== null && $valor !== '');
    }

    /** @return array<string, string> */
    public function parametros(): array
    {
        return ($this->tipo ? ['tipo_reporte' => $this->tipo->value] : [])
            + $this->caracteristicas()
            + ($this->ubicacion !== null && $this->ubicacion !== '' ? ['ubicacion' => $this->ubicacion] : []);
    }
}
