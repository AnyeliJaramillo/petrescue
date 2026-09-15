<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImagenMascota extends Model
{
    protected $fillable = [
        'mascota_id',
        'ruta_imagen',
        'descripcion',
    ];

    public function getUrlAttribute(): string
    {
        $ruta = (string) $this->ruta_imagen;

        return preg_match('#^https?://#i', $ruta) ? $ruta : asset('storage/'.$ruta);
    }

    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }
}
