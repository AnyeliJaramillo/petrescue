<?php

namespace App\Http\Requests;

use App\Application\Reportes\Data\FiltrosReportes;
use App\Domain\Reportes\TipoReporte;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BuscarReportesRequest extends FormRequest
{
    protected $redirectRoute = 'busquedas.index';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_reporte' => ['nullable', Rule::enum(TipoReporte::class)],
            'especie' => 'nullable|string|max:100',
            'color_principal' => 'nullable|string|max:100',
            'tamano' => 'nullable|string|max:50',
            'sexo' => 'nullable|string|max:50',
            'raza' => 'nullable|string|max:100',
            'page' => 'nullable|integer|min:1|max:1000000',
        ];
    }

    public function aFiltros(): FiltrosReportes
    {
        $datos = $this->validated();

        return new FiltrosReportes(
            tipo: isset($datos['tipo_reporte']) ? TipoReporte::from($datos['tipo_reporte']) : null,
            especie: $datos['especie'] ?? null,
            color_principal: $datos['color_principal'] ?? null,
            tamano: $datos['tamano'] ?? null,
            sexo: $datos['sexo'] ?? null,
            raza: $datos['raza'] ?? null,
        );
    }

    public function messages(): array
    {
        return [
            'tipo_reporte.Illuminate\Validation\Rules\Enum' => 'Selecciona Perdida o Encontrada.',
            'tipo_reporte.enum' => 'Selecciona Perdida o Encontrada.',
            'string' => 'El filtro :attribute debe contener un único valor de texto.',
            'max.string' => 'El filtro :attribute no puede superar :max caracteres.',
            'page.integer' => 'La página debe ser un número entero.',
            'page.min' => 'La página debe ser mayor o igual a 1.',
            'page.max' => 'El número de página es demasiado grande.',
        ];
    }
}
