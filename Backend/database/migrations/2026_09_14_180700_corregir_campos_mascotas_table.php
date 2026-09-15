<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mascotas', function (Blueprint $table) {
            if (!Schema::hasColumn('mascotas', 'nombre')) {
                $table->string('nombre')->nullable();
            }

            if (!Schema::hasColumn('mascotas', 'especie')) {
                $table->string('especie')->nullable();
            }

            if (!Schema::hasColumn('mascotas', 'raza')) {
                $table->string('raza')->nullable();
            }

            if (!Schema::hasColumn('mascotas', 'color_principal')) {
                $table->string('color_principal')->nullable();
            }

            if (!Schema::hasColumn('mascotas', 'color_secundario')) {
                $table->string('color_secundario')->nullable();
            }

            if (!Schema::hasColumn('mascotas', 'tamano')) {
                $table->string('tamano')->nullable();
            }

            if (!Schema::hasColumn('mascotas', 'sexo')) {
                $table->string('sexo')->nullable();
            }

            if (!Schema::hasColumn('mascotas', 'edad_aproximada')) {
                $table->string('edad_aproximada')->nullable();
            }

            if (!Schema::hasColumn('mascotas', 'descripcion')) {
                $table->text('descripcion')->nullable();
            }

            if (!Schema::hasColumn('mascotas', 'rasgos_distintivos')) {
                $table->text('rasgos_distintivos')->nullable();
            }
        });
    }

    public function down(): void
    {
        //
    }
};