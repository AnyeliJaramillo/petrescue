<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mascota extends Model
{
    protected $fillable = [
        'nombre',
        'especie',
        'raza',
        'color_principal',
        'color_secundario',
        'tamano',
        'sexo',
        'edad_aproximada',
        'descripcion',
        'rasgos_distintivos',
    ];

    public function reportes()
    {
        return $this->hasMany(Reporte::class);
    }

    public function imagenes()
    {
        return $this->hasMany(ImagenMascota::class);
    }
}