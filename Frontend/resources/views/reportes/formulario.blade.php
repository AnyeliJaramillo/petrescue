@extends('layouts.app')
@section('title', 'Reporta una mascota ' . ($tipo === 'encontrada' ? 'vista' : 'perdida') . ' | Huellas en Casa')
@section('content')
@php
    // Configuración de presentación. El controlador determina el tipo publicado.
    $perdida = $tipo === 'perdida';
    $textos = $perdida ? [
        'etiqueta' => 'Alerta de mascota perdida',
        'introduccion' => 'Completa la información que pueda ayudar a reconocerla y encontrarla lo más pronto posible.',
        'fotos' => 'Agrega fotografías recientes',
        'ayuda_fotos' => 'Elige fotografías donde se vean su cara, su cuerpo y las marcas que ayuden a reconocerla.',
        'mascota' => 'Datos de la mascota',
        'ayuda_mascota' => 'Cada detalle puede ayudar a alguien a reconocer a tu mascota.',
        'evento' => 'Pérdida y ubicación', 'fecha' => 'Fecha en que se perdió',
        'descripcion' => 'Cuéntanos qué ocurrió', 'ubicacion' => 'Última ubicación conocida',
        'ayuda_ubicacion' => 'Indica el lugar donde se vio por última vez. Puedes escribir la dirección o seleccionar un punto en el mapa.',
        'consejo' => 'Ayuda a reconocerla',
        'ayuda_consejo' => 'Una foto nítida y sus rasgos distintivos pueden hacer la diferencia para que alguien la identifique.',
    ] : [
        'etiqueta' => 'Reporte de mascota vista',
        'introduccion' => 'Comparte sus características y el lugar donde fue vista para ayudar a localizar a su familia.',
        'fotos' => 'Agrega fotografías de la mascota vista',
        'ayuda_fotos' => 'Comparte fotografías recientes y claras del momento en que viste la mascota, donde se distingan sus características.',
        'mascota' => 'Características observadas',
        'ayuda_mascota' => 'Describe lo que puedes observar. Si no conoces un dato opcional, puedes dejarlo vacío.',
        'evento' => 'Vista y ubicación', 'fecha' => 'Fecha en que fue vista',
        'descripcion' => 'Información de la mascota vista', 'ubicacion' => 'Ubicación donde fue vista',
        'ayuda_ubicacion' => 'Indica dónde viste la mascota para que su familia pueda reconocer el lugar.',
        'consejo' => 'Acércala a su familia',
        'ayuda_consejo' => 'Describe sus colores y las características que observaste. No necesitas conocer su nombre ni su raza.',
    ];
    $campos = [
        'nombre' => [$perdida ? 'Nombre de la mascota' : '¿Conoces su nombre?', false, 255, 'Por ejemplo, Luna'],
        'especie' => ['Especie', true, 100, 'Perro, gato u otra especie'],
        'raza' => [$perdida ? 'Raza' : 'Raza aparente', false, 100, 'Si la conoces'],
        'color_principal' => ['Color principal', true, 100, 'Por ejemplo, café'],
        'color_secundario' => ['Color secundario', false, 100, 'Por ejemplo, blanco'],
        'tamano' => ['Tamaño', true, 50, ''], 'sexo' => ['Sexo', false, 50, ''],
        'edad_aproximada' => ['Edad aproximada', false, 100, 'Por ejemplo, aproximadamente 2 años'],
        'rasgos_distintivos' => [$perdida ? 'Rasgos distintivos' : 'Características visibles', false, null, 'Manchas, forma de las orejas u otras marcas reconocibles'],
    ];
    $orden = $perdida
        ? ['nombre', 'especie', 'raza', 'color_principal', 'color_secundario', 'tamano', 'sexo', 'edad_aproximada', 'rasgos_distintivos']
        : ['especie', 'color_principal', 'color_secundario', 'tamano', 'sexo', 'raza', 'edad_aproximada', 'nombre', 'rasgos_distintivos'];
