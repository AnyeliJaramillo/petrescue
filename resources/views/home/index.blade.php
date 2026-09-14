<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Huellas en Casa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#f8f4ec] text-[#263b35]">

    <header class="bg-[#f8f4ec] border-b border-[#e6ddd0]">
        <div class="max-w-6xl mx-auto px-6 py-5 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-[#2f7d68] flex items-center justify-center text-white font-bold">
                    H
                </div>
                <div>
                    <h1 class="font-bold text-lg">Huellas en Casa</h1>
                    <p class="text-xs text-[#60746c]">Portal de mascotas perdidas y encontradas</p>
                </div>
            </div>

            <nav class="hidden md:flex gap-8 text-sm">
                <a href="{{ route('home') }}" class="text-[#2f7d68] font-semibold">Inicio</a>
                <a href="{{ route('reportes-perdidos.create') }}" class="hover:text-[#2f7d68]">Mascotas perdidas</a>
                <a href="{{ route('reportes-encontrados.create') }}" class="hover:text-[#2f7d68]">Mascotas
                    encontradas</a>
                <a href="#" class="hover:text-[#2f7d68]">Cómo funciona</a>
            </nav>

            <a href="{{ route('reportes-perdidos.create') }}"
                class="border border-[#2f7d68] text-[#2f7d68] px-4 py-2 rounded-full text-sm font-semibold">
                Publicar reporte
            </a>
        </div>
    </header>

    <main>
        <section class="max-w-6xl mx-auto px-6 py-16 grid md:grid-cols-2 gap-12 items-center">
            <div>
                <p class="text-xs uppercase tracking-wide text-[#2f7d68] font-bold mb-4">
                    Una red que ayuda a volver a casa
                </p>

                <h2 class="text-5xl font-bold leading-tight mb-6 text-[#263b35]">
                    Cada huella merece encontrar el camino de regreso.
                </h2>

                <p class="text-[#60746c] text-lg mb-8 max-w-xl">
                    Publica una alerta, comparte información y conecta con personas cerca de ti.
                    Juntos podemos reunir más familias.
                </p>

                <div class="flex flex-wrap gap-4 mb-5">
                    <a href="{{ route('reportes-perdidos.create') }}"
                        class="bg-[#2f7d68] text-white px-6 py-3 rounded-full font-semibold">
                        Perdí mi mascota
                    </a>

                    <a href="{{ route('reportes-encontrados.create') }}"
                        class="border border-[#2f7d68] text-[#2f7d68] px-6 py-3 rounded-full font-semibold">
                        Encontré una mascota
                    </a>
                </div>

                <div class="flex gap-6 text-sm text-[#60746c]">
                    <span>Publicar es gratis</span>
                    <span>Alertas locales</span>
                </div>
            </div>

            <div class="relative">
                <div
                    class="rounded-3xl overflow-hidden shadow-xl bg-[#e7d8c5] h-[380px] flex items-center justify-center">
                    <div class="text-center px-10">
                        <p class="text-6xl mb-4">🐾</p>
                        <h3 class="text-2xl font-bold text-[#263b35]">Imagen principal</h3>
                        <p class="text-[#60746c] mt-2">
                            Aquí puedes colocar una imagen de un perro y un gato como en el diseño.
                        </p>
                    </div>
                </div>

                <div class="absolute left-8 bottom-8 bg-white rounded-2xl shadow-lg p-4 w-64">
                    <p class="font-bold text-sm">Luna volvió a casa</p>
                    <p class="text-xs text-[#60746c]">Encontrada a 800 m - hace 2 h</p>
                </div>
            </div>
        </section>

        <section class="max-w-6xl mx-auto px-6 pb-16">
            <h2 class="text-2xl font-bold text-center mb-8">Tres pasos, una comunidad cerca</h2>

            <div class="grid md:grid-cols-3 gap-5">
                <div class="bg-white p-6 rounded-2xl border border-[#eadfD2]">
                    <h3 class="font-bold mb-2">Crea una alerta</h3>
                    <p class="text-sm text-[#60746c]">Cuéntanos dónde y cuándo ocurrió.</p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-[#eadfD2]">
                    <h3 class="font-bold mb-2">Activa la comunidad</h3>
                    <p class="text-sm text-[#60746c]">Tu reporte llega a personas cercanas.</p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-[#eadfD2]">
                    <h3 class="font-bold mb-2">Recibe novedades</h3>
                    <p class="text-sm text-[#60746c]">Conecta de forma segura y vuelve a casa.</p>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-[#153f35] text-white py-8">
        <div class="max-w-6xl mx-auto px-6 flex justify-between text-sm">
            <p>Huellas en Casa</p>
            <p>Ayuda · Privacidad · Contacto</p>
        </div>
    </footer>

</body>

</html>
