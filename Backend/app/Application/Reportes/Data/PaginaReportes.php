<?php

namespace App\Application\Reportes\Data;

final readonly class PaginaReportes
{
    /** @param list<ReporteDetalle|ReporteTarjeta> $items */
    public function __construct(
        public array $items,
        public int $total,
        public int $pagina,
        public int $porPagina,
    ) {}
}
