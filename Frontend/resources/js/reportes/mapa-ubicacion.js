import L from 'leaflet';
import { iniciarMapa } from './control-ubicacion.js';

export function iniciarMapasUbicacion(root) {
    for (const container of root.querySelectorAll('[data-selector-ubicacion]')) {
        iniciarMapa(container, {
            L,
            geolocation: navigator.geolocation,
            fetch: window.fetch.bind(window),
        });
    }
}