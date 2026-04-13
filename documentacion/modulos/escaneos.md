# # Módulo: ESCANEOS

## 1. Descripción general

El módulo **Escaneos** registra los escaneos de rondas realizados por los vigiladores.  
Cada escaneo representa la lectura de un QR asociado a:

- una **ronda**,  
- un **sector**,  
- un **vigilador**,  
- y una **fecha/hora**.

Este módulo es fundamental para:

- Control de rondas  
- Auditoría de cumplimiento  
- Reportes operativos  
- Feedback inmediato al vigilador  

El flujo es extremadamente simple y robusto:  
**un GET registra el escaneo y redirige a una vista de feedback.**

---

## 2. Rutas del módulo

### ✔ rutas_rondas.php 
```
<?php

// Registrar escaneo (GET)
if (isset($_GET['r']) && $_GET['r'] === 'registrar_escaneo') {
    EscaneosController::registrar();
    return;
}

// Feedback del escaneo
if (isset($_GET['r']) && $_GET['r'] === 'escaneo_feedback') {
    EscaneosController::feedback();
    return;
}
```

---

## 3. Controladores involucrados

### **EscaneosController**

| Método | Descripción |
|--------|-------------|
| `registrar()` | Registra un escaneo vía GET y redirige a feedback. |
| `feedback()` | Muestra información de la ronda y objetivo escaneado. |

---

### ✔ registrar()

Flujo:

1. Recibe parámetros vía GET:  
   - `ronda_id`  
   - `sector_id`  
   - `vigilador_id`  

2. Valida que existan  
3. Inserta el escaneo en la tabla `escaneos`  
4. Guarda en sesión los IDs para feedback  
5. Redirige a `?r=escaneo_feedback`  

Si falla:

- Muestra Toastify de error  
- No detiene el sistema  

---

### ✔ feedback()

- Recupera `ronda_id` y `sector_id` desde sesión  
- Consulta la ronda y su objetivo  
- Muestra la vista:

```
vistas/paginas/rondas/feedback.php
```

---

## 4. Modelos involucrados

### **ModeloEscaneos**

| Método | Descripción |
|--------|-------------|
| `mdlGuardarEscaneo($tabla, $datos)` | Inserta un escaneo y devuelve `'ok'` o mensaje de error. |

---

## 5. Tablas de base de datos

### Tabla: `escaneos`

```sql
CREATE TABLE `escaneos` (
  `idEscaneo` int NOT NULL AUTO_INCREMENT,
  `ronda_id` int DEFAULT NULL,
  `sector_id` int DEFAULT NULL,
  `vigilador_id` int DEFAULT NULL,
  `fecha_hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idEscaneo`),
  KEY `ronda_id` (`ronda_id`),
  KEY `sector_id` (`sector_id`),
  KEY `vigilador_id` (`vigilador_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
```

### Campos

| Campo | Tipo | Descripción |
|--------|------|-------------|
| idEscaneo | int | Identificador único |
| ronda_id | int | Relación con rondas.idRonda |
| sector_id | int | Sector escaneado |
| vigilador_id | int | Usuario que escaneó |
| fecha_hora | timestamp | Fecha y hora del escaneo |

### Relaciones

| Campo | Relación |
|--------|----------|
| ronda_id | rondas.idRonda |
| sector_id | sectores.idSector (si existe) |
| vigilador_id | usuarios.idUsuario |

---

## 6. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| feedback.php | `vistas/paginas/rondas/feedback.php` | Muestra datos del escaneo recién registrado |

---

## 7. Flujo de trabajo del módulo

### 1. El vigilador escanea un QR
El QR contiene:

```
?r=registrar_escaneo&ronda_id=X&sector_id=Y&vigilador_id=Z
```

### 2. El sistema registra el escaneo
- Inserta en BD  
- Guarda IDs en sesión  
- Redirige a feedback  

### 3. Se muestra feedback
- Nombre del objetivo  
- Nombre del puesto  
- Sector escaneado  
- Fecha y hora  

---

## 8. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| Parámetros obligatorios | ronda_id, sector_id, vigilador_id |
| Método | GET |
| Seguridad | No requiere login (depende del QR) |
| Feedback | Siempre redirige a `escaneo_feedback` |
| Integridad | Si falta un parámetro → error 400 |

---

## 9. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| “Parámetros incompletos” | Falta ronda_id, sector_id o vigilador_id | Revisar QR |
| No registra escaneo | Error SQL | Revisar permisos de BD |
| Feedback vacío | No se guardaron IDs en sesión | Revisar redirección |
| Objetivo desconocido | Ronda no encontrada | Revisar tabla rondas |

---

## 10. Mejoras futuras sugeridas

- Registrar ubicación GPS del escaneo  
- Registrar batería del dispositivo  
- Registrar latencia o calidad del QR  
- Integrar alertas por escaneos fuera de horario  
- Dashboard de rondas en tiempo real  


