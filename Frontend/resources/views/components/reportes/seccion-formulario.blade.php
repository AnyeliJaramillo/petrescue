@props(['numero', 'titulo', 'ayuda'])
<section class="report-section" data-report-step aria-labelledby="report-step-{{ $numero }}">
    <div class="report-section-heading">
        <span class="report-section-number" aria-hidden="true">0{{ $numero }}</span>
        <div><h2 id="report-step-{{ $numero }}" data-report-heading tabindex="-1">{{ $titulo }}</h2><p>{{ $ayuda }}</p></div>
    </div>
    {{ $slot }}
</section>
