<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ArquitecturaTest extends TestCase
{
    public function test_las_capas_internas_no_importan_framework_http_ni_persistencia(): void
    {
        $root = dirname(__DIR__, 2);
        foreach (['Domain', 'Application'] as $capa) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/app/'.$capa)) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }
                $codigo = file_get_contents($file->getPathname());
                $this->assertDoesNotMatchRegularExpression(
                    '~(?:Illuminate|Symfony|App\\\\(?:Models|Infrastructure|Http|Providers))\\\\~',
                    $codigo, $file->getPathname(),
                );
                if ($capa === 'Domain') {
                    $this->assertStringNotContainsString('App\\Application\\', $codigo);
                }
            }
        }
    }

    public function test_los_controladores_no_consultan_modelos_ni_infraestructura(): void
    {
        foreach (glob(dirname(__DIR__, 2).'/app/Http/Controllers/*.php') as $file) {
            $codigo = file_get_contents($file);
            $this->assertStringNotContainsString('App\\Models\\', $codigo);
            $this->assertStringNotContainsString('App\\Infrastructure\\', $codigo);
            $this->assertStringNotContainsString('Facades\\DB', $codigo);
            $this->assertStringNotContainsString('Facades\\Storage', $codigo);
        }
    }
}
