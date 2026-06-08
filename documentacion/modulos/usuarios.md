# # Módulo: USUARIOS

## 1. Descripción general

El módulo **Usuarios** administra todo el ciclo de vida de los empleados dentro del sistema:

- Creación de usuarios  
- Modificación de datos personales  
- Gestión de imágenes (perfil y repriv)  
- Asignación de roles  
- Activación / desactivación (bajas)  
- Reactivación  
- Listados activos e inactivos  
- Perfil del usuario  
- Integración con módulos: Roles, Bajas, Archivos, Turnos, Uniformes, Salud, Permisos  

Este módulo es uno de los más importantes del sistema, ya que determina:

- Quién puede ingresar  
- Qué permisos tiene  
- Qué información personal y operativa se almacena  

---

# 2. Rutas del módulo

Estas rutas deben estar en:

```
/rutas/rutas_usuarios.php
```

### ✔ Rutas recomendadas

```php
<?php

// Crear usuario
if (isset($_GET['r']) && $_GET['r'] === 'crear_usuario') {
    UsuariosController::vistaCrearUsuario();
    return;
}

// Guardar usuario
if (isset($_GET['r']) && $_GET['r'] === 'guardar_usuario') {
    UsuariosController::crtGuardarUsuario();
    return;
}

// Editar usuario
if (isset($_GET['r']) && $_GET['r'] === 'editar_usuario') {
    UsuariosController::vistaPerfilUsuario();
    return;
}

// Modificar usuario
if (isset($_GET['r']) && $_GET['r'] === 'modificar_usuario') {
    UsuariosController::crtModificarUsuario();
    return;
}

// Listado activos
if (isset($_GET['r']) && $_GET['r'] === 'listado_usuarios') {
    UsuariosController::vistaListadoUsuarios();
    return;
}

// Listado inactivos
if (isset($_GET['r']) && $_GET['r'] === 'listado_usuarios_inactivos') {
    UsuariosController::vistaListadoUsuariosInactivos();
    return;
}

// Reactivar usuario
if (isset($_GET['r']) && $_GET['r'] === 'reactivar_usuario') {
    UsuariosController::crtReactivarUsuario();
    return;
}
```

---

# 3. Controladores involucrados

### **UsuariosController**

| Método | Descripción |
|--------|-------------|
| `crtGuardarUsuario()` | Crea un usuario nuevo con imágenes y rol. |
| `crtModificarUsuario()` | Modifica datos personales, rol, imágenes y estado. |
| `crtReactivarUsuario()` | Reactiva un usuario inactivo. |
| `vistaListadoUsuarios()` | Lista usuarios activos. |
| `vistaListadoUsuariosInactivos()` | Lista usuarios dados de baja. |
| `vistaCrearUsuario()` | Formulario de creación. |
| `vistaPerfilUsuario()` | Formulario de edición. |

---

# 4. Submódulo: Crear usuario

## ✔ crtGuardarUsuario()

Flujo:

1. Valida permisos  
2. Inicia transacción  
3. Recibe datos personales  
4. Hashea contraseña  
5. Normaliza nombre de archivo  
6. Guarda imágenes usando:

```
ControladorArchivos::guardarArchivo()
```

7. Inserta usuario en BD  
8. Commit  
9. Toastify de éxito  

### Campos manejados

- nombre  
- apellido  
- dni  
- pass  
- f_nac  
- telefono  
- tel_emergencia  
- nombre_contacto  
- parentesco  
- domicilio  
- provincia  
- rol_id  
- imgPerfil  
- imgRepriv  
- resetPass = 0  
- activo = 1  

### ✔ Manejo especial de imgRepriv

El controlador incluye un bloque adicional:

- Si se sube un archivo en `imgRepriv`, se guarda en `/uploads/docs/`  
- Luego se actualiza el campo `imgRepriv` del usuario recién insertado  

---

# 5. Submódulo: Modificar usuario

## ✔ crtModificarUsuario()

Flujo:

1. Valida permisos  
2. Inicia transacción  
3. Recibe datos personales  
4. Maneja imágenes:
   - Si se sube nueva → reemplaza  
   - Si no → mantiene la actual  
5. Actualiza usuario  
6. Si se marcó “dar de baja”:
   - Valida motivo  
   - Inserta registro en tabla `bajas`  
7. Commit  
8. Toastify de éxito  

### Campos manejados

- Datos personales  
- Rol  
- Imágenes  
- resetPass (checkbox)  
- activo (checkbox → baja)  

---

