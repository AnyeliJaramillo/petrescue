const MAX_BYTES = 2 * 1024 * 1024;

// Comprobaciones de UX. El servidor comprueba de nuevo el contenido real.
export function errorDeFoto(file) {
    if (!['image/jpeg', 'image/png'].includes(file.type)) {
        return `${file.name}: selecciona una imagen JPG o PNG.`;
    }
    if (file.size === 0) return `${file.name}: el archivo está vacío.`;
    if (file.size > MAX_BYTES) return `${file.name}: supera el máximo de 2 MB.`;
    return null;
}

export function iniciarSelectorFotos(root, urls = URL) {
    const cleanups = [];
    for (const selector of root.querySelectorAll('[data-selector-fotos]')) {
        const input = selector.querySelector('[data-fotos-input]');
        const status = selector.querySelector('[data-fotos-estado]');
        const errorsList = selector.querySelector('[data-fotos-errores]');
        const previews = selector.querySelector('[data-fotos-previews]');
        const clear = selector.querySelector('[data-fotos-limpiar]');
        const label = selector.querySelector('[data-fotos-etiqueta]');
        const count = selector.querySelector('[data-fotos-cantidad]');
        const form = input.closest('form');
        const doc = selector.ownerDocument;
        let objectUrls = [];
        let version = 0;
        let serverInvalid = input.getAttribute('aria-invalid') === 'true';

        const release = () => {
            for (const url of objectUrls) urls.revokeObjectURL(url);
            objectUrls = [];
        };

        const render = () => {
            const currentVersion = ++version;
            release();
            previews.replaceChildren();
            const files = Array.from(input.files ?? []);
            if (files.length && !Number(count.value)) count.value = String(files.length);
            const errors = new Map();
            const missingFiles = files.length !== Number(count.value);
            if (missingFiles) errors.set('missing', 'Las fotos anteriores ya no están adjuntas. Selecciónalas nuevamente o elige Continuar sin fotos.');
            clear.hidden = files.length === 0 && !missingFiles;
            clear.textContent = missingFiles ? 'Continuar sin fotos' : 'Quitar selección';
            label.textContent = files.length ? 'Cambiar fotos' : 'Seleccionar fotos';

            const refreshStatus = () => {
                errorsList.replaceChildren();
                for (const message of errors.values()) {
                    const item = doc.createElement('li');
                    item.textContent = message;
                    errorsList.append(item);
                }
                errorsList.hidden = errors.size === 0;
                input.setCustomValidity(errors.size ? 'Revisa las fotos seleccionadas o quita la selección.' : '');
                input.setAttribute('aria-invalid', String(errors.size > 0 || serverInvalid));
                status.textContent = errors.size
                    ? 'Revisa los archivos indicados antes de publicar.'
                    : files.length
                        ? `${files.length} ${files.length === 1 ? 'foto seleccionada' : 'fotos seleccionadas'}. Se adjuntarán al publicar el reporte.`
                        : 'No has seleccionado fotos.';
            };

            files.forEach((file, index) => {
                const error = errorDeFoto(file);
                if (error) {
                    errors.set(index, error);
                    return;
                }
                const figure = doc.createElement('figure');
                figure.className = 'photo-selector-preview';
                const image = doc.createElement('img');
                image.alt = `Vista previa de ${file.name}`;
                const caption = doc.createElement('figcaption');
                caption.textContent = `${file.name} · ${Math.max(1, Math.ceil(file.size / 1024))} KB`;
                figure.append(image, caption);
                previews.append(figure);
                image.addEventListener('error', () => {
                    if (currentVersion !== version) return;
                    image.hidden = true;
                    errors.set(index, `${file.name}: no se pudo leer la imagen. Selecciona otra foto.`);
                    refreshStatus();
                }, { once: true });
                try {
                    const url = urls.createObjectURL(file);
                    objectUrls.push(url);
                    image.src = url;
                } catch {
                    image.hidden = true;
                    errors.set(index, `${file.name}: no se pudo cargar la vista previa. Selecciona otra foto.`);
                }
            });
            previews.hidden = previews.children.length === 0;
            refreshStatus();
        };

        const clearSelection = () => {
            serverInvalid = false;
            input.value = '';
            count.value = '0';
            render();
        };
        const onChange = () => {
            serverInvalid = false;
            count.value = String(input.files?.length ?? 0);
            render();
        };
        const onSubmit = (event) => {
            if (Number(count.value) !== (input.files?.length ?? 0)) {
                event.preventDefault();
                render();
                input.reportValidity();
            }
        };
        // El evento reset se emite antes de que el navegador restablezca los campos.
        const onReset = (event) => queueMicrotask(() => {
            if (!event.defaultPrevented) { count.value = '0'; render(); }
        });
        input.addEventListener('change', onChange);
        clear.addEventListener('click', clearSelection);
        form?.addEventListener('reset', onReset);
        form?.addEventListener('submit', onSubmit);
        render();
        cleanups.push(() => {
            version++;
            release();
            input.removeEventListener('change', onChange);
            clear.removeEventListener('click', clearSelection);
            form?.removeEventListener('reset', onReset);
            form?.removeEventListener('submit', onSubmit);
        });
    }
    return () => cleanups.forEach((cleanup) => cleanup());
}
