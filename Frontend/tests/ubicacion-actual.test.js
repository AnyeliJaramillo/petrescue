import test from 'node:test';
import assert from 'node:assert/strict';
import { iniciarMapa } from '../resources/js/reportes/control-ubicacion.js';

class Elemento {
    value = '';
    textContent = '';
    hidden = false;
    disabled = false;
    focused = false;
    scrolled = false;
    focus() { this.focused = true; }
    scrollIntoView() { this.scrolled = true; }
    listeners = new Map();
    addEventListener(name, handler) { this.listeners.set(name, handler); }
    fire(name, event = {}) { return this.listeners.get(name)?.(event); }
}

const direccion = {
    direccion: 'Calle 18 # 45-20', barrio: 'Universitario',
    ciudad: 'Pasto', departamento: 'Nariño', latitud: 1.22, longitud: -77.29,
};
const gps = { latitude: 1.21, longitude: -77.28 };
const respuesta = (data = { direccion }) => ({ ok: true, json: async () => data });

function preparar(fetch = async () => respuesta()) {
    const fields = Object.fromEntries(['direccion', 'barrio', 'ciudad', 'departamento', 'latitud', 'longitud', 'ubicacion_confirmada']
        .map(name => [name, new Elemento()]));
    const controls = Object.fromEntries(['status', 'confirm', 'reverse', 'clear', 'receipt', 'summary', 'results', 'map', 'search', 'current']
        .map(name => [name, new Elemento()]));
    const form = new Elemento();
    form.querySelector = () => ({ value: 'csrf-test' });
    const container = {
        dataset: { reverseUrl: '/ubicaciones/invertir', searchUrl: '/ubicaciones/buscar' },
        closest: () => form,
        querySelector(selector) {
            const field = selector.match(/^\[name="(.+)"\]$/);
            return field ? fields[field[1]] : controls[selector.match(/^\[data-location-(.+)\]$/)[1]];
        },
    };
    const map = { setView() { return this; }, on() { return this; } };
    const marker = {
        position: null,
        addTo() { return this; }, on() { return this; },
        setLatLng(position) { this.position = position; }, remove() {},
    };
    const L = {
        map: () => map,
        tileLayer: () => ({ on() { return this; }, addTo() {} }),
        divIcon: options => options,
        marker(position) { marker.position = position; return marker; },
    };
    const location = {};
    const calls = [];
    iniciarMapa(container, {
        L,
        geolocation: { getCurrentPosition(success, error) { Object.assign(location, { success, error }); } },
        fetch(url, options) { calls.push({ url, options }); return fetch(url, options); },
    });
    const locate = () => {
        controls.current.fire('click');
        return location.success({ coords: gps });
    };
    const submit = () => {
        const event = { defaultPrevented: false, preventDefault() { this.defaultPrevented = true; } };
        form.fire('submit', event);
        return event;
    };
    return { fields, controls, location, marker, calls, locate, submit };
}

test('ubicación actual rellena los cuatro campos y conserva el GPS hasta la confirmación explícita', async () => {
    const ui = preparar();
    await ui.locate();
    assert.equal(ui.calls.length, 1);
    assert.equal(ui.calls[0].url, '/ubicaciones/invertir');
    assert.deepEqual(JSON.parse(ui.calls[0].options.body), { latitud: gps.latitude, longitud: gps.longitude });
    assert.equal(ui.calls[0].options.headers['X-CSRF-TOKEN'], 'csrf-test');
    for (const key of ['direccion', 'barrio', 'ciudad', 'departamento']) assert.equal(ui.fields[key].value, direccion[key]);
    assert.deepEqual(ui.marker.position, [gps.latitude, gps.longitude]);
    assert.equal(ui.fields.latitud.value, '');
    assert.equal(ui.controls.receipt.hidden, true);
    ui.controls.confirm.fire('click');
    assert.equal(ui.fields.latitud.value, gps.latitude);
    assert.equal(ui.fields.longitud.value, gps.longitude);
    assert.equal(ui.fields.ubicacion_confirmada.value, '1');
    assert.equal(ui.controls.receipt.hidden, false);
    assert.match(ui.controls.summary.textContent, /Pasto, Nariño/);
});

test('indica el barrio faltante y permite confirmar sin ese dato opcional', async () => {
    const ui = preparar(async () => respuesta({ direccion: { ...direccion, barrio: null } }));
    ui.fields.barrio.value = 'Barrio anterior';
    await ui.locate();
    assert.equal(ui.fields.barrio.value, '');
    assert.match(ui.controls.status.textContent, /Falta barrio/);
    ui.controls.confirm.fire('click');
    assert.equal(ui.fields.ubicacion_confirmada.value, '1');
});

