# PetRescue — Huellas en Casa

Aplicación Laravel MVC para publicar y consultar reportes de mascotas perdidas y encontradas. El código se divide físicamente en **Backend** y **Frontend** y mantiene las capas de Clean Architecture.

## Estructura

```text
petrescue/
├── Backend/
│   ├── app/
│   │   ├── Domain/
│   │   ├── Application/
│   │   ├── Infrastructure/
│   │   ├── Http/
│   │   ├── Models/
│   │   └── Providers/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── routes/web.php
│   ├── public/
│   ├── storage/
│   ├── tests/
│   ├── artisan
│   └── composer.json
├── Frontend/
│   ├── resources/
│   │   ├── views/
│   │   ├── css/
│   │   └── js/
│   ├── tests/
│   ├── package.json
│   └── vite.config.js
├── docs/ARQUITECTURA.md
└── README.md
```

**Backend:** rutas, validación, controladores, casos de uso, dominio, persistencia y fotos. **Frontend:** HTML Blade, estilos e interacción del navegador. Laravel renderiza las vistas del Frontend mediante `Backend/config/view.php`; Vite publica los recursos en `Backend/public/build`.

Consulta [la guía de arquitectura](docs/ARQUITECTURA.md) para conocer los contratos, dependencias y flujos.

La selección de ubicación utiliza Leaflet, OpenStreetMap y un adaptador de Geoapify. Para activar la búsqueda de direcciones configura `GEOAPIFY_API_KEY` en `Backend/.env`: consulta [configuración y uso de mapas](docs/MAPAS.md).

## Ejecutar el proyecto preparado

Desde la carpeta `petrescue`:

```powershell
php Backend/artisan serve
```

Abre la dirección que muestre Laravel. Para recompilar los recursos:

```powershell
npm.cmd --prefix Frontend run build
```

Para iniciar servidor, cola y Vite con recarga automática:

```powershell
composer --working-dir=Backend run dev
```

También puedes iniciar los procesos en terminales separadas: `php Backend/artisan serve` y `npm.cmd --prefix Frontend run dev`. Con Vite activo, abre la dirección de Laravel para usar la aplicación.

## Instalación nueva

Requisitos: PHP 8.2+, Composer, SQLite con `pdo_sqlite`, `fileinfo` para imágenes y Node.js compatible con Vite 7 (20.19+ o 22.12+).

Desde `petrescue`:

```powershell
composer --working-dir=Backend install
if (!(Test-Path Backend/.env)) { Copy-Item Backend/.env.example Backend/.env }
```

Ejecuta `php Backend/artisan key:generate` si la nueva instalación tiene `APP_KEY` vacío. Conserva la clave de instalaciones existentes.

```powershell
if (!(Test-Path Backend/database/database.sqlite)) { New-Item Backend/database/database.sqlite -ItemType File }
php Backend/artisan migrate
php Backend/artisan storage:link
npm.cmd --prefix Frontend ci
npm.cmd --prefix Frontend run build
php Backend/artisan serve
```

El enlace `Backend/public/storage` debe apuntar a `Backend/storage/app/public`. Si el enlace no existe y Windows no permite enlaces simbólicos, puedes crear una unión desde `petrescue`:

```powershell
New-Item -ItemType Junction -Path Backend/public/storage -Target (Join-Path (Get-Location) 'Backend/storage/app/public')
```

Configura el servidor web para servir **Backend/public**. La configuración privada está en `Backend/.env`; `Frontend` consume únicamente las variables públicas `VITE_*` mediante Vite.

## Pruebas

Desde `petrescue`:

```powershell
composer --working-dir=Backend test
npm.cmd --prefix Frontend test
npm.cmd --prefix Frontend run build
php Backend/artisan view:cache
php Backend/artisan route:list --except-vendor
```

Las pruebas PHP usan SQLite en memoria y cubren los flujos de reportes, fotos, validación, paginación, DTO, arquitectura y recuperación ante fallos. Las pruebas JavaScript comprueban fotos, geolocalización y el formulario de cuatro pasos. Las pruebas del formulario usan jsdom y renderizan las vistas reales de Blade mediante un fixture PHP, por lo que requieren PHP y las dependencias de Composer instaladas.

Los formularios comparten una vista y componentes, con contenido y acentos distintos para pérdida y hallazgo. JavaScript organiza los campos en cuatro pasos y genera un resumen local; todos los datos se publican juntos por las rutas existentes. Sin JavaScript, las cuatro secciones permanecen visibles. Consulta [el detalle del rediseño y su verificación](docs/FORMULARIOS_REPORTES.md).

## Rutas y datos

- `/`: inicio.
- `/reportes-perdidos` y `/reportes-encontrados`: listados paginados.
- Las mismas rutas con `/crear`: formularios.
- Las mismas rutas con `/{id}`: detalles.

Se aceptan fotos JPG/PNG de hasta 2 MB, coordenadas opcionales enviadas juntas y fechas del evento que no sean futuras. Los módulos de búsqueda y mapa siguen pendientes.

La reorganización de carpetas no necesita nuevas migraciones ni reiniciar la base. La base SQLite existente vive en `Backend/database/database.sqlite`, las fotos en `Backend/storage/app/public` y los respaldos en `Backend/storage/app/private/backups`. Para actualizar un esquema existente usa `php Backend/artisan migrate`; evita `migrate:fresh` y `migrate:reset` si debes conservar datos.
