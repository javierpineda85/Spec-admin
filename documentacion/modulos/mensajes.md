# # Módulo: MENSAJES

## 1. Descripción general

El módulo **Mensajes** permite la comunicación interna entre usuarios del sistema, con reglas estrictas basadas en:

- Rol  
- Categoría (operativo, referente, supervisor, administrativo, dirección, programador)  
- Objetivo asignado  
- Historial de conversación  

Este módulo soporta:

- Envío de mensajes  
- Bandeja de entrada  
- Bandeja de enviados  
- Lectura de mensajes  
- Respuestas condicionadas  
- Marcar como leído / no leído  
- Restricciones avanzadas según jerarquía y objetivo  

Es uno de los módulos con reglas de negocio más complejas del sistema.

---

# 2. Rutas del módulo


## 📌 **rutas_mensajes.php (versión completa y final)**

```php
<?php

// Bandeja de entrada
if (isset($_GET['r']) && $_GET['r'] === 'bandeja-entrada') {
    require_once 'controladores/mensajes.controller.php';
    $mensajes = ControladorMensajes::crtMostrarMensajesRecibidos('destinatario_id', $_SESSION['idUsuario']);
    require 'vistas/paginas/mensajes/bandeja-entrada.php';
    return;
}

// Bandeja de enviados
if (isset($_GET['r']) && $_GET['r'] === 'mensajes-enviados') {
    require_once 'controladores/mensajes.controller.php';
    $mensajes = ControladorMensajes::crtMostrarMensajesEnviados('remitente_id', $_SESSION['idUsuario']);
    require 'vistas/paginas/mensajes/mensajes-enviados.php';
    return;
}

// Nuevo mensaje
if (isset($_GET['r']) && $_GET['r'] === 'nuevo-mensaje') {
    require_once 'controladores/mensajes.controller.php';
    $destinatarios = ControladorMensajes::obtenerDestinatariosDisponibles($_SESSION['idUsuario']);
    require 'vistas/paginas/mensajes/nuevo-mensaje.php';
    return;
}

// Guardar mensaje
if (isset($_GET['r']) && $_GET['r'] === 'guardar_mensaje') {
    require_once 'controladores/mensajes.controller.php';
    ControladorMensajes::crtGuardarMensaje();
    header("Location: ?r=bandeja-entrada");
    return;
}

// Ver mensaje
if (isset($_GET['r']) && $_GET['r'] === 'ver-mensaje') {
    require_once 'controladores/mensajes.controller.php';
    $mensaje = ControladorMensajes::crtMostrarUnMensaje($_GET['id']);
    require 'vistas/paginas/mensajes/ver-mensaje.php';
    return;
}

// Marcar leído
if (isset($_GET['r']) && $_GET['r'] === 'marcar-leido') {
    require_once 'controladores/mensajes.controller.php';
    ControladorMensajes::crtMarcarLeido($_GET['id']);
    header("Location: ?r=bandeja-entrada");
    return;
}

// Marcar no leído
if (isset($_GET['r']) && $_GET['r'] === 'marcar-no-leido') {
    require_once 'controladores/mensajes.controller.php';
    ControladorMensajes::crtMarcarNoLeido($_GET['id']);
    header("Location: ?r=bandeja-entrada");
    return;
}
```

---

# 3. Controladores involucrados

### **ControladorMensajes**

| Método | Descripción |
|--------|-------------|
| `crtGuardarMensaje()` | Envía un mensaje nuevo. |
| `crtMostrarMensajesRecibidos()` | Devuelve bandeja de entrada. |
| `crtMostrarMensajesEnviados()` | Devuelve bandeja de enviados. |
| `crtMostrarUnMensaje()` | Devuelve un mensaje específico. |
| `crtMarcarLeido()` | Marca un mensaje como leído. |
| `crtMarcarNoLeido()` | Marca un mensaje como no leído. |
| `puedeEnviar()` | Valida si un usuario puede enviar a otro. |
| `obtenerDestinatariosDisponibles()` | Lista usuarios a los que se puede enviar. |

