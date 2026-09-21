@props(['reporte'])
@php
    $nombre = trim($reporte->mascota['nombre'] ?? '') ?: 'Mascota sin nombre';
    $perdida = $reporte->tipo_reporte === 'perdida';
    $tipoVisible = $perdida ? 'Perdida' : 'Vista';
    $estadoActivo = $reporte->estado === 'activo';
    $imagen = $reporte->imagen_url ?? (isset($reporte->imagenes[0]['url']) ? $reporte->imagenes[0]['url'] : null);
    $especie = trim($reporte->mascota['especie'] ?? '');
    $color = trim($reporte->mascota['color_principal'] ?? '');
    $tamano = trim($reporte->mascota['tamano'] ?? '');
    $ubicacion = array_filter([
        $reporte->ubicacion['barrio'] ?? null,
        $reporte->ubicacion['ciudad'] ?? null,
    ]);
@endphp
<article class="search-card">
    <div class="search-card-photo">
        <div class="search-photo-fallback" data-photo-fallback @if ($imagen) hidden @endif>
            <svg aria-hidden="true" width="48" height="48" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="7" y="9" width="34" height="30" rx="5"/><circle cx="18" cy="19" r="3"/><path d="m9 33 10-9 7 6 6-4 8 9"/></svg>
            <span>Fotografía no disponible</span>
        </div>
        @if ($imagen)
            <img src="{{ $imagen }}" alt="Fotografía de {{ $nombre }}" loading="lazy" decoding="async" data-search-photo>
        @endif
        <span class="search-type search-type--{{ $reporte->tipo_reporte }}">{{ $tipoVisible }}</span>
    </div>
    <div class="search-card-body">
        @if ($especie)
            <p class="search-card-species">{{ ucfirst($especie) }}</p>
        @endif
        <h3>{{ $nombre }}</h3>
        <span class="search-card-status {{ $estadoActivo ? 'search-card-status--active' : 'search-card-status--closed' }}">
            <span aria-hidden="true">{{ $estadoActivo ? '●' : '✓' }}</span>
            {{ $estadoActivo ? 'Búsqueda activa' : 'Caso cerrado' }}
        </span>
        @if ($color || $tamano)
            <dl class="search-card-traits">
                @if ($color)<div><dt>Color</dt><dd>{{ ucfirst($color) }}</dd></div>@endif
                @if ($tamano)<div><dt>Tamaño</dt><dd>{{ ucfirst($tamano) }}</dd></div>@endif
            </dl>
        @endif
        @if ($ubicacion)
            <p class="search-card-location"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z"/><circle cx="12" cy="10" r="2.5"/></svg><span>{{ implode(', ', $ubicacion) }}</span></p>
        @endif
        @if ($reporte->fecha_evento)
            <p class="search-card-date">{{ $perdida ? 'Se perdió el' : 'Fue vista el' }} <time datetime="{{ $reporte->fecha_evento }}">{{ $reporte->fecha_evento }}</time></p>
        @endif
        <a class="search-card-link" href="{{ route(($perdida ? 'reportes-perdidos' : 'reportes-encontrados') . '.show', $reporte->id) }}" aria-label="Ver reporte de {{ $nombre }}">Ver reporte <span aria-hidden="true">→</span></a>
        @if (!empty($reporte->responsable_nombre))
            <span class="sr-only">Contacto: {{ $reporte->responsable_nombre }}</span>
        @endif
    </div>
</article>
