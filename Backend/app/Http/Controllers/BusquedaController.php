<?php

namespace App\Http\Controllers;

use App\Application\Reportes\UseCases\ConsultarReportes;
use App\Http\Presenters\ReportesPresenter;
use App\Http\Requests\BuscarReportesRequest;
use Illuminate\Contracts\View\View;

class BusquedaController extends Controller
{
    public function index(BuscarReportesRequest $request, ConsultarReportes $consultar, ReportesPresenter $presenter): View
    {
        $filtros = $request->aFiltros();
        $reportes = $presenter->paginar(
            $consultar->filtrar($filtros, $request->integer('page', 1)),
            $request,
            $filtros->parametros(),
        );

        return view('busquedas.index', [
            'reportes' => $reportes,
            'filtros' => $filtros->parametros(),
            'opciones' => $consultar->opcionesFiltros(),
        ]);
    }
}
