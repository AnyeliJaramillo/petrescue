<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    protected $table = 'ubicaciones';

    protected $fillable = [
        'reporte_id',
        'direccion',
        'barrio',
        'ciudad',
        'departamento',
        'latitud',
        'longitud',
        'referencia',
    ];

    public function reporte()
    {
        return $this->belongsTo(Reporte::class);
    }
}
