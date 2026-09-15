<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('imagen_mascotas', function (Blueprint $table) {
            $table->foreignId('mascota_id')->nullable()->constrained('mascotas')->onDelete('cascade');
            $table->string('ruta_imagen')->nullable();
            $table->string('descripcion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('imagen_mascotas', function (Blueprint $table) {
            $table->dropForeign(['mascota_id']);
            $table->dropColumn(['mascota_id', 'ruta_imagen', 'descripcion']);
        });
    }
};