@endphp
<div class="report-page report-page--{{ $tipo }}">
    <header class="report-header">
        <div class="report-header-inner">
            <a href="{{ route('home') }}" class="report-brand"><span class="report-brand-mark" aria-hidden="true">H</span> Huellas en Casa</a>
            <nav aria-label="Navegación principal">
                <a href="{{ route('home') }}">Inicio</a>
                <a href="{{ route('reportes-perdidos.index') }}">Mascotas perdidas</a>
                <a href="{{ route('reportes-encontrados.index') }}">Mascotas vistas</a>
            </nav>
        </div>
    </header>
    <main class="report-main">
        <a class="report-back" href="{{ route('home') }}"><span aria-hidden="true">←</span> Volver al inicio</a>
        <div class="report-intro">
            <span class="report-badge">{{ $textos['etiqueta'] }}</span>
            <h1>Reporta una mascota {{ $perdida ? 'perdida' : 'vista' }}</h1>
            <p>{{ $textos['introduccion'] }}</p>
        </div>
        <div class="report-layout">
            <form action="{{ route($ruta . '.store') }}" method="POST" enctype="multipart/form-data"
                class="report-form" data-report-form data-report-type="{{ $tipo }}">
                @csrf
                <x-reportes.stepper />
                @if (session('success'))<p class="report-notice" role="status">{{ session('success') }}</p>@endif
                @if ($errors->any())
                    <div class="report-errors" role="alert" tabindex="-1">
                        <h2>Revisa estos datos antes de publicar</h2>
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                <p class="report-required-note">Los campos con <span aria-hidden="true">*</span> son obligatorios. Los demás son opcionales.</p>
                <x-reportes.seccion-formulario numero="1" :titulo="$textos['fotos']" :ayuda="$textos['ayuda_fotos']">
                    <x-selector-fotos :descripcion="$textos['ayuda_fotos']" />
                    <div class="report-tip"><strong>Una buena fotografía ayuda a reconocerla.</strong><p>Busca luz natural y una imagen sin filtros. Puedes seleccionar varias fotos o continuar sin ellas.</p></div>
                </x-reportes.seccion-formulario>
                <x-reportes.seccion-formulario numero="2" :titulo="$textos['mascota']" :ayuda="$textos['ayuda_mascota']">
                    <div class="report-fields">
                        @foreach ($orden as $campo)
                            @php
                                [$etiqueta, $obligatorio, $maximo, $ejemplo] = $campos[$campo];
                                $opciones = match ($campo) {
                                    'especie' => config('mascotas.especies'),
                                    'raza' => config('mascotas.razas.perro')
                                        + config('mascotas.razas.gato'),
                                    'color_principal' => config('mascotas.colores'),
                                    'color_secundario' => config('mascotas.colores_secundarios'),
                                    'tamano' => config('mascotas.tamanos'),
                                    'sexo' => $perdida ? ['macho' => 'Macho', 'hembra' => 'Hembra'] : config('mascotas.sexos'),
                                    'edad_aproximada' => config('mascotas.edades'),
                                    default => [],
                                };
                                if ($campo === 'raza' && $perdida) {
                                    unset($opciones['no_se']);
                                }
                                $personalizable = in_array($campo, ['especie', 'raza', 'color_principal', 'color_secundario', 'edad_aproximada'], true);
                                $customPlaceholder = match ($campo) {
                                    'especie' => 'Escribe la especie',
                                    'raza' => 'Escribe la raza',
                                    'edad_aproximada' => 'Por ejemplo, aproximadamente 2 años',
                                    default => 'Escribe el dato',
                                };
                            @endphp
                            <x-reportes.campo :name="$campo" :label="$etiqueta" :required="$obligatorio" :maxlength="$maximo"
                                :placeholder="$ejemplo" :options="$opciones" :type="$campo === 'rasgos_distintivos' ? 'textarea' : 'text'"
                                :wide="$campo === 'rasgos_distintivos' || ($perdida && $campo === 'nombre')"
                                :custom="$personalizable ? ['placeholder' => $customPlaceholder] : null"
                                :dataAttributes="$campo === 'raza' ? ['race-select' => true] : ($campo === 'especie' ? ['species-select' => true] : [])" />
                        @endforeach
                    </div>
                </x-reportes.seccion-formulario>
                <x-reportes.seccion-formulario numero="3" :titulo="$textos['evento']" ayuda="Comparte la fecha y el lugar del evento con la mayor precisión que puedas.">
                    <div class="report-fields report-event-fields">
                        <x-reportes.campo name="fecha_evento" :label="$textos['fecha']" type="date" :required="true" :max="now()->toDateString()" />
                        <x-reportes.campo name="descripcion" :label="$textos['descripcion']" type="textarea" :wide="true" placeholder="Describe lo ocurrido y cualquier detalle que pueda ayudar." />
                    </div>
                    <x-selector-ubicacion :titulo="$textos['ubicacion']" :descripcion="$textos['ayuda_ubicacion']" />
                </x-reportes.seccion-formulario>
                <x-reportes.seccion-formulario numero="4" titulo="Contacto y revisión" ayuda="Revisa la información y deja un contacto para quienes puedan ayudar.">
                    <fieldset class="report-contact">
                        <legend>Datos de contacto</legend>
                        <div class="report-fields">
                            <x-reportes.campo name="responsable_nombre" label="Nombre de contacto" :required="true" :maxlength="255" autocomplete="name" />
                            <x-reportes.campo name="responsable_telefono" label="Teléfono" type="tel" :required="true" :maxlength="30" autocomplete="tel" />
                            <x-reportes.campo name="responsable_correo" label="Correo electrónico" type="email" :maxlength="255" autocomplete="email" :wide="true" />
                        </div>
                    </fieldset>
                    <p class="report-contact-note">Los datos de contacto y la ubicación se muestran en el reporte publicado. Comparte únicamente la información que deseas publicar.</p>
                    <x-reportes.resumen-reporte :tipo="$tipo" :fecha="$textos['fecha']" />
                </x-reportes.seccion-formulario>
                <div class="report-actions">
                    <a href="{{ route('home') }}" class="report-cancel">Cancelar</a>
                    <div class="report-actions-buttons">
                        <button type="button" class="report-button report-button-secondary" data-report-previous hidden>Anterior</button>
                        <button type="button" class="report-button" data-report-next hidden>Continuar: datos de la mascota</button>
                        <button type="submit" class="report-button" data-report-submit>Publicar reporte</button>
                    </div>
                </div>
            </form>
            <aside class="report-aside" aria-label="Consejos para tu reporte">
                <div class="report-aside-symbol" aria-hidden="true"><svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 24 24 11l15 13v17H9V24Z"/><path d="M17 28c0-6 7-5 7-1 0-4 7-5 7 1 0 4-7 8-7 8s-7-4-7-8Z"/><path d="M33 9V4M39 12l4-4M40 18h6"/></svg></div>
                <p class="report-aside-eyebrow">CADA DETALLE CUENTA</p>
                <h2>{{ $textos['consejo'] }}</h2><p>{{ $textos['ayuda_consejo'] }}</p>
                <ul><li>Fotografías claras y recientes.</li><li>Características fáciles de reconocer.</li><li>Fecha y ubicación del evento.</li><li>Un teléfono para contactarte.</li></ul>
                <div class="report-aside-footer">Una comunidad que ayuda a reunir familias.</div>
            </aside>
        </div>
    </main>
</div>
@endsection
