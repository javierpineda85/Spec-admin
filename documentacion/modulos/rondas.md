# # Módulo: RONDAS

## 1. Descripción general

El módulo **Rondas** administra los puntos de control que deben ser escaneados por los vigiladores durante un recorrido operativo.

Cada ronda incluye:

- Puesto o punto de control  
- Objetivo al que pertenece  
- Tipo (Fija / Eventual)  
- Orden de escaneo  
- Estado (`draft`, `active`, `inactive`)  
- Integración con QR dinámicos  

Este módulo es fundamental para:

- Control de rondas operativas  
- Auditoría de recorridos  
- Seguridad del servicio  
- Integración con el módulo QR  
- Registro de escaneos  

---

# 2. Rutas del módulo

Estas rutas deben estar en:

```
/rutas/rutas_rondas.php
```

### ✔ Rutas recomendadas (completas)

```php
<?php

// Crear rondas
if (isset($_GET['r']) && $_GET['r'] === 'crear_rondas') {
    RondasController::vistaCrearRondas();
    return;
}

// Guardar rondas (múltiples)
if (isset($_GET['r']) && $_GET['r'] === 'guardar_rondas') {
    RondasController::crtGuardarRondas($_POST['rondas'] ?? []);
    return;
}

// Listado de rondas activas
if (isset($_GET['r']) && $_GET['r'] === 'listado_rondas') {
    RondasController::vistaListadoRondas();
    return;
}

// Editar ronda
if (isset($_GET['r']) && $_GET['r'] === 'editar_ronda') {
    RondasController::vistaEditarRondas();
    return;
}

// Actualizar ronda
if (isset($_GET['r']) && $_GET['r'] === 'actualizar_ronda') {
    RondasController::crtActualizarRonda();
    return;
}

// Desactivar ronda
if (isset($_GET['r']) && $_GET['r'] === 'desactivar_ronda') {
    RondasController::crtDesactivarRonda($_POST['idRonda']);
    return;
}

// Vista de escaneo
if (isset($_GET['r']) && $_GET['r'] === 'escanear_rondas') {
    RondasController::vistaEscanearRondas();
    return;
}
```

---

# 3. Controladores involucrados

### **RondasController**

| Método | Descripción |
|--------|-------------|
| `crtGuardarRondas()` | Guarda múltiples rondas en una transacción. |
| `eliminarImagenesQR()` | Limpia archivos PNG generados. |
| `eliminarErroresQR()` | Limpia logs de la librería QR. |
| `limpiarSesionQR()` | Limpia `$_SESSION['qr_codes']`. |
| `crtDesactivarRonda()` | Cambia estado a `inactive`. |
| `crtActualizarRonda()` | Actualiza una ronda y genera nuevo QR. |
| `vistaListadoRondas()` | Lista rondas activas. |
| `vistaCrearRondas()` | Formulario de creación. |
| `vistaEditarRondas()` | Formulario de edición. |
| `vistaEscanearRondas()` | Vista para escanear rondas. |

---

# 4. Submódulo: Crear rondas

## ✔ crtGuardarRondas()

Flujo:

1. Valida permisos  
2. Inicia transacción  
3. Recorre array de rondas  
4. Inserta cada ronda con:

```
status = 'draft'
```

5. Si todo sale bien:
   - Commit  
   - Limpia `$_SESSION['qr_codes']`  
   - Devuelve:

```json
{ "success": true, "inserted_ids": [1,2,3] }
```

6. Si falla:
   - Rollback  
   - Devuelve error  

---

# 5. Submódulo: Actualizar ronda

## ✔ crtActualizarRonda()

Flujo:

1. Valida permisos  
2. Recibe datos por POST  
3. Valida campos  
4. Actualiza ronda  
5. Limpia sesión de QR  
6. Obtiene datos actualizados de la ronda  
7. Genera un nuevo QR en sesión:

```php
$_SESSION['qr_codes'][] = [
    'idRonda' => ...,
    'objetivo_id' => ...,
    'objetivo_nombre' => ...,
    'puesto' => ...,
    'orden' => ...,
    'tipo' => ...
];
```

