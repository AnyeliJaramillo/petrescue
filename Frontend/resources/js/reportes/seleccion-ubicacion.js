export class SeleccionUbicacion {
    punto = null;
    confirmada = false;

    seleccionar(latitud, longitud) {
        if (!Number.isFinite(latitud) || !Number.isFinite(longitud) || Math.abs(latitud) > 90 || Math.abs(longitud) > 180) {
            throw new Error('No se pudo seleccionar ese punto. Inténtalo de nuevo.');
        }
        this.punto = { latitud, longitud };
        this.confirmada = false;
    }

    editar() { this.confirmada = false; }
    limpiar() { this.punto = null; this.confirmada = false; }

    confirmar(direccion) {
        if (!this.punto) throw new Error('Selecciona primero un punto en el mapa.');
        if (!direccion.direccion.trim() || !direccion.ciudad.trim() || !direccion.departamento.trim()) {
            throw new Error('Completa dirección, ciudad o municipio y departamento antes de confirmar.');
        }
        this.confirmada = true;
    }

    datos() {
        return this.confirmada
            ? { ...this.punto, ubicacion_confirmada: '1' }
            : { latitud: '', longitud: '', ubicacion_confirmada: '0' };
    }
}
