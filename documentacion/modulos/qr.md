# # Módulo: QR / RONDAS

## 1. Descripción general

El módulo **QR / Rondas** permite:

- Crear rondas operativas dentro de un objetivo  
- Generar códigos QR para cada punto de escaneo  
- Mostrar QR en pantalla (PNG dinámico)  
- Registrar escaneos realizados por vigiladores  
- Eliminar rondas en estado *draft*  
- Guardar QR temporalmente en sesión  

Este módulo se integra directamente con:

- **Escaneos**  
- **Objetivos**  
- **Vigiladores**  
- **Rondas** (tabla propia)  

---

# 2. Rutas del módulo

Estas rutas deben estar en:

```
/rutas/rutas_qr.php
```

### ✔ Rutas recomendadas

```php
<?php

// Generar QR (crear ronda)
if (isset($_GET['r']) && $_GET['r'] === 'generar_qr') {
    QrController::generar();
    return;
}

// Mostrar QR en PNG
if (isset($_GET['r']) && $_GET['r'] === 'mostrar_qr') {
    QrController::mostrar();
    return;
}

// Eliminar QR en estado draft
if (isset($_GET['r']) && $_GET['r'] === 'eliminar_qr') {
    QrController::delete();
    return;
}
```

---

# 3. Controladores involucrados

### **QrController**

| Método | Descripción |
|--------|-------------|
| `generar()` | Crea una ronda y la guarda en sesión para generar QR. |
| `mostrar()` | Genera y devuelve un PNG con el QR. |
| `delete()` | Elimina una ronda en estado draft y la quita de sesión. |

---

# 4. Submódulo: Generación de QR

## ✔ generar()

Flujo:

1. Valida datos recibidos por POST:
   - puesto  
   - objetivo_id  
   - tipo  
   - orden  

2. Inserta una ronda en la tabla `rondas` con estado:

```
status = 'draft'
```

3. Obtiene el nombre del objetivo

4. Guarda en sesión:

```php
$_SESSION['qr_codes'][] = [
    'idRonda' => ...,
    'objetivo_id' => ...,
    'objetivo_nombre' => ...,
    'puesto' => ...,
    'orden' => ...
];
```

5. Redirige a la vista de creación de rondas

---

# 5. Submódulo: Mostrar QR

## ✔ mostrar()

Flujo:

1. Limpia buffers (`ob_end_clean()`)  
2. Recibe `ronda_id` por GET  
3. Construye la URL que irá dentro del QR:

```
https://HOST/index.php?r=registrar_escaneo&ronda_id=XX&sector_id=XX&vigilador_id=YY
```

4. Genera PNG usando:

```
QRcode::png()
```

5. Devuelve la imagen directamente al navegador

---

# 6. Submódulo: Eliminar QR (draft)

## ✔ delete()

Flujo:

1. Limpia buffers  
2. Recibe `key` (índice del array de sesión)  
3. Obtiene `idRonda`  
4. Elimina el elemento de `$_SESSION['qr_codes']`  
5. Elimina la ronda de la BD **solo si está en estado draft**  
6. Devuelve JSON:

```json
{ "success": true }
```

---

# 7. Modelo involucrado

### **QrModel**

| Método | Descripción |
|--------|-------------|
| `guardarQrEnSesion()` | Agrega un QR al array de sesión. |
| `guardarTodosEnBD()` | Inserta todos los QR generados en la tabla escaneos. |

---

# 8. Tablas de base de datos

## 🟦 Tabla: `escaneos`

```sql
CREATE TABLE `escaneos` (
  `idEscaneo` int NOT NULL AUTO_INCREMENT,
  `ronda_id` int DEFAULT NULL,
  `sector_id` int DEFAULT NULL,
  `vigilador_id` int DEFAULT NULL,
  `fecha_hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idEscaneo`)
);
```

### Campos

| Campo | Descripción |
|--------|-------------|
| ronda_id | Punto de ronda escaneado |
| sector_id | Sector asociado (en tu sistema coincide con ronda_id) |
| vigilador_id | Usuario que escaneó |
| fecha_hora | Timestamp automático |

---

# 9. Integración con otros módulos

### ✔ Con Rondas
- Cada QR corresponde a una ronda creada en estado *draft*  
- Luego se usa para registrar escaneos reales  

### ✔ Con Escaneos
- El QR genera una URL que dispara el registro en `escaneos`  
- El vigilador escanea → se registra fecha, hora y usuario  

### ✔ Con Objetivos
- Cada ronda pertenece a un objetivo  
- Se usa para recorridos internos  

---

# 10. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| crear_rondas.php | `vistas/paginas/rondas/` | Formulario para generar QR |
| listado_rondas.php | `vistas/paginas/rondas/` | Listado de rondas creadas |
| qr_preview.php | `vistas/paginas/rondas/` | Vista previa del QR |

---

# 11. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| Datos obligatorios | puesto, objetivo_id, tipo, orden |
| Rondas draft | Solo se eliminan si status = 'draft' |
| QR dinámico | No se guarda archivo, se genera al vuelo |
| Seguridad | Requiere permisos (Auth::check) |
| Sesión | QR se guarda temporalmente en `$_SESSION['qr_codes']` |

---

# 12. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| “Faltan datos para generar la ronda” | POST incompleto | Revisar formulario |
| QR no se muestra | Falta parámetro ronda_id | Revisar URL |
| No se elimina QR | No está en estado draft | Revisar BD |
| Imagen corrupta | Buffer no limpiado | `ob_end_clean()` |

---

# 13. Mejoras futuras sugeridas

- Descargar QR como PNG o PDF  
- Generar múltiples QR en lote  
- Exportar rondas completas  
- Integración con app móvil  
- Validación de ubicación GPS al escanear  
- Auditoría de rondas completas  

---

