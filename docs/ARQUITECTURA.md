# Arquitectura de PetRescue

## Enfoque

PetRescue mantiene una aplicación Laravel MVC con frontend renderizado en Blade. El código está separado físicamente en Backend y Frontend, dentro del mismo proyecto y despliegue MVC. Los formularios conservan sus rutas, sesiones, validación y protección CSRF.

Clean Architecture organiza el backend de reportes para que los casos de uso puedan ejecutarse con PHP y contratos, sin iniciar Laravel ni conectarse a una base real. MVC organiza la entrada HTTP y la presentación.

## Responsabilidad de cada carpeta

| Carpeta | Responsabilidad |
| --- | --- |
| `Frontend/resources/views` | Frontend: HTML con Blade, datos recibidos, mensajes, enlaces y formularios. |
| `Frontend/resources/views/layouts` | Estructura común de las páginas y carga de Vite. |
| `Frontend/resources/css` | Estilos y componentes visuales, incluido el formulario. |
| `Frontend/resources/js` | Interacción del navegador, como obtener coordenadas. |
| `Backend/routes/web.php` | Asociar una URL y un verbo HTTP con un controlador. |
| `Backend/app/Http/Controllers` | Recibir la solicitud, llamar al caso de uso y devolver una vista, una redirección o un 404. |
| `Backend/app/Http/Requests` | Validar el formulario en el servidor y convertirlo en datos de entrada. |
| `Backend/app/Http/Presenters` | Convertir resultados de aplicación en elementos de presentación de Laravel, como enlaces paginados. |
| `Backend/app/Domain/Reportes` | Tipos de reporte admitidos y construcción del título. PHP independiente del framework. |
| `Backend/app/Application/Reportes/UseCases` | Coordinar publicación y consulta. En publicación, coordinar la transacción y la limpieza de fotos ante errores. |
| `Backend/app/Application/Reportes/Contracts` | Definir lo que se necesita de la persistencia, las transacciones y el almacenamiento de imágenes. |
| `Backend/app/Application/Reportes/Data` | Objetos de transferencia de datos (DTO): entradas y salidas sin Eloquent, peticiones HTTP ni archivos de Laravel. |
| `Backend/app/Infrastructure/Persistence` | Implementar los contratos mediante Eloquent y transacciones Laravel; convertir modelos en DTO. |
| `Backend/app/Infrastructure/Storage` | Guardar y eliminar archivos mediante el disco público de Laravel. |
| `Backend/app/Models` | Modelos Eloquent y relaciones existentes. Pertenecen al mecanismo de persistencia y conservan su ubicación convencional. |
| `Backend/app/Providers/AppServiceProvider.php` | Conectar los contratos con sus implementaciones mediante inyección de dependencias. |
| `Backend/database` | Esquema, migraciones y datos. |

## Flujo de publicación

1. El formulario Blade envía los campos y las fotos mediante POST con CSRF.
2. `StoreReporteRequest` valida en el servidor. `aDatos()` transforma la entrada en `NuevoReporte` e `ImagenEntrada`; descarta campos no permitidos.
3. El controlador fija el tipo de reporte según la ruta y llama a `PublicarReporte`.
4. El caso de uso abre una transacción a través de `Transacciones`, guarda las fotos mediante `AlmacenImagenes` y persiste el reporte mediante `RepositorioReportes`.
5. Las implementaciones de infraestructura utilizan Laravel. Si falla el proceso, se revierten las escrituras de la transacción y se intenta eliminar las fotos creadas por ese intento.
6. El controlador redirige al listado y muestra el mensaje de éxito.

La transacción SQL y el almacenamiento de archivos son sistemas distintos: la limpieza de archivos compensa los errores de publicación, pero un cierre abrupto del proceso o un fallo del disco puede requerir revisar los registros y archivos huérfanos.

## Flujo de consulta

1. El controlador llama a `ConsultarReportes`.
2. El repositorio consulta por tipo y carga todas las relaciones necesarias.
3. Infraestructura devuelve `ReporteDetalle` o `PaginaReportes`, con datos ordinarios y sin relaciones que puedan ejecutar SQL al leerlas.
4. El presenter crea la paginación visual. Blade muestra los datos; las imágenes y ubicaciones son arreglos de valores.

