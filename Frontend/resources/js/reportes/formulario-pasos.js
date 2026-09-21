// Organización visual únicamente: un formulario, un envío y validación nativa.
export function iniciarFormulariosPasos(root, urls = URL) {
    const cleanups = [];
    for (const form of root.querySelectorAll('[data-report-form]')) {
        const steps = Array.from(form.querySelectorAll('[data-report-step]'));
        const buttons = Array.from(form.querySelectorAll('[data-report-go]'));
        const select = (name) => form.querySelector(`[data-report-${name}]`);
        if (!steps.length || form.hasAttribute('data-enhanced')) continue;
        const headings = steps.map(step => step.querySelector('[data-report-heading]'));
        const next = select('next');
        const previous = select('previous');
        const submit = select('submit');
        const progress = select('progress');
        const announcement = select('announcement');
        const thumbnail = select('thumbnail');
        const listeners = [];
        const completed = new Set();
        const originalNoValidate = form.noValidate;
        let current = 0;
        let photo;
        let photoUrl;
        let disposed = false;
        const listen = (target, event, callback, capture = false) => {
            target.addEventListener(event, callback, capture);
            listeners.push(() => target.removeEventListener(event, callback, capture));
        };
        const value = (name) => form.elements.namedItem(name)?.value.trim() ?? '';
        const releasePhoto = () => {
            if (photoUrl) urls.revokeObjectURL(photoUrl);
            photoUrl = undefined;
        };
        const summary = () => {
            if (disposed) return;
            const values = {
                nombre: value('nombre') || 'Mascota sin nombre',
                colores: [value('color_principal'), value('color_secundario')].filter(Boolean).join(' y '),
                lugar: [value('barrio'), value('ciudad')].filter(Boolean).join(', '),
            };
            for (const output of form.querySelectorAll('[data-summary]')) {
                const key = output.dataset.summary;
                const text = values[key] ?? value(key);
                output.textContent = text || 'Sin indicar';
                if (['raza', 'sexo', 'edad_aproximada'].includes(key)) {
                    output.closest('[data-summary-row]').hidden = !text;
                }
            }
            const file = form.querySelector('[data-fotos-input]')?.files?.[0];
            if (thumbnail && file !== photo) {
                releasePhoto();
                photo = file;
                thumbnail.hidden = true;
                thumbnail.removeAttribute('src');
                if (file && ['image/jpeg', 'image/png'].includes(file.type)) {
                    try {
                        photoUrl = urls.createObjectURL(file);
                        thumbnail.src = photoUrl;
                        thumbnail.hidden = false;
                    } catch { /* La selección y sus errores pertenecen al selector de fotos. */ }
                }
            }
        };
        const focus = (target) => {
            target?.focus({ preventScroll: true });
            target?.scrollIntoView({ block: 'center', behavior: 'instant' });
        };
        const show = (index, moveFocus = true) => {
            current = index;
            steps.forEach((step, i) => { step.hidden = i !== current; });
            buttons.forEach((button, i) => {
                button.disabled = i !== current && !completed.has(i);
                button.dataset.complete = String(completed.has(i));
                if (i === current) button.setAttribute('aria-current', 'step');
                else button.removeAttribute('aria-current');
                button.querySelector('[data-report-state]').textContent = i === current ? 'Actual' : completed.has(i) ? 'Completado' : '';
            });
            progress.value = current + 1;
            progress.textContent = `${current + 1} de ${steps.length}`;
            announcement.textContent = `Paso ${current + 1} de ${steps.length}: ${headings[current].textContent}`;
            previous.hidden = current === 0;
            next.hidden = current === steps.length - 1;
            submit.hidden = current !== steps.length - 1;
            if (!next.hidden) next.textContent = `Continuar: ${buttons[current + 1].querySelectorAll('span')[1].textContent.toLowerCase()}`;
            summary();
            // Leaflet conserva su instancia. Solo recalcula las dimensiones al hacerse visible.
            for (const map of steps[current].querySelectorAll('[data-selector-ubicacion]')) {
                map.dispatchEvent(new form.ownerDocument.defaultView.Event('report:step-shown'));
            }
            if (moveFocus) focus(headings[current]);
        };
        const reveal = (target) => {
            const index = steps.findIndex(step => step.contains(target));
            if (index !== -1 && index !== current) show(index, false);
            focus(target);
        };
        const validate = (step) => {
            const invalid = Array.from(step.querySelectorAll('input, select, textarea'))
                .find(field => field.willValidate && !field.validity.valid);
            if (!invalid) return true;
            reveal(invalid);
            invalid.reportValidity();
            return false;
        };
        const advance = () => {
            if (current < steps.length - 1 && validate(steps[current])) {
                completed.add(current);
                show(current + 1);
            }
        };
        listen(next, 'click', advance);
        listen(previous, 'click', () => { if (current > 0) show(current - 1); });
        buttons.forEach((button, index) => listen(button, 'click', () => {
            if (index === current) return;
            if (completed.has(index) && (index < current || validate(steps[current]))) show(index);
        }));
        // Captura antes de los listeners de fotos/mapa para evitar efectos de publicación
        // al pulsar Enter en un paso intermedio. No se deshabilitan los campos ocultos.
        listen(form, 'submit', (event) => {
            if (current !== steps.length - 1) {
                event.preventDefault();
                event.stopImmediatePropagation();
                advance();
                return;
            }
            for (const step of steps) {
                if (!validate(step)) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    return;
                }
            }
        }, true);
        listen(form, 'invalid', event => reveal(event.target), true);
        listen(form, 'report:reveal', event => reveal(event.target));
        listen(form, 'input', summary);
        listen(form, 'change', summary);
        // El botón de quitar fotos no emite change; se lee la selección tras sus listeners.
        listen(form, 'click', () => queueMicrotask(summary));
        listen(form, 'reset', event => queueMicrotask(() => {
            if (!disposed && !event.defaultPrevented) { completed.clear(); show(0); }
        }));
        if (thumbnail) listen(thumbnail, 'error', () => { thumbnail.hidden = true; });
        form.noValidate = true;
        form.setAttribute('data-enhanced', '');
        select('stepper').hidden = false;
        select('summary').hidden = false;
        const serverError = form.querySelector('[data-server-error]');
        show(0, false);
        if (serverError) reveal(serverError);
        cleanups.push(() => {
            disposed = true;
            listeners.forEach(remove => remove());
            releasePhoto();
            form.noValidate = originalNoValidate;
            form.removeAttribute('data-enhanced');
            steps.forEach(step => { step.hidden = false; });
            select('stepper').hidden = true;
            select('summary').hidden = true;
            next.hidden = true;
            previous.hidden = true;
            submit.hidden = false;
        });
    }
    return () => cleanups.forEach(cleanup => cleanup());
}
