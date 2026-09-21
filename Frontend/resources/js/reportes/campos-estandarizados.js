export function iniciarCamposEstandarizados(root) {
    const cleanups = [];
    for (const form of root.querySelectorAll('[data-report-form]')) {
        const listeners = [];
        const species = form.querySelector('[data-species-select]');
        const races = form.querySelector('[data-race-select]');
        const customOptions = new Set(['otra']);
        const updateCustom = select => {
            const input = form.querySelector(`[data-custom-for="${select.id}"]`);
            if (!input) return;
            const visible = customOptions.has(select.value);
            input.hidden = !visible;
            input.required = visible && select.required;
            if (!visible) input.value = '';
        };
        const updateRaces = () => {
            if (!races) return;
            const speciesValue = species?.value;
            for (const option of races.options) {
                option.hidden = !['', 'mestizo', 'otra', 'no_se'].includes(option.value)
                    && !((speciesValue === 'perro' && ['labrador', 'pastor-aleman', 'golden-retriever', 'bulldog', 'poodle'].includes(option.value))
                        || (speciesValue === 'gato' && ['siames', 'persa', 'angora'].includes(option.value)));
            }
            if (races.options[races.selectedIndex]?.hidden) races.value = '';
        };
        const listen = (target, event, callback) => {
            target?.addEventListener(event, callback);
            if (target) listeners.push(() => target.removeEventListener(event, callback));
        };
        for (const select of form.querySelectorAll('select')) {
            listen(select, 'change', () => updateCustom(select));
            updateCustom(select);
        }
        listen(species, 'change', updateRaces);
        updateRaces();
        cleanups.push(() => listeners.forEach(remove => remove()));
    }
    return () => cleanups.forEach(cleanup => cleanup());
}
