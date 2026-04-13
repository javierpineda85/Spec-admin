# # Módulo: NOTIFICACIONES

## 1. Descripción general

El módulo **Notificaciones** centraliza la obtención de:

- Alertas no leídas  
- Mensajes no leídos  

Su función principal es proveer datos al **header**, **sidebar**, o **panel principal** del sistema para mostrar:

- Contadores de notificaciones  
- Listas desplegables de alertas  
- Listas de mensajes recientes  

Este módulo **no guarda datos**, sino que **consume otros módulos**:

- `AlertasController`
- `ControladorMensajes`

Es un módulo de **agregación**, no de almacenamiento.

---

## 2. Rutas del módulo

Este módulo **no requiere rutas propias**, porque:

- No tiene vistas
- No tiene formularios
- No procesa POST
- Solo expone métodos estáticos para ser usados desde cualquier parte del sistema

Generalmente se usa desde:

- `header.php`
- `navbar.php`
- `sidebar.php`
- Widgets de dashboard

Ejemplo de uso:

```php
$alertas = NotificacionesController::contarAlertasNoLeidas($_SESSION['idUsuario']);
$mensajes = NotificacionesController::contarMensajesNoLeidos($_SESSION['idUsuario']);
```

---

## 3. Controladores involucrados

### **NotificacionesController**

| Método | Descripción |
|--------|-------------|
| `contarAlertasNoLeidas($usuarioId)` | Devuelve cantidad de alertas no leídas. |
| `obtenerAlertasNoLeidas($usuarioId, $limite)` | Devuelve lista de alertas no leídas. |
| `contarMensajesNoLeidos($usuarioId)` | Devuelve cantidad de mensajes no leídos. |
| `obtenerMensajesNoLeidos($usuarioId, $limite)` | Devuelve lista de mensajes no leídos. |

---

## 4. Implementación del controlador

### ✔ Alertas

```php
public static function contarAlertasNoLeidas($usuarioId)
{
    return AlertasController::contarNoLeidas($usuarioId);
}

public static function obtenerAlertasNoLeidas($usuarioId, $limite = 10)
{
    return AlertasController::obtenerNoLeidas($usuarioId, $limite);
}
```

Depende directamente del módulo **Alertas**.

---

### ✔ Mensajes

```php
public static function contarMensajesNoLeidos($usuarioId)
{
    $recibidos = ControladorMensajes::crtMostrarMensajesEnviados('destinatario_id', $usuarioId);
    $noLeidos = array_filter($recibidos, fn($m) => $m['leido'] == 0);
    return count($noLeidos);
}

public static function obtenerMensajesNoLeidos($usuarioId, $limite = 10)
{
    $recibidos = ControladorMensajes::crtMostrarMensajesEnviados('destinatario_id', $usuarioId);
    $noLeidos = array_filter($recibidos, fn($m) => $m['leido'] == 0);
    return array_slice($noLeidos, 0, $limite);
}
```

> **Nota:**  
`crtMostrarMensajesEnviados()` está mal nombrado:  
en realidad devuelve **mensajes recibidos**, porque filtra por `destinatario_id`.

Esto ya lo corregimos en la documentación del módulo Mensajes.

---

## 5. Tablas utilizadas

Este módulo **no tiene tabla propia**.

Consume:

### Tabla: `alertas`
- Para alertas no leídas

### Tabla: `mensajes`
- Para mensajes no leídos

---

## 6. Vistas del módulo

Este módulo **no tiene vistas propias**.

Se usa desde:

- Header
- Sidebar
- Dashboard
- Widgets de notificaciones

Ejemplo típico:

```php
$alertas = NotificacionesController::obtenerAlertasNoLeidas($_SESSION['idUsuario'], 5);
$mensajes = NotificacionesController::obtenerMensajesNoLeidos($_SESSION['idUsuario'], 5);
```

---

## 7. Flujo de trabajo del módulo

### 1. El usuario inicia sesión
- Se carga su `idUsuario`

### 2. El header solicita notificaciones
- Se llama a los métodos del controlador

### 3. Se muestran:
- Cantidad de alertas no leídas  
- Cantidad de mensajes no leídos  
- Listas desplegables con los últimos elementos  

### 4. El usuario abre un mensaje o alerta
- Se marca como leído desde el módulo correspondiente  
- El contador se actualiza automáticamente

---

## 8. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| No almacena datos | Solo consulta otros módulos |
| No requiere rutas | No procesa formularios |
| No requiere vistas | Es un módulo backend |
| Seguridad | Depende de Auth en módulos Alertas/Mensajes |
| Límite | Por defecto devuelve máximo 10 elementos |

---

## 9. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| “Undefined index idUsuario” | No hay sesión | Iniciar sesión antes de llamar |
| No muestra mensajes | Error en rutas de mensajes | Revisar rutas_mensajes.php |
| No muestra alertas | Módulo Alertas no cargado | Revisar rutas_alertas.php |
| Contadores incorrectos | Método equivocado en Mensajes | Usar versión corregida |

---

## 10. Mejoras futuras sugeridas

### ✔ 1. Unificar alertas y mensajes en un solo feed  
Tipo “campanita” con:

- Alertas  
- Mensajes  
- Novedades  
- Newsletter  

### ✔ 2. Notificaciones push  
Integración con:

- Web Push  
- App móvil  
- Email  

### ✔ 3. Notificaciones en tiempo real  
Con WebSockets o SSE.

### ✔ 4. Historial de notificaciones  
Tabla propia:

```
notificaciones
```

### ✔ 5. Integración con módulo Noticias  
Para mostrar:

- Cumpleaños  
- Comunicados  
- Cambios operativos  
- Newsletter mensual  

---
