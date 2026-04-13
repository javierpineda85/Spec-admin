# # Módulo: CONFIGURACIÓN

## 1. Descripción general

El módulo **Configuración** administra los parámetros globales del sistema.  
Permite:

- Ver todas las configuraciones del sistema  
- Editar valores existentes  
- Crear nuevas claves de configuración  
- Mantener parámetros críticos sin modificar código  

Este módulo es utilizado exclusivamente por:

- **Rol Programador**  
- **Administradores de sistema autorizados**

El módulo controla valores como:

- Nombre del sistema  
- Datos de empresa  
- Parámetros operativos  
- Ajustes internos usados por otros módulos  

---

## 2. Rutas del módulo

| Ruta | Método | Descripción |
|------|--------|-------------|
| `configuracion/panel` | GET | Muestra el panel de configuración. |
| `configuracion/ctrGuardarConfig` | POST | Guarda o actualiza configuraciones. |

Estas rutas están definidas en `rutas_configuracion.php`.

---

## 3. Controladores involucrados

### **ConfigController**

| Método | Descripción |
|--------|-------------|
| `vistaPanel()` | Muestra el panel con todas las configuraciones. |
| `ctrGuardarConfig()` | Guarda o actualiza configuraciones enviadas por POST. |

---

### ✔ vistaPanel()

- Requiere permisos: `Auth::check('roles', 'vistaConfigSistema')`
- Obtiene todas las configuraciones desde el modelo
- Las transforma en un array asociativo `clave => valor`
- Renderiza la vista:

```
vistas/paginas/config/configuracion.php
```

---

### ✔ ctrGuardarConfig()

- Requiere permisos: `Auth::check('roles', 'ctrGuardarConfig')`
- Recorre todos los valores enviados por POST
- Guarda cada clave/valor usando el modelo
- Redirige nuevamente al panel con mensaje de éxito

---

## 4. Modelos involucrados

### **Configuracion**

| Método | Descripción |
|--------|-------------|
| `obtenerTodo()` | Devuelve todas las configuraciones (clave, valor). |
| `obtener($clave)` | Devuelve el valor de una clave específica. |
| `guardar($clave, $valor)` | Inserta o actualiza una configuración. |

---

## 5. Tablas de base de datos

### Tabla: `configuracion`

```sql
CREATE TABLE `configuracion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `clave` varchar(50) NOT NULL,
  `valor` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Campos

| Campo | Tipo | Descripción |
|--------|------|-------------|
| id | int | Identificador único |
| clave | varchar(50) | Nombre de la configuración |
| valor | varchar(255) | Valor asignado |

### Relaciones

Esta tabla **no tiene relaciones** con otras.  
Es una tabla de configuración global.

---

## 6. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| configuracion.php | `vistas/paginas/config/configuracion.php` | Panel de edición de configuraciones |

---

## 7. Flujo de trabajo del módulo

### 1. Acceso al panel
- Usuario con rol programador accede a `configuracion/panel`
- Se cargan todas las configuraciones existentes
- Se muestran en un formulario editable

### 2. Guardar cambios
- Usuario modifica valores
- Se envía POST a `configuracion/ctrGuardarConfig`
- El controlador recorre cada clave enviada
- Se guarda o actualiza en BD mediante `ON DUPLICATE KEY UPDATE`
- Se redirige nuevamente al panel

### 3. Uso por otros módulos
Otros módulos obtienen valores mediante:

```php
$configModel->obtener('clave');
```

---

## 8. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| Permisos | Solo rol programador puede acceder |
| Claves únicas | No se permiten claves duplicadas |
| Valores vacíos | No se guardan valores vacíos |
| Persistencia | Si la clave existe, se actualiza; si no, se crea |

---

## 9. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| No se guardan cambios | Valores vacíos en POST | Asegurar que los inputs tengan contenido |
| Vista en blanco | Falta permiso | Revisar `Auth::check()` |
| Configuración no encontrada | Clave inexistente | Crear la clave desde el panel |
| No se actualiza valor | Error en BD | Revisar índice UNIQUE en `clave` |

---

## 10. Mejoras futuras sugeridas

- Agregar categorías de configuración (ej: sistema, empresa, seguridad)
- Validación por tipo (string, número, booleano)
- Historial de cambios (auditoría)
- Exportar/importar configuraciones
- Cachear configuraciones para mejorar rendimiento

---