test('permiso denegado no consulta al proveedor ni borra la dirección escrita', () => {
    const ui = preparar();
    ui.fields.direccion.value = 'Dirección manual';
    ui.controls.current.fire('click');
    ui.location.error({ code: 1 });
    assert.equal(ui.calls.length, 0);
    assert.equal(ui.fields.direccion.value, 'Dirección manual');
    assert.match(ui.controls.status.textContent, /No se pudo obtener/);
});

test('servicio sin configurar conserva los campos y permite completar manualmente el punto', async () => {
    const message = 'La búsqueda de direcciones no está disponible.';
    const ui = preparar(async () => ({ ok: false, status: 503, json: async () => ({ message }) }));
    ui.fields.direccion.value = 'Dirección manual';
    await ui.locate();
    assert.equal(ui.fields.direccion.value, 'Dirección manual');
    assert.equal(ui.controls.status.textContent, message);
    assert.equal(ui.controls.confirm.disabled, false);
    assert.equal(ui.fields.ubicacion_confirmada.value, '0');
});

test('una respuesta tardía no sobrescribe cambios manuales', async () => {
    let resolve;
    const ui = preparar(() => new Promise(done => { resolve = done; }));
    const pending = ui.locate();
    ui.fields.direccion.value = 'Mi corrección';
    ui.fields.direccion.fire('input');
    assert.equal(ui.calls[0].options.signal.aborted, true);
    resolve(respuesta());
    await pending;
    assert.equal(ui.fields.direccion.value, 'Mi corrección');
    assert.equal(ui.fields.ciudad.value, '');
});

test('quitar el punto mientras se obtiene el GPS descarta el resultado', async () => {
    const ui = preparar();
    ui.controls.current.fire('click');
    ui.controls.clear.fire('click');
    await ui.location.success({ coords: gps });
    assert.equal(ui.calls.length, 0);
    assert.equal(ui.controls.confirm.disabled, true);
});

test('completar dirección del punto sigue disponible para reintentar', async () => {
    const ui = preparar();
    await ui.locate();
    ui.fields.direccion.value = '';
    ui.fields.direccion.fire('input');
    await ui.controls.reverse.fire('click');
    assert.equal(ui.calls.length, 2);
    assert.equal(ui.fields.direccion.value, direccion.direccion);
});

test('publicar confirma el punto completo y envía sus coordenadas sin un clic previo de confirmación', async () => {
    const ui = preparar();
    await ui.locate();
    assert.equal(ui.fields.ubicacion_confirmada.value, '0');
    assert.equal(ui.submit().defaultPrevented, false);
    assert.equal(ui.fields.ubicacion_confirmada.value, '1');
    assert.equal(ui.fields.latitud.value, gps.latitude);
    assert.equal(ui.fields.longitud.value, gps.longitude);
    assert.equal(ui.controls.receipt.hidden, false);
});

for (const campo of ['direccion', 'ciudad', 'departamento']) {
    test(`publicar con punto y sin ${campo} muestra el error y enfoca el dato faltante`, async () => {
        const ui = preparar();
        await ui.locate();
        ui.fields[campo].value = '  ';
        ui.fields[campo].fire('input');
        assert.equal(ui.submit().defaultPrevented, true);
        assert.equal(ui.fields[campo].focused, true);
        assert.equal(ui.fields[campo].scrolled, true);
        assert.match(ui.controls.status.textContent, /Completa dirección/);
        assert.equal(ui.fields.latitud.value, '');
        assert.equal(ui.fields.ubicacion_confirmada.value, '0');
    });
}

test('publicar permite dirección manual sin punto y no inventa coordenadas', () => {
    const ui = preparar();
    ui.fields.ciudad.value = 'Pasto';
    assert.equal(ui.submit().defaultPrevented, false);
    assert.equal(ui.fields.latitud.value, '');
    assert.equal(ui.fields.longitud.value, '');
    assert.equal(ui.fields.ubicacion_confirmada.value, '0');
});

test('publicar una dirección corregida la confirma con el punto y admite barrio vacío', async () => {
    const ui = preparar();
    await ui.locate();
    ui.controls.confirm.fire('click');
    ui.fields.direccion.value = 'Parque central';
    ui.fields.barrio.value = '';
    ui.fields.direccion.fire('input');
    assert.equal(ui.submit().defaultPrevented, false);
    assert.equal(ui.fields.ubicacion_confirmada.value, '1');
    assert.match(ui.controls.summary.textContent, /Parque central/);
});
