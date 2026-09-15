<?php

namespace App\Application\Reportes\Data;

final readonly class ImagenEntrada
{
    public function __construct(public string $rutaTemporal, public string $extension) {}
}
