# # Módulo: OBJETIVOS

## 1. Descripción general

El módulo **Objetivos** administra todos los lugares donde se presta servicio operativo.  
Cada objetivo contiene:

- Datos geográficos (latitud, longitud, radio de geocerca)  
- Localidad  
- Tipo (fijo, móvil, eventual)  
- Estado (activo / inactivo)  
- Vigiladores asignados  
- Referentes asignados  
- Base operativa asociada  

Este módulo es fundamental para:

- Marcaciones (geofence)  
- Cronogramas  
- Rondas  
- Asignaciones del día  
- Novedades  
- Hombre Vivo  
- Mensajes (reglas por objetivo)  

Es uno de los módulos centrales del sistema.

---

# 2. Rutas del módulo

Estas rutas deben estar en:

```
/rutas/rutas_objetivos.php
```

### ✔ Rutas recomendadas

```php
<?php

// Crear objetivo
if (isset($_GET['r']) && $_GET['r'] === 'crear_objetivo') {
    ControladorObjetivos::vistaCrearObjetivo();
    return;
}

// Guardar objetivo
if (isset($_GET['r']) && $_GET['r'] === 'guardar_objetivo') {
    ControladorObjetivos::crtGuardarObjetivo();
    return;
}

// Editar objetivo
if (isset($_GET['r']) && $_GET['r'] === 'editar_objetivo') {
    ControladorObjetivos::vistaEditarObjetivo();
    return;
}

// Modificar objetivo
if (isset($_GET['r']) && $_GET['r'] === 'modificar_objetivo') {
    ControladorObjetivos::crtModificarObjetivo();
    return;
}

// Listado de objetivos activos
if (isset($_GET['r']) && $_GET['r'] === 'listado_objetivos') {
    ControladorObjetivos::vistaListadoObjetivos();
    return;
}

// Listado de objetivos inactivos
if (isset($_GET['r']) && $_GET['r'] === 'listado_objetivos_inactivos') {
    ControladorObjetivos::vistaListadoObjetivosInactivos();
    return;
}

// Desactivar objetivo
if (isset($_GET['r']) && $_GET['r'] === 'desactivar_objetivo') {
    ControladorObjetivos::crtDesactivarObjetivo();
    return;
}

// Reactivar objetivo
if (isset($_GET['r']) && $_GET['r'] === 'reactivar_objetivo') {
    ControladorObjetivos::crtReactivarObjetivo();
    return;
}
```

---

# 3. Controladores involucrados

### **ControladorObjetivos**

| Método | Descripción |
|--------|-------------|
| `crtGuardarObjetivo()` | Crea un objetivo nuevo con asignaciones. |
| `crtModificarObjetivo()` | Modifica datos y relaciones del objetivo. |
| `crtDesactivarObjetivo()` | Soft-delete del objetivo. |
| `crtReactivarObjetivo()` | Reactiva objetivo desactivado. |
| `vistaListadoObjetivos()` | Lista objetivos activos. |
| `vistaListadoObjetivosInactivos()` | Lista objetivos inactivos. |
| `vistaCrearObjetivo()` | Formulario de creación. |
| `vistaEditarObjetivo()` | Formulario de edición. |

---

## ✔ crtGuardarObjetivo()

Flujo:

1. Valida permisos  
2. Inicia transacción  
3. Inserta objetivo principal  
4. Inserta relaciones:
   - Vigiladores  
   - Referentes  
   - Base operativa  
5. Commit  
6. Toastify de éxito  

Campos guardados:

- nombre  
- latitud  
- longitud  
- radio_m  
- localidad  
- tipo  
- activo = 1  

---

## ✔ crtModificarObjetivo()

Flujo:

1. Valida permisos  
2. Inicia transacción  
3. Actualiza datos del objetivo  
4. Compara relaciones actuales vs nuevas  
5. Si cambiaron:
   - Elimina relaciones  
   - Inserta nuevas  
6. Commit  
7. Toastify de éxito  

Relaciones gestionadas:

- objetivo_vigiladores  
- objetivo_referentes  
- objetivo_base_operativa  

---

## ✔ crtDesactivarObjetivo() / crtReactivarObjetivo()

Soft-delete:

- activo = 0 → desactivado  
- activo = 1 → reactivado  

---

## ✔ Listados

### vistaListadoObjetivos()

- Muestra solo objetivos activos  
- Ordenados por nombre  

