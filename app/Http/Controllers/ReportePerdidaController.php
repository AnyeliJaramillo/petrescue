<?php

namespace App\Http\Controllers;

use App\Models\ImagenMascota;
use App\Models\Mascota;
use App\Models\Reporte;
use App\Models\Ubicacion;
use Illuminate\Http\Request;

class ReportePerdidaController extends Controller
{
    public function index()
    {
        $reportes = Reporte::with(['mascota', 'ubicacion', 'mascota.imagenes'])
            ->where('tipo_reporte', 'perdida')
            ->latest()
            ->get();

        return view('reportes.perdidas.index', compact('reportes'));
    }

    public function create()
    {
        return view('reportes.perdidas.create');
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
            'color_secundario' => 'nullable|string|max:100',
            'tamano' => 'required|string|max:50',
            'sexo' => 'nullable|string|max:50',
            'edad_aproximada' => 'nullable|string|max:100',
            'descripcion' => 'nullable|string',
            'rasgos_distintivos' => 'nullable|string',

            'fecha_evento' => 'required|date',
            'ciudad' => 'required|string|max:100',
            'departamento' => 'nullable|string|max:100',
            'barrio' => 'nullable|string|max:100',
            'direccion' => 'nullable|string|max:255',
            'referencia' => 'nullable|string',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',

            'imagenes.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $mascota = Mascota::create([
            'nombre' => $request->nombre,
            'especie' => $request->especie,
            'raza' => $request->raza,
            'color_principal' => $request->color_principal,
            'color_secundario' => $request->color_secundario,
            'tamano' => $request->tamano,
            'sexo' => $request->sexo,
            'edad_aproximada' => $request->edad_aproximada,
            'descripcion' => $request->descripcion,
            'rasgos_distintivos' => $request->rasgos_distintivos,
        ]);

        $nombreMascota = $mascota->nombre ?: $mascota->especie;

        $reporte = Reporte::create([
            'mascota_id' => $mascota->id,
            'tipo_reporte' => 'perdida',
            'titulo' => 'Mascota perdida: ' . $nombreMascota,
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
                    'descripcion' => 'Foto de mascota perdida',
                ]);
            }
        }

        return redirect()->route('reportes-perdidos.index')
            ->with('success', 'Reporte de mascota perdida publicado correctamente.');
    }

    public function show(string $id)
    {
        $reporte = Reporte::with(['mascota', 'ubicacion', 'mascota.imagenes'])
            ->where('tipo_reporte', 'perdida')
            ->findOrFail($id);

        return view('reportes.perdidas.show', compact('reporte'));
    }
}