<?php

namespace Tests\Feature;

use App\Application\Reportes\Data\ReporteDetalle;
use App\Models\ImagenMascota;
use App\Models\Mascota;
use App\Models\Reporte;
use App\Models\Ubicacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportesTest extends TestCase
{
    use RefreshDatabase;

    public function test_las_vistas_reciben_datos_sin_modelos_y_conservan_la_paginacion(): void
    {
        for ($i = 1; $i <= 13; $i++) {
            $this->post('/reportes-perdidos', $this->datos() + ['nombre' => 'Mascota '.$i])->assertRedirect();
        }

        $response = $this->get('/reportes-perdidos');
        $response->assertOk()->assertSee('page=2', false)->assertDontSee('@endsection');
        $pagina = $response->viewData('reportes');
        $this->assertCount(12, $pagina->items());
        $this->assertInstanceOf(ReporteDetalle::class, $pagina->items()[0]);
        $this->assertIsArray($pagina->items()[0]->mascota);
        $this->get('/reportes-perdidos?page=2')->assertOk()->assertSee('Mascota 1');
        $this->get('/reportes-perdidos?page=-2')->assertOk();
    }

    private function datos(): array
    {
        return [
            'responsable_nombre' => 'Ana',
            'responsable_telefono' => '3001234567',
            'especie' => 'perro',
            'color_principal' => 'negro',
            'tamano' => 'mediano',
            'fecha_evento' => '2026-09-01',
            'ciudad' => 'Pasto',
        ];
    }

    public function test_publica_y_muestra_ambos_tipos_de_reporte_con_fotos(): void
    {
        Storage::fake('public');

        foreach (['perdidos' => 'perdida', 'encontrados' => 'encontrada'] as $ruta => $tipo) {
            $this->get("/reportes-$ruta/crear")->assertOk()->assertSee('name="especie"', false)
                ->assertSee('data-selector-fotos', false)
                ->assertSee('data-fotos-previews', false)
                ->assertSee('name="imagenes[]"', false)
                ->assertSee('data-selector-ubicacion', false)
                ->assertSee('type="hidden" name="latitud"', false)
                ->assertSee('type="hidden" name="longitud"', false);
            $this->post("/reportes-$ruta", $this->datos() + [
                'imagenes' => [UploadedFile::fake()->image('mascota.jpg')],
                'imagenes_cantidad' => '1',
                'latitud' => '1.2136', 'longitud' => '-77.2811',
                'ubicacion_confirmada' => '1', 'departamento' => 'Nariño', 'direccion' => 'Parque central',
            ])->assertSessionHasNoErrors()->assertRedirect(route("reportes-$ruta.index"));

            $reporte = Reporte::where('tipo_reporte', $tipo)->firstOrFail();
            $this->assertSame('Pasto', $reporte->ubicacion->ciudad);
            $this->assertSame('Parque central', $reporte->ubicacion->direccion);
            $this->assertSame('Nariño', $reporte->ubicacion->departamento);
            $this->assertEqualsWithDelta(1.2136, (float) $reporte->ubicacion->latitud, 0.000001);
            $this->assertEqualsWithDelta(-77.2811, (float) $reporte->ubicacion->longitud, 0.000001);
            Storage::disk('public')->assertExists($reporte->mascota->imagenes->first()->ruta_imagen);
            $this->get("/reportes-$ruta")->assertOk()->assertSee('Ana');
            $this->get("/reportes-$ruta/{$reporte->id}")->assertOk()->assertSee('3001234567');
            $urlImagen = asset('storage/'.$reporte->mascota->imagenes->first()->ruta_imagen);
            $this->get("/reportes-$ruta")->assertSee($urlImagen, false);
            $this->get("/reportes-$ruta/{$reporte->id}")->assertSee($urlImagen, false)
                ->assertDontSee('Este reporte todavía no tiene fotos.');
        }

        $this->assertDatabaseCount('mascotas', 2);
        $this->assertDatabaseCount('reportes', 2);
        $this->assertDatabaseCount('ubicaciones', 2);
        $this->assertDatabaseCount('imagen_mascotas', 2);
        $this->get('/reportes-perdidos/'.Reporte::where('tipo_reporte', 'encontrada')->first()->id)->assertNotFound();
        $this->get('/reportes-encontrados/999999')->assertNotFound();
        $this->get('/reportes-perdidos/'.str_repeat('9', 30))->assertNotFound();
        $this->get('/reportes-encontrados/0')->assertNotFound();
    }

    public function test_rechaza_datos_invalidos_sin_crear_registros(): void
    {
        foreach (['perdidos', 'encontrados'] as $ruta) {
            foreach ([
                ['imagenes' => 'no-es-un-arreglo'],
                ['imagenes' => [UploadedFile::fake()->create('archivo.txt', 1, 'text/plain')]],
                ['latitud' => 91, 'longitud' => -181],
                ['latitud' => 1],
                ['latitud' => 1.21, 'longitud' => -77.28, 'ubicacion_confirmada' => '0'],
                ['ubicacion_confirmada' => '1'],
                ['color_secundario' => ['valor']],
                ['departamento' => str_repeat('x', 101)],
                ['fecha_evento' => '2099-01-01'],
            ] as $invalido) {
                $this->post("/reportes-$ruta", array_replace($this->datos(), $invalido))
                    ->assertSessionHasErrors();
            }
        }

        $this->assertDatabaseCount('mascotas', 0);
        $this->assertDatabaseCount('reportes', 0);
    }

    public function test_no_publica_si_las_fotos_seleccionadas_no_llegan_y_pide_seleccionarlas_de_nuevo(): void
    {
        Storage::fake('public');
        foreach (['perdidos', 'encontrados'] as $ruta) {
            $this->from("/reportes-$ruta/crear")->post("/reportes-$ruta", $this->datos() + [
                'imagenes_cantidad' => '1',
            ])->assertSessionHasErrors('imagenes')->assertRedirect("/reportes-$ruta/crear");
            $this->get("/reportes-$ruta/crear")->assertOk()
                ->assertSee('No llegaron todas las fotos seleccionadas')
                ->assertSee('selecciónalas nuevamente');
            $this->post("/reportes-$ruta", $this->datos() + [
                'imagenes_cantidad' => '2',
                'imagenes' => [UploadedFile::fake()->image('una.jpg')],
            ])->assertSessionHasErrors('imagenes');
        }
        $this->assertDatabaseCount('reportes', 0);
        $this->assertDatabaseCount('imagen_mascotas', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_un_error_al_guardar_revierte_los_registros_y_las_fotos(): void
    {
        Storage::fake('public');
        ImagenMascota::creating(function () {
            throw new \RuntimeException('Fallo de persistencia simulado');
        });

        try {
            $this->post('/reportes-perdidos', $this->datos() + [
                'imagenes' => [UploadedFile::fake()->image('mascota.png')],
            ])->assertServerError();

            $this->assertSame([], Storage::disk('public')->allFiles());
            $this->assertSame(0, Mascota::count());
            $this->assertSame(0, Reporte::count());
            $this->assertSame(0, Ubicacion::count());
        } finally {
            ImagenMascota::flushEventListeners();
        }
    }

    public function test_el_formulario_conserva_datos_y_muestra_el_error(): void
    {
        $this->followingRedirects()->from('/reportes-encontrados/crear')
            ->post('/reportes-encontrados', array_replace($this->datos(), ['responsable_correo' => 'incorrecto']))
            ->assertOk()->assertSee('value="Ana"', false)->assertSee('responsable_correo');

        $this->followingRedirects()->from('/reportes-perdidos/crear')
            ->post('/reportes-perdidos', array_replace($this->datos(), ['nombre' => ['invalido']]))
            ->assertOk()->assertSee('debe ser texto');
    }

    public function test_admite_nombre_largo_sin_fotos_y_escapa_el_contenido_publicado(): void
    {
        $this->post('/reportes-encontrados', $this->datos() + [
            'nombre' => str_repeat('ñ', 255),
            'descripcion' => '<script>alert(1)</script>',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $reporte = Reporte::firstOrFail();
        $this->assertLessThanOrEqual(255, mb_strlen($reporte->titulo));
        $this->assertSame(0, $reporte->mascota->imagenes->count());
        $this->get('/reportes-encontrados/'.$reporte->id)->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}
