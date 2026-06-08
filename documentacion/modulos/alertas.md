
# # Módulo: ALERTAS

## 1. Descripción general

El módulo **Alertas** administra todas las notificaciones automáticas generadas por el sistema.  
Su función principal es advertir sobre eventos importantes, especialmente:

- Falta de reporte de “Hombre Vivo”
- Alertas generales generadas por otros módulos
- Visualización de alertas no leídas
- Marcado de alertas como leídas
- Historial filtrado de alertas

Este módulo es utilizado por:

- Supervisores  
- Administrativos  
- Vigiladores (solo para ver sus alertas)

---

## 2. Rutas del módulo

| Ruta | Método | Descripción |
|------|--------|-------------|
| `registrar_alerta_hombrevivo` | POST | Registra una alerta automática por demora en reporte. |
| `alertas_supervisor` | GET | Vista HTML para supervisores. |
| `ver_alertas` | GET | Devuelve alertas no leídas en formato JSON. |
| `marcar_alerta_leida` | POST | Marca una alerta como leída. |
| `ver_historial_alertas` | GET | Devuelve historial filtrado de alertas leídas. |

---

## 3. Controladores involucrados

### AlertasController

| Método | Descripción |
|--------|-------------|
| `registrarDemoraHombreVivo()` | Genera alerta automática por demora en reporte. |
| `registrarAlertaGeneral()` | Inserta alerta evitando duplicados. |
| `contarNoLeidas()` | Devuelve cantidad de alertas no leídas. |
| `verAlertasNoLeidas()` | Devuelve JSON con alertas no leídas. |
| `marcarLeida()` | Marca una alerta como leída. |
| `verHistorialLeidas()` | Devuelve historial filtrado. |
| `obtenerNoLeidas()` | Método auxiliar para obtener alertas no leídas. |

---

## 4. Modelos involucrados

Este módulo **no utiliza un modelo dedicado**.  
Todas las consultas se realizan mediante la clase `Conexion`.

---

## 5. Tablas de base de datos

### Tabla: `alertas`

```sql
CREATE TABLE `alertas` (
  `idAlerta` int NOT NULL AUTO_INCREMENT,
  `tipo` varchar(50) DEFAULT NULL,
  `mensaje` text,
  `usuario_id` int DEFAULT NULL,
  `objetivo_id` int DEFAULT NULL,
  `creada_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `leida` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`idAlerta`)
);
```

### Campos

| Campo | Tipo | Descripción |
|--------|------|-------------|
| idAlerta | int | Identificador único de la alerta |
| tipo | varchar(50) | Tipo de alerta (ej: “hombre_vivo”) |
| mensaje | text | Mensaje descriptivo |
| usuario_id | int | Usuario destinatario |
| objetivo_id | int | Objetivo relacionado |
| creada_en | datetime | Fecha de creación |
| leida | tinyint(1) | 0 = no leída, 1 = leída |

### Relaciones

| Campo | Relación |
|--------|----------|
| usuario_id | usuarios.idUsuario |
| objetivo_id | objetivos.idObjetivo |

---

## 6. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| alertas_supervisor.php | `vistas/paginas/supervisores/alertas_supervisor.php` | Vista HTML para supervisores |
| ver_alertas (JSON) | — | Devuelve alertas no leídas |
| ver_historial_alertas (JSON) | — | Devuelve historial filtrado |

---

## 7. Flujo de trabajo del módulo

### 1. Generación automática de alerta
- Un vigilador no reporta “Hombre Vivo”
- El sistema detecta demora > 300 segundos
- Se llama a `registrarDemoraHombreVivo()`
- Se genera alerta si no existe una abierta

### 2. Visualización de alertas
- El usuario abre la app
- Se llama a `verAlertasNoLeidas()`
- Se muestran las últimas 10 alertas

### 3. Marcado como leída
- El usuario hace clic en “Marcar como leída”
- Se envía POST a `marcar_alerta_leida`
- Se actualiza la tabla

### 4. Historial
- El supervisor filtra por tipo o fecha
- `verHistorialLeidas()` devuelve JSON con JOINs

---

## 8. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| Tiempo mínimo | No se registran alertas si el tiempo < 300 segundos |
| Duplicados | No se registran alertas duplicadas del mismo tipo y usuario si están sin leer |
| Seguridad | Solo el usuario dueño de la alerta puede marcarla como leída |
| Límite | Historial devuelve máximo 100 registros |
| Optimización | Alertas no leídas limitadas a 10 |

---

## 9. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| No se generan alertas | tiempo < 300 | Ajustar lógica del cliente |
| Alertas duplicadas | No se usa registrarAlertaGeneral | Centralizar creación |
| No se muestran alertas | Sesión no iniciada | Agregar `session_start()` |
| No marca como leída | usuario_id incorrecto | Revisar sesión y POST |

---

## 10. Mejoras futuras sugeridas

- Crear un modelo dedicado `ModeloAlertas`
- Agregar paginación al historial
- Agregar niveles de severidad (info, warning, critical)
- Enviar alertas por email o WhatsApp
- Integrar WebSockets para alertas en tiempo real

---


