import { test } from 'node:test';
import assert from 'node:assert/strict';
import { SeleccionUbicacion } from '../resources/js/reportes/seleccion-ubicacion.js';

const address = { direccion: 'Parque central', barrio: '', ciudad: 'Pasto', departamento: 'Nariño' };

test('el centro inicial del mapa no equivale a una selección', () => {
    const state = new SeleccionUbicacion();
    assert.equal(state.punto, null);
    assert.deepEqual(state.datos(), { latitud: '', longitud: '', ubicacion_confirmada: '0' });
});

test('solo envía el punto después de una confirmación con dirección completa', () => {
    const state = new SeleccionUbicacion();
    state.seleccionar(1.21, -77.28);
    assert.equal(state.datos().latitud, '');
    state.confirmar(address);
    assert.deepEqual(state.datos(), { latitud: 1.21, longitud: -77.28, ubicacion_confirmada: '1' });
});

test('editar la dirección invalida la confirmación y exige revisar el punto otra vez', () => {
    const state = new SeleccionUbicacion();
    state.seleccionar(1.21, -77.28); state.confirmar(address); state.editar();
    assert.equal(state.confirmada, false);
    assert.equal(state.datos().longitud, '');
    assert.notEqual(state.punto, null);
});

test('mover el marcador invalida las coordenadas confirmadas anteriores', () => {
    const state = new SeleccionUbicacion();
    state.seleccionar(1.21, -77.28); state.confirmar(address); state.seleccionar(1.22, -77.3);
    assert.equal(state.datos().ubicacion_confirmada, '0');
    state.confirmar(address);
    assert.equal(state.datos().latitud, 1.22);
});

test('permite barrio desconocido pero pide dirección, ciudad y departamento', () => {
    const state = new SeleccionUbicacion();
    assert.throws(() => state.confirmar(address), /punto/);
    state.seleccionar(1.21, -77.28);
    for (const field of ['direccion', 'ciudad', 'departamento']) {
        assert.throws(() => state.confirmar({ ...address, [field]: ' ' }), /Completa/);
    }
    assert.doesNotThrow(() => state.confirmar(address));
});

test('quitar el punto permite volver a la dirección manual y rechaza coordenadas inválidas', () => {
    const state = new SeleccionUbicacion();
    for (const [lat, lng] of [[91, 0], [0, -181], [NaN, 0], [1, Infinity]]) {
        assert.throws(() => state.seleccionar(lat, lng));
    }
    state.seleccionar(0, 0); state.confirmar(address); state.limpiar();
    assert.equal(state.punto, null);
    assert.equal(state.datos().ubicacion_confirmada, '0');
});
