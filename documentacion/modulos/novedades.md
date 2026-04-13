# # Módulo: NOVEDADES

## 1. Descripción general

El módulo **Novedades** permite registrar, visualizar y analizar eventos relevantes ocurridos durante el servicio, incluyendo:

- Novedades operativas cargadas por vigiladores o supervisores  
- Adjuntos (fotos, documentos, evidencia)  
- Listado general de novedades  
- Análisis detallado de **entradas y salidas** con comparación contra horarios teóricos  
- Historial de marcaciones por vigilador  

Este módulo es utilizado por:

- Vigiladores (solo carga y lectura limitada)  
- Supervisores  
- Administrativos  
- Dirección  

Es uno de los módulos más completos del sistema, ya que integra:

- Marcaciones  
- Turnos  
- Puestos  
- Objetivos  
- Usuarios  
- Archivos adjuntos  

---

# 2. Rutas del módulo

Estas rutas deben estar en:

```
/rutas/rutas_novedades.php
```

### ✔ Rutas recomendadas

```php
<?php

// Crear novedad
if (isset($_GET['r']) && $_GET['r'] === 'crear_novedad') {
    NovedadesController::vistaCrearNovedades();
    return;
}

// Guardar novedad
if (isset($_GET['r']) && $_GET['r'] === 'registrar_novedad') {
    NovedadesController::crtRegistrar();
    return;
}

// Listado de novedades
if (isset($_GET['r']) && $_GET['r'] === 'listado_novedades') {
    NovedadesController::vistaListadoNovedades();
    return;
}

// Vista de entradas y salidas (dashboard)
if (isset($_GET['r']) && $_GET['r'] === 'entradas_salidas') {
    NovedadesController::vistaEntradaSalida();
    return;
}

// Listado analítico de entradas y salidas
if (isset($_GET['r']) && $_GET['r'] === 'listado_entrada_salida') {
    NovedadesController::vistaListadoEntradaSalida();
    return;
}

// Historial de marcaciones
if (isset($_GET['r']) && $_GET['r'] === 'historial_marcaciones') {
    NovedadesController::vistaHistorialMarcaciones();
    return;
}
```

---

# 3. Controladores involucrados

### **NovedadesController**

| Método | Descripción |
|--------|-------------|
| `crtRegistrar()` | Registra una novedad con adjunto. |
| `vistaListadoNovedades()` | Lista novedades según rol. |
| `vistaListadoEntradaSalida()` | Analiza entradas/salidas con horarios teóricos. |
| `vistaEntradaSalida()` | Vista simple de marcaciones. |
| `vistaCrearNovedades()` | Formulario para crear novedad. |
| `vistaHistorialMarcaciones()` | Historial de marcaciones por vigilador. |
| `calcularHoraEsperadaConBase()` | Determina hora esperada según turno. |
| `calcularDiffMin()` | Calcula diferencia en minutos. |
| `calcularEstado()` | Determina estado textual. |
| `calcularBadge()` | Determina badge visual (color + estado). |

---

# 4. Registro de novedades

### ✔ crtRegistrar()

Flujo:

1. Valida permisos  
2. Obtiene vigilador, objetivo, detalle  
3. Maneja archivo adjunto con `ControladorArchivos::guardarArchivo()`  
4. Inserta en tabla `novedades`  
5. Commit + Toastify de éxito  

Campos guardados:

- vigilador_id  
- objetivo_id  
- fecha  
- hora  
- detalle  
- adjunto  

---

# 5. Listado de novedades

### ✔ vistaListadoNovedades()

- Si el usuario es **Vigilador**, solo ve novedades de su objetivo  
- Otros roles ven todas  
- JOIN con usuarios y objetivos  
- Ordenado por fecha y hora descendente  

---

# 6. Análisis de entradas y salidas

### ✔ vistaListadoEntradaSalida()

Este es uno de los módulos más avanzados del sistema.

Incluye:

- Marcación real  
- Turno calendarizado del día  
- Turno del día anterior (para nocturnos)  
- Horarios del puesto  
- Cálculo de hora esperada  
- Diferencia en minutos  
- Determinación de estado  
- Badge visual (color + texto)  
- Link a OpenStreetMap con coordenadas  

### Estados posibles:

| Estado | Descripción |
|--------|-------------|
| En rango | ±10 min |
| Tarde ≤ 30 min | Entrada tardía leve |
| Muy temprano | Entrada demasiado anticipada |
| Salida anticipada | Salida antes de hora |
| Extra no remunerado | Salida después de hora |
| Sin horario | No hay horario del puesto |
| Fuera de rango | No coincide con turno calendarizado |

---

# 7. Historial de marcaciones

### ✔ vistaHistorialMarcaciones()

Permite filtrar por:

- Vigilador  
- Fecha desde  
- Fecha hasta  

Incluye:

- Objetivo  
- Puesto  
- Tipo de evento  
- Fecha/hora  
- Coordenadas  
- Horarios del puesto  

---

# 8. Modelos involucrados

### **ModeloNovedades**

| Método | Descripción |
|--------|-------------|
| `mdlGuardarNovedad()` | Inserta una novedad. |
| `obtenerMarcacionesPorVigiladorYFecha()` | Historial filtrado. |
| `obtenerVigiladores()` | Lista vigiladores (no usado actualmente). |

---

# 9. Tablas de base de datos

### Tabla: `novedades`

```sql
CREATE TABLE `novedades` (
  `idNovedad` int NOT NULL AUTO_INCREMENT,
  `vigilador_id` int NOT NULL,
  `objetivo_id` int DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `detalle` text,
  `adjunto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idNovedad`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
```

### Relaciones

| Campo | Relación |
|--------|----------|
| vigilador_id | usuarios.idUsuario |
| objetivo_id | objetivos.idObjetivo |

---

# 10. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| crear_novedades.php | `vistas/paginas/novedades/` | Formulario de carga |
| listado_novedades.php | `vistas/paginas/novedades/` | Listado general |
| listado_entradaSalidas.php | `vistas/paginas/novedades/` | Análisis de entradas/salidas |
| entradas_salidas.php | `vistas/paginas/novedades/` | Vista simple |
| historialMarcaciones.php | `vistas/paginas/novedades/` | Historial filtrado |

---

# 11. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| Detalle obligatorio | No se permite novedad vacía |
| Vigilador obligatorio | Debe existir |
| Adjuntos | Se guardan en `img/novedades/` |
| Permisos | Todos los métodos usan `Auth::check()` |
| Transacciones | Registro de novedades usa beginTransaction |
| Horarios | Se comparan contra turnos y puestos |

---

# 12. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| “Faltan datos obligatorios” | vigilador_id o detalle vacío | Revisar formulario |
| No se guarda adjunto | Error en ruta | Revisar permisos de carpeta |
| No aparecen novedades | Vigilador sin objetivo | Revisar asignación |
| Estados incorrectos | Horarios del puesto incompletos | Revisar tabla puestos_turnos |

---

# 13. Mejoras futuras sugeridas

- Adjuntar múltiples archivos  
- Clasificación por tipo de novedad  
- Notificaciones automáticas a supervisores  
- Dashboard de novedades por objetivo  
- Exportación a Excel/PDF  
- Integración con IA para detectar patrones de tardanzas  
- Geolocalización en mapa interactivo  

---


