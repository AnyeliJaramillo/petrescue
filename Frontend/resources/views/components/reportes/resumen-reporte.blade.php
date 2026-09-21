@props(['tipo', 'fecha'])
<section class="report-summary" data-report-summary hidden aria-label="Resumen del reporte">
    <div class="report-summary-heading"><h3>Así se verá tu información</h3><span class="report-badge">Mascota {{ $tipo }}</span></div>
    <div class="report-summary-pet"><img data-report-thumbnail alt="Primera fotografía seleccionada para el reporte" hidden><div><p class="report-summary-name" data-summary="nombre">Mascota sin nombre</p><p data-summary="especie"></p></div></div>
    <dl>
        @foreach (['raza' => 'Raza', 'colores' => 'Colores', 'tamano' => 'Tamaño', 'sexo' => 'Sexo', 'edad_aproximada' => 'Edad aproximada', 'fecha_evento' => $fecha, 'lugar' => 'Barrio y ciudad', 'responsable_nombre' => 'Contacto', 'responsable_telefono' => 'Teléfono'] as $campo => $etiqueta)
            <div data-summary-row="{{ $campo }}"><dt>{{ $etiqueta }}</dt><dd data-summary="{{ $campo }}">Sin indicar</dd></div>
        @endforeach
    </dl>
    <p class="report-summary-note">Puedes volver a los pasos anteriores para corregir cualquier dato antes de publicar.</p>
</section>
