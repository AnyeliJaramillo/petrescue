import test from 'node:test';
import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';
import { JSDOM } from 'jsdom';
import { iniciarFormulariosPasos } from '../resources/js/reportes/formulario-pasos.js';
import { iniciarSelectorFotos } from '../resources/js/components/selector-fotos.js';
import { iniciarMapa } from '../resources/js/reportes/control-ubicacion.js';

const fixture = fileURLToPath(new URL('../../Backend/tests/Fixtures/render-report-form.php', import.meta.url));
const templates = Object.fromEntries(['perdida', 'encontrada'].map(tipo => [tipo, execFileSync('php', [fixture, tipo], { encoding: 'utf8' })]));

function preparar({ tipo = 'perdida', error, fotos = false, mapa = false, initialize = true } = {}) {
    const dom = new JSDOM(templates[tipo], { url: 'http://localhost' });
    const { document, Event } = dom.window;
    dom.window.HTMLElement.prototype.scrollIntoView = function () {};
    const form = document.querySelector('form');
    const field = name => form.elements.namedItem(name);
    if (error) field(error).setAttribute('data-server-error', '');
    const revoked = [];
    let serial = 0;
    const urls = { createObjectURL: () => `blob:test-${++serial}`, revokeObjectURL: url => revoked.push(url) };
    const cleanupPhotos = fotos ? iniciarSelectorFotos(document, urls) : () => {};
    let resized = 0;
    let mapInstances = 0;
    let mapClick;
    if (mapa) {
        const map = { setView() { return this; }, on(name, callback) { if (name === 'click') mapClick = callback; return this; }, invalidateSize() { resized++; } };
        iniciarMapa(form.querySelector('[data-selector-ubicacion]'), {
            L: {
                map() { mapInstances++; return map; },
                tileLayer: () => ({ on() { return this; }, addTo() {} }),
                divIcon: () => ({}),
                marker: () => ({ addTo() { return this; }, on() {}, setLatLng() {}, remove() {} }),
            },
            fetch: async () => { throw new Error('No debería solicitar geocodificación al navegar'); },
        });
    }
    const cleanup = initialize ? iniciarFormulariosPasos(document, urls) : () => {};
    const query = selector => form.querySelector(selector);
    const click = name => query(`[data-report-${name}]`).click();
    const fill = (name, value) => { field(name).value = value; field(name).dispatchEvent(new Event('input', { bubbles: true })); };
    const pet = () => { fill('especie', 'Perro'); fill('color_principal', 'Café'); fill('tamano', 'mediano'); };
    const event = () => { fill('fecha_evento', '2026-01-01'); fill('ciudad', 'Pasto'); };
    const contact = () => { fill('responsable_nombre', 'Persona de prueba'); fill('responsable_telefono', '3000000000'); };
    return {
        dom, document, form, field, query, click, fill, pet, event, contact, revoked,
        steps: [...form.querySelectorAll('[data-report-step]')],
        visible: () => [...form.querySelectorAll('[data-report-step]')].findIndex(step => !step.hidden),
        toFinal() { click('next'); pet(); click('next'); event(); click('next'); },
        submit() { const e = new Event('submit', { bubbles: true, cancelable: true }); form.dispatchEvent(e); return e; },
        choose(files) { Object.defineProperty(field('imagenes[]'), 'files', { configurable: true, value: files }); field('imagenes[]').dispatchEvent(new Event('change', { bubbles: true })); },
        mapPoint() { mapClick({ latlng: { lat: 1.21, lng: -77.28 } }); },
        resized: () => resized, mapInstances: () => mapInstances,
        cleanup() { cleanup(); cleanupPhotos(); },
    };
}

test('inicia en el primer paso, anuncia progreso y conserva un solo formulario', () => {
    const ui = preparar();
    assert.equal(ui.document.querySelectorAll('form').length, 1);
    assert.deepEqual(ui.steps.map(step => step.hidden), [false, true, true, true]);
    assert.equal(ui.query('[data-report-go="0"]').getAttribute('aria-current'), 'step');
    assert.match(ui.query('[data-report-announcement]').textContent, /Paso 1 de 4/);
    assert.equal(ui.query('[data-report-submit]').hidden, true);
    ui.cleanup();
});

test('avanza, enfoca el título, actualiza progreso y regresa conservando valores', () => {
    const ui = preparar();
    ui.click('next');
    assert.equal(ui.visible(), 1);
    assert.equal(ui.document.activeElement, ui.steps[1].querySelector('h2'));
    assert.equal(ui.query('[data-report-progress]').value, 2);
    ui.fill('nombre', 'Luna');
    ui.click('previous');
    ui.click('next');
    assert.equal(ui.field('nombre').value, 'Luna');
    ui.cleanup();
});

