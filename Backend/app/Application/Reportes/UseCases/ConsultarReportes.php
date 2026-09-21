<?php

namespace App\Application\Reportes\UseCases;

use App\Application\Reportes\Contracts\RepositorioReportes;
use App\Application\Reportes\Data\FiltrosReportes;
use App\Application\Reportes\Data\PaginaReportes;
use App\Application\Reportes\Data\ReporteDetalle;
use App\Domain\Reportes\TipoReporte;

final class ConsultarReportes
{
    public function __construct(private RepositorioReportes $reportes) {}

    public function listar(TipoReporte $tipo, int $pagina = 1): PaginaReportes
    {
        return $this->reportes->listar($tipo, max(1, $pagina), 12);
    }

    public function buscar(int $id, TipoReporte $tipo): ?ReporteDetalle
    {
        return $this->reportes->buscar($id, $tipo);
    }

    public function filtrar(FiltrosReportes $filtros, int $pagina = 1): PaginaReportes
    {
        return $this->reportes->filtrar($filtros, max(1, $pagina), 12);
    }

    public function opcionesFiltros(): array
    {
        return $this->reportes->opcionesFiltros();
    }
}
