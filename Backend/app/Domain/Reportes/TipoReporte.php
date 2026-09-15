<?php

namespace App\Domain\Reportes;

enum TipoReporte: string
{
    case Perdida = 'perdida';
    case Encontrada = 'encontrada';

    public function titulo(?string $nombre, string $especie): string
    {
        return mb_substr('Mascota '.$this->value.': '.($nombre ?: $especie), 0, 255);
    }
}
