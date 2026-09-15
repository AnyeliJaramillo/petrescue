<?php

namespace App\Application\Reportes\UseCases;

use App\Application\Reportes\Contracts\AlmacenImagenes;
use App\Application\Reportes\Contracts\RepositorioReportes;
use App\Application\Reportes\Contracts\Transacciones;
use App\Application\Reportes\Data\NuevoReporte;
use App\Domain\Reportes\TipoReporte;
use Throwable;

final class PublicarReporte
{
    public function __construct(
        private RepositorioReportes $reportes,
        private AlmacenImagenes $imagenes,
        private Transacciones $transacciones,
    ) {}

    public function ejecutar(NuevoReporte $datos, TipoReporte $tipo): int
    {
        $rutas = [];

        try {
            return $this->transacciones->ejecutar(function () use ($datos, $tipo, &$rutas) {
                foreach ($datos->imagenes as $imagen) {
                    $rutas[] = $this->imagenes->guardar($imagen);
                }

                return $this->reportes->crear($datos, $tipo, $rutas);
            });
        } catch (Throwable $error) {
            $this->imagenes->eliminar($rutas);
            throw $error;
        }
    }
}
