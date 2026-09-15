<?php

namespace App\Http\Controllers;

use App\Application\Ubicaciones\ConsultarUbicaciones;
use App\Application\Ubicaciones\ServicioNoDisponible;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UbicacionController extends Controller
{
    public function buscar(Request $request, ConsultarUbicaciones $consultar): JsonResponse
    {
        $datos = $request->validate([
            'direccion' => 'required|string|min:3|max:255',
            'barrio' => 'nullable|string|max:100',
            'ciudad' => 'required|string|max:100',
            'departamento' => 'required|string|max:100',
        ]);
        try {
            return response()->json(['resultados' => $consultar->buscar(
                $datos['direccion'], $datos['barrio'] ?? '', $datos['ciudad'], $datos['departamento'],
            )]);
        } catch (ServicioNoDisponible $error) {
            return response()->json(['message' => $error->getMessage()], 503);
        }
    }

    public function invertir(Request $request, ConsultarUbicaciones $consultar): JsonResponse
    {
        $datos = $request->validate([
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
        ]);
        try {
            return response()->json(['direccion' => $consultar->invertir((float) $datos['latitud'], (float) $datos['longitud'])]);
        } catch (ServicioNoDisponible $error) {
            return response()->json(['message' => $error->getMessage()], 503);
        }
    }
}
