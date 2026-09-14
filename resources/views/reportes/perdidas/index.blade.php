<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mascotas perdidas</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#f8f4ec] text-[#263b35]">

    <header class="bg-[#f8f4ec] border-b border-[#e6ddd0]">
        <div class="max-w-6xl mx-auto px-6 py-5 flex justify-between items-center">
            <a href="{{ route('home') }}" class="font-bold text-lg">
                Huellas en Casa
            </a>

            <nav class="flex gap-6 text-sm">
                <a href="{{ route('home') }}">Inicio</a>
                <a href="{{ route('reportes-perdidos.index') }}" class="text-[#2f7d68] font-semibold">Mascotas
                    perdidas</a>
                <a href="{{ route('reportes-encontrados.create') }}">Mascotas encontradas</a>
            </nav>

            <a href="{{ route('reportes-perdidos.create') }}"
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
                <h1 class="text-4xl font-bold mt-2">Mascotas perdidas</h1>
                <p class="text-[#60746c] mt-2">
                    Aquí aparecen las mascotas perdidas publicadas por los usuarios.
                </p>
            </div>
        </div>

        @if ($reportes->count() == 0)
            <div class="bg-white rounded-3xl p-10 text-center shadow">
                <p class="text-5xl mb-4">🐾</p>
                <h2 class="text-2xl font-bold">Aún no hay reportes publicados</h2>
                <p class="text-[#60746c] mt-2 mb-6">
                    Cuando se publique una mascota perdida aparecerá aquí.
                </p>
                <a href="{{ route('reportes-perdidos.create') }}"
                    class="bg-[#2f7d68] text-white px-6 py-3 rounded-full font-semibold">
                    Publicar reporte
                </a>
            </div>
        @else
            <div class="grid md:grid-cols-3 gap-6">
                @foreach ($reportes as $reporte)
                    <div class="bg-white rounded-3xl shadow overflow-hidden border border-[#eadfd2]">

                        <div class="h-52 bg-[#eadfd2]">
                            @if ($reporte->mascota && $reporte->mascota->imagenes->count() > 0)
                                <img src="{{ asset('storage/' . $reporte->mascota->imagenes->first()->ruta_imagen) }}"
                                    class="w-full h-full object-cover" alt="Mascota perdida">
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
                                        {{ $reporte->mascota->nombre ?? 'Mascota sin nombre' }}
                                    </h2>
                                    <p class="text-sm text-[#60746c]">
                                        {{ ucfirst($reporte->mascota->especie ?? 'Sin especie') }}
                                    </p>
                                </div>

                                <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-semibold">
                                    Perdida
                                </span>
                            </div>

                            <p class="text-sm text-[#60746c]">
                                <strong>Color:</strong> {{ $reporte->mascota->color_principal ?? 'No registrado' }}
                            </p>

                            <p class="text-sm text-[#60746c]">
                                <strong>Tamaño:</strong> {{ $reporte->mascota->tamano ?? 'No registrado' }}
                            </p>

                            @if ($reporte->ubicacion)
                                <p class="text-sm text-[#60746c]">
                                    <strong>Zona:</strong>
                                    {{ $reporte->ubicacion->barrio ?? 'Sin barrio' }},
                                    {{ $reporte->ubicacion->ciudad ?? 'Sin ciudad' }}
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

                            <div class="border-t mt-4 pt-4">
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

    </main>

</body>

</html>
