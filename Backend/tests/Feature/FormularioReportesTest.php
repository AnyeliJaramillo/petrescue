<?php

namespace Tests\Feature;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class FormularioReportesTest extends TestCase
{
    public function test_formularios_conservan_contrato_y_diferencian_presentacion(): void
    {
        foreach (['perdidos' => 'perdida', 'encontrados' => 'encontrada'] as $ruta => $tipo) {
            $response = $this->get("/reportes-$ruta/crear")->assertOk()
                ->assertSee('Reporta una mascota '.($tipo === 'encontrada' ? 'vista' : 'perdida'))
                ->assertSee('method="POST"', false)
                ->assertSee('enctype="multipart/form-data"', false)
                ->assertSee('action="'.route("reportes-$ruta.store").'"', false)
                ->assertSee('name="_token"', false)
                ->assertSee('name="color_secundario"', false)
                ->assertSee('name="referencia"', false)
                ->assertSee('max="'.now()->toDateString().'"', false)
                ->assertDontSee('name="tipo_reporte"', false);
            $html = $response->getContent();
            $this->assertSame(1, substr_count($html, '<form '));
            $this->assertSame(4, substr_count($html, 'data-report-step aria-labelledby'));
            $this->assertSame(1, substr_count($html, 'data-selector-ubicacion'));
            $this->assertSame(1, substr_count($html, 'data-selector-fotos'));
        }
    }

    public function test_old_y_errores_individuales_son_accesibles_incluso_en_fotos_y_coordenadas(): void
    {
        $errors = new ViewErrorBag;
        $errors->put('default', new MessageBag([
            'color_secundario' => 'Color no válido.',
            'imagenes.0' => 'Cada foto debe pesar como máximo 2 MB.',
            'latitud' => 'Selecciona y confirma nuevamente el punto en el mapa.',
            'referencia' => 'La referencia debe ser texto.',
        ]));
        foreach (['perdidos', 'encontrados'] as $ruta) {
            $this->withSession(['errors' => $errors, '_old_input' => [
                'nombre' => '<script>prueba</script>', 'color_secundario' => 'Blanco',
                'referencia' => 'Frente al parque', 'imagenes_cantidad' => 2,
                'tamano' => 'Otro tamaño admitido por el Request',
            ]])->get("/reportes-$ruta/crear")->assertOk()
                ->assertSee('value="Blanco"', false)
                ->assertSee('Frente al parque')
                ->assertSee('Otro tamaño admitido por el Request')
                ->assertSee('&lt;script&gt;prueba&lt;/script&gt;', false)
                ->assertDontSee('<script>prueba</script>', false)
                ->assertSee('aria-describedby="reporte-color_secundario-error" data-server-error', false)
                ->assertSee('id="reporte-referencia-error"', false)
                ->assertSee('id="imagenes-servidor"', false)
                ->assertSee('Cada foto debe pesar como máximo 2 MB.')
                ->assertSee('id="ubicacion-errores"', false)
                ->assertSee('selecciónalas nuevamente');
        }
    }
}
