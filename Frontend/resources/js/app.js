import './bootstrap';
import { iniciarMapasUbicacion } from './reportes/mapa-ubicacion';
import { iniciarSelectorFotos } from './components/selector-fotos';
import { iniciarFormulariosPasos } from './reportes/formulario-pasos';
import { iniciarBusquedaReportes } from './reportes/busqueda';
import { iniciarCamposEstandarizados } from './reportes/campos-estandarizados';

iniciarMapasUbicacion(document);
const limpiarSelectores = iniciarSelectorFotos(document);
const limpiarFormularios = iniciarFormulariosPasos(document);
const limpiarBusquedas = iniciarBusquedaReportes(document);
const limpiarCampos = iniciarCamposEstandarizados(document);
window.addEventListener('pagehide', (event) => {
    if (!event.persisted) { limpiarBusquedas(); limpiarFormularios(); limpiarSelectores(); limpiarCampos(); }
});
