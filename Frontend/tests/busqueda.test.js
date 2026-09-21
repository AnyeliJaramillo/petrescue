import test from 'node:test';
import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';
import { JSDOM } from 'jsdom';
import { iniciarBusquedaReportes } from '../resources/js/reportes/busqueda.js';

const fixture = fileURLToPath(new URL('../../Backend/tests/Fixtures/render-search.php', import.meta.url));
const views = Object.fromEntries(['perdida', 'encontrada', 'vacio'].map(tipo => [tipo, execFileSync('php', [fixture, tipo], { encoding: 'utf8' })]));
const response = html => ({ ok: true, text: async () => html });
const settle = () => new Promise(resolve => setImmediate(resolve));
function ui(request = async () => response(views.encontrada)) {
    const dom = new JSDOM(views.perdida, { url: 'http://localhost/buscar' });
    dom.window.HTMLElement.prototype.scrollIntoView = function () {};
    const doc = dom.window.document;
    const calls = [];
    const cleanup = iniciarBusquedaReportes(doc, (url, options) => { calls.push({ url, options }); return request(url, options); });
    const select = selector => doc.querySelector(selector);
    return {
        dom, doc, select, calls, cleanup,
        submit() { select('form').dispatchEvent(new dom.window.Event('submit', { bubbles: true, cancelable: true })); },
    };
}

test('la búsqueda inicial está renderizada, tiene seis selects y funciona sin fetch inicial', () => {
    const page = ui();
    assert.equal(page.calls.length, 0);
    assert.equal(page.select('form').method, 'get');
    assert.equal(page.doc.querySelectorAll('select').length, 6);
    assert.equal(page.select('[name="estado"]'), null);
    assert.equal(page.doc.querySelectorAll('.search-card').length, 2);
    assert.match(page.doc.body.textContent, /Mascota sin nombre/);
    for (const select of page.doc.querySelectorAll('select')) assert.ok(select.labels.length);
    page.cleanup();
});

test('envía filtros combinados por GET, muestra cargando y actualiza resultados e historial', async () => {
    let resolve;
    const page = ui(() => new Promise(done => { resolve = done; }));
    page.select('[name="color_principal"]').value = 'negro';
    page.select('[name="tamano"]').value = 'mediano';
    page.submit();
    assert.equal(page.select('[data-search-status]').textContent, 'Cargando reportes…');
    assert.equal(page.select('[data-search-content]').getAttribute('aria-busy'), 'true');
    const url = new URL(page.calls[0].url);
    assert.equal(url.searchParams.get('especie'), 'perro');
    assert.equal(url.searchParams.get('color_principal'), 'negro');
    assert.equal(url.searchParams.get('tamano'), 'mediano');
    assert.equal(url.searchParams.has('page'), false);
    resolve(response(views.encontrada)); await settle();
    assert.equal(page.select('[name="tipo_reporte"]').value, 'encontrada');
    assert.equal(page.select('[data-search-content]').hasAttribute('aria-busy'), false);
    assert.equal(page.doc.activeElement, page.select('[data-search-heading]'));
    assert.match(page.dom.window.location.search, /color_principal=negro/);
    page.cleanup();
});

test('estado sin resultados no contiene tarjetas y limpiar filtros consulta URL sin parámetros', async () => {
    const page = ui(async () => response(views.vacio));
    page.submit(); await settle();
    assert.equal(page.doc.querySelectorAll('.search-card').length, 0);
    assert.match(page.doc.body.textContent, /No encontramos reportes con estos filtros\./);
    page.select('.search-empty [data-search-link]').click(); await settle();
    assert.equal(new URL(page.calls[1].url).search, '');
    page.cleanup();
});

test('la paginación mantiene filtros y no intercepta clics modificados', async () => {
    const page = ui();
    const link = page.select('[data-search-pagination] a');
    link.dispatchEvent(new page.dom.window.MouseEvent('click', { bubbles: true, cancelable: true, ctrlKey: true }));
    assert.equal(page.calls.length, 0);
    link.click(); await settle();
    assert.match(page.calls[0].url, /page=2/);
    assert.match(page.calls[0].url, /especie=perro/);
    page.cleanup();
});

test('un fallo de red permite reintentar sin mostrar resultados anteriores como actuales', async () => {
    let fail = true;
    const page = ui(async () => { if (fail) throw new Error('dato interno'); return response(views.encontrada); });
    page.submit(); await settle();
    assert.equal(page.select('[data-search-error]').hidden, false);
    assert.equal(page.select('[data-search-results]').hidden, true);
    assert.match(page.select('[data-search-error]').textContent, /No pudimos cargar los reportes/);
    assert.doesNotMatch(page.doc.body.textContent, /dato interno/);
    fail = false; page.select('[data-search-retry]').click(); await settle();
    assert.equal(page.select('[data-search-error]').hidden, true);
    assert.equal(page.select('[data-search-results]').hidden, false);
    page.cleanup();
});

test('respuestas HTTP fallidas o inesperadas muestran el error de carga', async () => {
    for (const result of [{ ok: false }, response('<h1>Error</h1>')]) {
        const page = ui(async () => result);
        page.submit(); await settle();
        assert.equal(page.select('[data-search-error]').hidden, false);
        page.cleanup();
    }
});

test('una respuesta tardía no sobrescribe una búsqueda más reciente', async () => {
    const resolves = [];
    const page = ui(() => new Promise(done => resolves.push(done)));
    page.submit(); page.submit();
    assert.equal(page.calls[0].options.signal.aborted, true);
    resolves[1](response(views.encontrada)); await settle();
    resolves[0](response(views.vacio)); await settle();
    assert.equal(page.doc.querySelectorAll('.search-card').length, 2);
    assert.equal(page.select('[name="tipo_reporte"]').value, 'encontrada');
    page.cleanup();
});

test('atrás/adelante recarga la URL actual y las imágenes fallidas muestran fallback', async () => {
    const page = ui();
    page.dom.window.history.replaceState(null, '', '/buscar?tipo_reporte=encontrada');
    page.dom.window.dispatchEvent(new page.dom.window.PopStateEvent('popstate'));
    await settle();
    assert.match(page.calls[0].url, /tipo_reporte=encontrada/);
    const image = page.select('[data-search-photo]');
    image.dispatchEvent(new page.dom.window.Event('error'));
    assert.equal(image.hidden, true);
    assert.equal(image.parentElement.querySelector('[data-photo-fallback]').hidden, false);
    page.cleanup();
});

test('desmontar cancela solicitudes y en páginas sin búsqueda no añade comportamiento', () => {
    assert.doesNotThrow(iniciarBusquedaReportes(new JSDOM('<main>Inicio</main>').window.document));
    const page = ui(() => new Promise(() => {}));
    page.submit(); page.cleanup();
    assert.equal(page.calls[0].options.signal.aborted, true);
    page.submit(); assert.equal(page.calls.length, 1);
    page.dom.window.close();
});
