<?php

namespace Tests\Unit;

use App\Application\Reportes\Contracts\AlmacenImagenes;
use App\Application\Reportes\Contracts\RepositorioReportes;
use App\Application\Reportes\Contracts\Transacciones;
use App\Application\Reportes\Data\ImagenEntrada;
use App\Application\Reportes\Data\NuevoReporte;
use App\Application\Reportes\UseCases\PublicarReporte;
use App\Domain\Reportes\TipoReporte;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class PublicarReporteTest extends TestCase
{
    private function transacciones(): Transacciones
    {
        return new class implements Transacciones
        {
            public function ejecutar(callable $operacion): mixed
            {
                return $operacion();
            }
        };
    }

    public function test_publica_sin_arrancar_laravel_ni_conectar_una_base(): void
    {
        $datos = new NuevoReporte(['especie' => 'gato'], [], [], [new ImagenEntrada('/tmp/foto', 'jpg')]);
        $imagenes = $this->createMock(AlmacenImagenes::class);
        $imagenes->expects($this->once())->method('guardar')->with($datos->imagenes[0])->willReturn('mascotas/foto.jpg');
        $imagenes->expects($this->never())->method('eliminar');
        $reportes = $this->createMock(RepositorioReportes::class);
        $reportes->expects($this->once())->method('crear')
            ->with($datos, TipoReporte::Encontrada, ['mascotas/foto.jpg'])->willReturn(42);

        $publicar = new PublicarReporte($reportes, $imagenes, $this->transacciones());
        $this->assertSame(42, $publicar->ejecutar($datos, TipoReporte::Encontrada));
    }

    public function test_si_falla_la_segunda_foto_limpia_la_primera_y_no_crea_el_reporte(): void
    {
        $datos = new NuevoReporte([], [], [], [new ImagenEntrada('/tmp/uno', 'jpg'), new ImagenEntrada('/tmp/dos', 'png')]);
        $imagenes = $this->createMock(AlmacenImagenes::class);
        $imagenes->expects($this->exactly(2))->method('guardar')->willReturnCallback(function (ImagenEntrada $imagen) {
            if ($imagen->extension === 'png') {
                throw new RuntimeException('Almacenamiento no disponible');
            }

            return 'mascotas/uno.jpg';
        });
        $imagenes->expects($this->once())->method('eliminar')->with(['mascotas/uno.jpg']);
        $reportes = $this->createMock(RepositorioReportes::class);
        $reportes->expects($this->never())->method('crear');

        $this->expectExceptionMessage('Almacenamiento no disponible');
        (new PublicarReporte($reportes, $imagenes, $this->transacciones()))->ejecutar($datos, TipoReporte::Perdida);
    }

    public function test_el_dominio_define_tipos_y_limita_el_titulo(): void
    {
        $this->assertSame('Mascota encontrada: gato', TipoReporte::Encontrada->titulo(null, 'gato'));
        $this->assertSame(255, mb_strlen(TipoReporte::Perdida->titulo(str_repeat('ñ', 255), 'perro')));
        $this->assertNull(TipoReporte::tryFrom('invalido'));
    }
}