test('no avanza con campos obligatorios vacíos ni permite saltar pasos pendientes', () => {
    const ui = preparar();
    ui.query('[data-report-go="3"]').click();
    assert.equal(ui.visible(), 0);
    ui.click('next'); ui.click('next');
    assert.equal(ui.visible(), 1);
    assert.equal(ui.document.activeElement, ui.field('especie'));
    ui.pet(); ui.click('next');
    assert.equal(ui.visible(), 2);
    ui.query('[data-report-go="0"]').click();
    assert.equal(ui.visible(), 0);
    ui.cleanup();
});

test('abre y enfoca el primer error del servidor en el orden visual', () => {
    const ui = preparar({ initialize: false });
    ui.field('responsable_nombre').setAttribute('data-server-error', '');
    ui.field('fecha_evento').setAttribute('data-server-error', '');
    const cleanup = iniciarFormulariosPasos(ui.document);
    assert.equal(ui.visible(), 2);
    assert.equal(ui.document.activeElement, ui.field('fecha_evento'));
    assert.equal(ui.query('[data-report-progress]').value, 3);
    cleanup();
});

test('el resumen muestra datos actuales de forma segura y omite opcionales vacíos', () => {
    const ui = preparar(); ui.toFinal(); ui.contact();
    ui.fill('nombre', '<img src=x onerror=alert(1)>');
    ui.fill('color_secundario', 'Blanco'); ui.fill('barrio', 'Centro');
    assert.equal(ui.query('[data-summary="nombre"]').textContent, '<img src=x onerror=alert(1)>');
    assert.equal(ui.query('[data-summary="nombre"] img'), null);
    assert.equal(ui.query('[data-summary="colores"]').textContent, 'Café y Blanco');
    assert.equal(ui.query('[data-summary="lugar"]').textContent, 'Centro, Pasto');
    assert.equal(ui.query('[data-summary="responsable_telefono"]').textContent, '3000000000');
    assert.equal(ui.query('[data-summary-row="raza"]').hidden, true);
    ui.fill('raza', 'Mestizo');
    assert.equal(ui.query('[data-summary-row="raza"]').hidden, false);
    ui.fill('nombre', '');
    assert.equal(ui.query('[data-summary="nombre"]').textContent, 'Mascota sin nombre');
    ui.cleanup();
});

test('las vistas reales diferencian textos, orden y contexto sin cambiar nombres enviados', () => {
    const lost = preparar(); const found = preparar({ tipo: 'encontrada' });
    assert.match(lost.document.body.textContent, /Alerta de mascota perdida/);
    assert.match(found.document.body.textContent, /Reporte de mascota encontrada/);
    assert.match(found.steps[1].textContent, /Raza aparente/);
    assert.match(found.steps[1].textContent, /¿Conoces su nombre\?/);
    assert.equal(lost.steps[1].querySelector('input').name, 'nombre');
    assert.equal(found.steps[1].querySelector('input').name, 'especie');
    assert.match(lost.steps[2].textContent, /Última ubicación conocida/);
    assert.match(found.steps[2].textContent, /Ubicación del hallazgo/);
    assert.deepEqual([...lost.form.elements].map(f => f.name).sort(), [...found.form.elements].map(f => f.name).sort());
    for (const name of ['nombre', 'raza', 'edad_aproximada', 'sexo']) assert.equal(found.field(name).required, false);
    lost.cleanup(); found.cleanup();
});

test('funciona sin formularios y permite instancias independientes y desmontaje', () => {
    const dom = new JSDOM('<main>Inicio</main>');
    assert.doesNotThrow(iniciarFormulariosPasos(dom.window.document));
    const ui = preparar({ initialize: false });
    const second = ui.form.cloneNode(true); ui.document.body.append(second);
    const cleanup = iniciarFormulariosPasos(ui.document);
    ui.click('next');
    assert.equal(ui.visible(), 1);
    assert.equal(second.querySelector('[data-report-step]').hidden, false);
    cleanup();
    assert.equal(ui.form.noValidate, false);
    assert.ok(ui.steps.every(step => !step.hidden));
    ui.click('next');
    assert.ok(ui.steps.every(step => !step.hidden));
});

