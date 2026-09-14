<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mascotas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->string('especie');
            $table->string('raza')->nullable();
            $table->string('color_principal');
            $table->string('color_secundario')->nullable();
            $table->string('tamano');
            $table->string('sexo')->nullable();
            $table->string('edad_aproximada')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('rasgos_distintivos')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mascotas');
    }
};