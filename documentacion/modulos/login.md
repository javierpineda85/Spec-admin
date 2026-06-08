# # Módulo: LOGIN

## 1. Descripción general

El módulo **Login** gestiona la autenticación de usuarios en el sistema.  
Es uno de los módulos centrales, ya que:

- Valida credenciales (DNI + contraseña)
- Carga datos del usuario en sesión
- Carga permisos del rol
- Redirige según estado del usuario (ej: cambio de contraseña obligatorio)
- Asigna objetivo y puesto del día para vigiladores y referentes

Este módulo es utilizado por **todos los usuarios del sistema**.

---

## 2. Rutas del módulo

Estas rutas deben estar en:

```
/rutas/rutas_login.php
```

| Ruta | Método | Descripción |
|------|--------|-------------|
| `login` | GET | Muestra el formulario de login. |
| `procesar_login` | POST | Procesa credenciales y autentica al usuario. |
| `reset-password` | GET | Vista para forzar cambio de contraseña (si aplica). |

Ejemplo de archivo:

```php
<?php

if (isset($_GET['r']) && $_GET['r'] === 'login') {
    LoginController::mostrarLogin();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'procesar_login') {
    LoginController::procesarLogin();
    return;
}
```

---

## 3. Controladores involucrados

### **LoginController**

| Método | Descripción |
|--------|-------------|
| `mostrarLogin()` | Renderiza el formulario de login. |
| `procesarLogin()` | Valida credenciales, carga sesión y permisos. |

---

### ✔ mostrarLogin()

- Renderiza:

```
vistas/login.php
```

- No requiere permisos (es público)
- No realiza validaciones

---

### ✔ procesarLogin()

Flujo completo:

1. Inicia sesión si no existe  
2. Recibe DNI y contraseña  
3. Llama a `ModeloUsuarios::authenticate()`  
4. Si las credenciales son válidas:
   - Verifica si el usuario debe cambiar contraseña (`resetPass = 0`)
   - Carga en sesión:
     - idUsuario  
     - nombre, apellido  
     - imgPerfil  
     - rol_id, rol, nivel, categoría  
     - reservado  
   - Si es vigilador o referente:
     - Obtiene asignación del día (`getAsignacionHoy()`)
     - Carga puesto_id, objetivo_id, isReferente  
   - Carga permisos del rol desde:
     ```
     role_permissions → permissions
     ```
     o `['*']` si es Programador  
   - Redirige a `index.php`

5. Si las credenciales son incorrectas:
   - Muestra mensaje de error
   - Redirige a `login`

---

## 4. Modelos involucrados

### **ModeloUsuarios** (referenciado)

| Método | Descripción |
|--------|-------------|
| `authenticate($dni, $password)` | Devuelve datos del usuario si las credenciales son correctas. |
| `getAsignacionHoy($idUsuario)` | Devuelve objetivo y puesto asignado para el día. |

---

## 5. Tablas de base de datos

### Tabla: `usuarios`

```sql
CREATE TABLE `usuarios` (
  `idUsuario` int NOT NULL AUTO_INCREMENT,
  `rol_id` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `dni` varchar(15) NOT NULL,
  `pass` varchar(150) NOT NULL,
  `f_nac` date NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `tel_emergencia` varchar(20) DEFAULT NULL,
  `nombre_contacto` varchar(30) NOT NULL,
  `parentesco` varchar(30) NOT NULL,
  `domicilio` varchar(100) DEFAULT NULL,
  `provincia` varchar(30) DEFAULT NULL,
  `imgPerfil` varchar(60) DEFAULT NULL,
  `imgRepriv` varchar(60) DEFAULT NULL,
  `resetPass` int NOT NULL,
  `activo` int NOT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idUsuario`),
  UNIQUE KEY `dni` (`dni`),
  KEY `fk_usuarios_roles` (`rol_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
```

### Campos relevantes para Login

| Campo | Descripción |
|--------|-------------|
| dni | Usuario ingresa este valor |
| pass | Contraseña encriptada |
| resetPass | 0 = debe cambiar contraseña |
| rol_id | Rol del usuario |
| activo | 1 = habilitado |
| imgPerfil | Imagen de perfil |
| categoria | operativo / referente / supervisor / programador |

---

## 6. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| login.php | `vistas/` | Formulario de login |
| reset-password.php | `vistas/` | Cambio obligatorio de contraseña |

---

## 7. Flujo de trabajo del módulo

### 1. Usuario ingresa DNI y contraseña
- Se envía POST a `procesar_login`

### 2. Validación
- Se verifica DNI y contraseña
- Si falla → mensaje de error

### 3. Carga de sesión
- Datos personales
- Datos de rol
- Permisos del rol
- Asignación del día (si aplica)

### 4. Redirección
- Si debe cambiar contraseña → `reset-password`
- Si no → `index.php`

---

## 8. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| DNI obligatorio | No puede estar vacío |
| Contraseña obligatoria | No puede estar vacía |
| resetPass | Si es 0 → debe cambiar contraseña |
| Permisos | Se cargan desde role_permissions |
| Programador | Tiene permisos `['*']` |

---

## 9. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| “DNI o contraseña incorrectos” | Credenciales inválidas | Revisar datos ingresados |
| No carga permisos | rol_id incorrecto | Revisar tabla roles |
| No asigna objetivo | Usuario sin asignación del día | Revisar módulo de asignaciones |
| Vista en blanco | Falta incluir vista login.php | Revisar rutas |

---

## 10. Mejoras futuras sugeridas

- Autenticación con 2FA  
- Registro de intentos fallidos  
- Bloqueo automático por intentos  
- Historial de accesos  
- Recuperación de contraseña por email  
- Integración con LDAP / Active Directory  


