import { test } from 'node:test';
import assert from 'node:assert/strict';
import { errorDeFoto, iniciarSelectorFotos } from '../resources/js/components/selector-fotos.js';

function element(tag = 'div') {
    const listeners = new Map();
    let value = '';
    return {
        tag, children: [], hidden: false, textContent: '', attributes: {}, files: [],
        append(...children) { this.children.push(...children); },
        replaceChildren(...children) { this.children = children; },
        addEventListener(event, callback) { listeners.set(event, callback); },
        removeEventListener(event) { listeners.delete(event); },
        fire(event, detail = {}) { listeners.get(event)?.(detail); },
        setAttribute(key, value) { this.attributes[key] = value; },
        setCustomValidity(value) { this.validityMessage = value; },
        get value() { return value; },
        set value(next) { value = next; if (next === '') this.files = []; },
        reportValidity() { this.validityReported = true; },
    };
}

function selector(expected = '0') {
    const fields = Object.fromEntries(['input', 'estado', 'errores', 'previews', 'limpiar', 'etiqueta', 'cantidad']
        .map((name) => [name, element()]));
    const form = element('form');
    fields.cantidad.value = expected;
    fields.input.closest = () => form;
    const container = {
        ownerDocument: { createElement: (tag) => element(tag) },
        querySelector: (query) => fields[query.slice('[data-fotos-'.length, -1)],
    };
    const created = [];
    const revoked = [];
    const urls = {
        createObjectURL(file) { created.push(file); return `blob:foto-${created.length}`; },
        revokeObjectURL(url) { revoked.push(url); },
    };
    const cleanup = iniciarSelectorFotos({ querySelectorAll: () => [container] }, urls);
    return {
        ...fields, form, container, created, revoked, cleanup,
        choose(files) { fields.input.files = files; fields.input.fire('change'); },
    };
}

const foto = (name = 'mascota.jpg', type = 'image/jpeg', size = 1024) => ({ name, type, size });

test('muestra miniaturas, nombres seguros y cantidad sin modificar los archivos a enviar', () => {
    const ui = selector();
    const files = [foto('<b>mascota</b>.jpg'), foto('gato.png', 'image/png')];
    ui.choose(files);
    assert.equal(ui.previews.children.length, 2);
    assert.equal(ui.previews.children[0].children[0].src, 'blob:foto-1');
    assert.equal(ui.previews.children[0].children[1].textContent, '<b>mascota</b>.jpg · 1 KB');
    assert.match(ui.estado.textContent, /2 fotos seleccionadas/);
    assert.equal(ui.input.files, files);
    assert.equal(ui.input.validityMessage, '');
    assert.equal(ui.limpiar.hidden, false);
});

test('al cambiar o quitar las fotos libera las vistas previas y limpia la selección real', () => {
    const ui = selector();
    ui.choose([foto()]);
    ui.choose([foto('otra.png', 'image/png')]);
    assert.deepEqual(ui.revoked, ['blob:foto-1']);
    assert.equal(ui.previews.children.length, 1);
    ui.limpiar.fire('click');
    assert.deepEqual(ui.input.files, []);
    assert.deepEqual(ui.revoked, ['blob:foto-1', 'blob:foto-2']);
    assert.equal(ui.previews.hidden, true);
    assert.equal(ui.limpiar.hidden, true);
    assert.match(ui.estado.textContent, /No has seleccionado/);
});

test('valida los límites de formato, tamaño y archivos vacíos', () => {
    assert.equal(errorDeFoto(foto('foto.jpg', 'image/jpeg', 2 * 1024 * 1024)), null);
    assert.match(errorDeFoto(foto('grande.png', 'image/png', 2 * 1024 * 1024 + 1)), /2 MB/);
    assert.match(errorDeFoto(foto('vacia.jpg', 'image/jpeg', 0)), /vacío/);
    assert.match(errorDeFoto(foto('archivo.svg', 'image/svg+xml')), /JPG o PNG/);
});

test('una selección mixta conserva los archivos y señala el error sin enviarlos silenciosamente', () => {
    const ui = selector();
    const files = [foto(), foto('archivo.pdf', 'application/pdf')];
    ui.choose(files);
    assert.equal(ui.previews.children.length, 1);
    assert.equal(ui.input.files, files);
    assert.notEqual(ui.input.validityMessage, '');
    assert.equal(ui.errores.hidden, false);
    assert.match(ui.errores.children[0].textContent, /archivo.pdf/);
    ui.choose([foto()]);
    assert.equal(ui.input.validityMessage, '');
    assert.equal(ui.errores.hidden, true);
});

test('detecta imágenes que no se pueden decodificar e ignora errores de una selección anterior', () => {
    const ui = selector();
    ui.choose([foto()]);
    const oldImage = ui.previews.children[0].children[0];
    oldImage.fire('error');
    assert.match(ui.errores.children[0].textContent, /no se pudo leer/);
    ui.choose([foto('nueva.jpg')]);
    oldImage.fire('error');
    assert.equal(ui.input.validityMessage, '');
    assert.match(ui.estado.textContent, /1 foto seleccionada/);
});

test('restablecer el formulario limpia las miniaturas y los mensajes después del reset nativo', async () => {
    const ui = selector();
    ui.choose([foto()]);
    ui.form.fire('reset', { defaultPrevented: false });
    ui.input.value = '';
    await Promise.resolve();
    assert.equal(ui.previews.hidden, true);
    assert.equal(ui.input.validityMessage, '');
    assert.deepEqual(ui.revoked, ['blob:foto-1']);
});

test('cancelar el selector conserva las fotos y desmontar libera sus URLs', () => {
    const ui = selector();
    ui.choose([foto()]);
    ui.input.fire('cancel');
    assert.equal(ui.previews.children.length, 1);
    ui.cleanup();
    assert.deepEqual(ui.revoked, ['blob:foto-1']);
});

test('funciona en páginas sin selector de fotos', () => {
    const cleanup = iniciarSelectorFotos({ querySelectorAll: () => [] });
    assert.doesNotThrow(cleanup);
});

test('al volver de un error pide volver a adjuntar las fotos y permite renunciar explícitamente', () => {
    const ui = selector('2');
    assert.match(ui.errores.children[0].textContent, /Selecciónalas nuevamente/);
    assert.notEqual(ui.input.validityMessage, '');
    assert.equal(ui.limpiar.textContent, 'Continuar sin fotos');
    ui.choose([foto()]);
    assert.equal(ui.cantidad.value, '1');
    assert.equal(ui.input.validityMessage, '');
    ui.limpiar.fire('click');
    assert.equal(ui.cantidad.value, '0');
    assert.equal(ui.input.validityMessage, '');
});

test('si el navegador pierde los archivos después de seleccionarlos impide el envío silencioso', () => {
    const ui = selector();
    ui.choose([foto()]);
    ui.input.files = [];
    let prevented = false;
    ui.form.fire('submit', { preventDefault() { prevented = true; } });
    assert.equal(prevented, true);
    assert.equal(ui.cantidad.value, '1');
    assert.notEqual(ui.input.validityMessage, '');
    assert.equal(ui.input.validityReported, true);
});

test('el envío válido conserva los archivos y la cantidad seleccionada', () => {
    const ui = selector();
    ui.choose([foto(), foto('segunda.png', 'image/png')]);
    ui.form.fire('submit', { preventDefault() { assert.fail('No debe bloquear fotos válidas'); } });
    assert.equal(ui.cantidad.value, '2');
    assert.equal(ui.input.files.length, 2);
});
