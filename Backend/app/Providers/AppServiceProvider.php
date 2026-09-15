<?php

namespace App\Providers;

use App\Application\Reportes\Contracts\AlmacenImagenes;
use App\Application\Reportes\Contracts\RepositorioReportes;
use App\Application\Reportes\Contracts\Transacciones;
use App\Application\Ubicaciones\Contracts\Geocodificador;
use App\Infrastructure\Geocoding\GeoapifyGeocodificador;
use App\Infrastructure\Persistence\EloquentRepositorioReportes;
use App\Infrastructure\Persistence\LaravelTransacciones;
use App\Infrastructure\Storage\LaravelAlmacenImagenes;
use App\Infrastructure\Storage\SupabaseAlmacenImagenes;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Geocodificador::class, GeoapifyGeocodificador::class);
        $this->app->bind(RepositorioReportes::class, EloquentRepositorioReportes::class);
        $this->app->bind(AlmacenImagenes::class, fn ($app) => match (config('imagenes.almacen')) {
            'local' => $app->make(LaravelAlmacenImagenes::class),
            'supabase' => $app->make(SupabaseAlmacenImagenes::class),
            default => throw new \RuntimeException('IMAGENES_ALMACEN debe ser local o supabase.'),
        });
        $this->app->bind(Transacciones::class, LaravelTransacciones::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('geocoding', fn (Request $request) => [
            Limit::perMinute(20)->by('geocoding:ip:'.$request->ip()),
            Limit::perMinute(max(1, config('ubicaciones.consultas_por_minuto')))->by('geocoding:global'),
        ]);
    }
}
