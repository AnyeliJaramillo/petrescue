@props(['reporte'])
@php
    $nombre = trim($reporte->mascota['nombre'] ?? '') ?: 'Mascota sin nombre';
    $perdida = $reporte->tipo_reporte === 'perdida';
@endphp
<article class="search-card">
    <div class="search-card-photo">
        <div class="search-photo-fallback" data-photo-fallback @if ($reporte->imagen_url) hidden @endif>
            <svg aria-hidden="true" width="48" height="48" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="7" y="9" width="34" height="30" rx="5"/><circle cx="18" cy="19" r="3"/><path d="m9 33 10-9 7 6 6-4 8 9"/></svg>
            <span>Fotografía no disponible</span>
        </div>
        @if ($reporte->imagen_url)
            <img src="{{ $reporte->imagen_url }}" alt="Fotografía de {{ $nombre }}" loading="lazy" decoding="async" data-search-photo>
        @endif
        <span class="search-type search-type--{{ $reporte->tipo_reporte }}">{{ $perdida ? 'Perdida' : 'Encontrada' }}</span>
    </div>
    <div class="search-card-body">
        <p class="search-card-species">{{ ucfirst($reporte->mascota['especie'] ?? 'Especie sin indicar') }}</p>
        <h3>{{ $nombre }}</h3>
        <dl class="search-card-traits">
            <div><dt>Color</dt><dd>{{ ucfirst($reporte->mascota['color_principal'] ?? 'Sin indicar') }}</dd></div>
            <div><dt>Tamaño</dt><dd>{{ ucfirst($reporte->mascota['tamano'] ?? 'Sin indicar') }}</dd></div>
        </dl>
        <p class="search-card-location"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z"/><circle cx="12" cy="10" r="2.5"/></svg><span>{{ implode(', ', array_filter([$reporte->ubicacion['barrio'] ?? null, $reporte->ubicacion['ciudad'] ?? null])) ?: 'Ubicación sin indicar' }}</span></p>
        <p class="search-card-date">{{ $perdida ? 'Se perdió el' : 'Encontrada el' }} <time datetime="{{ $reporte->fecha_evento }}">{{ $reporte->fecha_evento }}</time></p>
        <a class="search-card-link" href="{{ route(($perdida ? 'reportes-perdidos' : 'reportes-encontrados') . '.show', $reporte->id) }}" aria-label="Ver reporte de {{ $nombre }}">Ver reporte <span aria-hidden="true">→</span></a>
    </div>
</article>