test('Enter intermedio nunca publica; el envío final valida todos los pasos', () => {
    const ui = preparar();
    let published = 0; ui.form.addEventListener('submit', () => published++);
    assert.equal(ui.submit().defaultPrevented, true);
    assert.equal(published, 0); assert.equal(ui.visible(), 1);
    ui.pet(); ui.click('next'); ui.event(); ui.click('next'); ui.contact();
    ui.field('color_principal').value = '';
    assert.equal(ui.submit().defaultPrevented, true);
    assert.equal(ui.visible(), 1); assert.equal(published, 0);
    ui.pet(); ui.click('next'); ui.click('next');
    assert.equal(ui.submit().defaultPrevented, false); assert.equal(published, 1);
    ui.cleanup();
});

test('valida fecha futura y correo usando restricciones nativas', () => {
    const ui = preparar(); ui.toFinal(); ui.contact();
    ui.fill('responsable_correo', 'incorrecto');
    assert.equal(ui.submit().defaultPrevented, true);
    assert.equal(ui.document.activeElement, ui.field('responsable_correo'));
    ui.fill('responsable_correo', 'prueba@example.com');
    ui.fill('fecha_evento', '2999-01-01');
    assert.equal(ui.submit().defaultPrevented, true);
    assert.equal(ui.visible(), 2);
    ui.cleanup();
});

test('selector real de fotos mantiene archivos, errores, cantidad y miniatura independiente', async () => {
    const ui = preparar({ fotos: true });
    const files = [new ui.dom.window.File(['foto'], 'foto.png', { type: 'image/png' })];
    ui.choose(files); ui.toFinal();
    assert.equal(ui.field('imagenes[]').files, files);
    assert.equal(ui.field('imagenes_cantidad').value, '1');
    assert.equal(ui.query('[data-fotos-previews]').children.length, 1);
    assert.equal(ui.query('[data-report-thumbnail]').hidden, false);
    ui.click('previous'); ui.click('previous'); ui.click('previous');
    ui.choose([new ui.dom.window.File(['no'], 'foto.txt', { type: 'text/plain' })]);
    ui.click('next'); assert.equal(ui.visible(), 0);
    ui.choose([]); ui.query('[data-fotos-limpiar]').click();
    await Promise.resolve();
    assert.equal(ui.field('imagenes_cantidad').value, '0');
    assert.equal(ui.query('[data-report-thumbnail]').hidden, true);
    assert.equal(ui.revoked.length, 2);
    ui.cleanup();
});

test('fotos perdidas por el navegador bloquean el avance hasta elegir o renunciar', () => {
    const ui = preparar({ initialize: false });
    ui.field('imagenes_cantidad').value = '2';
    const cleanupPhotos = iniciarSelectorFotos(ui.document);
    const cleanupSteps = iniciarFormulariosPasos(ui.document);
    ui.click('next'); assert.equal(ui.visible(), 0);
    assert.match(ui.query('[data-fotos-errores]').textContent, /Selecciónalas nuevamente/);
    ui.query('[data-fotos-limpiar]').click(); ui.click('next');
    assert.equal(ui.visible(), 1);
    cleanupSteps(); cleanupPhotos();
});

test('mostrar el mapa invalida su tamaño sin recrearlo ni borrar coordenadas', () => {
    const ui = preparar({ mapa: true });
    ui.click('next'); ui.pet(); ui.click('next'); ui.event();
    ui.fill('direccion', 'Parque central'); ui.fill('departamento', 'Nariño');
    ui.mapPoint(); ui.query('[data-location-confirm]').click();
    ui.click('next'); ui.click('previous');
    assert.equal(ui.resized(), 2); assert.equal(ui.mapInstances(), 1);
    assert.equal(ui.field('latitud').value, '1.21');
    assert.equal(ui.field('longitud').value, '-77.28');
    assert.equal(ui.field('ubicacion_confirmada').value, '1');
    ui.cleanup();
});

test('un error del mapa al publicar abre su paso y conserva la dirección', () => {
    const ui = preparar({ mapa: true }); ui.toFinal(); ui.contact();
    ui.mapPoint();
    assert.equal(ui.submit().defaultPrevented, true);
    assert.equal(ui.visible(), 2);
    assert.equal(ui.document.activeElement, ui.field('direccion'));
    assert.equal(ui.field('ciudad').value, 'Pasto');
    ui.cleanup();
});

test('sin JavaScript todos los pasos y el botón de publicar están disponibles', () => {
    const ui = preparar({ initialize: false });
    assert.ok(ui.steps.every(step => !step.hidden));
    assert.equal(ui.form.noValidate, false);
    assert.equal(ui.query('[data-report-submit]').hidden, false);
    assert.equal(ui.query('[data-report-stepper]').hidden, true);
    for (const field of ui.form.querySelectorAll('input:not([type="hidden"]),select,textarea')) {
        assert.ok(field.labels.length, `Falta label para ${field.name}`);
    }
});
