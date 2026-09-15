@props(['id' => 'imagenes', 'name' => 'imagenes[]'])

<section class="photo-selector" data-selector-fotos>
    <input type="hidden" name="{{ $id }}_cantidad" data-fotos-cantidad
        value="{{ is_numeric(old($id . '_cantidad')) ? max(0, (int) old($id . '_cantidad')) : 0 }}">
    <div class="photo-selector-heading">
        <label class="photo-selector-button">
            <span class="photo-selector-icon" aria-hidden="true">📷</span>
            <span data-fotos-etiqueta>Seleccionar fotos</span>
            <input id="{{ $id }}" name="{{ $name }}" type="file" multiple
                accept="image/jpeg,image/png" class="photo-selector-input" data-fotos-input
                aria-describedby="{{ $id }}-ayuda {{ $id }}-estado {{ $id }}-errores">
            <span class="photo-selector-help" id="{{ $id }}-ayuda">JPG o PNG, máximo 2 MB por foto</span>
        </label>
        <div>
            <h2 class="font-bold text-lg">Fotos de la mascota</h2>
            <p class="text-sm text-[#60746c] mt-1">Elige imágenes recientes y claras donde se vea bien la mascota.</p>
            @if ($errors->any())
                <p class="photo-selector-help">Si habías adjuntado fotos, selecciónalas nuevamente: al volver de un error, el navegador no las rellena automáticamente.</p>
            @endif
            <p id="{{ $id }}-estado" class="photo-selector-status" data-fotos-estado role="status" aria-live="polite">No has seleccionado fotos.</p>
            <button type="button" class="photo-selector-clear" data-fotos-limpiar hidden>Quitar selección</button>
        </div>
    </div>
    <ul id="{{ $id }}-errores" class="photo-selector-errors" data-fotos-errores aria-live="polite" hidden></ul>
    <div class="photo-selector-previews" data-fotos-previews aria-label="Fotos seleccionadas" hidden></div>
    <noscript><p class="photo-selector-help">Activa JavaScript para ver la vista previa de las fotos seleccionadas.</p></noscript>
</section>
