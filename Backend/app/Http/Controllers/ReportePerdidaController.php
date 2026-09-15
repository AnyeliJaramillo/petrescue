<?php

namespace App\Http\Controllers;

use App\Application\Reportes\UseCases\ConsultarReportes;
use App\Application\Reportes\UseCases\PublicarReporte;
use App\Domain\Reportes\TipoReporte;
use App\Http\Presenters\ReportesPresenter;
use App\Http\Requests\StoreReporteRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportePerdidaController extends Controller
{
    public function index(Request $request, ConsultarReportes $consultar, ReportesPresenter $presenter): View
    {
        $pagina = $consultar->listar(TipoReporte::Perdida, $request->integer('page', 1));
        $reportes = $presenter->paginar($pagina, $request);

        return view('reportes.perdidas.index', compact('reportes'));
    }

    public function create(): View
    {
        return view('reportes.perdidas.create');
    }

    public function store(StoreReporteRequest $request, PublicarReporte $publicar): RedirectResponse
    {
        $publicar->ejecutar($request->aDatos(), TipoReporte::Perdida);

        return redirect()->route('reportes-perdidos.index')
            ->with('success', 'Reporte de mascota perdida publicado correctamente.');
    }

    public function show(string $id, ConsultarReportes $consultar): View
    {
        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($id === false, 404);
        $reporte = $consultar->buscar($id, TipoReporte::Perdida);
        abort_if($reporte === null, 404);

        return view('reportes.perdidas.show', compact('reporte'));
    }
}
