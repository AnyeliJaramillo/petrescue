@extends('layouts.app')

@section('title')
{{ $reporte->titulo }} — Huellas en Casa
@endsection

@section('content')
<header class="max-w-5xl mx-auto px-6 py-6 flex flex-wrap justify-between gap-4">
        <a href="{{ route('home') }}" class="font-bold">Huellas en Casa</a>
        <a href="{{ route($ruta . '.index') }}">Volver a los reportes</a>
    </header>
    <main class="max-w-5xl mx-auto px-6 pb-12">
        <article class="bg-white rounded-3xl p-6 md:p-10 shadow">
            <p class="text-[#2f7d68] font-semibold">{{ ucfirst($reporte->tipo_reporte) }} · {{ ucfirst($reporte->estado) }}</p>
            <h1 class="text-3xl font-bold mt-2 mb-6">{{ $reporte->titulo }}</h1>
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                @forelse ($reporte->imagenes as $imagen)
                    <img src="{{ $imagen['url'] }}" alt="{{ $imagen['descripcion'] ?? 'Foto de la mascota' }}" class="w-full rounded-2xl max-h-96 object-contain bg-[#f8f4ec]">
                @empty
                    <p class="rounded-2xl bg-[#f8f4ec] p-8">Este reporte todavía no tiene fotos.</p>
                @endforelse
            </div>
            <div class="grid md:grid-cols-2 gap-8">
                <section>
                    <h2 class="text-xl font-bold mb-4">Datos de la mascota</h2>
                    <dl class="space-y-2">
                        @foreach (['nombre' => 'Nombre', 'especie' => 'Especie', 'raza' => 'Raza', 'tamano' => 'Tamaño', 'sexo' => 'Sexo', 'color_principal' => 'Color principal', 'color_secundario' => 'Color secundario', 'edad_aproximada' => 'Edad aproximada', 'rasgos_distintivos' => 'Rasgos distintivos'] as $campo => $etiqueta)
                            <div><dt class="font-semibold inline">{{ $etiqueta }}:</dt> <dd class="inline">{{ ($reporte->mascota[$campo] ?? null) ?: 'No registrado' }}</dd></div>
                        @endforeach
                    </dl>
                </section>
                <section>
                    <h2 class="text-xl font-bold mb-4">Fecha y ubicación</h2>
                    <p>Fecha: {{ $reporte->fecha_evento }}</p>
                    @if ($reporte->ubicacion)
                        @foreach (['ciudad', 'departamento', 'barrio', 'direccion', 'referencia'] as $campo)
                            @if ($reporte->ubicacion[$campo])
                                <p>{{ ucfirst($campo) }}: {{ $reporte->ubicacion[$campo] }}</p>
                            @endif
                        @endforeach
                        @if ($reporte->ubicacion['latitud'] !== null && $reporte->ubicacion['longitud'] !== null)
                            <p class="text-[#2f7d68] mt-2">✓ Ubicación guardada con el reporte.</p>
                        @endif
                    @else
                        <p>Ubicación no registrada.</p>
                    @endif
                    <h2 class="text-xl font-bold mt-6 mb-2">Contacto</h2>
                    <p>{{ $reporte->responsable_nombre }}</p>
                    <p>{{ $reporte->responsable_telefono }}</p>
                    @if ($reporte->responsable_correo)
                        <p>{{ $reporte->responsable_correo }}</p>
                    @endif
                </section>
            </div>
            @if ($reporte->descripcion)
                <section class="mt-8 border-t pt-6">
                    <h2 class="text-xl font-bold mb-2">Descripción</h2>
                    <p class="whitespace-pre-line break-words">{{ $reporte->descripcion }}</p>
                </section>
            @endif
        </article>
    </main>
@endsection
