# Configurar el esquema de PetRescue en Supabase

## Script completo

Archivo: `Backend/database/sql/supabase_petrescue.sql`.

Este script adapta el SQL original del proyecto a los modelos actuales de Laravel.
También permite crear el esquema en una base vacía. No elimina tablas ni filas.
La adaptación se ejecuta dentro de una transacción: si ocurre un error antes de
`COMMIT`, no se confirma una corrección parcial.

1. Abre tu proyecto de Supabase y entra en **SQL Editor → New query**.
2. Copia **todo** el archivo, desde `BEGIN` hasta la consulta de comprobación final
   (puedes copiar también los comentarios iniciales).
3. Ejecuta la consulta con el rol `postgres`, el usado por esta configuración del backend.
4. Verifica que la consulta termine correctamente y que aparezcan las columnas de las
   cuatro tablas del flujo de publicación.
5. Vuelve al formulario de Laravel y publica el reporte. Si la página anterior mostró
   un error, vuelve al formulario antes de enviar de nuevo.

Si se informa que existen simultáneamente nombres antiguos y nuevos, el script
se detiene para evitar una fusión ambigua. No borres ninguna tabla: se deben revisar
los registros de ambas antes de continuar. Este archivo cubre el SQL original
entregado y el esquema actual, no todas las modificaciones manuales posibles.

## Qué cambia

| SQL original | Lo que espera Laravel |
| --- | --- |
| `mascotas.id_mascota` | `mascotas.id` |
| `reportes.id_reporte` | `reportes.id` |
| `reportes.id_mascota` | `reportes.mascota_id` |
| `ubicaciones.id_ubicacion` | `ubicaciones.id` |
| `ubicaciones.id_reporte` | `ubicaciones.reporte_id` |
| `imagenes_mascota` | `imagen_mascotas` |
| `id_imagen`, `id_mascota`, `url_imagen` de imágenes | `id`, `mascota_id`, `ruta_imagen` |
| Fechas históricas con nombres propios | `created_at`, `updated_at` |
| Contacto únicamente en `usuarios` | `responsable_nombre`, `responsable_correo`, `responsable_telefono` en `reportes` |

Los renombres conservan claves primarias, secuencias y referencias de las claves
foráneas. `id_usuario` pasa a ser opcional en mascotas y reportes: el formulario
actual permite publicar sin iniciar sesión y no envía ese campo. Se conservan
los vínculos de los registros antiguos y se copian sus contactos cuando existen.
Los campos de contacto permiten nulos para esos datos históricos; Laravel exige
nombre y teléfono en las nuevas publicaciones.

Las longitudes de nombre, especie, edad y título se amplían para admitir los
valores que ya acepta la validación del backend. Se mantienen las restricciones
de tipo de reporte, estado y relaciones del SQL original.

## Tablas

- Flujo de publicación: `mascotas`, `reportes`, `ubicaciones`, `imagen_mascotas`.
- Usuarios históricos: `usuarios`, conservada del SQL original.
- Infraestructura Laravel: `users`, `password_reset_tokens`, `sessions`, `cache`,
  `cache_locks`, `jobs`, `job_batches`, `failed_jobs`.

`users`, `usuarios` y `auth.users` son tablas diferentes. Este cambio no implementa
una integración entre Laravel Auth y Supabase Auth, ni cambia `auth.users`.
La tabla antigua `ubicacions`, si existe por migraciones anteriores, no participa
en los reportes y no se elimina.

## Imágenes y acceso

El backend permite guardar las fotos localmente o en **Supabase Storage**.
Para que el equipo vea las mismas fotos, todos deben usar la misma base de datos
de Supabase y configurar la subida a Storage en sus respectivos backends.

### Activar Supabase Storage

1. En el proyecto de Supabase abre **Storage → New bucket** y crea `mascotas`.
   Activa **Public bucket**: cualquiera que tenga la URL podrá ver la foto,
   igual que en los reportes públicos de la aplicación. Limita el bucket a
   `image/jpeg` e `image/png`, con un máximo de **2 MB** por archivo.
