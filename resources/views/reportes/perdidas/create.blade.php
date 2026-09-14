<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reportar mascota perdida</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#f8f4ec] text-[#263b35]">

    <header class="bg-[#f8f4ec] border-b border-[#e6ddd0]">
        <div class="max-w-6xl mx-auto px-6 py-5 flex justify-between items-center">
            <a href="{{ route('home') }}" class="font-bold text-lg text-[#263b35]">
                Huellas en Casa
            </a>

            <nav class="flex gap-6 text-sm">
                <a href="{{ route('home') }}" class="hover:text-[#2f7d68]">Inicio</a>
                <a href="{{ route('reportes-perdidos.create') }}" class="text-[#2f7d68] font-semibold">Mascotas
                    perdidas</a>
                <a href="{{ route('reportes-encontrados.create') }}" class="hover:text-[#2f7d68]">Mascotas
                    encontradas</a>
            </nav>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-6 py-10">
        <div class="bg-white rounded-3xl shadow-lg p-8">

            <div class="mb-8">
                <p class="text-sm text-[#2f7d68] font-semibold mb-2">Formulario HU-01</p>
                <h1 class="text-3xl font-bold">Reportar mascota perdida</h1>
                <p class="text-[#60746c] mt-2">
                    Completa la información para publicar el reporte y ayudar a encontrar a tu mascota.
                </p>
            </div>

            @if (session('success'))
                <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-6">
                    Revisa los campos obligatorios antes de guardar.
                </div>
            @endif

            <form action="{{ route('reportes-perdidos.store') }}" method="POST" enctype="multipart/form-data"
                class="space-y-8">
                @csrf

                <section class="grid md:grid-cols-3 gap-6 items-center border-b pb-8">
                    <div class="border-2 border-dashed border-[#b8d6cc] rounded-2xl p-6 text-center">
                        <p class="text-4xl mb-2">📷</p>
                        <label class="cursor-pointer text-[#2f7d68] font-semibold">
                            Subir foto
                            <input name="imagenes[]" type="file" multiple accept="image/*" class="hidden">
                        </label>
                        <p class="text-xs text-[#60746c] mt-2">JPG o PNG</p>
                    </div>

                    <div class="md:col-span-2">
                        <h2 class="font-bold text-lg">Foto de la mascota</h2>
                        <p class="text-sm text-[#60746c] mt-1">
                            Elige una imagen reciente, clara y donde se vea bien la mascota.
                        </p>
                    </div>
                </section>

                <section>
                    <h2 class="font-bold text-lg mb-4">Datos de la mascota</h2>

                    <div class="grid md:grid-cols-2 gap-4">
                        <input name="nombre" placeholder="Nombre de la mascota" class="input">
                        <input name="especie" placeholder="Especie: perro, gato, otro" class="input" required>

                        <input name="raza" placeholder="Raza" class="input">
                        <select name="sexo" class="input">
                            <option value="">Sexo</option>
                            <option value="macho">Macho</option>
                            <option value="hembra">Hembra</option>
                        </select>

                        <select name="tamano" class="input" required>
                            <option value="">Tamaño</option>
                            <option value="pequeño">Pequeño</option>
                            <option value="mediano">Mediano</option>
                            <option value="grande">Grande</option>
                        </select>

                        <input name="color_principal" placeholder="Color principal" class="input" required>

                        <input name="edad_aproximada" placeholder="Edad aproximada" class="input">
                        <input name="rasgos_distintivos" placeholder="Características o rasgos distintivos"
                            class="input">
                    </div>
                </section>

                <section>
                    <h2 class="font-bold text-lg mb-4">Fecha y lugar de pérdida</h2>

                    <div class="grid md:grid-cols-2 gap-4">
                        <input name="fecha_evento" type="date" class="input" required>
                        <input name="ciudad" placeholder="Ciudad" class="input" required>

                        <input name="departamento" placeholder="Departamento" class="input">
                        <input name="barrio" placeholder="Barrio" class="input">

                        <input name="direccion" placeholder="Lugar o dirección aproximada" class="input md:col-span-2">
                    </div>
                </section>

                <section>
                    <h2 class="font-bold text-lg mb-4">Última ubicación</h2>

                    <div class="bg-[#f2e4d7] rounded-2xl h-56 flex items-center justify-center mb-4">
                        <div class="text-center">
                            <p class="text-4xl mb-2">📍</p>
                            <p class="font-semibold">Mapa de ubicación</p>
                            <p class="text-sm text-[#60746c]">Puedes usar tu ubicación actual</p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <input id="latitud" name="latitud" placeholder="Latitud" class="input">
                        <input id="longitud" name="longitud" placeholder="Longitud" class="input">
                    </div>

                    <button type="button" onclick="obtenerUbicacion()"
                        class="mt-4 border border-[#2f7d68] text-[#2f7d68] px-5 py-2 rounded-full font-semibold">
                        Usar mi ubicación actual
                    </button>
                </section>

                <section>
                    <h2 class="font-bold text-lg mb-4">Descripción</h2>
                    <textarea name="descripcion" rows="4"
                        placeholder="Describe cómo se perdió, cómo es su comportamiento o cualquier dato importante." class="input"></textarea>
                </section>

                <section>
                    <h2 class="font-bold text-lg mb-4">Datos de contacto</h2>

                    <div class="grid md:grid-cols-2 gap-4">
                        <input name="responsable_nombre" placeholder="Nombre" class="input" required>
                        <input name="responsable_telefono" placeholder="Teléfono" class="input" required>
                        <input name="responsable_correo" type="email" placeholder="Correo electrónico"
                            class="input md:col-span-2">
                    </div>
                </section>

                <div class="flex justify-between items-center pt-6 border-t">
                    <a href="{{ route('home') }}"
                        class="border border-[#2f7d68] text-[#2f7d68] px-6 py-3 rounded-full font-semibold">
                        Cancelar
                    </a>

                    <button class="bg-[#2f7d68] text-white px-8 py-3 rounded-full font-semibold">
                        Publicar reporte
                    </button>
                </div>
            </form>
        </div>
    </main>

    <style>
        .input {
            width: 100%;
            border: 1px solid #e0d8ce;
            border-radius: 12px;
            padding: 12px 14px;
            background: #fffdf9;
            outline: none;
        }

        .input:focus {
            border-color: #2f7d68;
            box-shadow: 0 0 0 3px rgba(47, 125, 104, 0.12);
        }
    </style>

    <script>
        function obtenerUbicacion() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    document.getElementById('latitud').value = position.coords.latitude;
                    document.getElementById('longitud').value = position.coords.longitude;
                });
            } else {
                alert('Tu navegador no permite obtener la ubicación.');
            }
        }
    </script>

</body>

</html>
