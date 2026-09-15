<?php

namespace App\Application\Reportes\Contracts;

use App\Application\Reportes\Data\NuevoReporte;
use App\Application\Reportes\Data\PaginaReportes;
use App\Application\Reportes\Data\ReporteDetalle;
use App\Domain\Reportes\TipoReporte;

interface RepositorioReportes
{
    /** @param list<string> $rutasImagenes */
    public function crear(NuevoReporte $datos, TipoReporte $tipo, array $rutasImagenes): int;

    public function listar(TipoReporte $tipo, int $pagina, int $porPagina): PaginaReportes;

    public function buscar(int $id, TipoReporte $tipo): ?ReporteDetalle;
}