---

# 4. Reglas de envío (núcleo del módulo)

El método **puedeEnviar()** define reglas estrictas:

### ✔ 1. Usuarios con permiso total
- Programador (`reservado = 1`)
- Dirección
- Supervisor

Pueden enviar a cualquiera.

---

### ✔ 2. Operativo / Referente → Operativo / Referente
Solo pueden enviarse mensajes si:

- Ambos están asignados al **mismo objetivo**
- Si es respuesta, el mensaje original debe pertenecer al mismo objetivo

---

### ✔ 3. Operativo / Referente → Administrativo / Dirección
Solo permitido si:

- El administrativo/dirección **inició la conversación**
- El operativo **solo puede responder una vez**

---

### ✔ 4. Administrativo / Dirección → Cualquiera
Siempre permitido.

---

### ✔ 5. Respuestas condicionadas
Si el mensaje es respuesta (`idMensajeOriginal`):

- Se verifica el objetivo del mensaje original  
- Si no coincide con el objetivo del remitente → **no puede responder**

---

# 5. Modelos involucrados

### **ModeloMensajes**

| Método | Descripción |
|--------|-------------|
| `mdlMostrarMensajes()` | Bandeja de entrada. |
| `mdlMostrarMensajesEnviados()` | Bandeja de enviados. |
| `mdlMostrarUnMensaje()` | Un mensaje específico. |
| `mdlGuardarMensaje()` | Inserta un mensaje. |
| `mdlMarcarLeido()` | Marca como leído. |
| `mdlMarcarNoLeido()` | Marca como no leído. |

---

# 6. Tabla de base de datos

### Tabla: `mensajes`

```sql
CREATE TABLE `mensajes` (
  `idMensaje` int NOT NULL AUTO_INCREMENT,
  `remitente_id` int DEFAULT NULL,
  `destinatario_id` int DEFAULT NULL,
  `contenido` text NOT NULL,
  `objetivo_id` int DEFAULT NULL,
  `fecha_hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `leido` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`idMensaje`),
  KEY `remitente_id` (`remitente_id`),
  KEY `destinatario_id` (`destinatario_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
```

---

# 7. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| bandeja-entrada.php | `vistas/paginas/mensajes/` | Bandeja de entrada |
| mensajes-enviados.php | `vistas/paginas/mensajes/` | Bandeja de enviados |
| nuevo-mensaje.php | `vistas/paginas/mensajes/` | Formulario de envío |
| ver-mensaje.php | `vistas/paginas/mensajes/` | Vista de un mensaje |

---

# 8. Flujo de trabajo del módulo

### 1. Usuario abre bandeja de entrada
- Se cargan mensajes donde `destinatario_id = usuario`

### 2. Usuario abre un mensaje
- Se marca como leído  
- Se muestra contenido y remitente  

### 3. Usuario responde
- Se valida con `puedeEnviar()`  
- Se guarda mensaje con `objetivo_id` del remitente  

### 4. Usuario envía mensaje nuevo
- Se listan destinatarios permitidos  
- Se valida envío  
- Se guarda mensaje  

---

# 9. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| Contenido obligatorio | No se permite mensaje vacío |
| Remitente y destinatario | Deben existir |
| Objetivo | Se guarda automáticamente |
| Permisos | Se valida con `puedeEnviar()` |
| Respuestas | Deben respetar objetivo del mensaje original |
| Jerarquía | Supervisores y dirección pueden enviar a todos |

---

# 10. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| “No tienes permiso para enviar este mensaje” | Reglas de envío | Revisar objetivo y categoría |
| No se envía mensaje | POST incompleto | Revisar formulario |
| No aparecen destinatarios | Reglas de filtrado | Revisar asignación del día |
| No se marca leído | Error SQL | Revisar modelo |

---

# 11. Mejoras futuras sugeridas

- Adjuntar archivos  
- Notificaciones push  
- Conversaciones agrupadas (threads)  
- Buscador avanzado  
- Mensajes destacados  
- Eliminación lógica (soft delete)  

---

