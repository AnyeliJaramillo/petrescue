# Mapas y direcciones

## Configurar Geoapify

1. Crea tu proyecto y una clave en https://myprojects.geoapify.com/ siguiendo https://apidocs.geoapify.com/docs/geocoding/forward-geocoding/.
2. Configura la clave **privada** en `Backend/.env`:

   ```dotenv
   GEOAPIFY_API_KEY=tu_clave
   GEOCODING_REQUESTS_PER_MINUTE=60
   ```

3. Desde `petrescue`, ejecuta `php Backend/artisan config:clear`. Si utilizas procesos persistentes, reinícialos después de cambiar la configuración.
4. Abre cualquiera de los formularios. Escribe dirección, ciudad y departamento; pulsa **Buscar dirección**, elige un resultado y pulsa **Confirmar ubicación**.

El registro de la cuenta y la clave corresponden al propietario del proyecto. No publiques la clave ni uses `VITE_GEOAPIFY_API_KEY`: las consultas se realizan desde Laravel. Configura las restricciones de la clave para tu servidor y ajusta las cuotas a tu plan. La cuota global local predeterminada es 60 solicitudes por minuto, con 20 por minuto por IP. Es un límite de tráfico, no un límite diario de facturación: configura también las cuotas y alertas del proveedor. Con varios servidores, utiliza un caché compartido para aplicar estos límites conjuntamente.

Sin clave, el mapa y la escritura manual de la dirección siguen disponibles. Los botones de búsqueda devuelven un mensaje controlado. No se realizaron consultas reales a Geoapify durante la implementación; las pruebas del adaptador usan respuestas simuladas.

## Selección y confirmación

- El mapa empieza centrado en Colombia sin guardar ese centro como ubicación.
- Puedes elegir un resultado, pulsar sobre el mapa, arrastrar el marcador o usar la geolocalización del navegador.
- **Usar mi ubicación actual** obtiene el punto y consulta automáticamente su dirección: rellena dirección, barrio, ciudad y departamento cuando estén disponibles. Hace falta una clave válida de Geoapify. Si no se permite la geolocalización, no se consulta al proveedor; si falta algún dato, se informa para completarlo manualmente.
- **Completar dirección del punto** hace una consulta inversa a Geoapify. La dirección resultante puede corresponder al lugar más próximo; el marcador conserva el punto que eligió el usuario.
- Los campos faltantes o aproximados se corrigen a mano. Barrio es opcional.
- **Confirmar ubicación** requiere punto, dirección, ciudad y departamento y muestra un resumen visual. La confirmación expresa la elección del usuario; no certifica oficialmente la dirección.
- **Publicar reporte** también confirma el punto seleccionado si dirección, ciudad y departamento están completos, sin exigir un clic previo en **Confirmar ubicación**. Si falta un dato, detiene el envío y enfoca el campo pendiente. Sin punto se mantiene la publicación con dirección manual. El backend sigue validando coordenadas y confirmación.
- Editar la dirección o mover el marcador invalida la confirmación. **Quitar punto** permite publicar solo los datos escritos.
- Las coordenadas se transmiten en campos ocultos y se validan en el backend; no se muestran como campos visibles ni en el texto del detalle del reporte.
- Ocultar los campos es una decisión de interfaz. Las coordenadas siguen presentes en la petición y en los datos que necesita el mapa.

Las búsquedas se filtran a `countrycode:co` y el adaptador descarta resultados de otros países. La selección manual del marcador no constituye una validación de fronteras territoriales.

## Arquitectura

```text
Frontend/resources/views/components/selector-ubicacion.blade.php
Frontend/resources/js/reportes/mapa-ubicacion.js
Frontend/resources/js/reportes/seleccion-ubicacion.js
Frontend/resources/css/components/location-selector.css
    → POST /ubicaciones/buscar o /ubicaciones/invertir
Backend/app/Http/Controllers/UbicacionController.php
    → Application/Ubicaciones/ConsultarUbicaciones
    → contrato Application/Ubicaciones/Contracts/Geocodificador
    ← Infrastructure/Geocoding/GeoapifyGeocodificador
```

El provider conecta el contrato con el adaptador. Los casos de uso y DTO son PHP independiente del framework. Las llamadas tienen timeout, caché de una hora y errores controlados sin incluir la clave. El componente es compartido por reportes de mascotas perdidas y encontradas. No se necesitan migraciones nuevas.

## Teselas y despliegue público

Leaflet usa inicialmente las teselas estándar de OpenStreetMap con atribución visible. Su servicio público no ofrece garantía de disponibilidad ni capacidad ilimitada. Respeta https://operations.osmfoundation.org/policies/tiles/: no quitar la atribución ni el Referer del navegador, no descargar mapas masivamente ni precargarlos para uso offline.

Para un despliegue con tráfico o garantías de servicio, contrata un proveedor de teselas acorde a tus necesidades y configura ambos valores:

```dotenv
MAP_TILES_URL="https://tu-proveedor/{z}/{x}/{y}.png"
MAP_TILES_ATTRIBUTION="Atribución exigida por tu proveedor"
```

La URL de teselas se entrega al navegador, así que cualquier clave incluida debe ser una clave pública restringida por dominio. La clave privada de geocodificación nunca se utiliza en esta URL. La integración permite cambiar las teselas sin modificar los componentes.

En producción utiliza HTTPS para la geolocalización del navegador. Buscar o completar una dirección envía esos datos a Geoapify; informa sobre ese tratamiento en la política de privacidad de la aplicación. El mapa solicita imágenes al proveedor de teselas.

## Verificar

```powershell
composer --working-dir=Backend test
npm.cmd --prefix Frontend test
npm.cmd --prefix Frontend run build
php Backend/artisan view:cache
```

Después de añadir la clave, prueba una dirección conocida de Colombia, corrige el marcador, confirma, publica y revisa el detalle. Prueba también búsquedas sin resultados, pérdida de conexión y rechazo del permiso de geolocalización.
