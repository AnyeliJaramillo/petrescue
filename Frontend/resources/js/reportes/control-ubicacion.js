import { SeleccionUbicacion } from './seleccion-ubicacion.js';

export function iniciarMapa(container, { L, geolocation, fetch: request }) {
    const select = (name) => container.querySelector(`[data-location-${name}]`);
    const fields = Object.fromEntries(['direccion', 'barrio', 'ciudad', 'departamento', 'latitud', 'longitud', 'ubicacion_confirmada']
        .map((name) => [name, container.querySelector(`[name="${name}"]`)]));
    const form = container.closest('form');
    const state = new SeleccionUbicacion();
    let marker;
    let pending;
    let version = 0;
    let map;
    const status = (message) => { select('status').textContent = message; };
    const address = () => Object.fromEntries(['direccion', 'barrio', 'ciudad', 'departamento'].map((key) => [key, fields[key].value.trim()]));
    const cancel = () => { version++; pending?.abort(); };
    const refresh = () => {
        for (const [key, value] of Object.entries(state.datos())) fields[key].value = value;
        select('confirm').disabled = !state.punto;
        select('reverse').disabled = !state.punto;
        select('clear').hidden = !state.punto;
        select('receipt').hidden = !state.confirmada;
        select('summary').textContent = Object.values(address()).filter(Boolean).join(', ');
    };
    const point = (lat, lng) => {
        state.seleccionar(lat, lng);
        if (map) {
            if (!marker) {
                marker = L.marker([lat, lng], {
                    draggable: true, title: 'Ubicación seleccionada; arrastra para ajustar',
                    icon: L.divIcon({ className: 'location-pin', iconSize: [24, 24], iconAnchor: [12, 12] }),
                }).addTo(map);
                marker.on('dragend', () => {
                    cancel();
                    const position = marker.getLatLng();
                    point(position.lat, position.lng);
                });
            } else marker.setLatLng([lat, lng]);
        }
        refresh();
        status('Punto seleccionado. Revisa la dirección: puedes confirmar la ubicación ahora o al publicar el reporte.');
    };
    const fill = (result) => {
        for (const key of ['direccion', 'barrio', 'ciudad', 'departamento']) fields[key].value = result[key] || '';
        state.editar();
        refresh();
    };
    const post = async (url, body) => {
        cancel();
        const current = version;
        pending = new AbortController();
        status('Consultando dirección…');
        try {
            const response = await request(url, {
                method: 'POST', signal: pending.signal,
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value },
                body: JSON.stringify(body),
            });
            const data = await response.json().catch(() => ({}));
            if (current !== version) return null;
            if (!response.ok) {
                if (response.status === 429) throw new Error('Hay demasiadas consultas. Espera un minuto antes de volver a buscar.');
                if (response.status === 419) throw new Error('La sesión ha caducado. Recarga la página antes de buscar de nuevo.');
                throw new Error(data.message || 'No se pudo consultar la dirección. Puedes completarla manualmente.');
            }
            return data;
        } catch (error) {
            if (current === version && error.name !== 'AbortError') status(error.message || 'No hay conexión con el servicio de direcciones.');
            return null;
        }
    };

    try {
        map = L.map(select('map'), { scrollWheelZoom: false }).setView([4.57, -74.3], 5);
        L.tileLayer(container.dataset.tilesUrl, {
            maxZoom: 19, attribution: container.dataset.tilesAttribution,
        }).on('tileerror', () => status('No se pudo cargar parte del mapa. Comprueba tu conexión; puedes escribir la dirección.')).addTo(map);
        map.on('click', ({ latlng }) => { cancel(); point(latlng.lat, latlng.lng); });
    } catch {
        select('map').hidden = true;
        status('No se pudo cargar el mapa. Puedes completar la dirección manualmente.');
    }

    const initial = { lat: fields.latitud.value, lng: fields.longitud.value, confirmed: fields.ubicacion_confirmada.value };
    if (initial.lat !== '' && initial.lng !== '') {
        try {
            point(Number(initial.lat), Number(initial.lng));
            map?.setView([Number(initial.lat), Number(initial.lng)], 16);
            if (initial.confirmed === '1') state.confirmar(address());
        } catch { state.limpiar(); }
    }
    refresh();

    container.addEventListener('report:step-shown', () => map?.invalidateSize({ animate: false }));

    for (const key of ['direccion', 'barrio', 'ciudad', 'departamento']) {
        fields[key].addEventListener('input', () => {
            cancel(); state.editar(); refresh();
            select('results').hidden = true;
            status(state.punto ? 'La dirección cambió. Revisa el marcador; se confirmará al publicar el reporte.' : 'Dirección escrita. Puedes buscarla para seleccionar el punto.');
        });
    }
    select('search').addEventListener('click', async () => {
        const values = address();
        if (values.direccion.length < 3 || !values.ciudad || !values.departamento) {
            status('Escribe una dirección de al menos tres caracteres, ciudad o municipio y departamento.'); return;
        }
        select('results').replaceChildren(); select('results').hidden = true;
        const data = await post(container.dataset.searchUrl, values);
        if (!data) return;
        status(data.resultados.length ? 'Selecciona una de las direcciones encontradas y revisa el marcador.' : 'No encontramos esa dirección en Colombia. Puedes seleccionar el punto en el mapa y escribir los datos.');
        for (const result of data.resultados) {
            const button = document.createElement('button');
            button.type = 'button'; button.textContent = result.etiqueta;
            button.addEventListener('click', () => {
                cancel(); fill(result); point(result.latitud, result.longitud);
                map?.setView([result.latitud, result.longitud], 16);
                select('results').hidden = true;
            });
            select('results').append(button);
        }
        select('results').hidden = !data.resultados.length;
    });
    const completeAddress = async () => {
        if (!state.punto) return;
        const data = await post(container.dataset.reverseUrl, state.punto);
        if (!data) return;
        if (!data.direccion) { status('No se encontró una dirección en Colombia para este punto. Revisa el marcador y completa los campos manualmente.'); return; }
        fill(data.direccion);
        const missing = Object.entries({ direccion: 'dirección', barrio: 'barrio', ciudad: 'ciudad o municipio', departamento: 'departamento' })
            .filter(([key]) => !fields[key].value.trim()).map(([, name]) => name);
        status(missing.length
            ? `Dirección aproximada completada. Falta ${missing.join(', ')}; completa los datos disponibles y confirma la ubicación.`
            : 'Dirección aproximada completada. Revisa los datos y confirma la ubicación.');
    };
    select('reverse').addEventListener('click', completeAddress);
    select('current').addEventListener('click', () => {
        if (!geolocation) { status('Tu navegador no permite obtener tu ubicación. Busca la dirección o selecciona el lugar en el mapa.'); return; }
        cancel(); const current = version;
        status('Obteniendo tu ubicación…');
        const onError = () => { if (current === version) status('No se pudo obtener tu ubicación. Permite el acceso, busca la dirección o selecciónala en el mapa.'); };
        try {
            geolocation.getCurrentPosition(async ({ coords }) => {
                if (current !== version) return;
                point(coords.latitude, coords.longitude);
                map?.setView([coords.latitude, coords.longitude], 16);
                select('results').hidden = true;
                await completeAddress();
            }, onError, { timeout: 10000 });
        } catch { onError(); }
    });
    select('confirm').addEventListener('click', () => {
        cancel();
        try { state.confirmar(address()); refresh(); status('Ubicación confirmada para este reporte.'); }
        catch (error) { status(error.message); }
    });
    const clear = () => {
        cancel(); state.limpiar(); marker?.remove(); marker = null; refresh();
        select('results').hidden = true;
        status('Punto retirado. Puedes publicar solo la dirección escrita o seleccionar otro lugar.');
    };
    select('clear').addEventListener('click', clear);
    form.addEventListener('reset', (event) => queueMicrotask(() => { if (!event.defaultPrevented) clear(); }));
    form.addEventListener('submit', (event) => {
        if (event.defaultPrevented) return;
        cancel();
        if (state.punto && !state.confirmada) {
            try {
                state.confirmar(address());
                refresh();
                status('Ubicación confirmada para este reporte.');
            } catch (error) {
                event.preventDefault();
                status(error.message);
                const missing = ['direccion', 'ciudad', 'departamento'].find(key => !fields[key].value.trim());
                const target = missing ? fields[missing] : select('confirm');
                target.dispatchEvent(new container.ownerDocument.defaultView.Event('report:reveal', { bubbles: true }));
                target.focus();
                target.scrollIntoView({ block: 'center', behavior: 'instant' });
            }
        }
    });
}
