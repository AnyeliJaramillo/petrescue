@extends('layouts.app')
@section('title', 'Buscar mascotas reportadas | Huellas en Casa')
@section('content')
<x-site-header />
<main class="search-main" data-report-search>
    <header class="search-intro">
        <p class="search-eyebrow">UNA PISTA PUEDE REUNIR UNA FAMILIA</p>
        <h1>Buscar mascotas reportadas</h1>
        <p>Utiliza los filtros para encontrar mascotas perdidas o encontradas con características similares.</p>
    </header>
    <p class="search-status" data-search-status role="status" aria-live="polite" aria-atomic="true"></p>
    <div class="search-error" data-search-error role="alert" hidden>
        <p>No pudimos cargar los reportes. Inténtalo nuevamente.</p>
        <button type="button" class="search-button" data-search-retry>Intentar nuevamente</button>
    </div>
    <div data-search-content>
        @if ($errors->any())
            <div class="search-error" role="alert"><h2>Revisa los filtros</h2><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <form action="{{ route('busquedas.index') }}" method="GET" class="search-filters" data-search-form>
            <fieldset>
                <legend>Encuentra posibles coincidencias</legend>
                <div class="search-filter-grid">
                    <div class="search-field">
                        <label for="buscar-tipo_reporte">Tipo de reporte</label>
                        <select id="buscar-tipo_reporte" name="tipo_reporte">
                            <option value="">Perdidas y encontradas</option>
                            <option value="perdida" @selected(($filtros['tipo_reporte'] ?? '') === 'perdida')>Perdida</option>
                            <option value="encontrada" @selected(($filtros['tipo_reporte'] ?? '') === 'encontrada')>Encontrada</option>
                        </select>
                    </div>
                    @foreach (['especie' => 'Especie', 'color_principal' => 'Color principal', 'tamano' => 'Tamaño', 'sexo' => 'Sexo', 'raza' => 'Raza'] as $campo => $etiqueta)
                        @php($seleccion = $filtros[$campo] ?? '')
                        <div class="search-field">
                            <label for="buscar-{{ $campo }}">{{ $etiqueta }}</label>
                            <select id="buscar-{{ $campo }}" name="{{ $campo }}">
                                <option value="">Cualquier {{ mb_strtolower($etiqueta) }}</option>
                                @if ($seleccion !== '' && !in_array($seleccion, $opciones[$campo], true))
                                    <option value="{{ $seleccion }}" selected>{{ ucfirst($seleccion) }}</option>
                                @endif
                                @foreach ($opciones[$campo] as $valor)
                                    <option value="{{ $valor }}" @selected($seleccion === $valor)>{{ ucfirst($valor) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
            </fieldset>
            <div class="search-filter-actions">
                <p>Se muestran reportes activos. Puedes combinar varios filtros.</p>
                <div><a href="{{ route('busquedas.index') }}" class="search-clear" data-search-link>Limpiar filtros</a><button type="submit" class="search-button">Aplicar filtros</button></div>
            </div>
        </form>
        <section class="search-results" data-search-results aria-labelledby="search-results-title">
            <div class="search-results-heading">
                <h2 id="search-results-title" tabindex="-1" data-search-heading>{{ $reportes->total() }} {{ $reportes->total() === 1 ? 'reporte encontrado' : 'reportes encontrados' }}</h2>
                <p>Más recientes primero</p>
            </div>
            @if ($filtros)
                <ul class="search-applied" aria-label="Filtros aplicados">
                    @foreach ($filtros as $campo => $valor)
                        <li><a data-search-link href="{{ route('busquedas.index', array_diff_key($filtros, [$campo => true])) }}"
                            aria-label="Quitar filtro {{ str_replace('_', ' ', $campo) }}: {{ $valor }}">{{ ['tipo_reporte' => 'Reporte', 'especie' => 'Especie', 'color_principal' => 'Color', 'tamano' => 'Tamaño', 'sexo' => 'Sexo', 'raza' => 'Raza'][$campo] }}: {{ ucfirst($valor) }} <span aria-hidden="true">×</span></a></li>
                    @endforeach
                </ul>
            @endif
            @if ($reportes->isEmpty())
                <div class="search-empty">
                    <svg aria-hidden="true" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2"><circle cx="21" cy="21" r="13"/><path d="m31 31 10 10M16 21h10"/></svg>
                    <h3>No encontramos reportes con estos filtros.</h3>
                    <p>Prueba con menos características o consulta todos los reportes activos.</p>
                    <a href="{{ route('busquedas.index') }}" class="search-button" data-search-link>Limpiar filtros</a>
                </div>
            @else
                <div class="search-card-grid">
                    @foreach ($reportes as $reporte)<x-reportes.tarjeta :reporte="$reporte" />@endforeach
                </div>
            @endif
            <div class="search-pagination" data-search-pagination>{{ $reportes->links() }}</div>
        </section>
    </div>
</main>
@endsection
