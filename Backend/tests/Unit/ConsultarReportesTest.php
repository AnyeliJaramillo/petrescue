<?php

namespace Tests\Unit;

use App\Application\Reportes\Contracts\RepositorioReportes;
use App\Application\Reportes\Data\FiltrosReportes;
use App\Application\Reportes\Data\PaginaReportes;
use App\Application\Reportes\UseCases\ConsultarReportes;
use App\Domain\Reportes\TipoReporte;
use PHPUnit\Framework\TestCase;

class ConsultarReportesTest extends TestCase
{
    public function test_busqueda_coordina_el_contrato_sin_laravel_y_pagina_de_doce(): void
    {
        $filtros = new FiltrosReportes(tipo: TipoReporte::Encontrada, especie: 'perro', color_principal: 'negro', tamano: 'mediano');
        $pagina = new PaginaReportes([], 0, 1, 12);
        $repo = $this->createMock(RepositorioReportes::class);
        $repo->expects($this->once())->method('filtrar')->with($filtros, 1, 12)->willReturn($pagina);
        $this->assertSame($pagina, (new ConsultarReportes($repo))->filtrar($filtros, -5));
        $this->assertSame([
            'tipo_reporte' => 'encontrada', 'especie' => 'perro', 'color_principal' => 'negro', 'tamano' => 'mediano',
        ], $filtros->parametros());
    }
}