### vistaListadoObjetivosInactivos()

- Muestra solo objetivos inactivos  

---

# 4. Modelos involucrados

### **ModeloObjetivos**

| Método | Descripción |
|--------|-------------|
| `mdlGuardarObjetivo()` | Inserta objetivo y devuelve ID. |
| `mdlModificarObjetivo()` | Actualiza datos del objetivo. |
| `mdlGuardarVigiladoresObjetivo()` | Inserta vigiladores asignados. |
| `mdlGuardarReferentesObjetivo()` | Inserta referentes asignados. |
| `mdlGuardarBaseOperativaObjetivo()` | Inserta base operativa. |
| `mdlEliminarVigiladoresObjetivo()` | Elimina asignaciones. |
| `mdlEliminarReferentesObjetivo()` | Elimina asignaciones. |
| `mdlEliminarBaseOperativaObjetivo()` | Elimina asignaciones. |
| `mdlObtenerVigiladoresPorObjetivo()` | IDs de vigiladores asignados. |
| `mdlObtenerReferentesPorObjetivo()` | IDs de referentes asignados. |
| `mdlObtenerBaseOperativaPorObjetivo()` | IDs de base operativa. |
| `mdlDesactivarObjetivo()` | Soft-delete. |
| `mdlReactivarObjetivo()` | Reactivar. |

---

# 5. Tablas de base de datos

### Tabla: `objetivos`

```sql
CREATE TABLE `objetivos` (
  `idObjetivo` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `latitud` decimal(10,8) NOT NULL,
  `longitud` decimal(11,8) NOT NULL,
  `radio_m` int NOT NULL DEFAULT '200',
  `localidad` varchar(100) NOT NULL,
  `tipo` enum('fijo','movil','eventual') NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`idObjetivo`)
);
```

---

### Tabla: `objetivo_vigiladores`

```sql
CREATE TABLE `objetivo_vigiladores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `objetivo_id` int NOT NULL,
  `vigilador_id` int NOT NULL,
  PRIMARY KEY (`id`)
);
```

---

### Tabla: `objetivo_referentes`

```sql
CREATE TABLE `objetivo_referentes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `objetivo_id` int NOT NULL,
  `referente_id` int NOT NULL,
  PRIMARY KEY (`id`)
);
```

---

### Tabla: `objetivo_base_operativa`

```sql
CREATE TABLE `objetivo_base_operativa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `objetivo_id` int NOT NULL,
  `base_id` int NOT NULL,
  PRIMARY KEY (`id`)
);
```

---

# 6. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| crear_objetivo.php | `vistas/paginas/objetivos/` | Formulario de creación |
| editar_objetivo.php | `vistas/paginas/objetivos/` | Formulario de edición |
| listado_objetivos.php | `vistas/paginas/objetivos/` | Objetivos activos |
| listado_objetivos_desactivados.php | `vistas/paginas/objetivos/` | Objetivos inactivos |

---

# 7. Flujo de trabajo del módulo

### 1. Crear objetivo
- Se completan datos geográficos y administrativos  
- Se asignan vigiladores, referentes y base operativa  
- Se guarda en BD  

### 2. Editar objetivo
- Se modifican datos  
- Se recalculan relaciones  

### 3. Desactivar objetivo
- No se elimina  
- Se oculta de listados operativos  

### 4. Reactivar objetivo
- Vuelve a estar disponible  

---

# 8. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| Nombre obligatorio | No puede estar vacío |
| Latitud/longitud | Requeridos para geofence |
| Radio | Define área permitida para marcaciones |
| Tipo | fijo / móvil / eventual |
| Soft-delete | No se eliminan objetivos |
| Relaciones | Se reescriben completamente en edición |

---

# 9. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| No se crea objetivo | Datos incompletos | Revisar formulario |
| No se asignan vigiladores | POST vacío | Revisar select múltiple |
| Marcaciones fuera de área | Radio incorrecto | Ajustar radio_m |
| No aparecen en cronogramas | Objetivo inactivo | Reactivar objetivo |

---

# 10. Mejoras futuras sugeridas

- Mapa interactivo para seleccionar ubicación  
- Historial de cambios del objetivo  
- Adjuntar documentación del sitio  
- Clasificación por cliente / contrato  
- Dashboard de actividad por objetivo  
- Integración con IA para sugerir dotación óptima  

---


