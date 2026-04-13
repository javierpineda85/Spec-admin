# # Módulo: ROLES

## 1. Descripción general

El módulo **Roles** administra:

- La creación de roles del sistema  
- La edición de roles existentes  
- La activación/desactivación de roles  
- La asignación de permisos a roles  
- La protección de roles reservados (como Programador)  
- La jerarquía del sistema mediante niveles  

Este módulo es fundamental para:

- Seguridad  
- Control de acceso  
- Organización operativa  
- Integración con permisos  
- Integración con usuarios  

---

# 2. Rutas del módulo

Estas rutas deben estar en:

```
/rutas/rutas_roles.php
```

### ✔ Rutas recomendadas (completas)

```php
<?php

// Listado de roles
if (isset($_GET['r']) && $_GET['r'] === 'roles/listado') {
    RolesController::vistaListadoRoles();
    return;
}

// Crear rol
if (isset($_GET['r']) && $_GET['r'] === 'roles/crear') {
    RolesController::vistaCrearRol();
    return;
}

// Guardar rol
if (isset($_GET['r']) && $_GET['r'] === 'roles/guardar') {
    RolesController::ctrGuardarRol();
    return;
}

// Editar rol
if (isset($_GET['r']) && $_GET['r'] === 'roles/editar') {
    RolesController::vistaEditarRol();
    return;
}

// Actualizar rol
if (isset($_GET['r']) && $_GET['r'] === 'roles/actualizar') {
    RolesController::ctrActualizarRol();
    return;
}

// Desactivar rol
if (isset($_GET['r']) && $_GET['r'] === 'roles/desactivar') {
    RolesController::ctrDesactivarRol();
    return;
}

// Permisos del rol
if (isset($_GET['r']) && $_GET['r'] === 'roles/permisos') {
    RolesController::vistaPermisosRol();
    return;
}

// Guardar permisos del rol
if (isset($_GET['r']) && $_GET['r'] === 'roles/permisos/guardar') {
    RolesController::ctrGuardarPermisosRol();
    return;
}
```

---

# 3. Controladores involucrados

### **RolesController**

| Método | Descripción |
|--------|-------------|
| `vistaListadoRoles()` | Lista todos los roles. |
| `vistaCrearRol()` | Formulario de creación. |
| `ctrGuardarRol()` | Crea un rol nuevo. |
| `vistaEditarRol()` | Formulario de edición. |
| `ctrActualizarRol()` | Actualiza un rol existente. |
| `ctrDesactivarRol()` | Desactiva un rol. |
| `vistaPermisosRol()` | Muestra permisos asignados al rol. |
| `ctrGuardarPermisosRol()` | Guarda permisos del rol. |

---

# 4. Modelo involucrado

### **ModeloRoles**

| Método | Descripción |
|--------|-------------|
| `listar()` | Lista roles (activos o todos). |
| `obtenerPorId()` | Obtiene un rol por ID. |
| `obtenerPorNombre()` | Obtiene un rol por nombre. |
| `mdlObtenerRolesActivos()` | Lista roles activos excepto Programador. |
| `crear()` | Inserta un rol nuevo. |
| `actualizar()` | Actualiza un rol existente. |
| `desactivar()` | Soft-delete del rol. |
| `asignarPermisos()` | Asigna permisos a un rol. |

---

# 5. Jerarquía y categorías

Cada rol tiene:

- **nombre**
- **alias**
- **tipo** (fijo / temporal)
- **nivel** (jerarquía)
- **categoria** (operativo, supervisor, dirección, etc.)
- **reservado** (1 = protegido)
- **activo** (1 = visible)

### ✔ Mapeo automático de niveles

| Categoría | Nivel |
|-----------|--------|
| operativo | 1 |
| referente | 2 |
| baseOperativa | 2 |
| supervisor | 3 |
| administrativo | 4 |
| direccion | 5 |
| reservado | 99 |

---

# 6. Roles reservados

El sistema protege roles especiales:

### ✔ Programador
- No puede ser creado por usuarios normales  
- No puede ser editado  
- No puede ser desactivado  
- Solo un usuario con:  
  - `nivel = 99`  
  - `reservado = 1`  
  puede modificarlo  

### ✔ Otros roles reservados
- No pueden ser editados por usuarios sin privilegios  
- No pueden ser desactivados  

---

# 7. Submódulo: Crear rol

### ✔ ctrGuardarRol()

Flujo:

1. Valida permisos  
2. Recibe nombre, alias, tipo, categoría  
3. Valida campos obligatorios  
4. Determina nivel según categoría  
5. Valida si intenta crear un rol reservado sin permisos  
6. Inserta rol  
7. Redirige con Toastify  

---

# 8. Submódulo: Editar rol

### ✔ ctrActualizarRol()

Flujo:

1. Valida permisos  
2. Recibe datos del formulario  
3. Valida campos obligatorios  
4. Valida rol reservado  
5. Actualiza rol  
6. Redirige con Toastify  

---

# 9. Submódulo: Desactivar rol

### ✔ ctrDesactivarRol()

Reglas:

- No se puede desactivar un rol reservado  
- No se puede desactivar Programador  
- Soft-delete: `activo = 0`  

---

# 10. Submódulo: Permisos del rol

### ✔ vistaPermisosRol()

Carga:

- Lista de roles  
- Permisos visibles  
- Permisos asignados al rol  
- Datos del rol seleccionado  

Renderiza:

```
vistas/paginas/roles/gestionar_roles.php
```

---

### ✔ ctrGuardarPermisosRol()

Flujo:

1. Valida permisos  
2. Obtiene rol por nombre  
3. Obtiene ID del rol  
4. Elimina permisos actuales  
5. Inserta nuevos permisos  
6. Limpia permisos en sesión  
7. Redirige  

---

# 11. Tablas de base de datos

## Tabla: `roles`

```sql
id, nombre, alias, nivel, categoria, tipo, reservado, activo
```

## Tabla: `permissions`

```sql
id, controlador, accion, alias, descripcion, visible
```

## Tabla: `role_permissions`

```sql
role_id, permission_id
```

---

# 12. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| listado_roles.php | `vistas/paginas/roles/` | Lista de roles |
| crear_rol.php | `vistas/paginas/roles/` | Formulario |
| editar_rol.php | `vistas/paginas/roles/` | Edición |
| gestionar_roles.php | `vistas/paginas/roles/` | Permisos del rol |

---

# 13. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| Nombre obligatorio | Siempre requerido |
| Categoría obligatoria | Determina nivel |
| Rol Programador | Solo editable por superusuario |
| Roles reservados | No se pueden desactivar |
| Permisos | Solo visibles = 1 |
| Soft-delete | No se eliminan roles |

---

# 14. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| “Rol reservado no puede editarse” | Intento de editar Programador | Revisar permisos |
| “No tienes permiso para crear roles reservados” | Usuario sin nivel 99 | Revisar sesión |
| No aparecen roles | Tabla vacía | Revisar migración |
| Permisos no se guardan | POST vacío | Revisar name="permission_ids[]" |

---

# 15. Mejoras futuras sugeridas

- Permisos heredados por categoría  
- Roles clonables  
- Auditoría de cambios de roles  
- Roles temporales con expiración  
- Integración con Active Directory  

---

