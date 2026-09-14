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

    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }
}