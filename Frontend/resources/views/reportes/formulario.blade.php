@extends('layouts.app')

@section('title')
Reportar mascota {{ $tipo }}
@endsection

@section('content')
<header class="bg-[#f8f4ec] border-b border-[#e6ddd0]">
        <div class="max-w-6xl mx-auto px-6 py-5 flex flex-wrap gap-4 justify-between items-center">
            <a href="{{ route('home') }}" class="font-bold text-lg text-[#263b35]">
                Huellas en Casa
            </a>

            <nav class="flex flex-wrap gap-6 text-sm">
                <a href="{{ route('home') }}" class="hover:text-[#2f7d68]">Inicio</a>
                <a href="{{ route('reportes-perdidos.index') }}" class="text-[#2f7d68] font-semibold">Mascotas
                    perdidas</a>
                <a href="{{ route('reportes-encontrados.index') }}" class="hover:text-[#2f7d68]">Mascotas
                    encontradas</a>
            </nav>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-6 py-10">
        <div class="bg-white rounded-3xl shadow-lg p-8">

            <div class="mb-8">
                <p class="text-sm text-[#2f7d68] font-semibold mb-2">Publicar una alerta</p>
                <h1 class="text-3xl font-bold">Reportar mascota {{ $tipo }}</h1>
                <p class="text-[#60746c] mt-2">
                    Completa la información para publicar el reporte y ayudar a reunir a la mascota con su familia.
                </p>
            </div>

            @if (session('success'))
                <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-6">
                    <p>Revisa los siguientes campos:</p><ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route($ruta . '.store') }}" method="POST" enctype="multipart/form-data"
                class="space-y-8">
                @csrf

                <x-selector-fotos />

                <section>
                    <h2 class="font-bold text-lg mb-4">Datos de la mascota</h2>

                    <div class="grid md:grid-cols-2 gap-4">
                        <input name="nombre" placeholder="Nombre de la mascota" class="input" value="{{ is_scalar(old('nombre')) ? old('nombre') : '' }}">
                        <input name="especie" placeholder="Especie: perro, gato, otro" class="input" required value="{{ is_scalar(old('especie')) ? old('especie') : '' }}">

                        <input name="raza" placeholder="Raza" class="input" value="{{ is_scalar(old('raza')) ? old('raza') : '' }}">
                        <select name="sexo" class="input">
                            <option value="" @selected(old('sexo') === '')>Sexo</option>
                            <option value="macho" @selected(old('sexo') === 'macho')>Macho</option>
                            <option value="hembra" @selected(old('sexo') === 'hembra')>Hembra</option>
                        </select>

                        <select name="tamano" class="input" required>
                            <option value="" @selected(old('tamano') === '')>Tamaño</option>
                            <option value="pequeño" @selected(old('tamano') === 'pequeño')>Pequeño</option>
                            <option value="mediano" @selected(old('tamano') === 'mediano')>Mediano</option>
                            <option value="grande" @selected(old('tamano') === 'grande')>Grande</option>
                        </select>

                        <input name="color_principal" placeholder="Color principal" class="input" required value="{{ is_scalar(old('color_principal')) ? old('color_principal') : '' }}">

                        <input name="edad_aproximada" placeholder="Edad aproximada" class="input" value="{{ is_scalar(old('edad_aproximada')) ? old('edad_aproximada') : '' }}">
                        <input name="rasgos_distintivos" placeholder="Características o rasgos distintivos"
                            class="input" value="{{ is_scalar(old('rasgos_distintivos')) ? old('rasgos_distintivos') : '' }}">
                    </div>
                </section>

                <section>
                    <label class="font-bold text-lg" for="fecha_evento">Fecha del evento</label>
                    <input id="fecha_evento" name="fecha_evento" type="date" class="input mt-3" required value="{{ is_scalar(old('fecha_evento')) ? old('fecha_evento') : '' }}">
                </section>

                <x-selector-ubicacion />
                <section>
                    <h2 class="font-bold text-lg mb-4">Descripción</h2>
                    <textarea name="descripcion" rows="4"
                        placeholder="Describe lo ocurrido, su comportamiento y cualquier dato importante." class="input">{{ is_scalar(old('descripcion')) ? old('descripcion') : '' }}</textarea>
                </section>

                <section>
                    <h2 class="font-bold text-lg mb-4">Datos de contacto</h2>

                    <div class="grid md:grid-cols-2 gap-4">
                        <input name="responsable_nombre" placeholder="Nombre" class="input" required value="{{ is_scalar(old('responsable_nombre')) ? old('responsable_nombre') : '' }}">
                        <input name="responsable_telefono" placeholder="Teléfono" class="input" required value="{{ is_scalar(old('responsable_telefono')) ? old('responsable_telefono') : '' }}">
                        <input name="responsable_correo" type="email" placeholder="Correo electrónico"
                            class="input md:col-span-2" value="{{ is_scalar(old('responsable_correo')) ? old('responsable_correo') : '' }}">
                    </div>
                </section>

                <div class="flex justify-between items-center pt-6 border-t">
                    <a href="{{ route('home') }}"
                        class="border border-[#2f7d68] text-[#2f7d68] px-6 py-3 rounded-full font-semibold">
                        Cancelar
                    </a>

                    <button type="submit" class="bg-[#2f7d68] text-white px-8 py-3 rounded-full font-semibold">
                        Publicar reporte
                    </button>
                </div>
            </form>
        </div>
    </main>
@endsection
