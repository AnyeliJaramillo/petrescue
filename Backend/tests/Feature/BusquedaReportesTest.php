<?php

namespace Tests\Feature;

use App\Application\Reportes\Contracts\RepositorioReportes;
use App\Application\Reportes\Data\ReporteTarjeta;
use App\Models\Mascota;
use App\Models\Reporte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusquedaReportesTest extends TestCase
{
    use RefreshDatabase;

    private function reporte(array $mascota = [], array $reporte = []): Reporte
    {
        $pet = Mascota::create(array_replace([
            'nombre' => 'Luna', 'especie' => 'perro', 'color_principal' => 'negro',
            'tamano' => 'mediano', 'sexo' => 'hembra', 'raza' => 'labrador',
        ], $mascota));
        $result = $pet->reportes()->create(array_replace([
            'tipo_reporte' => 'perdida', 'titulo' => 'Mascota perdida', 'estado' => 'activo',
            'fecha_evento' => '2026-01-01', 'responsable_nombre' => 'Contacto privado',
            'responsable_telefono' => '3001234567', 'responsable_correo' => 'privado@example.com',
        ], $reporte));
        $result->ubicacion()->create(['ciudad' => 'Pasto', 'barrio' => 'Centro', 'direccion' => 'Dirección exacta']);

        return $result;
    }

    private function ids(array $filtros = []): array
    {
        return array_column($this->get('/buscar?'.http_build_query($filtros))
            ->assertOk()->viewData('reportes')->items(), 'id');
    }

    public function test_busqueda_publica_inicial_y_cada_tipo(): void
    {
        $lost = $this->reporte();
        $found = $this->reporte([], ['tipo_reporte' => 'encontrada']);
        $this->assertEqualsCanonicalizing([$lost->id, $found->id], $this->ids());
        $this->assertSame([$lost->id], $this->ids(['tipo_reporte' => 'perdida']));
        $this->assertSame([$found->id], $this->ids(['tipo_reporte' => 'encontrada']));
    }

    public function test_cada_filtro_real_y_su_combinacion_utilizan_and(): void
    {
        $match = $this->reporte();
        $this->reporte(['especie' => 'gato', 'color_principal' => 'blanco', 'tamano' => 'pequeño', 'sexo' => 'macho', 'raza' => 'persa']);
        $filters = ['especie' => 'perro', 'color_principal' => 'negro', 'tamano' => 'mediano', 'sexo' => 'hembra', 'raza' => 'labrador'];
        foreach ($filters as $key => $value) {
            $this->assertSame([$match->id], $this->ids([$key => $value]));
        }
        $this->reporte(['color_principal' => 'blanco']);
        $this->reporte(['tamano' => 'grande']);
        $this->assertSame([$match->id], $this->ids($filters + ['tipo_reporte' => 'perdida']));
        $this->assertSame([], $this->ids(['especie' => 'gato', 'color_principal' => 'negro']));
    }

    public function test_ubicacion_busca_en_barrio_ciudad_y_departamento_y_se_combina_con_and(): void
    {
        $pasto = $this->reporte([], []);
        $pasto->ubicacion()->update(['barrio' => 'San Ignacio', 'ciudad' => 'Pasto', 'departamento' => 'Nariño']);
        $bogota = $this->reporte(['especie' => 'gato'], []);
        $bogota->ubicacion()->update(['barrio' => 'Centro', 'ciudad' => 'Bogotá', 'departamento' => 'Cundinamarca']);

        $this->assertSame([$pasto->id], $this->ids(['ubicacion' => 'past']));
        $this->assertSame([$pasto->id], $this->ids(['ubicacion' => 'Nariño']));
        $this->assertSame([$pasto->id], $this->ids(['ubicacion' => 'san ignacio']));
        $this->assertSame([$pasto->id], $this->ids(['especie' => 'perro', 'ubicacion' => 'Pasto']));
        $this->assertSame([], $this->ids(['especie' => 'perro', 'tamano' => 'grande', 'ubicacion' => 'Pasto']));
        $this->assertSame([], $this->ids(['ubicacion' => 'Lugar inexistente']));
        $this->assertEqualsCanonicalizing([$pasto->id, $bogota->id], $this->ids(['ubicacion' => '']));
    }

    public function test_excluye_inactivos_aunque_el_cliente_envie_estado(): void
    {
        $active = $this->reporte();
        foreach (['cerrado', 'resuelto', 'inactivo', 'eliminado'] as $estado) {
            $this->reporte(['especie' => 'solo-inactiva'], ['estado' => $estado]);
        }
        $this->assertSame([$active->id], $this->ids(['estado' => 'cerrado']));
        $options = $this->get('/buscar')->viewData('opciones');
        $this->assertNotContains('solo-inactiva', $options['especie']);
        $this->get('/reportes-perdidos')->assertViewHas('reportes', fn ($pagina) => $pagina->total() === 1);
    }

    public function test_parametros_son_validados_y_los_valores_no_se_interpolan_en_sql(): void
    {
        $this->reporte();
        foreach ([['tipo_reporte' => 'otro'], ['especie' => ['perro']], ['tamano' => str_repeat('x', 51)], ['ubicacion' => str_repeat('x', 151)], ['page' => -1], ['page' => '1 OR 1=1']] as $invalid) {
            $this->getJson('/buscar?'.http_build_query($invalid))->assertUnprocessable()->assertJsonValidationErrors(array_key_first($invalid));
        }
        $this->get('/buscar?tipo_reporte=invalido')->assertRedirect(route('busquedas.index'))->assertSessionHasErrors('tipo_reporte');
        $this->assertSame([], $this->ids(['raza' => "' OR 1=1 --"]));
        $this->assertSame(1, Reporte::count());
    }

    public function test_pagina_conserva_filtros_y_orden_estable_sin_contactos_en_dto(): void
    {
        for ($i = 0; $i < 13; $i++) {
            $this->reporte(['nombre' => 'Mascota '.$i]);
        }
        $response = $this->get('/buscar?especie=perro&estado=cerrado')->assertOk();
        $page = $response->viewData('reportes');
        $this->assertSame(13, $page->total());
        $this->assertCount(12, $page->items());
        $this->assertStringContainsString('especie=perro', $page->nextPageUrl());
        $this->assertStringNotContainsString('estado=', $page->nextPageUrl());
        $card = $page->items()[0];
        $this->assertInstanceOf(ReporteTarjeta::class, $card);
        $this->assertFalse(property_exists($card, 'responsable_telefono'));
        $this->assertFalse(property_exists($card, 'responsable_correo'));
        $this->assertArrayNotHasKey('direccion', $card->ubicacion);
        $second = $this->get('/buscar?especie=perro&page=2')->viewData('reportes');
        $this->assertCount(1, $second->items());
        $this->assertSame('Mascota 0', $second->items()[0]->mascota['nombre']);
    }

    public function test_imagenes_locales_y_remotas_tienen_una_foto_por_tarjeta_sin_n_mas_uno(): void
    {
        $one = $this->reporte();
        $two = $this->reporte();
        $one->mascota->imagenes()->create(['ruta_imagen' => 'mascotas/primera.png']);
        $one->mascota->imagenes()->create(['ruta_imagen' => 'mascotas/segunda.png']);
        $two->mascota->imagenes()->create(['ruta_imagen' => 'https://example.com/mascota.jpg']);
        $connection = $this->app->make('db')->connection();
        $connection->enableQueryLog();
        $connection->flushQueryLog();
        $cards = $this->get('/buscar')->assertOk()->viewData('reportes')->keyBy('id');
        $firstCount = count($connection->getQueryLog());
        $this->assertSame(asset('storage/mascotas/primera.png'), $cards[$one->id]->imagen_url);
        $this->assertSame('https://example.com/mascota.jpg', $cards[$two->id]->imagen_url);
        for ($i = 0; $i < 8; $i++) { $this->reporte(); }
        $connection->flushQueryLog();
        $this->get('/buscar')->assertOk();
        $this->assertSame($firstCount, count($connection->getQueryLog()));
        $connection->disableQueryLog();
    }

    public function test_opciones_proceden_de_datos_y_no_hay_coincidencias_parciales(): void
    {
        $match = $this->reporte(['especie' => ' Perro ', 'color_principal' => 'Negro', 'raza' => '']);
        $this->reporte(['color_principal' => 'negro y blanco', 'raza' => null]);
        $response = $this->get('/buscar?especie=perro&color_principal=negro');
        $this->assertSame([$match->id], array_column($response->viewData('reportes')->items(), 'id'));
        $this->assertSame(['perro', 'gato'], $response->viewData('opciones')['especie']);
        $this->assertContains('pug', $response->viewData('opciones')['raza']);
    }

    public function test_vista_conserva_filtros_muestra_fallback_y_omite_contactos(): void
    {
        $report = $this->reporte(['nombre' => null]);
        $this->get('/buscar?tipo_reporte=perdida&especie=perro')->assertOk()
            ->assertSee('Buscar mascotas reportadas')->assertSee('Mascota sin nombre')
            ->assertSee('Fotografía no disponible')->assertSee('Ver reporte')
            ->assertSee('value="perro" selected', false)
            ->assertSee(route('reportes-perdidos.show', $report->id), false)
            ->assertDontSee('name="estado"', false)->assertDontSee('3001234567')
            ->assertDontSee('privado@example.com')->assertDontSee('Dirección exacta');
        $this->get('/buscar?especie=gato')->assertOk()
            ->assertSee('No encontramos reportes con estos filtros.')
            ->assertSee('Limpiar filtros')->assertDontSee('<article class="search-card">', false);
    }

    public function test_fallo_de_consulta_muestra_error_recuperable_sin_exponer_detalles(): void
    {
        $repo = $this->createMock(RepositorioReportes::class);
        $repo->method('filtrar')->willThrowException(new \Illuminate\Database\QueryException(
            'sqlite', 'consulta interna', [], new \RuntimeException('detalle privado'),
        ));
        $this->app->instance(RepositorioReportes::class, $repo);
        $this->get('/buscar?especie=perro')->assertStatus(503)
            ->assertSee('No pudimos cargar los reportes. Inténtalo nuevamente.')
            ->assertSee('Intentar nuevamente')->assertDontSee('detalle privado')->assertDontSee('consulta interna');
    }
}