# 6. Submódulo: Reactivar usuario

## ✔ crtReactivarUsuario()

Flujo:

1. Valida permisos  
2. Inicia transacción  
3. Ejecuta:

```
ModeloUsuarios::mdlReactivarUsuario()
```

4. Commit  
5. Toastify de éxito  

---

# 7. Submódulo: Listados

## ✔ vistaListadoUsuarios()

Consulta:

```sql
SELECT 
    u.idUsuario,
    u.apellido,
    u.nombre,
    u.telefono,
    u.tel_emergencia,
    u.nombre_contacto,
    u.parentesco,
    r.nombre AS rol_nombre,
    r.nivel  AS rol_nivel
FROM usuarios u
JOIN roles r ON u.rol_id = r.id
WHERE u.activo = 1
AND r.nombre <> 'Programador'
ORDER BY r.nivel ASC, u.apellido ASC, u.nombre ASC
```

Renderiza:

```
vistas/paginas/usuario/listado-usuarios.php
```

---

## ✔ vistaListadoUsuariosInactivos()

Consulta:

```sql
SELECT 
    u.idUsuario,
    CONCAT(u.apellido, ' ', u.nombre) AS empleado,
    b.motivo,
    b.fecha,
    CONCAT(e.apellido, ' ', e.nombre) AS eliminado_por
FROM usuarios u
JOIN bajas b ON u.idUsuario = b.usuario_id
JOIN usuarios e ON b.eliminado_por = e.idUsuario
JOIN roles r ON u.rol_id = r.id
WHERE u.activo = 0
AND r.nombre <> 'Programador'
ORDER BY empleado
```

Renderiza:

```
vistas/paginas/usuario/listado-usuarios-inactivos.php
```

---

# 8. Submódulo: Vistas principales

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| crear-usuario.php | `vistas/paginas/usuario/` | Alta de usuario |
| perfil-usuario.php | `vistas/paginas/usuario/` | Edición |
| listado-usuarios.php | `vistas/paginas/usuario/` | Activos |
| listado-usuarios-inactivos.php | `vistas/paginas/usuario/` | Inactivos |

---

# 9. Modelo involucrado

### **ModeloUsuarios**

| Método | Descripción |
|--------|-------------|
| `authenticate()` | Verifica credenciales. |
| `getUsuarioPorDni()` | Obtiene usuario + rol. |
| `getAsignacionHoy()` | Obtiene turno y puesto del día. |
| `mdlGuardarUsuario()` | Inserta usuario. |
| `mdlModificarUsuario()` | Actualiza usuario. |
| `mdlReactivarUsuario()` | Reactiva usuario. |
| `mdlActualizarPassReset()` | Cambia contraseña y marca resetPass = 1. |

---

# 10. Tabla de base de datos

### Tabla: `usuarios`

Campos relevantes:

| Campo | Descripción |
|--------|-------------|
| rol_id | Relación con roles |
| dni | Único |
| pass | Contraseña hasheada |
| resetPass | 0 = debe cambiar contraseña |
| activo | 1 = activo, 0 = baja |
| imgPerfil | Foto del usuario |
| imgRepriv | Foto de repriv / documentación |

---

# 11. Integración con otros módulos

### ✔ Roles
- Cada usuario tiene un `rol_id`  
- Determina permisos y nivel  

### ✔ Bajas
- Cuando un usuario se desactiva, se registra en `bajas`  

### ✔ Archivos
- Manejo de imágenes con `ControladorArchivos`  

### ✔ Turnos
- `getAsignacionHoy()` integra turnos + marcaciones  

### ✔ Uniformes / Salud
- Se vinculan por `usuario_id`  

---

# 12. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| DNI único | No se permiten duplicados |
| Rol obligatorio | Determina permisos |
| Imágenes opcionales | Se guardan si existen |
| Baja requiere motivo | Obligatorio |
| Programador | No aparece en listados |
| Seguridad | Todos los métodos usan `Auth::check()` |

---

# 13. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| “Error al guardar usuario” | Fallo en BD | Revisar datos |
| Imágenes no se guardan | Ruta incorrecta | Revisar permisos de carpeta |
| Usuario no aparece | Está inactivo | Revisar listado inactivos |
| No se registra baja | Falta motivo | Completar campo |

---

# 14. Mejoras futuras sugeridas

- Historial completo de modificaciones  
- Auditoría de accesos  
- Validación avanzada de imágenes  
- Importación masiva de usuarios  
- Integración con biometría  
- Roles temporales o por objetivo  

---
