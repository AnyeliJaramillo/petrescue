@extends('layouts.app')

@section('title')
Mascotas {{ $tipo }}s
@endsection

@section('content')
<header class="bg-[#f8f4ec] border-b border-[#e6ddd0]">
        <div class="max-w-6xl mx-auto px-6 py-5 flex flex-wrap gap-4 justify-between items-center">
            <a href="{{ route('home') }}" class="font-bold text-lg">
                Huellas en Casa
            </a>

            <nav class="flex flex-wrap gap-6 text-sm">
                <a href="{{ route('home') }}">Inicio</a>
                <a href="{{ route('reportes-perdidos.index') }}" class="text-[#2f7d68] font-semibold">Mascotas
                    perdidas</a>
                <a href="{{ route('reportes-encontrados.index') }}">Mascotas encontradas</a>
            </nav>

            <a href="{{ route($ruta . '.create') }}"
                class="border border-[#2f7d68] text-[#2f7d68] px-4 py-2 rounded-full text-sm font-semibold">
                Publicar reporte
            </a>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-12">

        @if (session('success'))
            <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-6">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex justify-between items-end mb-8">
            <div>
                <p class="text-sm text-[#2f7d68] font-semibold">Reportes publicados</p>
                <h1 class="text-4xl font-bold mt-2">Mascotas {{ $tipo }}s</h1>
                <p class="text-[#60746c] mt-2">
                    Aquí aparecen las mascotas {{ $tipo }}s publicadas por los usuarios.
                </p>
                <a href="{{ route('busquedas.index', ['tipo_reporte' => $tipo]) }}" class="home-button mt-4">Buscar por características</a>
            </div>
        </div>

        @if ($reportes->count() == 0)
            <div class="bg-white rounded-3xl p-10 text-center shadow">
                <p class="text-5xl mb-4">🐾</p>
                <h2 class="text-2xl font-bold">Aún no hay reportes publicados</h2>
                <p class="text-[#60746c] mt-2 mb-6">
                    Cuando se publique una mascota {{ $tipo }} aparecerá aquí.
                </p>
                <a href="{{ route($ruta . '.create') }}"
                    class="bg-[#2f7d68] text-white px-6 py-3 rounded-full font-semibold">
                    Publicar reporte
                </a>
            </div>
        @else
            <div class="grid md:grid-cols-3 gap-6">
                @foreach ($reportes as $reporte)
                    <div class="bg-white rounded-3xl shadow overflow-hidden border border-[#eadfd2]">

                        <div class="h-52 bg-[#eadfd2]">
                            @if ($reporte->imagenes !== [])
                                <img src="{{ $reporte->imagenes[0]['url'] }}"
                                    class="w-full h-full object-cover" alt="Mascota {{ $tipo }}">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-5xl">
                                    🐾
                                </div>
                            @endif
                        </div>

                        <div class="p-5">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h2 class="text-xl font-bold">
                                        {{ $reporte->mascota['nombre'] ?? 'Mascota sin nombre' }}
                                    </h2>
                                    <p class="text-sm text-[#60746c]">
                                        {{ ucfirst($reporte->mascota['especie'] ?? 'Sin especie') }}
                                    </p>
                                </div>

                                <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-semibold">
                                    {{ ucfirst($tipo) }}
                                </span>
                            </div>

                            <p class="text-sm text-[#60746c]">
                                <strong>Color:</strong> {{ $reporte->mascota['color_principal'] ?? 'No registrado' }}
                            </p>

                            <p class="text-sm text-[#60746c]">
                                <strong>Tamaño:</strong> {{ $reporte->mascota['tamano'] ?? 'No registrado' }}
                            </p>

                            @if ($reporte->ubicacion)
                                <p class="text-sm text-[#60746c]">
                                    <strong>Zona:</strong>
                                    {{ $reporte->ubicacion['barrio'] ?? 'Sin barrio' }},
                                    {{ $reporte->ubicacion['ciudad'] ?? 'Sin ciudad' }}
                                </p>
                            @endif

                            <p class="text-sm text-[#60746c]">
                                <strong>Fecha:</strong> {{ $reporte->fecha_evento }}
                            </p>

                            @if ($reporte->descripcion)
                                <p class="text-sm mt-4">
                                    {{ \Illuminate\Support\Str::limit($reporte->descripcion, 100) }}
                                </p>
                            @endif

                            <a class="inline-block mt-4 font-semibold text-[#2f7d68]" href="{{ route($ruta . '.show', $reporte->id) }}">Ver detalle</a><div class="border-t mt-4 pt-4">
                                <p class="text-sm font-bold">Contacto</p>
                                <p class="text-sm text-[#60746c]">
                                    {{ $reporte->responsable_nombre }}
                                </p>
                                <p class="text-sm text-[#60746c]">
                                    {{ $reporte->responsable_telefono }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-8">{{ $reportes->links() }}</div></main>
@endsection
