@extends('layouts.app')

@section('title')
Mascotas {{ $tipo === 'encontrada' ? 'vistas' : 'perdidas' }}
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
                <a href="{{ route('reportes-encontrados.index') }}">Mascotas vistas</a>
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
                <h1 class="text-4xl font-bold mt-2">Mascotas {{ $tipo === 'encontrada' ? 'vistas' : 'perdidas' }}</h1>
                <p class="text-[#60746c] mt-2">
                    {{ $tipo === 'encontrada' ? 'Aquí aparecen mascotas vistas y reportadas por ciudadanos que podrían estar buscando a su familia.' : 'Aquí aparecen las mascotas perdidas publicadas por los usuarios.' }}
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
            <div class="search-card-grid">
                @foreach ($reportes as $reporte)
                    <x-reportes.tarjeta :reporte="$reporte" />
                @endforeach
            </div>
        @endif

        <div class="mt-8">{{ $reportes->links() }}</div></main>
@endsection
