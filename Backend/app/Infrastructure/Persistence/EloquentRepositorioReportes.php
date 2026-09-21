<?php

namespace App\Infrastructure\Persistence;

use App\Application\Reportes\Contracts\RepositorioReportes;
use App\Application\Reportes\Data\NuevoReporte;
use App\Application\Reportes\Data\FiltrosReportes;
use App\Application\Reportes\Data\ReporteTarjeta;
use App\Application\Reportes\Data\PaginaReportes;
use App\Application\Reportes\Data\ReporteDetalle;
use App\Domain\Reportes\TipoReporte;
use App\Models\Mascota;
use App\Models\Reporte;

final class EloquentRepositorioReportes implements RepositorioReportes
{
    public function crear(NuevoReporte $datos, TipoReporte $tipo, array $rutasImagenes): int
    {
        $mascota = Mascota::create($datos->mascota);
        $reporte = $mascota->reportes()->create(array_merge($datos->contactoYEvento, [
            'tipo_reporte' => $tipo->value,
            'titulo' => $tipo->titulo($mascota->nombre, $mascota->especie),
            'estado' => 'activo',
        ]));
        $reporte->ubicacion()->create($datos->ubicacion);

        foreach ($rutasImagenes as $ruta) {
            $mascota->imagenes()->create([
                'ruta_imagen' => $ruta,
                'descripcion' => 'Foto de mascota '.$tipo->value,
            ]);
        }

        return $reporte->id;
    }

    public function listar(TipoReporte $tipo, int $pagina, int $porPagina): PaginaReportes
    {
        $resultado = Reporte::with(['mascota.imagenes', 'ubicacion'])
            ->where('estado', 'activo')
            ->where('tipo_reporte', $tipo->value)->latest()->orderByDesc('id')
            ->paginate($porPagina, ['*'], 'page', $pagina);

        return new PaginaReportes(
            array_map($this->aDetalle(...), $resultado->items()),
            $resultado->total(), $resultado->currentPage(), $resultado->perPage(),
        );
    }

    public function buscar(int $id, TipoReporte $tipo): ?ReporteDetalle
    {
        $reporte = Reporte::with(['mascota.imagenes', 'ubicacion'])
            ->where('tipo_reporte', $tipo->value)->find($id);

        return $reporte ? $this->aDetalle($reporte) : null;
    }

    public function filtrar(FiltrosReportes $filtros, int $pagina, int $porPagina): PaginaReportes
    {
        $consulta = Reporte::query()->where('estado', 'activo')
            ->whereIn('tipo_reporte', array_column(TipoReporte::cases(), 'value'))
            ->with([
                'mascota:id,nombre,especie,color_principal,tamano',
                'mascota.imagenes' => fn ($query) => $query->select('id', 'mascota_id', 'ruta_imagen')->orderBy('id')->limit(1),
                'ubicacion:reporte_id,barrio,ciudad',
            ]);
        if ($filtros->tipo) {
            $consulta->where('tipo_reporte', $filtros->tipo->value);
        }
        $consulta->whereHas('mascota', function ($query) use ($filtros) {
            // Nombres de columnas cerrados por el DTO; valores siempre parametrizados.
            foreach ($filtros->caracteristicas() as $campo => $valor) {
                $query->whereRaw('LOWER(TRIM('.$campo.')) = LOWER(?)', [trim($valor)]);
            }
        });
        $resultado = $consulta->orderByDesc('created_at')->orderByDesc('id')
            ->paginate($porPagina, ['id', 'mascota_id', 'tipo_reporte', 'fecha_evento'], 'page', $pagina);

        return new PaginaReportes(
            array_map(fn (Reporte $reporte) => new ReporteTarjeta(
                id: $reporte->id,
                tipo_reporte: $reporte->tipo_reporte,
                fecha_evento: $reporte->fecha_evento,
                mascota: $reporte->mascota->only(['nombre', 'especie', 'color_principal', 'tamano']),
                ubicacion: $reporte->ubicacion?->only(['barrio', 'ciudad']),
                imagen_url: $reporte->mascota->imagenes->first()?->url,
            ), $resultado->items()),
            $resultado->total(), $resultado->currentPage(), $resultado->perPage(),
        );
    }

    public function opcionesFiltros(): array
    {
        $opciones = [];
        foreach (['especie', 'color_principal', 'tamano', 'sexo', 'raza'] as $campo) {
            $opciones[$campo] = Mascota::whereHas('reportes', fn ($query) => $query
                ->where('estado', 'activo')->whereIn('tipo_reporte', array_column(TipoReporte::cases(), 'value')))
                ->whereNotNull($campo)->whereRaw('TRIM('.$campo.") <> ''")
                ->selectRaw('LOWER(TRIM('.$campo.')) AS valor')
                ->distinct()->orderBy('valor')->pluck('valor')->all();
        }

        return $opciones;
    }

    private function aDetalle(Reporte $reporte): ReporteDetalle
    {
        return new ReporteDetalle(
            id: $reporte->id,
            tipo_reporte: $reporte->tipo_reporte,
            titulo: $reporte->titulo,
            estado: $reporte->estado,
            fecha_evento: $reporte->fecha_evento,
            descripcion: $reporte->descripcion,
            responsable_nombre: $reporte->responsable_nombre,
            responsable_telefono: $reporte->responsable_telefono,
            responsable_correo: $reporte->responsable_correo,
            mascota: $reporte->mascota?->only([
                'nombre', 'especie', 'raza', 'color_principal', 'color_secundario',
                'tamano', 'sexo', 'edad_aproximada', 'rasgos_distintivos',
            ]),
            ubicacion: $reporte->ubicacion?->only([
                'ciudad', 'departamento', 'barrio', 'direccion', 'referencia', 'latitud', 'longitud',
            ]),
            imagenes: $reporte->mascota?->imagenes->map(fn ($imagen) => $imagen->only([
                'ruta_imagen', 'descripcion', 'url',
            ]))->all() ?? [],
        );
    }
}
