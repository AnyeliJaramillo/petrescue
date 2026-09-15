<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UbicacionesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['ubicaciones.geoapify_key' => 'clave-solo-para-pruebas']);
    }

    private function direccion(): array
    {
        return [
            'country_code' => 'co', 'lat' => 1.21, 'lon' => -77.28,
            'address_line1' => 'Parque central', 'suburb' => 'Centro',
            'city' => 'Pasto', 'state' => 'Nariño', 'formatted' => 'Parque central, Pasto, Colombia',
        ];
    }

    private function consulta(): array
    {
        return ['direccion' => 'Parque central', 'ciudad' => 'Pasto', 'departamento' => 'Nariño'];
    }

    public function test_busca_en_colombia_y_reutiliza_la_cache_sin_exponer_la_clave(): void
    {
        Http::fake(['api.geoapify.com/*' => Http::response(['results' => [
            $this->direccion(), array_replace($this->direccion(), ['country_code' => 'ec']),
        ]])]);
        for ($i = 0; $i < 2; $i++) {
            $this->postJson('/ubicaciones/buscar', $this->consulta())->assertOk()
                ->assertJsonCount(1, 'resultados')->assertJsonPath('resultados.0.ciudad', 'Pasto')
                ->assertJsonPath('resultados.0.barrio', 'Centro')
                ->assertDontSee('clave-solo-para-pruebas');
        }
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['filter'] === 'countrycode:co'
            && $request['lang'] === 'es' && str_contains($request['text'], 'Colombia'));
    }

    public function test_consulta_inversa_y_admite_campos_ausentes(): void
    {
        $direccion = $this->direccion();
        unset($direccion['suburb']);
        Http::fake(['api.geoapify.com/*' => Http::response(['results' => [$direccion]])]);
        $this->postJson('/ubicaciones/invertir', ['latitud' => 1.21, 'longitud' => -77.28])
            ->assertOk()->assertJsonPath('direccion.barrio', '')->assertJsonPath('direccion.departamento', 'Nariño');
    }

    public function test_rechaza_coordenadas_o_consultas_invalidas_sin_contactar_al_proveedor(): void
    {
        $this->postJson('/ubicaciones/invertir', ['latitud' => 91, 'longitud' => -77.28])->assertUnprocessable();
        $this->postJson('/ubicaciones/buscar', ['direccion' => ['valor']])->assertUnprocessable();
        Http::assertNothingSent();
    }

    public function test_sin_clave_devuelve_un_error_controlado_y_permite_la_alternativa_manual(): void
    {
        config(['ubicaciones.geoapify_key' => '']);
        $this->postJson('/ubicaciones/buscar', $this->consulta())->assertStatus(503)->assertSee('mapa');
        Http::assertNothingSent();
    }

    public function test_oculta_los_errores_del_proveedor(): void
    {
        Http::fake(['api.geoapify.com/*' => Http::response(['apiKey' => 'secreto'], 401)]);
        $this->postJson('/ubicaciones/buscar', $this->consulta())->assertStatus(503)->assertDontSee('secreto');
    }

    public function test_limita_las_consultas_para_proteger_la_cuota(): void
    {
        config(['ubicaciones.consultas_por_minuto' => 1]);
        Http::fake(['api.geoapify.com/*' => Http::response(['results' => []])]);
        $this->postJson('/ubicaciones/buscar', $this->consulta())->assertOk();
        $this->postJson('/ubicaciones/buscar', $this->consulta())->assertStatus(429);
        Http::assertSentCount(1);
    }
}
