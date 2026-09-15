<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mascota_id')->constrained('mascotas')->onDelete('cascade');
            $table->string('tipo_reporte');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->date('fecha_evento');
            $table->string('estado')->default('activo');

            $table->string('responsable_nombre');
            $table->string('responsable_correo')->nullable();
            $table->string('responsable_telefono');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};