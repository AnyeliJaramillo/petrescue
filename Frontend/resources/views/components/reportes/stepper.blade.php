<nav class="report-stepper" aria-label="Pasos del reporte" data-report-stepper hidden>
    <ol>
        @foreach (['Fotografías', 'Datos de la mascota', 'Evento y ubicación', 'Contacto y revisión'] as $paso)
            <li><button type="button" data-report-go="{{ $loop->index }}" disabled aria-label="Paso {{ $loop->iteration }}: {{ $paso }}">
                <span class="report-step-number" aria-hidden="true">{{ $loop->iteration }}</span><span>{{ $paso }}</span><span class="report-step-state" data-report-state></span>
            </button></li>
        @endforeach
    </ol>
    <progress data-report-progress max="4" value="1" aria-label="Progreso del formulario">1 de 4</progress>
    <p class="report-progress-text" data-report-announcement role="status" aria-live="polite" aria-atomic="true"></p>
</nav>
