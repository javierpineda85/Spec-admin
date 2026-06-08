# # Módulo: UNIFORMES

## 1. Descripción general

El módulo **Uniformes** gestiona todo el ciclo de vida del uniforme entregado a cada empleado:

- Registro de **talles personales**  
- Administración de **ítems de uniforme** (catálogo)  
- Registro de **entregas** (con comprobante imprimible)  
- Registro de **devoluciones**  
- Gestión de **categorías**  
- Listado general para administración  
- Control de stock conceptual (a través de entregas y devoluciones)  

Este módulo es utilizado por:

- Vigiladores (para ver su uniforme y talles)  
- Supervisores  
- Administración  
- RRHH  

---

## 14. ActualizaciÃ³n reciente

- El listado general de uniformes sigue disponible en `?r=listado_uniformes`.
- El acceso se expone desde el panel de AdministraciÃ³n y desde accesos rÃ¡pidos del sistema.
- La vista conserva el detalle por talle y el resumen por prenda/talle.

# 2. Rutas del módulo

Estas rutas deben estar en:

```
/rutas/rutas_uniformes.php
```

### ✔ Rutas recomendadas

```php
<?php

// Vista principal del usuario
if (isset($_GET['r']) && $_GET['r'] === 'mi_uniforme') {
    UniformesController::vistaMiUniforme();
    return;
}

// Guardar talles
if (isset($_GET['r']) && $_GET['r'] === 'guardar_uniforme') {
    UniformesController::guardarUniforme();
    return;
}

// Registrar entrega múltiple
if (isset($_GET['r']) && $_GET['r'] === 'registrar_entrega_uniforme_multiple') {
    UniformesController::registrarEntregaMultiple();
    return;
}

// Registrar devolución
if (isset($_GET['r']) && $_GET['r'] === 'registrar_devolucion_uniforme') {
    UniformesController::registrarDevolucion();
    return;
}

// Administración de ítems
if (isset($_GET['r']) && $_GET['r'] === 'admin_items_uniforme') {
    UniformesController::adminItems();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'guardar_item_uniforme') {
    UniformesController::guardarItem();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'cambiar_estado_item_uniforme') {
    UniformesController::cambiarEstadoItem();
    return;
}

// Listado general
if (isset($_GET['r']) && $_GET['r'] === 'listado_uniformes') {
    UniformesController::vistaListadoUniformes();
    return;
}

// Comprobante de entrega
if (isset($_GET['r']) && $_GET['r'] === 'comprobante_entrega_uniforme') {
    UniformesController::comprobanteEntrega();
    return;
}
```

---

# 3. Controladores involucrados

### **UniformesController**

| Método | Descripción |
|--------|-------------|
| `vistaMiUniforme()` | Vista principal del usuario (talles + entregas). |
| `guardarUniforme()` | Guarda talles del usuario. |
| `registrarEntregaMultiple()` | Registra múltiples entregas en un solo envío. |
| `registrarDevolucion()` | Registra una devolución. |
| `adminItems()` | Vista administrativa del catálogo de ítems. |
| `guardarItem()` | Crea o edita un ítem. |
| `cambiarEstadoItem()` | Activa/desactiva un ítem. |
| `vistaListadoUniformes()` | Listado general de talles. |
| `comprobanteEntrega()` | Genera comprobante imprimible. |

---

# 4. Submódulo: Talles del usuario

## ✔ vistaMiUniforme()

Carga:

- Talles actuales (`uniformes`)  
- Ítems administrables (`uniforme_items`)  
- Categorías (`uniforme_categorias`)  
- Entregas del usuario (`uniforme_entregas`)  

Permite que:

- El usuario vea sus talles  
- Supervisores puedan ver talles de otros usuarios (según nivel)  
- Se registren entregas y devoluciones  

---

## ✔ guardarUniforme()

Flujo:

1. Valida permisos  
2. Recibe talles  
3. Si existe registro → actualiza  
4. Si no existe → inserta  
5. Toastify + redirección  

Campos:

- talle_pantalon  
- talle_remera  
- talle_polar  
- talle_campera  
- talle_calzado  

---

# 5. Submódulo: Entregas

## ✔ registrarEntregaMultiple()

Permite registrar **varias entregas en un solo envío**.

Cada entrega incluye:

- item_id (opcional)  
- item_libre (si no existe en catálogo)  
- categoria_id  
- talle  
- cantidad  
- observaciones  
- entregado_por  
- usuario_id  

Flujo:

1. Valida permisos  
2. Recorre `$_POST['entregas']`  
3. Inserta cada entrega  
4. Guarda IDs insertados  
5. Redirige al comprobante:

```
?r=comprobante_entrega_uniforme&ids=1,2,3
```

---

# 6. Submódulo: Devoluciones

## ✔ registrarDevolucion()

Campos:

