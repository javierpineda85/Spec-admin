# # Módulo: PUESTOS

## 1. Descripción general

El módulo **Puestos** administra toda la estructura operativa dentro de cada objetivo:

- Puestos (fijos, eventuales, rotativos)  
- Turnos teóricos del puesto  
- Rotaciones diarias (quién ocupa qué puesto en qué turno)  
- Auditoría completa de cambios (log)  
- APIs AJAX para edición dinámica  

Este módulo es crítico para:

- Cronogramas  
- Marcaciones (validación de horarios)  
- Novedades (entradas/salidas)  
- Asignaciones del día  
- Reportes operativos  
- Hombre Vivo  

Es uno de los módulos más complejos y transversales del sistema.

---

# 2. Rutas del módulo

Estas rutas deben estar en:

```
/rutas/rutas_puestos.php
```

### ✔ Rutas recomendadas (completas)

```php
<?php

// Listado de puestos activos
if (isset($_GET['r']) && $_GET['r'] === 'listado_puestos') {
    ControladorPuestos::vistaListadoPuestos();
    return;
}

// Listado de puestos desactivados
if (isset($_GET['r']) && $_GET['r'] === 'listado_puestos_desactivados') {
    ControladorPuestos::vistaListadoPuestosDesactivados();
    return;
}

// Crear puesto
if (isset($_GET['r']) && $_GET['r'] === 'crear_puesto') {
    ControladorPuestos::vistaCrearPuestos();
    return;
}

// Guardar puesto
if (isset($_GET['r']) && $_GET['r'] === 'guardar_puesto') {
    ControladorPuestos::ctrGuardarPuesto();
    return;
}

// Editar puesto
if (isset($_GET['r']) && $_GET['r'] === 'editar_puesto') {
    ControladorPuestos::vistaEditarPuesto();
    return;
}

// Modificar puesto
if (isset($_GET['r']) && $_GET['r'] === 'modificar_puesto') {
    ControladorPuestos::crtModificarPuesto();
    return;
}

// Desactivar puesto
if (isset($_GET['r']) && $_GET['r'] === 'desactivar_puesto') {
    ControladorPuestos::crtDesactivarPuesto();
    return;
}

// Reactivar puesto
if (isset($_GET['r']) && $_GET['r'] === 'reactivar_puesto') {
    ControladorPuestos::crtReactivarPuesto();
    return;
}

// Vista de rotaciones
if (isset($_GET['r']) && $_GET['r'] === 'rotaciones') {
    ControladorPuestos::vistaRotaciones();
    return;
}

// API: guardar rotación
if (isset($_GET['r']) && $_GET['r'] === 'guardar_rotacion') {
    ControladorPuestos::crtGuardarRotacion();
    return;
}

// API: eliminar rotación
if (isset($_GET['r']) && $_GET['r'] === 'eliminar_rotacion') {
    ControladorPuestos::crtEliminarRotacion();
    return;
}

// API: swap de rotaciones
if (isset($_GET['r']) && $_GET['r'] === 'swap_rotacion') {
    ControladorPuestos::crtSwapRotacion();
    return;
}

// API: autollenado equitativo
if (isset($_GET['r']) && $_GET['r'] === 'auto_rotar') {
    ControladorPuestos::crtAutoRotarEquitativo();
    return;
}
```

---

# 3. Controladores involucrados

### **ControladorPuestos**

| Método | Descripción |
|--------|-------------|
| `ctrGuardarPuesto()` | Crea un puesto y sus turnos. |
| `crtModificarPuesto()` | Modifica puesto y turnos. |
| `crtDesactivarPuesto()` | Soft-delete. |
| `crtReactivarPuesto()` | Reactiva puesto. |
| `vistaListadoPuestos()` | Lista activos. |
| `vistaListadoPuestosDesactivados()` | Lista inactivos. |
| `vistaCrearPuestos()` | Formulario de creación. |
| `vistaEditarPuesto()` | Formulario de edición. |
| `vistaRotaciones()` | Vista principal de rotaciones. |
| `crtGuardarRotacion()` | API: crear/actualizar rotación. |
| `crtEliminarRotacion()` | API: eliminar rotación. |
| `crtSwapRotacion()` | API: intercambio de vigiladores. |
| `crtAutoRotarEquitativo()` | API: autollenado round-robin. |

---

# 4. Submódulo: Puestos

## ✔ Crear puesto (`ctrGuardarPuesto`)

Flujo:

1. Valida permisos  
2. Inserta puesto  
3. Inserta turnos del puesto (si existen)  
4. Toastify + redirección  

Campos:

- puesto  
- objetivo_id  
- tipo  
- turnos (opcional)

---

## ✔ Modificar puesto (`crtModificarPuesto`)

Flujo:

1. Valida permisos  
2. Inicia transacción  
3. Actualiza puesto  
4. Elimina turnos anteriores  
5. Inserta turnos nuevos  
6. Commit  

---

## ✔ Desactivar / Reactivar

Soft-delete:

- activo = 0  
- activo = 1  

---

# 5. Submódulo: Turnos del puesto

### Tabla: `puestos_turnos`

