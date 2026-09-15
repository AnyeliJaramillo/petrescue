<?php

namespace App\Infrastructure\Geocoding;

use App\Application\Ubicaciones\Contracts\Geocodificador;
use App\Application\Ubicaciones\Data\Direccion;
use App\Application\Ubicaciones\ServicioNoDisponible;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class GeoapifyGeocodificador implements Geocodificador
{
    public function buscar(string $consulta): array
    {
        return $this->consultar('search', ['text' => $consulta, 'filter' => 'countrycode:co', 'limit' => 5]);
    }

    public function invertir(float $latitud, float $longitud): ?Direccion
    {
        return $this->consultar('reverse', ['lat' => $latitud, 'lon' => $longitud, 'limit' => 1])[0] ?? null;
    }

    private function consultar(string $operacion, array $parametros): array
    {
        $key = config('ubicaciones.geoapify_key');
        if (! is_string($key) || trim($key) === '') {
            throw new ServicioNoDisponible('La búsqueda de direcciones no está disponible. Puedes escribir la dirección y seleccionar el punto en el mapa.');
        }
        $cacheKey = 'geoapify:co:v1:'.hash('sha256', $operacion.json_encode($parametros));

        return Cache::remember($cacheKey, 3600, function () use ($operacion, $parametros, $key) {
            try {
                $response = Http::acceptJson()->connectTimeout(3)->timeout(8)
                    ->get('https://api.geoapify.com/v1/geocode/'.$operacion, $parametros + [
                        'apiKey' => $key, 'format' => 'json', 'lang' => 'es',
                    ]);
            } catch (ConnectionException) {
                // No propagar URLs con la clave ni datos de dirección a los registros.
                throw new ServicioNoDisponible('No se pudo consultar la dirección. Inténtalo de nuevo o completa los campos manualmente.');
            }
            if (! $response->successful() || ! is_array($response->json('results'))) {
                throw new ServicioNoDisponible('El servicio de direcciones no respondió correctamente. Inténtalo más tarde o escribe la dirección.');
            }
            $resultados = [];
            foreach ($response->json('results') as $item) {
                if (! is_array($item) || strtolower($item['country_code'] ?? '') !== 'co'
                    || ! is_numeric($item['lat'] ?? null) || ! is_numeric($item['lon'] ?? null)
                    || abs((float) $item['lat']) > 90 || abs((float) $item['lon']) > 180) {
                    continue;
                }
                $resultados[] = new Direccion(
                    direccion: $this->texto($item, ['address_line1', 'street'], 255),
                    barrio: $this->texto($item, ['suburb', 'quarter', 'neighbourhood'], 100),
                    ciudad: $this->texto($item, ['city', 'town', 'village', 'municipality'], 100),
                    departamento: $this->texto($item, ['state'], 100),
                    etiqueta: $this->texto($item, ['formatted', 'address_line1'], 500),
                    latitud: (float) $item['lat'],
                    longitud: (float) $item['lon'],
                );
            }

            return $resultados;
        });
    }

    private function texto(array $item, array $campos, int $max): string
    {
        foreach ($campos as $campo) {
            if (isset($item[$campo]) && is_string($item[$campo]) && trim($item[$campo]) !== '') {
                return mb_substr(trim($item[$campo]), 0, $max);
            }
        }

        return '';
    }
}
