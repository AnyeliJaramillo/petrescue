<?php

namespace App\Infrastructure\Storage;

use App\Application\Reportes\Contracts\AlmacenImagenes;
use App\Application\Reportes\Data\ImagenEntrada;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

final class SupabaseAlmacenImagenes implements AlmacenImagenes
{
    private string $url;

    private string $key;

    private string $bucket;

    public function __construct()
    {
        $this->url = rtrim((string) config('imagenes.supabase.url'), '/');
        $this->key = (string) config('imagenes.supabase.key');
        $this->bucket = (string) config('imagenes.supabase.bucket');

        if (! filter_var($this->url, FILTER_VALIDATE_URL)
            || parse_url($this->url, PHP_URL_SCHEME) !== 'https'
            || $this->key === '' || ! preg_match('/^[a-zA-Z0-9_-]+$/', $this->bucket)) {
            throw new RuntimeException('Configura SUPABASE_URL (https), SUPABASE_SECRET_KEY y SUPABASE_STORAGE_BUCKET en el backend.');
        }
    }

    public function guardar(ImagenEntrada $imagen): string
    {
        $tipo = match ($imagen->extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => throw new RuntimeException('Formato de foto no permitido.'),
        };
        $contenido = file_get_contents($imagen->rutaTemporal);
        if ($contenido === false) {
            throw new RuntimeException('No se pudo leer la foto seleccionada.');
        }

        $ruta = 'mascotas/'.Str::uuid().'.'.$imagen->extension;
        $respuesta = $this->cliente()->withBody($contenido, $tipo)
            ->post($this->url.'/storage/v1/object/'.$this->bucket.'/'.$ruta);

        if (! $respuesta->successful()) {
            throw new RuntimeException('No se pudo subir la foto a Supabase Storage (HTTP '.$respuesta->status().'). Revisa el bucket y la clave del backend.');
        }

        return $this->prefijoPublico().$ruta;
    }

    public function eliminar(array $rutas): void
    {
        if ($rutas === []) {
            return;
        }

        $prefijos = array_map(function (string $ruta): string {
            if (! str_starts_with($ruta, $this->prefijoPublico().'mascotas/')) {
                throw new RuntimeException('La foto no pertenece al bucket configurado.');
            }

            return substr($ruta, strlen($this->prefijoPublico()));
        }, $rutas);

        $respuesta = $this->cliente()->delete($this->url.'/storage/v1/object/'.$this->bucket, [
            'prefixes' => $prefijos,
        ]);
        if (! $respuesta->successful()) {
            throw new RuntimeException('No se pudieron limpiar las fotos del intento fallido en Supabase (HTTP '.$respuesta->status().').');
        }
    }

    private function prefijoPublico(): string
    {
        return $this->url.'/storage/v1/object/public/'.$this->bucket.'/';
    }

    private function cliente(): PendingRequest
    {
        $cliente = Http::acceptJson()->withHeaders(['apikey' => $this->key])
            ->connectTimeout(10)->timeout(30)->withoutRedirecting();

        // Las claves secretas nuevas no son JWT; solo las heredadas usan Bearer.
        return str_starts_with($this->key, 'sb_secret_')
            ? $cliente
            : $cliente->withToken($this->key);
    }
}
