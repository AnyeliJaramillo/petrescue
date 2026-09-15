<?php

namespace App\Infrastructure\Storage;

use App\Application\Reportes\Contracts\AlmacenImagenes;
use App\Application\Reportes\Data\ImagenEntrada;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

final class LaravelAlmacenImagenes implements AlmacenImagenes
{
    public function guardar(ImagenEntrada $imagen): string
    {
        $ruta = Storage::disk('public')->putFileAs(
            'mascotas', new File($imagen->rutaTemporal),
            Str::uuid().'.'.$imagen->extension,
        );
        if ($ruta === false) {
            throw new RuntimeException('No se pudo guardar la foto de la mascota.');
        }

        return $ruta;
    }

    public function eliminar(array $rutas): void
    {
        if ($rutas !== [] && ! Storage::disk('public')->delete($rutas)) {
            throw new RuntimeException('No se pudieron limpiar las fotos del intento fallido.');
        }
    }
}
