# # Módulo: RESET PASSWORD

## 1. Descripción general

El módulo **Reset Password** permite restablecer la contraseña de un usuario en dos escenarios:

### ✔ 1. Forced Reset (obligatorio)
Cuando el usuario inicia sesión y su campo `resetPass = 0`, el sistema:

- No permite continuar  
- Redirige automáticamente a `?r=reset-password`  
- Obliga a establecer una nueva contraseña  

### ✔ 2. Forgot Password (olvidé mi contraseña)
El usuario accede manualmente a:

```
?r=reset-password
```

Y debe ingresar:

- DNI  
- Nueva contraseña  
- Confirmación  

Este módulo es crítico para la seguridad del sistema.

---

# 2. Rutas del módulo

Estas rutas deben estar en:

```
/rutas/rutas_reset_password.php
```

### ✔ Rutas recomendadas

```php
<?php

// Mostrar formulario de reset
if (isset($_GET['r']) && $_GET['r'] === 'reset-password') {
    ResetPasswordController::vistaResetPassword();
    return;
}

// Procesar reset
if (isset($_GET['r']) && $_GET['r'] === 'procesar-reset') {
    ResetPasswordController::crtResetPassword();
    return;
}
```

---

# 3. Controladores involucrados

### **ResetPasswordController**

| Método | Descripción |
|--------|-------------|
| `vistaResetPassword()` | Muestra el formulario de reset. |
| `crtResetPassword()` | Procesa el cambio de contraseña. |

---

# 4. Submódulo: vistaResetPassword()

```php
public static function vistaResetPassword()
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    $forced = !empty($_SESSION['force_reset_id']);

    include __DIR__ . '/../vistas/reset-password.php';
}
```

### ✔ Comportamiento

- Si existe `$_SESSION['force_reset_id']` → **forced reset**
- Si no existe → **forgot password**

### ✔ La vista recibe:

```php
$forced = true/false
```

Y muestra:

| Campo | Forced Reset | Forgot Password |
|--------|--------------|-----------------|
| DNI | ❌ No | ✔ Sí |
| Nueva contraseña | ✔ Sí | ✔ Sí |
| Confirmación | ✔ Sí | ✔ Sí |

---

# 5. Submódulo: crtResetPassword()

Flujo completo:

### ✔ 1. Validación de contraseñas

- No pueden estar vacías  
- Deben coincidir  

Si falla:

```
$_SESSION['reset_error'] = 'Las contraseñas no coinciden...';
redirige a ?r=reset-password
```

---

### ✔ 2. Determinar el usuario a modificar

#### Caso A — Forced Reset

```php
$uid = $_SESSION['force_reset_id'];
```

#### Caso B — Forgot Password

- Se busca por DNI:

```php
$u = $modelo->getUsuarioPorDni($dni);
$uid = $u[0]['idUsuario'];
```

Si el DNI no existe → error.

---

### ✔ 3. Guardar nueva contraseña

```php
$hash = password_hash($newPass, PASSWORD_DEFAULT);
ModeloUsuarios::mdlActualizarPassReset($uid, $hash);
```

---

### ✔ 4. Finalización

#### Si todo salió bien:

- Limpia `force_reset_id`
- Muestra mensaje de éxito
- Redirige a login

#### Si falla:

- Muestra mensaje de error
- Redirige a reset-password

---

# 6. Modelos involucrados

### **ModeloUsuarios**

El módulo utiliza:

| Método | Descripción |
|--------|-------------|
| `getUsuarioPorDni($dni)` | Busca usuario por DNI. |
| `mdlActualizarPassReset($id, $hash)` | Actualiza contraseña y marca resetPass = 1. |

---

# 7. Tablas de base de datos

### Tabla: `usuarios`

Campos relevantes:

| Campo | Descripción |
|--------|-------------|
| dni | Usado en forgot password |
| pass | Contraseña hasheada |
| resetPass | 0 = debe cambiar contraseña |
| activo | Usuario habilitado |

---

# 8. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| reset-password.php | `vistas/` | Formulario de reset |

La vista debe mostrar:

- Errores (`$_SESSION['reset_error']`)  
- Campos según `$forced`  
- Botón de enviar  

---

# 9. Flujo de trabajo del módulo

### ✔ Forced Reset

1. Usuario inicia sesión  
2. Login detecta `resetPass = 0`  
3. Guarda `force_reset_id`  
4. Redirige a `reset-password`  
5. Usuario cambia contraseña  
6. Se habilita el acceso normal  

---

### ✔ Forgot Password

1. Usuario accede a `reset-password`  
2. Ingresa DNI  
3. Ingresa nueva contraseña  
4. Se actualiza contraseña  
5. Redirige a login  

---

# 10. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| Contraseñas iguales | Obligatorio |
| Contraseña no vacía | Obligatorio |
| DNI válido | Solo en forgot password |
| Forced reset | No requiere DNI |
| Seguridad | Contraseña siempre hasheada |
| Sesión | Manejo de `force_reset_id` |

---

# 11. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| “Las contraseñas no coinciden” | Validación fallida | Revisar campos |
| “DNI no registrado” | Forgot password | Revisar ingreso |
| No actualiza contraseña | Error en BD | Revisar modelo |
| Forced reset no desaparece | No se limpió `force_reset_id` | Revisar controlador |

---

# 12. Mejoras futuras sugeridas

- Recuperación por email  
- Token temporal de recuperación  
- Expiración de enlaces  
- Validación de contraseña fuerte  
- Historial de contraseñas  
- Doble factor de autenticación  

---

