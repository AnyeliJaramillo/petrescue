@props(['id' => 'imagenes', 'name' => 'imagenes[]', 'descripcion' => 'Elige imágenes recientes y claras donde se vea bien la mascota.'])
@php($erroresFotos = array_merge($errors->get($id), $errors->get($id . '.*') ? \Illuminate\Support\Arr::flatten($errors->get($id . '.*')) : [], $errors->get($id . '_cantidad')))

<section class="photo-selector" data-selector-fotos>
    <input type="hidden" name="{{ $id }}_cantidad" data-fotos-cantidad
        value="{{ is_numeric(old($id . '_cantidad')) ? max(0, (int) old($id . '_cantidad')) : 0 }}">
    <div class="photo-selector-heading">
        <label class="photo-selector-button" for="{{ $id }}">
            <svg class="photo-selector-icon" aria-hidden="true" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 5l1.5-2h5L16 5h4a1 1 0 0 1 1 1v13H3V6a1 1 0 0 1 1-1h4Z"/><circle cx="12" cy="12" r="4"/><path d="M17 8h2"/></svg>
            <span data-fotos-etiqueta>Seleccionar fotos</span>
            <input id="{{ $id }}" name="{{ $name }}" type="file" multiple
                accept="image/jpeg,image/png" class="photo-selector-input" data-fotos-input
                @if ($erroresFotos) aria-invalid="true" data-server-error @endif
                aria-describedby="{{ $id }}-ayuda {{ $id }}-estado {{ $id }}-errores {{ $id }}-servidor">
            <span class="photo-selector-help" id="{{ $id }}-ayuda">JPG o PNG, máximo 2 MB por foto</span>
        </label>
        <div>
            <h3 class="font-bold text-lg">Fotos de la mascota</h3>
            <p class="text-sm text-[#60746c] mt-1">{{ $descripcion }}</p>
            @if ($errors->any())
                <p class="photo-selector-help">Si habías adjuntado fotos, selecciónalas nuevamente: al volver de un error, el navegador no las rellena automáticamente.</p>
            @endif
            <p id="{{ $id }}-estado" class="photo-selector-status" data-fotos-estado role="status" aria-live="polite">No has seleccionado fotos.</p>
            <button type="button" class="photo-selector-clear" data-fotos-limpiar hidden>Quitar selección</button>
        </div>
    </div>
    <div id="{{ $id }}-servidor">@foreach ($erroresFotos as $mensaje)<p class="report-field-error">{{ $mensaje }}</p>@endforeach</div>
    <ul id="{{ $id }}-errores" class="photo-selector-errors" data-fotos-errores aria-live="polite" hidden></ul>
    <div class="photo-selector-previews" data-fotos-previews aria-label="Fotos seleccionadas" hidden></div>
    <noscript><p class="photo-selector-help">Activa JavaScript para ver la vista previa de las fotos seleccionadas.</p></noscript>
</section>