- entrega_id  
- fecha_devolucion  
- estado_devolucion (bueno / regular / malo / inservible)  
- recibido_por  
- observaciones  

Inserta en:

```
uniforme_devoluciones
```

---

# 7. Submódulo: Catálogo de ítems

## ✔ adminItems()

Carga:

- Lista de ítems (`uniforme_items`)  
- Categorías (`uniforme_categorias`)  

Renderiza:

```
vistas/paginas/admin/uniformes/items.php
```

---

## ✔ guardarItem()

Permite:

- Crear ítem  
- Editar ítem  

Campos:

- categoria_id  
- nombre  
- descripcion  
- estado (activo/inactivo)  

---

## ✔ cambiarEstadoItem()

Activa o desactiva un ítem.

---

# 8. Submódulo: Listado general

## ✔ vistaListadoUniformes()

Consulta:

```sql
SELECT 
    u.idUsuario,
    CONCAT(u.apellido, ', ', u.nombre) AS nombre_completo,
    uni.talle_pantalon,
    uni.talle_calzado,
    uni.talle_remera,
    uni.talle_polar,
    uni.talle_campera
FROM uniformes uni
INNER JOIN usuarios u ON u.idUsuario = uni.usuario_id
ORDER BY nombre_completo ASC
```

Renderiza:

```
vistas/paginas/admin/uniformes/listado_uniformes.php
```

---

# 9. Submódulo: Comprobante de entrega

## ✔ comprobanteEntrega()

Flujo:

1. Recibe lista de IDs  
2. Busca cada entrega  
3. Obtiene datos del usuario  
4. Renderiza:

```
vistas/paginas/datos/comprobante_uniforme.php
```

---

# 10. Modelos involucrados

### **ModeloUniformes**

| Método | Descripción |
|--------|-------------|
| `buscarPorUsuario()` | Devuelve talles del usuario. |
| `insertar()` | Inserta talles. |
| `actualizar()` | Actualiza talles. |
| `mdlListarUniformes()` | Listado general. |

---

### **ModeloUniformeItems**

| Método | Descripción |
|--------|-------------|
| `listar()` | Lista ítems (activos o todos). |
| `insertar()` | Crea ítem. |
| `actualizar()` | Edita ítem. |
| `cambiarEstado()` | Activa/desactiva ítem. |

---

### **ModeloUniformeEntregas**

| Método | Descripción |
|--------|-------------|
| `insertar()` | Inserta entrega. |
| `listarPorUsuario()` | Entregas del usuario. |
| `buscar()` | Busca entrega por ID. |

---

### **ModeloUniformeDevoluciones**

| Método | Descripción |
|--------|-------------|
| `insertar()` | Inserta devolución. |

---

# 11. Tablas de base de datos

## 🟦 Tabla: `uniformes` (talles)

```sql
usuario_id, talle_pantalon, talle_remera, talle_polar, talle_campera, talle_calzado
```

---

## 🟦 Tabla: `uniforme_items` (catálogo)

```sql
id, categoria_id, nombre, descripcion, estado
```

---

## 🟦 Tabla: `uniforme_entregas`

```sql
usuario_id, item_id, item_libre, categoria_id, fecha_entrega, talle, cantidad, observaciones, entregado_por
```

---

## 🟦 Tabla: `uniforme_devoluciones`

```sql
entrega_id, fecha_devolucion, estado_devolucion, recibido_por, observaciones
```

---

## 🟦 Tabla: `uniforme_categorias`

```sql
id, nombre
```

---

# 12. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| mi_uniforme.php | `vistas/paginas/datos/` | Vista principal del usuario |
| items.php | `vistas/paginas/admin/uniformes/` | Catálogo de ítems |
| listado_uniformes.php | `vistas/paginas/admin/uniformes/` | Listado general |
| comprobante_uniforme.php | `vistas/paginas/datos/` | Comprobante imprimible |

---

# 13. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| Un registro de talles por usuario | Se actualiza si existe |
| Entregas múltiples | Se registran en lote |
| Devoluciones | Solo sobre entregas existentes |
| Catálogo | Ítems administrables por categoría |
| Permisos | Todos los métodos usan `Auth::check()` |
| Supervisores | Pueden ver uniformes de otros usuarios |

---

# 14. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| “No se recibieron ítems para registrar” | POST vacío | Revisar formulario |
| No se generan comprobantes | IDs inválidos | Revisar URL |
| Ítems duplicados | Nombre único | Cambiar nombre |
| No aparecen categorías | Tabla vacía | Cargar categorías |

---

# 15. Mejoras futuras sugeridas

- Control de stock real  
- Adjuntar fotos del uniforme entregado  
- Historial completo por usuario  
- Exportación a PDF/Excel  
- Firma digital en comprobantes  
- Alertas por devoluciones pendientes  

---
