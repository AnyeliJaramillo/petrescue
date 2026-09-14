<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    protected $fillable = [
        'mascota_id',
        'tipo_reporte',
        'titulo',
        'descripcion',
        'fecha_evento',
        'estado',
        'responsable_nombre',
        'responsable_correo',
        'responsable_telefono',
    ];

    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }

    public function ubicacion()
    {
        return $this->hasOne(Ubicacion::class);
    }
}