2. En **Settings → API Keys**, copia una clave **secret** (`sb_secret_...`).
   La clave `service_role` heredada también es compatible. No uses la clave
   `anon`/publishable ni la contraseña de PostgreSQL para esta integración.
3. Completa estas variables en `Backend/.env`:

```dotenv
IMAGENES_ALMACEN=supabase
SUPABASE_URL=https://TU_PROYECTO.supabase.co
SUPABASE_SECRET_KEY=TU_CLAVE_SECRETA
SUPABASE_STORAGE_BUCKET=mascotas
```

La URL es la del proyecto, sin `/storage/v1` ni el host del pooler PostgreSQL.
La clave solo debe estar en el backend: no la subas a Git ni uses un prefijo
`VITE_`. Este flujo no necesita políticas que permitan escrituras anónimas.

4. Desde `petrescue`, ejecuta `php Backend/artisan config:clear` y reinicia el
   servidor Laravel si estaba abierto.
5. Publica un reporte con una foto. Comprueba que el archivo aparece en
   **Storage → mascotas → mascotas**, que `imagen_mascotas.ruta_imagen` contiene
   una URL HTTPS de Supabase y que se ve en el listado y detalle desde otro equipo.

Cada subida usa un nombre UUID y guarda su URL pública completa en la base de
datos. Si falla una subida o la creación del reporte, se intenta eliminar las
fotos ya subidas durante ese intento. Si Storage falla, el backend no cambia
automáticamente a almacenamiento local.

### Fotos anteriores y modo local

Las fotos que ya estaban en `Backend/storage/app/public/mascotas` **no se suben
automáticamente** al activar Storage. Deben importarse por separado y actualizar
sus registros con las URLs públicas correspondientes; conserva los archivos
originales hasta verificar la importación. Las URLs HTTP/HTTPS ya registradas se
muestran directamente y las rutas locales siguen usando `/storage/`.

Para trabajar sin Storage, usa `IMAGENES_ALMACEN=local` (valor por defecto).
Si no existe el enlace público local, ejecuta desde `petrescue`:

```powershell
php Backend/artisan storage:link
```

Referencias: [buckets y acceso público](https://supabase.com/docs/guides/storage/buckets/fundamentals),
[claves del backend](https://supabase.com/docs/guides/getting-started/api-keys).

El SQL habilita RLS en las tablas de la aplicación. Laravel accede con la conexión
PostgreSQL del backend y el rol `postgres` de la configuración actual. No crea
políticas de acceso para `anon` ni `authenticated`. Si ya existen políticas, no las
elimina. Un cambio de rol de conexión requiere revisar permisos y políticas.

## Historial de migraciones

El script no marca migraciones de Laravel como ejecutadas artificialmente.
No ejecutes `migrate:fresh` ni `migrate:refresh` sobre estos datos: recrean tablas.
Después de una instalación manual, el historial de `migrations` debe conciliarse
con el esquema existente antes de ejecutar las migraciones de creación originales.
Las modificaciones futuras deben quedar versionadas en nuevas migraciones del Backend.

## Verificación realizada

Se ejecutó el script en PostgreSQL local mediante PGlite, tanto en una base vacía
como sobre el SQL original con datos de prueba. Se comprobó:

- Renombres y conservación de identificadores, fechas, contactos y relaciones.
- Inserción de ambos tipos de reporte con ubicación y fotos, sin `id_usuario`.
- Valores de longitud máxima admitidos por los campos relevantes del formulario.
- Reejecución del script antes y después de insertar reportes.
- Claves foráneas, borrado en cascada en datos de prueba, sesiones, caché y colas.
- Activación de RLS y detención ante nombres de tablas en conflicto.

No se ejecutó este script en el proyecto real de Supabase. La prueba local no
comprueba sus permisos particulares, sus políticas existentes ni cambios de esquema
que no estén en el SQL entregado.
