@extends('layouts.app')
@section('title', 'Buscar mascotas reportadas | Huellas en Casa')
@section('content')
<x-site-header />
<main class="search-main">
    <header class="search-intro"><h1>Buscar mascotas reportadas</h1></header>
    <div class="search-error" role="alert">
        <p>No pudimos cargar los reportes. Inténtalo nuevamente.</p>
        <a href="{{ request()->fullUrl() }}" class="search-button">Intentar nuevamente</a>
    </div>
</main>
@endsection
