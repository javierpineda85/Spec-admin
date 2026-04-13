# # Módulo: FERIADOS

## 1. Descripción general

El módulo **Feriados** permite administrar todos los feriados del calendario que afectan:

- La visualización del cronograma  
- El cálculo de horas  
- La identificación de días especiales en la UI  
- La planificación operativa  

Este módulo permite:

- Crear feriados  
- Editar feriados  
- Eliminar feriados  
- Listarlos en una vista administrativa  

Los feriados se utilizan en otros módulos, especialmente en **Cronogramas**, donde se pintan en la tabla mensual y afectan la visualización de días especiales.

Este módulo es utilizado por:

- Administrativos  
- Supervisores  
- Programadores (gestión avanzada)  

---

## 2. Rutas del módulo

| Ruta | Método | Descripción |
|------|--------|-------------|
| `crear_feriados` | GET | Muestra formulario para crear feriados. |
| `listado_feriados` | GET | Lista todos los feriados. |
| `editar_feriado` | GET | Muestra formulario para editar un feriado. |
| `ctrGuardarFeriados` | POST | Guarda uno o varios feriados. |
| `ctrEditarFeriado` | POST | Actualiza un feriado existente. |
| `eliminar_feriado` | GET | Elimina un feriado por ID. |

Estas rutas están definidas en `rutas_feriados.php`.

---

## 3. Controladores involucrados

### **FeriadosController**

| Método | Descripción |
|--------|-------------|
| `ctrGuardarFeriados()` | Guarda múltiples feriados en una sola operación. |
| `ctrEditarFeriado()` | Edita un feriado existente. |
| `ctrEliminarFeriado()` | Elimina un feriado por ID. |
| `vistaCrearFeriados()` | Renderiza formulario de creación. |
| `vistaListadoFeriados()` | Renderiza listado de feriados. |
| `vistaEditarFeriado()` | Renderiza formulario de edición. |

---

### ✔ ctrGuardarFeriados()

Flujo:

1. Valida permisos  
2. Verifica que `$_POST['feriados']` sea un array  
3. Inicia transacción  
4. Inserta cada feriado usando el modelo  
5. Confirma transacción  
6. Redirige a listado  

---

### ✔ ctrEditarFeriado()

- Valida permisos  
- Recibe ID y datos del formulario  
- Actualiza el registro  
- Redirige a listado  

---

### ✔ ctrEliminarFeriado()

- Valida permisos  
- Recibe ID por GET  
- Elimina el feriado  
- Redirige a listado  

---

### ✔ Vistas

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| crear_feriados.php | `vistas/paginas/admin/feriados/` | Formulario para crear feriados |
| listado_feriados.php | `vistas/paginas/admin/feriados/` | Listado general |
| editar_feriado.php | `vistas/paginas/admin/feriados/` | Formulario de edición |

---

## 4. Modelos involucrados

### **ModeloFeriados**

| Método | Descripción |
|--------|-------------|
| `mdlGuardarFeriado()` | Inserta un feriado. |
| `mdlActualizarFeriado()` | Actualiza un feriado existente. |
| `mdlEliminarFeriado()` | Elimina un feriado por ID. |
| `mdlObtenerFeriado()` | Obtiene un feriado por ID. |

---

## 5. Tablas de base de datos

### Tabla: `feriados`

```sql
CREATE TABLE `feriados` (
  `idFeriado` int NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `motivo` varchar(70) NOT NULL,
  `tipo_feriado` varchar(50) NOT NULL,
  PRIMARY KEY (`idFeriado`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
```

### Campos

| Campo | Tipo | Descripción |
|--------|------|-------------|
| idFeriado | int | Identificador único |
| fecha | date | Fecha del feriado |
| motivo | varchar(70) | Descripción del feriado |
| tipo_feriado | varchar(50) | Tipo (nacional, provincial, puente, etc.) |

### Relaciones

Esta tabla **no tiene relaciones directas** con otras tablas.  
Sin embargo, es consumida por:

- **cronograma.js** para pintar días especiales  
- **CronogramasController** para cálculos visuales  

---

## 6. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| crear_feriados.php | `vistas/paginas/admin/feriados/` | Formulario de creación |
| listado_feriados.php | `vistas/paginas/admin/feriados/` | Listado general |
| editar_feriado.php | `vistas/paginas/admin/feriados/` | Formulario de edición |

---

## 7. Flujo de trabajo del módulo

### 1. Crear feriados
- Usuario accede a `crear_feriados`
- Completa uno o varios feriados
- Envía POST a `ctrGuardarFeriados`
- Se guardan todos en una transacción

---

### 2. Editar feriado
- Usuario accede a `editar_feriado&id=X`
- Modifica fecha, motivo o tipo
- Envía POST a `ctrEditarFeriado`

---

### 3. Eliminar feriado
- Usuario hace clic en eliminar
- Se envía GET a `eliminar_feriado&id=X`
- Se elimina el registro

---

### 4. Listado
- Usuario accede a `listado_feriados`
- Se muestran todos los feriados ordenados por fecha

---

## 8. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| Fecha obligatoria | No se permite fecha vacía |
| Motivo obligatorio | Debe tener descripción |
| Tipo obligatorio | Debe tener tipo definido |
| Transacciones | Guardado múltiple usa transacción |
| Permisos | Todas las acciones requieren `Auth::check()` |

---

## 9. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| No se guardan feriados | POST vacío o mal formado | Revisar formulario |
| No se edita feriado | ID incorrecto | Verificar hidden input |
| No se elimina | ID inexistente | Revisar URL |
| Vista en blanco | Falta permiso | Revisar `Auth::check()` |

---

## 10. Mejoras futuras sugeridas

- Importar feriados desde API oficial  
- Clasificación automática por tipo  
- Feriados recurrentes (ej: 1 de enero)  
- Exportación a Excel  
- Integración con calendario de Google  

---

