<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imagen_mascotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mascota_id')->constrained('mascotas')->onDelete('cascade');
            $table->string('ruta_imagen');
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imagen_mascotas');
    }
};