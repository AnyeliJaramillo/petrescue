<?php

namespace Tests\Feature;

use App\Application\Reportes\Contracts\AlmacenImagenes;
use App\Application\Reportes\Data\ImagenEntrada;
use App\Models\ImagenMascota;
use App\Models\Reporte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SupabaseImagenesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'imagenes.almacen' => 'supabase',
            'imagenes.supabase.url' => 'https://proyecto.supabase.co',
            'imagenes.supabase.key' => 'sb_secret_prueba',
            'imagenes.supabase.bucket' => 'mascotas',
        ]);
        Http::preventStrayRequests();
        Storage::fake('public');
    }

    private function datos(): array
    {
        return [
            'responsable_nombre' => 'Ana', 'responsable_telefono' => '3001234567',
            'especie' => 'perro', 'color_principal' => 'negro', 'tamano' => 'mediano',
            'fecha_evento' => '2026-09-01', 'ciudad' => 'Pasto',
            'imagenes' => [UploadedFile::fake()->image('foto.png')],
        ];
    }

    public function test_publica_ambos_reportes_y_muestra_la_url_compartida(): void
    {
        Http::fake(['https://proyecto.supabase.co/storage/v1/object/*' => Http::response(['Key' => 'foto'], 200)]);

        foreach (['perdidos', 'encontrados'] as $tipo) {
            $datos = $this->datos();
            $contenido = file_get_contents($datos['imagenes'][0]->getPathname());
            $this->post('/reportes-'.$tipo, $datos)->assertSessionHasNoErrors()->assertRedirect();
            $reporte = Reporte::latest('id')->firstOrFail();
            $url = $reporte->mascota->imagenes->first()->ruta_imagen;
            $this->assertStringStartsWith('https://proyecto.supabase.co/storage/v1/object/public/mascotas/mascotas/', $url);
            $this->get('/reportes-'.$tipo)->assertOk()->assertSee($url, false)->assertDontSee('sb_secret_prueba');
            $this->get('/reportes-'.$tipo.'/'.$reporte->id)->assertOk()->assertSee($url, false);
            Http::assertSent(fn (Request $request) => $request->method() === 'POST'
                && $request->hasHeader('apikey', 'sb_secret_prueba')
                && ! $request->hasHeader('Authorization')
                && $request->hasHeader('Content-Type', 'image/png')
                && $request->body() === $contenido);
        }
        Http::assertSentCount(2);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_si_falla_la_segunda_subida_limpia_la_primera_y_revierte_el_reporte(): void
    {
        Http::fake(['https://proyecto.supabase.co/storage/v1/object/*' => Http::sequence()
            ->push(['Key' => 'foto'], 200)->push(['message' => 'fallo'], 500)->push([], 200)]);
        $datos = $this->datos();
        $datos['imagenes'][] = UploadedFile::fake()->image('otra.jpg');
        $this->post('/reportes-perdidos', $datos)->assertServerError();
        $primera = Http::recorded()[0][0]->url();
        Http::assertSent(fn (Request $request) => $request->method() === 'DELETE'
            && $request->url() === 'https://proyecto.supabase.co/storage/v1/object/mascotas'
            && $request['prefixes'] === [substr($primera, strlen('https://proyecto.supabase.co/storage/v1/object/mascotas/'))]);
        $this->assertDatabaseCount('reportes', 0);
        $this->assertDatabaseCount('imagen_mascotas', 0);
        Http::assertSentCount(3);
    }

    public function test_si_falla_la_base_de_datos_elimina_la_foto_subida(): void
    {
        Http::fake(['https://proyecto.supabase.co/storage/v1/object/*' => Http::response([], 200)]);
        ImagenMascota::creating(fn () => throw new \RuntimeException('Fallo simulado'));
        try {
            $this->post('/reportes-encontrados', $this->datos())->assertServerError();
            Http::assertSent(fn (Request $request) => $request->method() === 'DELETE' && count($request['prefixes']) === 1);
            $this->assertDatabaseCount('reportes', 0);
            $this->assertDatabaseCount('mascotas', 0);
        } finally {
            ImagenMascota::flushEventListeners();
        }
    }

    public function test_admite_la_clave_service_role_heredada(): void
    {
        config(['imagenes.supabase.key' => 'jwt-de-prueba']);
        Http::fake(['https://proyecto.supabase.co/storage/v1/object/*' => Http::response([], 200)]);
        $foto = UploadedFile::fake()->image('foto.jpg');
        app(AlmacenImagenes::class)->guardar(new ImagenEntrada($foto->getPathname(), 'jpg'));
        Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'Bearer jwt-de-prueba')
            && $request->hasHeader('apikey', 'jwt-de-prueba'));
    }

    public function test_falla_sin_clave_y_no_guarda_silenciosamente_en_local(): void
    {
        config(['imagenes.supabase.key' => '']);
        $this->post('/reportes-perdidos', $this->datos())->assertServerError();
        Http::assertNothingSent();
        $this->assertDatabaseCount('reportes', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}