## Dirección de las dependencias

```text
HTTP (controladores, requests, presenters) ──> Aplicación ──> Dominio
Infraestructura ──> contratos y DTO de Aplicación
AppServiceProvider conecta contratos con Infraestructura

Frontend <── vistas y respuestas HTTP ──> Controladores
```

La aplicación recibe interfaces mediante el constructor. No instancia implementaciones ni importa `Illuminate`, `App\Models`, `App\Http` o `App\Infrastructure`. El dominio tampoco importa la aplicación.

## Reglas para mantener la separación

- Una vista presenta datos y conserva el escape HTML; no consulta modelos ni realiza escrituras.
- Un controlador coordina HTTP; no contiene consultas Eloquent ni llamadas a `DB` o `Storage`.
- Las reglas de formato y validación del formulario se aplican en `StoreReporteRequest`; cualquier futura entrada, como una API o una tarea de consola, debe validar su entrada antes de construir `NuevoReporte`.
- Las reglas del dominio que deban compartirse entre distintas entradas se incorporan en `Domain` y se invocan desde los casos de uso.
- El JavaScript mejora la interacción. La validación del servidor sigue siendo obligatoria aunque el navegador valide o proponga coordenadas.
- Los adaptadores de infraestructura implementan contratos y transforman datos; los casos de uso coordinan la operación.
- Los DTO de lectura no contienen modelos Eloquent, paginadores Laravel ni objetos de petición.
- Para añadir búsqueda o mapa, crear primero el caso de uso y su contrato necesario, luego su adaptador, controlador y vista. Los archivos vacíos existentes de esos módulos siguen pendientes.

## Verificación

### Selector de fotos compartido

Los formularios de mascotas perdidas y encontradas incluyen `<x-selector-fotos />` desde la vista compartida. Su HTML está en `Frontend/resources/views/components/selector-fotos.blade.php`, su interacción en `Frontend/resources/js/components/selector-fotos.js` y sus estilos en `Frontend/resources/css/components/photo-selector.css`. Para reutilizarlo en otra vista incluye el mismo componente; si hay varios en una página, asigna un `id` diferente a cada uno.

Las miniaturas usan URLs temporales locales del navegador. Seleccionar una foto no la sube: los archivos se envían con el formulario al publicar. Cambiar o quitar la selección libera las URLs anteriores. El frontend avisa por tipo, tamaño o errores de lectura; `Backend/app/Http/Requests/StoreReporteRequest.php` mantiene la validación definitiva del contenido recibido. Los casos de uso y repositorios no participan en la vista previa.

```powershell
composer --working-dir=Backend test
npm.cmd --prefix Frontend test
npm.cmd --prefix Frontend run build
php Backend/artisan view:cache
```

Las pruebas PHP comprueban los flujos existentes, paginación, DTO, migraciones, rollback real en SQLite en memoria y fallos de publicación. Las pruebas unitarias de aplicación usan implementaciones simuladas sin arrancar Laravel. Las pruebas de arquitectura impiden imports de las capas externas en dominio/aplicación y consultas directas desde los controladores. Las pruebas JavaScript comprueban geolocalización y manejo de errores con un formulario simulado; no sustituyen una revisión visual en navegador.

La reorganización no requiere migraciones nuevas. Antes de comenzar se guardó el código existente en `Backend/storage/app/private/backups/antes-arquitectura-*.zip`.

## Unión entre las carpetas físicas

Backend/config/view.php apunta a Frontend/resources/views. Vite se configura en Frontend/vite.config.js y escribe los recursos compilados en Backend/public/build y su archivo de desarrollo en Backend/public/hot. Las vistas conservan las entradas resources/css/app.css y resources/js/app.js del manifiesto de Vite. La configuración privada permanece en Backend/.env.

Ejecuta los comandos PHP y Composer desde Backend y los comandos npm desde Frontend. El servidor web debe servir Backend/public. Las fuentes Blade del Frontend son renderizadas por Laravel.
