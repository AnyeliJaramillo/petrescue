// Mejora progresiva del GET de búsqueda. Laravel sigue filtrando y renderizando.
export function iniciarBusquedaReportes(root, request) {
    const cleanups = [];
    for (const container of root.querySelectorAll('[data-report-search]')) {
        const doc = container.ownerDocument;
        const win = doc.defaultView;
        const fetchPage = request ?? win.fetch.bind(win);
        const select = selector => container.querySelector(selector);
        const status = select('[data-search-status]');
        const error = select('[data-search-error]');
        let pending;
        let version = 0;
        let disposed = false;
        let retryUrl;
        const listeners = [];
        const syncRaces = () => {
            const species = select('[data-filter-species]');
            const race = select('[data-filter-race]');
            if (!species || !race) return;
            const value = species.value;
            for (const option of race.options) {
                if (!option.value) continue;
                option.hidden = Boolean(value) && !(option.dataset.species || '').split(',').includes(value);
            }
            if (race.selectedOptions[0]?.hidden) race.value = '';
        };
        const listen = (target, name, callback, capture = false) => {
            target.addEventListener(name, callback, capture);
            listeners.push(() => target.removeEventListener(name, callback, capture));
        };
        const fallbackPhoto = image => {
            image.hidden = true;
            image.parentElement.querySelector('[data-photo-fallback]').hidden = false;
        };
        const checkPhotos = () => {
            for (const image of container.querySelectorAll('[data-search-photo]')) {
                if (image.complete && image.naturalWidth === 0) fallbackPhoto(image);
            }
        };
        const load = async (url, history = true) => {
            if (url.origin !== win.location.origin || disposed) return;
            pending?.abort();
            pending = new win.AbortController();
            const thisRequest = pending;
            const current = ++version;
            retryUrl = url;
            error.hidden = true;
            select('[data-search-content]').setAttribute('aria-busy', 'true');
            status.textContent = 'Cargando reportes…';
            let timeout = false;
            const timer = win.setTimeout(() => { timeout = true; thisRequest.abort(); }, 20000);
            try {
                const response = await fetchPage(url.href, {
                    signal: thisRequest.signal,
                    headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!response.ok) throw new Error('No disponible');
                const html = await response.text();
                if (current !== version || disposed) return;
                const page = new win.DOMParser().parseFromString(html, 'text/html');
                const content = page.querySelector('[data-search-content]');
                if (!content || !content.querySelector('[data-search-heading]')) throw new Error('Respuesta inesperada');
                // Una redirección de validación debe reflejar la URL final, no el filtro rechazado.
                const resolvedUrl = response.redirected ? new win.URL(response.url) : url;
                if (resolvedUrl.origin !== win.location.origin) throw new Error('Destino inesperado');
                select('[data-search-content]').replaceWith(doc.importNode(content, true));
                if (history && resolvedUrl.href !== win.location.href) win.history.pushState(null, '', resolvedUrl.href);
                const heading = select('[data-search-heading]');
                status.textContent = heading.textContent;
                heading.focus({ preventScroll: true });
                heading.scrollIntoView({ block: 'start', behavior: 'instant' });
                checkPhotos();
                syncRaces();
            } catch (failure) {
                if (current !== version || disposed || (failure.name === 'AbortError' && !timeout)) return;
                status.textContent = '';
                error.hidden = false;
                select('[data-search-results]').hidden = true;
            } finally {
                win.clearTimeout(timer);
                if (current === version && !disposed) select('[data-search-content]').removeAttribute('aria-busy');
            }
        };
        listen(container, 'submit', event => {
            const form = event.target.closest('[data-search-form]');
            if (!form) return;
            event.preventDefault();
            const url = new win.URL(form.action);
            // Campos del formulario GET, sin estado ni página heredada al cambiar filtros.
            for (const [key, value] of new win.FormData(form)) {
                if (typeof value === 'string' && value.trim()) url.searchParams.set(key, value.trim());
            }
            void load(url);
        });
        listen(container, 'change', event => {
            if (event.target.matches('[data-filter-species]')) syncRaces();
        });
        listen(container, 'click', event => {
            if (event.target.closest('[data-search-retry]')) {
                if (retryUrl) void load(retryUrl);
                return;
            }
            const link = event.target.closest('a[data-search-link], [data-search-pagination] a');
            if (!link || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
            const url = new win.URL(link.href);
            if (url.origin !== win.location.origin) return;
            event.preventDefault();
            void load(url);
        });
        listen(container, 'error', event => {
            if (event.target.matches('[data-search-photo]')) fallbackPhoto(event.target);
        }, true);
        listen(win, 'popstate', () => { void load(new win.URL(win.location.href), false); });
        checkPhotos();
        syncRaces();
        cleanups.push(() => {
            disposed = true;
            version++;
            pending?.abort();
            listeners.forEach(remove => remove());
            select('[data-search-content]').removeAttribute('aria-busy');
        });
    }
    return () => cleanups.forEach(cleanup => cleanup());
}
