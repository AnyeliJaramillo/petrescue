<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // La tabla ya existe desde 170452. La migración 180700 añade sus campos.
        // Se conserva este archivo para respetar el historial de instalaciones existentes.
    }

    public function down(): void
    {
        // Esta migración no crea tablas ni columnas.
    }
};
