# # Módulo: HOMBRE VIVO

## 1. Descripción general

El módulo **Hombre Vivo** permite que un vigilador reporte periódicamente su estado durante el servicio, indicando:

- Su **usuario**
- El **objetivo** donde está asignado
- La **demora** desde el último reporte
- La **fecha y hora** del reporte

Este módulo es crítico para:

- Control de actividad del vigilador  
- Seguridad operativa  
- Alertas automáticas por demora excesiva (integrado con el módulo Alertas)  
- Auditoría de cumplimiento  

El módulo funciona tanto por **formulario web** como por **AJAX**, permitiendo integración con apps móviles o dispositivos externos.

Actualización reciente:

- El tiempo entre reportes quedó configurable por turno **diurno** y **nocturno**.
- La tolerancia de alerta se mantiene fija en **3 minutos**.
- La portada muestra un bloque con los reportes recientes y enlaza al listado completo.

---

## 2. Rutas del módulo

Estas rutas deben estar en:

```
/rutas/rutas_hvivo.php
```

| Ruta | Método | Descripción |
|------|--------|-------------|
| `hombre_vivo` | GET | Muestra la vista principal del módulo. |
| `registrar_hvivo` | GET/POST | Registra un reporte Hombre Vivo. |
| `listado_hvivo` | GET | Lista todos los reportes. |
| `reporte_hombre_vivo` | GET | Vista operativa del reporte con temporizador. |
| `listado_reportes` | GET | Listado general de reportes Hombre Vivo. |
| `configuracion_hvivo` | GET | Pantalla de configuración por turno. |
| `guardar_configuracion_hvivo` | POST | Guarda la configuración diurna/nocturna. |
| `ajax_registrar_hvivo` | GET | Registra reporte vía AJAX. |

---

## 3. Controladores involucrados

### **HombreVivoController**

| Método | Descripción |
|--------|-------------|
| `registrar()` | Registra un reporte Hombre Vivo (GET/POST). |
| `vistaHombreVivo()` | Muestra la vista principal del módulo. |
| `vistaListadoReportesHombreVivo()` | Muestra listado de reportes. |
| `obtenerReportesRecientesInicio()` | Devuelve los últimos reportes para el panel de inicio. |
| `ajaxRegistrarReporte()` | Registra reporte vía AJAX. |

---

### ✔ registrar()

Flujo:

1. Valida permisos  
2. Obtiene usuario y objetivo desde sesión (más seguro)  
3. Acepta GET/POST como fallback  
4. Valida formato de demora `HH:MM:SS`  
5. Inserta en la tabla `reporte_hombre_vivo`  
6. Devuelve JSON con éxito o error  

Validaciones:

- `id_usuario > 0`  
- `objetivo_id > 0`  
- `demora` con formato válido  
- Normaliza horas a 2 dígitos  

---

### ✔ vistaHombreVivo()

- Valida permisos  
- Verifica si el usuario ya marcó **entrada** y **salida** hoy  
- Guarda estado en sesión  
- Carga la configuración de minutos por turno desde `hvivo_config`
- Renderiza:

```
vistas/paginas/h-vivo/reporte_hombre_vivo.php
```

---

### ✔ vistaListadoReportesHombreVivo()

Renderiza:

```
vistas/paginas/h-vivo/listado_reportesHvivo.php
```

---

### ✔ ajaxRegistrarReporte()

- Recibe parámetros vía GET  
- Inserta directamente en BD  
- Devuelve JSON  
- No usa el modelo (inserta con SQL directo)  

---

## 11. Inicio y paneles relacionados

La portada del sistema reutiliza parte de este módulo para mostrar:

- Resumen de reportes recientes de Hombre Vivo
- Enlace directo a `index.php?r=listado_reportes`
- Acceso a configuración para roles habilitados

---

## 4. Modelos involucrados

### **ModeloReporteHombreVivo**

| Método | Descripción |
|--------|-------------|
| `mdlGuardarReporte()` | Inserta un reporte Hombre Vivo. |

---

## 5. Tablas de base de datos

### Tabla: `reporte_hombre_vivo`

```sql
CREATE TABLE `reporte_hombre_vivo` (
  `idReporte` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `objetivo_id` int NOT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `demora` varchar(9) DEFAULT NULL,
  PRIMARY KEY (`idReporte`),
  KEY `idx_usuario` (`id_usuario`),
  KEY `idx_objetivo` (`objetivo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
```

### Campos

| Campo | Tipo | Descripción |
|--------|------|-------------|
| idReporte | int | Identificador único |
| id_usuario | int | Usuario que reporta |
| objetivo_id | int | Objetivo donde está asignado |
| fecha_hora | timestamp | Fecha y hora del reporte |
| demora | varchar(9) | Tiempo desde último reporte (HH:MM:SS) |

### Relaciones

| Campo | Relación |
|--------|----------|
| id_usuario | usuarios.idUsuario |
| objetivo_id | objetivos.idObjetivo |

---

## 6. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| reporte_hombre_vivo.php | `vistas/paginas/h-vivo/` | Formulario principal |
| listado_reportesHvivo.php | `vistas/paginas/h-vivo/` | Listado de reportes |

---

## 7. Flujo de trabajo del módulo

### 1. El vigilador accede a Hombre Vivo
- Se verifica si ya marcó entrada/salida  
- Se muestra formulario con demora  

### 2. El vigilador envía el reporte
- Se valida formato  
- Se inserta en BD  
- Se devuelve JSON  

### 3. AJAX (modo app)
- Se envía GET con parámetros  
- Se guarda en BD  
- Se devuelve JSON  

### 4. Listado
- Supervisores acceden a `listado_hvivo`  
- Se muestran reportes ordenados por fecha  

---

## 8. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| Formato de demora | Debe ser `HH:MM:SS` |
| Usuario y objetivo | Deben existir en sesión o request |
| Seguridad | Requiere `Auth::check()` |
| Fecha automática | `fecha_hora` se genera con `CURRENT_TIMESTAMP` |
| Normalización | Horas se formatean a 2 dígitos |

---

## 9. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| “Parámetros inválidos” | Falta usuario, objetivo o demora | Revisar sesión o request |
| “Formato de demora inválido” | No coincide con `HH:MM:SS` | Normalizar antes de enviar |
| No se guarda reporte | Error SQL | Revisar modelo y tabla |
| Vista en blanco | Falta permiso | Revisar `Auth::check()` |

---

## 10. Mejoras futuras sugeridas

- Integración con alertas automáticas por demora excesiva  
- Dashboard en tiempo real  
- Notificaciones push  
- Registro de ubicación GPS  
- Historial por usuario y objetivo  
- Exportación a Excel/PDF  

---

#