Define horarios teóricos:

- numero_turno  
- hora_entrada  
- hora_salida  

Usado por:

- Novedades (cálculo de hora esperada)  
- Marcaciones (validación de horario)  
- Cronogramas  

---

# 6. Submódulo: Rotaciones

Este es el corazón del módulo.

## ✔ Vista principal (`vistaRotaciones`)

Carga:

- Objetivos activos  
- Puestos del objetivo  
- Vigiladores elegibles  
- Turnos del mes  
- Rotaciones del mes  
- Turnos por puesto  

Renderiza:

```
vistas/paginas/puestos/asignar_puestos.php
```

---

## ✔ Guardar rotación (`crtGuardarRotacion`)

Flujo:

1. Valida permisos  
2. Valida que el vigilador tenga turno ese día  
3. Valida unicidad:
   - Un puesto no puede tener dos usuarios en el mismo turno  
   - Un usuario no puede estar en dos puestos en el mismo turno  
4. Inserta o actualiza  
5. Registra log  
6. Devuelve JSON  

---

## ✔ Eliminar rotación (`crtEliminarRotacion`)

- Elimina la fila  
- Registra log con acción `delete`  
- Devuelve JSON  

---

## ✔ Swap de rotaciones (`crtSwapRotacion`)

Permite intercambiar dos vigiladores:

- En un rango de fechas  
- En un turno  
- En un puesto específico o en todos  

Registra log con acción `swap`.

---

## ✔ Autollenado equitativo (`crtAutoRotarEquitativo`)

Algoritmo round-robin:

1. Obtiene puestos rotativos  
2. Obtiene vigiladores elegibles  
3. Obtiene turnos del mes  
4. Obtiene rotaciones existentes  
5. Para cada día:
   - Para cada puesto:
     - Asigna al siguiente vigilador disponible  
     - Respeta unicidad  
     - Registra log con acción `autofill`  

Devuelve:

```json
{ "ok": true, "msg": "Auto-rotación completada", "count": X }
```

---

# 7. Submódulo: Log de rotaciones

### Tabla: `rotaciones_log`

Registra:

- rotacion_id  
- objetivo_id  
- fecha  
- puesto_id  
- usuario_anterior  
- usuario_nuevo  
- codigo_turno  
- usuario_editor  
- accion  
- motivo  

Acciones:

- create  
- update  
- delete  
- swap  
- autofill  

---

# 8. Modelos involucrados

### **ModeloPuestos**

| Método | Descripción |
|--------|-------------|
| `mdlGuardarPuesto()` | Inserta puesto. |
| `mdlModificarPuesto()` | Actualiza puesto. |
| `mdlDesactivarPuesto()` | Soft-delete. |
| `mdlReactivarPuesto()` | Reactivar. |
| `mdlObtenerPuestosPorObjetivo()` | Lista puestos activos. |
| `mdlObtenerVigiladoresElegibles()` | Vigiladores del objetivo. |
| `mdlObtenerTurnosMesObjetivo()` | Turnos del mes. |
| `mdlObtenerRotacionesMes()` | Rotaciones del mes. |
| `mdlObtenerTurnosPorPuesto()` | Turnos teóricos del puesto. |
| `mdlGuardarRotacion()` | Inserta/actualiza rotación. |
| `mdlEliminarRotacion()` | Elimina rotación. |
| `mdlSwapRotaciones()` | Intercambio masivo. |
| `mdlAutoRotarEquitativo()` | Autollenado round-robin. |
| `mdlLogRotacion()` | Inserta log. |

---

# 9. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| listado_puestos.php | `vistas/paginas/puestos/` | Lista de puestos |
| listado_puestos_desactivados.php | `vistas/paginas/puestos/` | Inactivos |
| crear_puesto.php | `vistas/paginas/puestos/` | Formulario |
| editar_puesto.php | `vistas/paginas/puestos/` | Edición |
| asignar_puestos.php | `vistas/paginas/puestos/` | Gestión de rotaciones |

---

# 10. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| Unicidad por turno | Un puesto no puede tener dos usuarios |
| Unicidad por usuario | Un usuario no puede estar en dos puestos |
| Turnos válidos | Solo D/N |
| Turnos teóricos | Deben existir para análisis horario |
| Auditoría | Todo cambio queda registrado |
| Soft-delete | Puestos no se eliminan |

---

# 11. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| “El vigilador no tiene turno asignado” | Turno inexistente | Revisar módulo Turnos |
| “Usuario ya asignado” | Violación de unicidad | Cambiar puesto o turno |
| “Conflicto de unicidad en swap” | Intercambio inválido | Revisar rango |
| No aparecen turnos | No se cargaron turnos del puesto | Crear turnos |
| Auto-rotación no asigna | No hay vigiladores elegibles | Revisar objetivo_vigiladores |

---

# 12. Mejoras futuras sugeridas

- Editor visual de turnos por puesto  
- Drag & drop para rotaciones  
- Vista semanal/mensual tipo calendario  
- IA para sugerir asignaciones óptimas  
- Exportación a Excel/PDF  
- Integración con alertas automáticas  

---

