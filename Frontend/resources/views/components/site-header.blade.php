@props(['publicar' => null])
<header class="home-header site-header">
    <div class="home-container home-header-inner">
        <a href="{{ route('home') }}" class="home-brand" aria-label="Huellas en Casa, inicio">
            <span class="home-brand-mark" aria-hidden="true">H</span>
            <span><span class="home-brand-name block">Huellas en Casa</span><span class="home-brand-description block">Portal de mascotas perdidas y vistas</span></span>
        </a>
        <nav class="home-nav" aria-label="Navegación principal">
            <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Inicio</a>
            <a href="{{ route('reportes-perdidos.index') }}" @if(request()->routeIs('reportes-perdidos.index')) aria-current="page" @endif>Mascotas perdidas</a>
            <a href="{{ route('reportes-encontrados.index') }}" @if(request()->routeIs('reportes-encontrados.index')) aria-current="page" @endif>Mascotas vistas</a>
            <a href="{{ route('home') }}#como-funciona">Cómo funciona</a>
        </nav>
        <a href="{{ $publicar ?? route('reportes-perdidos.create') }}" class="home-button home-header-action">Publicar reporte</a>
    </div>
</header>
