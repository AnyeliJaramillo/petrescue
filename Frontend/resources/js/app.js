import './bootstrap';
import { iniciarMapasUbicacion } from './reportes/mapa-ubicacion';
import { iniciarSelectorFotos } from './components/selector-fotos';

iniciarMapasUbicacion(document);
const limpiarSelectores = iniciarSelectorFotos(document);
window.addEventListener('pagehide', (event) => {
    if (!event.persisted) limpiarSelectores();
});
