@extends('layouts.app')

@section('title')
Huellas en Casa
@endsection

@section('content')
<x-site-header />

<main>
    <section class="home-container home-hero" aria-labelledby="home-title">
        <div class="home-hero-copy">
            <p class="home-eyebrow">Una red que ayuda a volver a casa</p>
            <h1 id="home-title" class="home-title">Cada huella merece encontrar el camino de regreso.</h1>
            <p class="home-description">
                Publica una alerta, comparte información y conecta con personas cerca de ti.
                Juntos podemos reunir más familias.
            </p>
            <div class="home-actions">
                <a href="{{ route('reportes-perdidos.create') }}" class="home-button home-button-primary">Perdí mi mascota</a>
                <a href="{{ route('reportes-encontrados.create') }}" class="home-button">Encontré una mascota</a>
            </div>
            <div class="home-benefits">
                <span>Publicar es gratis</span>
                <span>Alertas locales</span>
            </div>
        </div>
        <aside class="home-hero-panel" aria-labelledby="home-panel-title">
            <div class="home-panel-content">
                <p class="home-paws" aria-hidden="true">🐾</p>
                <h2 id="home-panel-title" class="home-panel-title">Ayudemos a reunir familias</h2>
                <p class="home-panel-description">Consulta los reportes de mascotas perdidas y encontradas de la comunidad.</p>
            </div>
            <a href="{{ route('busquedas.index') }}" class="home-panel-link">
                <div>
                    <strong>Buscar mascotas reportadas</strong>
                    <p>Una pista puede ayudar a reunir una familia.</p>
                </div>
                <span class="home-panel-arrow" aria-hidden="true">→</span>
            </a>
        </aside>
    </section>

    <section id="como-funciona" class="home-container home-steps" aria-labelledby="home-steps-title">
        <h2 id="home-steps-title" class="home-section-title">Tres pasos, una comunidad cerca</h2>
        <div class="home-step-grid">
            <article class="home-step">
                <h3>Crea una alerta</h3>
                <p>Cuéntanos dónde y cuándo ocurrió.</p>
            </article>
            <article class="home-step">
                <h3>Activa la comunidad</h3>
                <p>Tu reporte llega a personas cercanas.</p>
            </article>
            <article class="home-step">
                <h3>Recibe novedades</h3>
                <p>Conecta de forma segura y vuelve a casa.</p>
            </article>
        </div>
    </section>
</main>

<footer class="home-footer">
    <div class="home-container home-footer-inner">
        <p>Huellas en Casa</p>
        <p>Ayuda · Privacidad · Contacto</p>
    </div>
</footer>
@endsection
