@props(['titulo' => 'Ubicación del evento', 'descripcion' => 'Escribe la dirección en Colombia y búscala, o señala el lugar en el mapa. Revisa los datos: al publicar el reporte también confirmas la ubicación seleccionada.'])
<section class="location-selector" data-selector-ubicacion
    data-search-url="{{ route('ubicaciones.buscar') }}"
    data-reverse-url="{{ route('ubicaciones.invertir') }}"
    data-tiles-url="{{ config('ubicaciones.tiles_url') }}"
    data-tiles-attribution="{{ config('ubicaciones.tiles_attribution') }}">
    <h3 class="font-bold text-lg mb-2">{{ $titulo }}</h3>
    <p class="location-help">{{ $descripcion }}</p>
    <div class="location-fields">
        @foreach (['direccion' => 'Dirección o lugar', 'barrio' => 'Barrio', 'ciudad' => 'Ciudad o municipio', 'departamento' => 'Departamento'] as $campo => $etiqueta)
            <x-reportes.campo :name="$campo" :label="$etiqueta" :maxlength="$campo === 'direccion' ? 255 : 100" :required="$campo === 'ciudad'" />
        @endforeach
    </div>
    <p class="location-help">Si seleccionas un punto, completa también la dirección y el departamento.</p>
    <x-reportes.campo name="referencia" label="Referencia del lugar" type="textarea" placeholder="Por ejemplo, frente al parque o cerca de una tienda." />
    <div class="location-actions">
        <button type="button" data-location-search class="location-button">Buscar dirección</button>
        <button type="button" data-location-current class="location-button">Usar mi ubicación actual</button>
    </div>
    <p class="location-help">Al buscar, usar tu ubicación actual o completar la dirección del punto, se consulta con Geoapify para rellenar los campos. Revisa los datos y completa cualquier información que falte.</p>
    <div data-location-results class="location-results" aria-label="Direcciones encontradas" hidden></div>
    <div data-location-map class="location-map" aria-label="{{ $titulo }}: mapa para seleccionar un punto" aria-describedby="ubicacion-errores" tabindex="0"
        @if ($errors->hasAny(['latitud', 'longitud', 'ubicacion_confirmada'])) aria-invalid="true" data-server-error @endif></div>
    <div id="ubicacion-errores">@foreach (['latitud', 'longitud', 'ubicacion_confirmada'] as $campo)@error($campo)<p class="report-field-error">{{ $message }}</p>@enderror @endforeach</div>
    <div class="location-actions">
        <button type="button" data-location-reverse class="location-button" disabled>Completar dirección del punto</button>
        <button type="button" data-location-confirm class="location-button location-button-primary" disabled>Confirmar ubicación</button>
        <button type="button" data-location-clear class="location-button" hidden>Quitar punto</button>
    </div>
    <p data-location-status class="location-status" role="status" aria-live="polite">No has seleccionado un punto. También puedes publicar solo la dirección escrita.</p>
    <div data-location-receipt class="location-receipt" hidden>
        <strong>✓ Ubicación seleccionada</strong>
        <p data-location-summary></p>
        <p class="location-help">Se guardará al publicar el reporte. Si modificas la dirección o mueves el marcador, revisa los datos antes de publicar.</p>
    </div>
    <input type="hidden" name="latitud" value="{{ is_scalar(old('latitud')) ? old('latitud') : '' }}">
    <input type="hidden" name="longitud" value="{{ is_scalar(old('longitud')) ? old('longitud') : '' }}">
    <input type="hidden" name="ubicacion_confirmada" value="{{ old('ubicacion_confirmada') === '1' ? '1' : '0' }}">
    <p class="location-attribution">Búsqueda de direcciones: <a href="https://www.geoapify.com/" target="_blank" rel="noopener noreferrer">Geoapify</a> · <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">© OpenStreetMap contributors</a></p>
</section>
