# # Módulo: PERMISOS

## 1. Descripción general

El módulo **Permisos** administra la relación entre:

- **Roles** (supervisor, administrativo, dirección, operativo, etc.)
- **Permisos** (controlador/acción)
- **Asignación de permisos a roles**

Este módulo permite:

- Ver todos los permisos del sistema  
- Ver todos los roles activos  
- Seleccionar un rol  
- Asignar o quitar permisos  
- Guardar cambios  
- Proteger roles especiales (como Programador)  

Es un módulo crítico para la seguridad del sistema, ya que determina qué puede hacer cada usuario.

---

# 2. Rutas del módulo

Estas rutas deben estar en:

```
/rutas/rutas_permisos.php
```

### ✔ Rutas recomendadas

```php
<?php

// Vista principal de gestión de permisos
if (isset($_GET['r']) && $_GET['r'] === 'permisos') {
    PermisosController::index();
    return;
}

// Guardar cambios de permisos
if (isset($_GET['r']) && $_GET['r'] === 'permisos_update') {
    PermisosController::update();
    return;
}
```

> En tu sistema, la vista final se llama:  
`vistas/paginas/roles/gestionar_roles.php`

---

# 3. Controladores involucrados

### **PermisosController**

| Método | Descripción |
|--------|-------------|
| `index()` | Muestra permisos, roles y permisos asignados. |
| `update()` | Guarda los permisos seleccionados para un rol. |

---

## ✔ index()

Flujo:

1. Valida permisos:  
   `Auth::check('permisos', 'index')`

2. Obtiene todos los permisos visibles:

```sql
SELECT id, controlador, accion, alias, descripcion
FROM permissions
WHERE visible = 1
ORDER BY controlador, accion
```

3. Obtiene todos los roles activos:

```sql
SELECT nombre AS rol, reservado FROM roles WHERE activo = 1
```

4. Determina el rol seleccionado:

```php
$selectedRole = $_GET['role'] ?? $roles[0]['rol'];
```

5. Obtiene datos del rol (nivel, reservado, etc.)

6. Obtiene permisos asignados al rol:

```sql
SELECT permission_id FROM role_permissions WHERE role = ?
```

7. Carga la vista:

```
vistas/paginas/roles/gestionar_roles.php
```

---

## ✔ update()

Flujo:

1. Valida permisos  
2. Obtiene rol y lista de permisos seleccionados  
3. Protege el rol **Programador**:

```php
if ($role === 'programador' && !Auth::isSuperRole($_SESSION['rol'])) { ... }
```

4. Elimina permisos actuales:

```sql
DELETE FROM role_permissions WHERE role = ?
```

5. Inserta nuevos permisos:

```sql
INSERT INTO role_permissions (role, permission_id)
```

6. Limpia permisos en sesión  
7. Redirige a la vista de permisos  

---

# 4. Modelos involucrados

Este módulo **no tiene un modelo propio**.  
Utiliza directamente:

- `Conexion`
- `roles`
- `permissions`
- `role_permissions`

---

# 5. Tablas de base de datos

## Tabla: `permissions`

```sql
CREATE TABLE `permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `controlador` varchar(100) NOT NULL,
  `accion` varchar(100) NOT NULL,
  `alias` varchar(100) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `visible` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uix_controlador_accion` (`controlador`,`accion`)
);
```

### Campos

| Campo | Descripción |
|--------|-------------|
| controlador | Nombre del controlador |
| accion | Método dentro del controlador |
| alias | Nombre amigable |
| descripcion | Explicación del permiso |
| visible | Si se muestra en la UI |

---

## Tabla: `role_permissions`

```sql
CREATE TABLE `role_permissions` (
  `role_id` int NOT NULL,
  `permission_id` int NOT NULL
);
```

### Campos

| Campo | Descripción |
|--------|-------------|
| role_id | Relación con roles.id |
| permission_id | Relación con permissions.id |

---

## Tabla: `roles`

```sql
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `alias` varchar(100) DEFAULT NULL,
  `nivel` decimal(3,1) NOT NULL DEFAULT '1.0',
  `categoria` enum('operativo','referente','supervisor','administrativo','direccion','reservado','baseOperativa') NOT NULL,
  `tipo` enum('fijo','temporal') NOT NULL DEFAULT 'fijo',
  `reservado` tinyint(1) NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
);
```

### Campos relevantes

| Campo | Descripción |
|--------|-------------|
| nombre | Nombre del rol |
| nivel | Nivel jerárquico |
| categoria | operativo / referente / supervisor / etc. |
| reservado | 1 = rol protegido |
| activo | 1 = visible en el sistema |

---

# 6. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| gestionar_roles.php | `vistas/paginas/roles/` | UI para asignar permisos a roles |

La vista muestra:

- Lista de roles  
- Lista de permisos  
- Checkboxes por permiso  
- Botón de guardar  

---

# 7. Flujo de trabajo del módulo

### 1. Usuario accede a `?r=permisos`
- Se listan roles  
- Se listan permisos  
- Se muestran permisos asignados al rol seleccionado  

### 2. Usuario selecciona/deselecciona permisos

### 3. Usuario guarda cambios
- Se eliminan permisos anteriores  
- Se insertan los nuevos  
- Se actualiza sesión  

---

# 8. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| Rol Programador | Solo Programador puede modificarlo |
| Permisos visibles | Solo se muestran permisos con `visible = 1` |
| Roles activos | Solo roles con `activo = 1` |
| Seguridad | Todos los métodos requieren `Auth::check()` |
| Sesión | Se limpia `permisos_usuario` al actualizar |

---

# 9. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| No aparecen roles | Tabla roles vacía | Revisar migración |
| No se guardan permisos | POST vacío | Revisar name="permissions[]" |
| No se puede editar Programador | Protección activa | Ingresar como Programador |
| Vista rota | Falta include de gestionar_roles.php | Revisar rutas |

---

# 10. Mejoras futuras sugeridas

- Agrupar permisos por módulo  
- Buscador de permisos  
- Permisos heredados por categoría  
- Auditoría de cambios de permisos  
- Exportación/importación de permisos  
- Permisos por usuario (además de por rol)  

---

