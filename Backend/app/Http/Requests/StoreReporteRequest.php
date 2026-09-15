<?php

namespace App\Http\Requests;

use App\Application\Reportes\Data\ImagenEntrada;
use App\Application\Reportes\Data\NuevoReporte;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreReporteRequest extends FormRequest
{
    public function aDatos(): NuevoReporte
    {
        $datos = $this->validated();

        return new NuevoReporte(
            mascota: Arr::only($datos, [
                'nombre', 'especie', 'raza', 'color_principal', 'color_secundario',
                'tamano', 'sexo', 'edad_aproximada', 'descripcion', 'rasgos_distintivos',
            ]),
            contactoYEvento: Arr::only($datos, [
                'descripcion', 'fecha_evento', 'responsable_nombre',
                'responsable_correo', 'responsable_telefono',
            ]),
            ubicacion: Arr::only($datos, [
                'direccion', 'barrio', 'ciudad', 'departamento', 'latitud', 'longitud', 'referencia',
            ]),
            imagenes: array_values(array_map(
                fn ($imagen) => new ImagenEntrada($imagen->getPathname(), $imagen->extension()),
                $datos['imagenes'] ?? [],
            )),
        );
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $conPunto = $this->filled('latitud') || $this->filled('longitud');

        return [
            'responsable_nombre' => 'required|string|max:255',
            'responsable_telefono' => 'required|string|max:30',
            'responsable_correo' => 'nullable|email|max:255',
            'nombre' => 'nullable|string|max:255',
            'especie' => 'required|string|max:100',
            'raza' => 'nullable|string|max:100',
            'color_principal' => 'required|string|max:100',
            'color_secundario' => 'nullable|string|max:100',
            'tamano' => 'required|string|max:50',
            'sexo' => 'nullable|string|max:50',
            'edad_aproximada' => 'nullable|string|max:100',
            'descripcion' => 'nullable|string',
            'rasgos_distintivos' => 'nullable|string',
            'fecha_evento' => 'required|date|before_or_equal:today',
            'ciudad' => 'required|string|max:100',
            'departamento' => [Rule::requiredIf($conPunto), 'nullable', 'string', 'max:100'],
            'barrio' => 'nullable|string|max:100',
            'direccion' => [Rule::requiredIf($conPunto), 'nullable', 'string', 'max:255'],
            'referencia' => 'nullable|string',
            'latitud' => [Rule::requiredIf($this->boolean('ubicacion_confirmada')), 'nullable', 'required_with:longitud', 'numeric', 'between:-90,90'],
            'longitud' => [Rule::requiredIf($this->boolean('ubicacion_confirmada')), 'nullable', 'required_with:latitud', 'numeric', 'between:-180,180'],
            'ubicacion_confirmada' => [Rule::when(
                $conPunto,
                ['required', 'accepted'], ['nullable', 'boolean'],
            )],
            'imagenes' => 'nullable|array',
            'imagenes_cantidad' => 'nullable|integer|min:0',
            'imagenes.*' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->has('imagenes_cantidad') || ! $this->filled('imagenes_cantidad')) {
                return;
            }
            $imagenes = $this->file('imagenes', []);
            $recibidas = is_array($imagenes) ? count($imagenes) : 0;
            if ((int) $this->input('imagenes_cantidad') !== $recibidas) {
                $validator->errors()->add('imagenes', 'No llegaron todas las fotos seleccionadas. Vuelve a seleccionarlas antes de publicar.');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'required_with' => 'Selecciona y confirma nuevamente el punto en el mapa.',
            'required_if' => 'Completa el campo :attribute para confirmar la ubicación.',
            'ubicacion_confirmada.required' => 'Confirma la ubicación elegida en el mapa.',
            'ubicacion_confirmada.accepted' => 'Confirma la ubicación elegida en el mapa.',
            'string' => 'El campo :attribute debe ser texto.',
            'email' => 'El campo :attribute debe ser un correo válido.',
            'max.string' => 'El campo :attribute no puede superar :max caracteres.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
            'before_or_equal' => 'La fecha del evento no puede ser futura.',
            'numeric' => 'El campo :attribute debe ser numérico.',
            'between.numeric' => 'El campo :attribute debe estar entre :min y :max.',
            'imagenes.array' => 'Selecciona las fotos mediante el campo de archivos.',
            'imagenes.*.required' => 'Selecciona un archivo de imagen válido.',
            'imagenes.*.image' => 'Cada archivo debe ser una imagen.',
            'imagenes.*.mimes' => 'Las fotos deben estar en formato JPG o PNG.',
            'imagenes.*.max' => 'Cada foto debe pesar como máximo 2 MB.',
        ];
    }
}