8. Redirige a `?r=imprimir_qr`  

---

# 6. Submódulo: Desactivar ronda

## ✔ crtDesactivarRonda()

- Cambia `status = 'inactive'`  
- Muestra Toastify de éxito o error  

---

# 7. Submódulo: Limpieza de archivos y sesión

### ✔ eliminarImagenesQR()
Elimina todos los PNG de:

```
img/qrcodes/
```

### ✔ eliminarErroresQR()
Elimina logs de:

```
libraries/phpqrcode/*.log
```

### ✔ limpiarSesionQR()
Elimina:

```
$_SESSION['qr_codes']
```

---

# 8. Submódulo: Listado de rondas

## ✔ vistaListadoRondas()

Consulta:

```sql
SELECT r.idRonda, r.puesto, r.objetivo_id, r.tipo, o.nombre AS objetivo
FROM rondas r
JOIN objetivos o ON r.objetivo_id = o.idObjetivo
WHERE r.status = 'active'
ORDER BY r.objetivo_id, r.orden_escaneo
```

Renderiza:

```
vistas/paginas/rondas/listado_rondas.php
```

---

# 9. Submódulo: Vistas principales

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| crear_rondas.php | `vistas/paginas/rondas/` | Formulario de creación |
| editar_ronda.php | `vistas/paginas/rondas/` | Edición |
| listado_rondas.php | `vistas/paginas/rondas/` | Listado |
| escanear_rondas.php | `vistas/paginas/rondas/` | Vista de escaneo |

---

# 10. Modelo involucrado

### **ModeloRondas**

| Método | Descripción |
|--------|-------------|
| `mdlGuardarRonda()` | Inserta ronda y devuelve ID. |
| `mdlDesactivarRonda()` | Cambia estado a `inactive`. |
| `mdlActualizarRonda()` | Actualiza datos de la ronda. |

---

# 11. Tabla de base de datos

### Tabla: `rondas`

```sql
CREATE TABLE `rondas` (
  `idRonda` int NOT NULL AUTO_INCREMENT,
  `puesto` varchar(100) NOT NULL,
  `objetivo_id` int NOT NULL,
  `tipo` enum('Fija','Eventual') NOT NULL,
  `orden_escaneo` int NOT NULL,
  `status` enum('draft','active','inactive') NOT NULL DEFAULT 'draft',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idRonda`)
);
```

### Campos

| Campo | Descripción |
|--------|-------------|
| puesto | Nombre del punto de control |
| objetivo_id | Relación con objetivos |
| tipo | Fija / Eventual |
| orden_escaneo | Orden dentro de la ronda |
| status | draft / active / inactive |
| fecha_creacion | Auditoría |

---

# 12. Integración con otros módulos

### ✔ Con QR
- Rondas en estado `draft` generan QR  
- QR se guarda en sesión  
- QR se imprime desde `imprimir_qr`  

### ✔ Con Escaneos
- Cada QR contiene una URL que registra un escaneo  
- El vigilador escanea → se registra en `escaneos`  

### ✔ Con Objetivos
- Cada ronda pertenece a un objetivo  
- Se usa para recorridos internos  

---

# 13. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| Datos obligatorios | puesto, objetivo_id, tipo, orden |
| Estado draft | Solo se eliminan rondas draft |
| Estado active | Solo se listan rondas activas |
| Seguridad | Requiere permisos (Auth::check) |
| Sesión | QR se guarda temporalmente |

---

# 14. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| “Error al guardar ronda” | Datos incompletos o error SQL | Revisar POST |
| QR no se genera | No se guardó ronda | Revisar sesión |
| No se actualiza ronda | Error en UPDATE | Revisar modelo |
| No se listan rondas | status != active | Revisar estado |

---

# 15. Mejoras futuras sugeridas

- Activar rondas automáticamente al generar QR  
- Editor visual de rondas (drag & drop)  
- Exportación de rondas a PDF  
- Mapa interactivo de rondas  
- Auditoría completa de cambios  
- Integración con app móvil  

---


