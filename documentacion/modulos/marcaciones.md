# # Módulo: MARCACIONES

## 1. Descripción general

El módulo **Marcaciones** registra las entradas y salidas de los vigiladores y referentes durante su jornada laboral.

Cada marcación incluye:

- Usuario (vigilador o referente)
- Objetivo donde está asignado
- Puesto
- Tipo de evento (**entrada** o **salida**)
- Fecha y hora
- Coordenadas GPS (latitud y longitud)
- Validación de geocerca (geofence)

Este módulo es crítico para:

- Control horario  
- Auditoría de cumplimiento  
- Cálculo de horas reales (usado por Cronogramas)  
- Seguridad operativa  

---

## 2. Rutas del módulo

Estas rutas deben agregarse en:

```
/rutas/rutas_marcaciones.php
```


## 3. Controladores involucrados

### **MarcacionesController**

| Método | Descripción |
|--------|-------------|
| `crtRegistrarMarcacion()` | Registra entrada o salida con validación de geocerca. |

---

### ✔ crtRegistrarMarcacion()

Flujo completo:

1. **Valida permisos**  
   `Auth::check('marcaciones', 'crtRegistrarMarcacion')`

2. **Inicia sesión** si no existe

3. **Obtiene datos desde sesión y POST**  
   - vigilador_id  
   - objetivo_id  
   - puesto_id  
   - tipo_evento (entrada/salida)  
   - latitud / longitud  

4. **Valida datos mínimos**  
   Si falta algo → error + redirección

5. **Validación de geocerca (solo vigilador/referente)**  
   - Obtiene latitud, longitud y radio del objetivo  
   - Calcula distancia con fórmula de Haversine  
   - Si está fuera del radio → error y no registra

6. **Inserta marcación** en `marcaciones_servicio`

7. **Commit** y redirección con Toastify de éxito

---

## 4. Modelos involucrados

Este módulo **no tiene un modelo dedicado**.  
El controlador ejecuta SQL directamente.

---

## 5. Tablas de base de datos

### Tabla: `marcaciones_servicio`

```sql
CREATE TABLE `marcaciones_servicio` (
  `idMarcacion` int NOT NULL AUTO_INCREMENT,
  `vigilador_id` int NOT NULL,
  `objetivo_id` int DEFAULT NULL,
  `puesto_id` int NOT NULL,
  `tipo_evento` enum('entrada','salida') NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `latitud` decimal(10,8) NOT NULL,
  `longitud` decimal(11,8) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idMarcacion`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
```

### Campos

| Campo | Tipo | Descripción |
|--------|------|-------------|
| idMarcacion | int | Identificador único |
| vigilador_id | int | Usuario que marca |
| objetivo_id | int | Objetivo donde está asignado |
| puesto_id | int | Puesto del objetivo |
| tipo_evento | entrada/salida | Tipo de marcación |
| fecha_hora | datetime | Fecha y hora exacta |
| latitud | decimal | Coordenada GPS |
| longitud | decimal | Coordenada GPS |
| created_at | timestamp | Fecha de creación |

### Relaciones

| Campo | Relación |
|--------|----------|
| vigilador_id | usuarios.idUsuario |
| objetivo_id | objetivos.idObjetivo |
| puesto_id | puestos.idPuesto |

---

## 6. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| entradas_salidas.php | `vistas/paginas/marcaciones/` | Formulario para marcar entrada/salida |

---

## 7. Flujo de trabajo del módulo

### 1. El vigilador abre la vista de marcaciones
- Se muestra formulario con botón de entrada/salida  
- El dispositivo obtiene coordenadas GPS  

### 2. El vigilador marca entrada o salida
- Se envía POST a `registrar_marcacion`  
- Se validan datos  

### 3. Validación de geocerca
- Se obtiene lat/lng del objetivo  
- Se calcula distancia  
- Si está fuera del radio → error  

### 4. Registro
- Se inserta en BD  
- Se muestra Toastify de éxito  

### 5. Redirección
- Vuelve a `entradas_salidas`

---

## 8. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| Coordenadas obligatorias | latitud y longitud no pueden ser nulas |
| Tipo de evento | Debe ser entrada o salida |
| Geocerca | Obligatoria para vigiladores y referentes |
| Radio | Se obtiene desde tabla objetivos |
| Transacciones | Se usa beginTransaction + commit |
| Seguridad | Requiere `Auth::check()` |

---

## 9. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| “Faltan datos para registrar la marcación” | POST incompleto | Revisar formulario |
| “Objetivo no encontrado” | objetivo_id inválido | Revisar sesión o asignación |
| “Fuera del área permitida” | GPS fuera del radio | Revisar ubicación |
| No registra | Error SQL | Revisar conexión o tabla |
| Vista en blanco | Falta ruta | Agregar rutas_marcaciones.php |

---

## 10. Mejoras futuras sugeridas

- Mostrar mapa con ubicación actual  
- Registrar precisión del GPS  
- Registrar batería del dispositivo  
- Notificaciones si el vigilador sale del área  
- Dashboard en tiempo real  
- Exportación de marcaciones a Excel/PDF  

---

