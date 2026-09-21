# Formularios de reporte

La vista compartida `Frontend/resources/views/reportes/formulario.blade.php` conserva un único formulario POST multipart con CSRF. Las vistas `perdidas/create` y `encontradas/create` siguen seleccionando la ruta y el contexto. La configuración por `$tipo` solo cambia presentación: textos, ayudas, orden de los campos y acentos. Los controladores siguen determinando el tipo publicado.

## Presentación

- Pérdida: acento coral, nombre al principio y a todo el ancho, última ubicación conocida y fecha de pérdida.
- Hallazgo: acento turquesa, características observables primero, nombre opcional con menor protagonismo y ubicación del hallazgo.
- Cuatro pasos: fotografías; mascota; evento y ubicación; contacto y revisión.
- Componentes Blade reutilizables en `components/reportes`: campos, secciones, stepper y resumen. Los selectores de fotos y ubicación siguen siendo independientes.
- `report-form.css` aporta estilos específicos, dos columnas en escritorio y una en móvil, foco visible, controles de al menos 44 px y compatibilidad con movimiento reducido.
- Se muestran `color_secundario` y `referencia`, campos ya admitidos por el Request, DTO y esquema. No se agregaron campos de dominio, columnas ni migraciones.

## Interacción y accesibilidad

`reportes/formulario-pasos.js` inicializa solo `[data-report-form]`, mantiene el estado dentro de cada instancia y devuelve una función de limpieza. No almacena datos en localStorage ni hace publicaciones parciales. Sin JavaScript, todas las secciones y el botón de publicar permanecen disponibles, con validación HTML nativa y del servidor.

Con JavaScript, el módulo muestra una sección, valida con `ValidityState` y `reportValidity()`, anuncia el paso y enfoca su título. El envío se controla en captura antes de los listeners de fotos y ubicación. Enter en pasos intermedios avanza sin publicar; el envío final vuelve a comprobar todos los pasos y muestra el primer campo inválido.

Los errores de Laravel conservan sus mensajes, resumen general, `old()`, referencias `aria-describedby` y marcadores `data-server-error`. Se abre el primer error en el orden visual. Las fotos no se pueden repoblar desde `old()`: se conserva el contador y se exige volver a seleccionarlas o renunciar explícitamente a ellas.

El evento local `report:step-shown` solicita `invalidateSize` en la instancia existente de Leaflet. Si la confirmación al publicar encuentra una dirección incompleta, `report:reveal` abre el paso de ubicación antes de enfocar el campo. Se conservan los adaptadores y endpoints actuales de geocodificación.

El resumen utiliza `textContent` y URLs locales de la primera foto. Sus URLs se liberan al cambiar la foto o desmontar el módulo y son independientes de las del selector. No crea campos enviados al servidor.

Los contactos y ubicaciones siguen siendo públicos según las vistas actuales. Su protección requiere un cambio funcional posterior; el rediseño no promete ocultarlos.

## Verificación

Desde la raíz:

```powershell
composer --working-dir=Backend test
npm --prefix Frontend test
npm --prefix Frontend run build
php Backend/artisan view:cache
php Backend/artisan route:list --except-vendor
```

Las pruebas nuevas verifican el contrato HTML, old y errores accesibles, los dos contextos, navegación, restricciones nativas, foco, progreso, resumen seguro, fotos perdidas, conservación de archivos y coordenadas, redimensionamiento del mapa, envío final, múltiples instancias y mejora progresiva. `Backend/tests/Fixtures/render-report-form.php` es exclusivamente un fixture de pruebas: no añade rutas ni escribe datos.

En la sesión de implementación pasaron 28 pruebas PHP (324 aserciones) y 44 pruebas JavaScript. La compilación, caché de vistas y listado de 11 rutas finalizaron correctamente. npm requirió ejecución fuera de la restricción de creación de procesos del entorno (`spawn EPERM`).

Pendiente: revisión visual real de los cuatro pasos de ambos formularios en escritorio y móvil (incluido 320 px), archivos seleccionados, errores del servidor, mapa al mostrarse, resumen, desbordamientos y consola del navegador. No se pudo realizar porque no había navegador conectado y el control nativo devolvió `Computer Use native pipe is unavailable`. Las pruebas DOM no comprueban geometría, carga real de mosaicos, permisos GPS ni la consola de un navegador real.
