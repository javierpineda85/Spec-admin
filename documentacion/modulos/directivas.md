# # Módulo: DIRECTIVAS

## 1. Descripción general

El módulo **Directivas** permite crear, editar, listar y eliminar directivas operativas asociadas a los objetivos.  
Una directiva puede incluir:

- Texto detallado  
- Un archivo adjunto (PDF, imagen, documento)  
- Un tipo: **general**, **particular** o **eventual**  
- Un objetivo asociado  

Además, cuando se crea una directiva, el sistema genera **alertas automáticas** para:

- Vigiladores  
- Referentes  
- Supervisores  

Este módulo es utilizado por:

- Supervisores  
- Administrativos  
- Programadores (gestión avanzada)  

---

## 2. Rutas del módulo

| Ruta | Método | Descripción |
|------|--------|-------------|
| `vistaCrearDirectiva` | GET | Muestra formulario para crear una directiva. |
| `vistaEditarDirectiva` | GET | Muestra formulario para editar una directiva. |
| `listado_directivas` | GET | Lista todas las directivas (filtradas por rol). |
| `crtGuardarDirectiva` | POST | Guarda una nueva directiva. |
| `crtModificarDirectiva` | POST | Modifica una directiva existente. |
| `crtEliminarDirectiva` | POST | Elimina una directiva (si no es general). |

---

## 3. Controladores involucrados

### **ControladorDirectivas**

| Método | Descripción |
|--------|-------------|
| `crtGuardarDirectiva()` | Guarda una directiva nueva con adjunto opcional. |
| `crtModificarDirectiva()` | Edita una directiva existente. |
| `crtEliminarDirectiva()` | Elimina una directiva (excepto las de tipo general). |
| `vistaListadoDirectivas()` | Lista directivas según rol del usuario. |
| `vistaCrearDirectiva()` | Renderiza formulario de creación. |
| `vistaEditarDirectiva()` | Renderiza formulario de edición. |

---

### ✔ crtGuardarDirectiva()

Flujo:

1. Valida permisos  
2. Inicia transacción  
3. Procesa archivo adjunto (si existe)  
4. Inserta directiva en BD  
5. Genera alertas automáticas para todos los usuarios activos de categorías:  
   - operativo  
   - referente  
   - supervisor  
6. Confirma transacción  
7. Muestra Toastify de éxito  

---

### ✔ crtModificarDirectiva()

- Valida permisos  
- Procesa nuevo adjunto (si existe)  
- Elimina adjunto anterior si fue reemplazado  
- Actualiza la directiva  
- Redirige a listado  

---

### ✔ crtEliminarDirectiva()

- Valida permisos  
- Verifica si la directiva es **general**  
  - Si lo es → **no se puede eliminar**  
- Si no, la elimina  
- Muestra Toastify  

---

### ✔ vistaListadoDirectivas()

- Si el usuario es **Vigilador**, solo ve directivas de su objetivo  
- Otros roles ven todas las directivas  
- Incluye JOIN con objetivos para mostrar nombre del objetivo  

---

## 4. Modelos involucrados

### **ModeloDirectivas**

| Método | Descripción |
|--------|-------------|
| `mdlGuardarDirectiva()` | Inserta una nueva directiva. |
| `mdlModificarDirectiva()` | Actualiza una directiva existente. |
| `mdlEliminarDirectiva()` | Elimina una directiva (si no es general). |
| `mdlObtenerTodas()` | Devuelve todas las directivas con nombre de objetivo. |

---

## 5. Tablas de base de datos

### Tabla: `directivas`

```sql
CREATE TABLE `directivas` (
  `idDirectiva` int NOT NULL AUTO_INCREMENT,
  `detalle` text NOT NULL,
  `adjunto` varchar(255) DEFAULT NULL,
  `tipo` enum('general','particular','eventual') NOT NULL DEFAULT 'particular',
  `id_objetivo` int NOT NULL,
  PRIMARY KEY (`idDirectiva`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
```

### Campos

| Campo | Tipo | Descripción |
|--------|------|-------------|
| idDirectiva | int | Identificador único |
| detalle | text | Texto de la directiva |
| adjunto | varchar(255) | Ruta del archivo adjunto |
| tipo | enum | general / particular / eventual |
| id_objetivo | int | Relación con objetivos.idObjetivo |

### Relaciones

| Campo | Relación |
|--------|----------|
| id_objetivo | objetivos.idObjetivo |

---

## 6. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| crear_directivas.php | `vistas/paginas/directivas/` | Formulario de creación |
| modificar_directivas.php | `vistas/paginas/directivas/` | Formulario de edición |
| listado_directivas.php | `vistas/paginas/directivas/` | Listado general |

---

## 7. Flujo de trabajo del módulo

### 1. Crear directiva
- Usuario accede a `vistaCrearDirectiva`
- Completa detalle, tipo, objetivo y adjunto
- Envía POST a `crtGuardarDirectiva`
- Se guarda en BD
- Se generan alertas automáticas
- Se confirma transacción

---

### 2. Editar directiva
- Usuario accede a `vistaEditarDirectiva&id=X`
- Puede reemplazar adjunto
- Se envía POST a `crtModificarDirectiva`
- Se actualiza en BD

---

### 3. Eliminar directiva
- Usuario envía POST a `crtEliminarDirectiva`
- Si la directiva es **general**, no se elimina
- Si es particular/eventual, se elimina

---

### 4. Listado de directivas
- Si el usuario es vigilador → solo ve directivas de su objetivo  
- Otros roles → ven todas  

---

## 8. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| Tipo general | No se puede eliminar |
| Adjuntos | Se guardan en `img/directivas/` |
| Permisos | Todos los métodos requieren `Auth::check()` |
| Transacciones | Crear, editar y eliminar usan transacciones |
| Alertas | Se generan alertas automáticas al crear una directiva |

---

## 9. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| No se guarda directiva | Error en adjunto o BD | Revisar permisos de carpeta y datos |
| No se elimina | Directiva es general | Cambiar tipo o no eliminar |
| No se ve listado | Usuario vigilador sin objetivo asignado | Asignar objetivo en usuarios |
| Archivo no se carga | Ruta incorrecta | Revisar ControladorArchivos |

---

## 10. Mejoras futuras sugeridas

- Historial de versiones de directivas  
- Notificaciones por email o WhatsApp  
- Categorías adicionales  
- Buscador avanzado por objetivo, tipo o fecha  
- Adjuntar múltiples archivos  
- Auditoría de cambios  

---

