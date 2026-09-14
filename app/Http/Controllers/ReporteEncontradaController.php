<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\Reporte;
use App\Models\Ubicacion;
use App\Models\ImagenMascota;
use Illuminate\Http\Request;

class ReporteEncontradaController extends Controller
{
    public function create()
    {
        return view('reportes.encontradas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'responsable_nombre' => 'required|string|max:255',
            'responsable_telefono' => 'required|string|max:30',
            'responsable_correo' => 'nullable|email|max:255',

            'nombre' => 'nullable|string|max:255',
            'especie' => 'required|string|max:100',
            'raza' => 'nullable|string|max:100',
            'color_principal' => 'required|string|max:100',
            'tamano' => 'required|string|max:50',
            'descripcion' => 'nullable|string',
            'rasgos_distintivos' => 'nullable|string',

            'fecha_evento' => 'required|date',
            'ciudad' => 'required|string|max:100',
            'direccion' => 'nullable|string|max:255',
            'barrio' => 'nullable|string|max:100',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'imagenes.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $mascota = Mascota::create($request->only([
            'nombre',
            'especie',
            'raza',
            'color_principal',
            'color_secundario',
            'tamano',
            'sexo',
            'edad_aproximada',
            'descripcion',
            'rasgos_distintivos',
        ]));

        $reporte = Reporte::create([
            'mascota_id' => $mascota->id,
            'tipo_reporte' => 'encontrada',
            'titulo' => 'Mascota encontrada: ' . ($mascota->nombre ?? $mascota->especie),
            'descripcion' => $request->descripcion,
            'fecha_evento' => $request->fecha_evento,
            'estado' => 'activo',
            'responsable_nombre' => $request->responsable_nombre,
            'responsable_correo' => $request->responsable_correo,
            'responsable_telefono' => $request->responsable_telefono,
        ]);

        Ubicacion::create([
            'reporte_id' => $reporte->id,
            'direccion' => $request->direccion,
            'barrio' => $request->barrio,
            'ciudad' => $request->ciudad,
            'departamento' => $request->departamento,
            'latitud' => $request->latitud,
            'longitud' => $request->longitud,
            'referencia' => $request->referencia,
        ]);

        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $imagen) {
                $ruta = $imagen->store('mascotas', 'public');

                ImagenMascota::create([
                    'mascota_id' => $mascota->id,
                    'ruta_imagen' => $ruta,
                ]);
            }
        }

        return redirect()->route('reportes-encontrados.create')
            ->with('success', 'Reporte de mascota encontrada registrado correctamente.');
    }
}