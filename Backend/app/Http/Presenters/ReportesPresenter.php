<?php

namespace App\Http\Presenters;

use App\Application\Reportes\Data\PaginaReportes;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

final class ReportesPresenter
{
    public function paginar(PaginaReportes $pagina, Request $request, ?array $parametros = null): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            $pagina->items, $pagina->total, $pagina->porPagina, $pagina->pagina,
            ['path' => $request->url(), 'query' => $parametros ?? $request->except('page')],
        );
    }
}
