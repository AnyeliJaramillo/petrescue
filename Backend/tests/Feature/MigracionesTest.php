<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigracionesTest extends TestCase
{
    public function test_actualiza_el_esquema_original_sin_perder_filas_y_permite_revertir(): void
    {
        // Esta prueba debe ejecutarse exclusivamente en la base efímera de PHPUnit.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        $originales = array_map(fn ($archivo) => 'database/migrations/'.$archivo, [
            '0001_01_01_000000_create_users_table.php',
            '0001_01_01_000001_create_cache_table.php',
            '0001_01_01_000002_create_jobs_table.php',
            '2026_09_14_170452_create_mascotas_table.php',
            '2026_09_14_170452_create_reportes_table.php',
            '2026_09_14_170452_create_ubicacions_table.php',
            '2026_09_14_170456_create_imagen_mascotas_table.php',
        ]);
        $this->artisan('migrate', ['--path' => $originales, '--force' => true])->assertSuccessful();
        DB::table('mascotas')->insert(['id' => 100]);
        DB::table('imagen_mascotas')->insert(['id' => 100]);
        DB::table('reportes')->insert([
            'mascota_id' => 100, 'tipo_reporte' => 'perdida', 'titulo' => 'Reporte previo',
            'fecha_evento' => '2026-09-01', 'responsable_nombre' => 'Ana', 'responsable_telefono' => '3001234567',
        ]);

        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $this->assertDatabaseHas('mascotas', ['id' => 100]);
        $this->assertDatabaseHas('imagen_mascotas', ['id' => 100]);
        $this->assertDatabaseHas('reportes', ['titulo' => 'Reporte previo']);
        $this->assertTrue(Schema::hasColumns('mascotas', ['nombre', 'especie', 'tamano']));
        $this->assertTrue(Schema::hasColumns('imagen_mascotas', ['mascota_id', 'ruta_imagen']));
        $this->assertTrue(Schema::hasColumn('ubicaciones', 'reporte_id'));

        $this->artisan('migrate:rollback', ['--force' => true])->assertSuccessful();
        $this->assertDatabaseHas('reportes', ['titulo' => 'Reporte previo']);
        $this->assertDatabaseHas('mascotas', ['id' => 100]);
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $this->artisan('migrate:reset', ['--force' => true])->assertSuccessful();
        $this->assertFalse(Schema::hasTable('mascotas'));
    }
}
