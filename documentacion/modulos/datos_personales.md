# # Módulo: DATOS PERSONALES

## 1. Descripción general

El módulo **Datos Personales** permite que cada usuario del sistema registre y mantenga actualizada su información personal, familiar y de pareja.

Este módulo administra:

- Datos básicos del usuario  
- Información de pareja  
- Hijos (biológicos y adoptivos)  
- Padres  
- Hermanos  
- Tutores con discapacidad  
- Nivel de estudio  
- Estado civil  
- Email  

Toda la información familiar se almacena en formato **JSON**, permitiendo múltiples registros por categoría.

Este módulo es utilizado por:

- Vigiladores  
- Administrativos  
- Supervisores (solo lectura, según permisos)

---

## 2. Rutas del módulo

| Ruta | Método | Descripción |
|------|--------|-------------|
| `mis_datos_personales` | GET | Muestra la vista con los datos personales del usuario. |
| `guardar_datos_personales` | POST | Guarda o actualiza los datos personales. |

> La ruta POST depende de tu archivo `rutas_datos.php`, pero el controlador es `DatosPersonalesController::guardarDatos()`.

---

## 3. Controladores involucrados

### **DatosPersonalesController**

| Método | Descripción |
|--------|-------------|
| `vistaMisDatosPersonales()` | Muestra la vista principal del módulo. |
| `guardarDatos()` | Inserta o actualiza los datos personales del usuario. |
| `normalizarArray()` | Convierte arrays o strings JSON en JSON válido para la BD. |

---

### ✔ vistaMisDatosPersonales()

- Requiere permisos: `Auth::check('datos_personales', 'verMisDatos')`
- Renderiza la vista:

```
vistas/paginas/datos/mis_datos_personales.php
```

---

### ✔ guardarDatos()

1. Valida permisos  
2. Recibe todos los campos del formulario  
3. Normaliza arrays JSON (hijos, padres, etc.)  
4. Verifica si el usuario ya tiene datos personales  
5. Inserta o actualiza según corresponda  
6. Redirige nuevamente a la vista del usuario  

---

### ✔ normalizarArray($campo)

Convierte cualquier entrada del formulario en JSON válido:

- Si viene como array → lo convierte a JSON  
- Si viene como string `"[]"` → JSON vacío  
- Si no existe → JSON vacío  

Esto evita errores de validación en MySQL.

---

## 4. Modelos involucrados

### **ModeloDatosPersonales**

| Método | Descripción |
|--------|-------------|
| `buscarPorUsuario($usuario_id)` | Obtiene datos personales del usuario. |
| `insertar($datos)` | Inserta un registro nuevo. |
| `actualizar($datos)` | Actualiza un registro existente. |
| `bindCampos()` | Asigna parámetros a la consulta SQL. |

---

## 5. Tablas de base de datos

### Tabla: `datos_personales`

```sql
CREATE TABLE `datos_personales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `estado_civil` varchar(20) DEFAULT NULL,
  `pareja_nombre` varchar(100) DEFAULT NULL,
  `pareja_nacimiento` date DEFAULT NULL,
  `pareja_dni` varchar(20) DEFAULT NULL,
  `nivel_estudio` enum(
      'primario_incompleto','primario_completo',
      'secundario_incompleto','secundario_completo',
      'terciario_incompleto','terciario_completo',
      'universitario_incompleto','universitario_completo'
  ) DEFAULT NULL,
  `hijos` longtext,
  `hijos_adoptivos` longtext,
  `padres` longtext,
  `hermanos` longtext,
  `tutores_discapacidad` longtext,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `datos_personales_chk_1` CHECK (json_valid(`hijos`)),
  CONSTRAINT `datos_personales_chk_2` CHECK (json_valid(`hijos_adoptivos`)),
  CONSTRAINT `datos_personales_chk_3` CHECK (json_valid(`padres`)),
  CONSTRAINT `datos_personales_chk_4` CHECK (json_valid(`hermanos`)),
  CONSTRAINT `datos_personales_chk_5` CHECK (json_valid(`tutores_discapacidad`))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
```

### Campos

| Campo | Tipo | Descripción |
|--------|------|-------------|
| id | int | Identificador único |
| usuario_id | int | Relación con usuarios.idUsuario |
| email | varchar(100) | Email personal |
| estado_civil | varchar(20) | Soltero, casado, etc. |
| pareja_nombre | varchar(100) | Nombre de la pareja |
| pareja_nacimiento | date | Fecha de nacimiento de la pareja |
| pareja_dni | varchar(20) | DNI de la pareja |
| nivel_estudio | enum | Nivel educativo |
| hijos | JSON | Lista de hijos |
| hijos_adoptivos | JSON | Lista de hijos adoptivos |
| padres | JSON | Lista de padres |
| hermanos | JSON | Lista de hermanos |
| tutores_discapacidad | JSON | Lista de tutores con discapacidad |

### Relaciones

| Campo | Relación |
|--------|----------|
| usuario_id | usuarios.idUsuario |

---

## 6. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| mis_datos_personales.php | `vistas/paginas/datos/` | Formulario completo de datos personales |

La vista incluye:

- Datos básicos (readonly)  
- Datos de pareja  
- Datos familiares (arrays dinámicos)  
- Nivel de estudio  
- Email  

---

## 7. Flujo de trabajo del módulo

### 1. El usuario accede a `mis_datos_personales`
- Se cargan datos existentes (si los hay)
- Se renderiza el formulario

### 2. El usuario completa o edita datos
- Puede agregar múltiples hijos, padres, hermanos, etc.
- Cada grupo se envía como array → JSON

### 3. Se envía POST a `guardarDatos`
- Se normalizan arrays
- Se valida si existe registro previo
- Se inserta o actualiza
- Se muestra Toastify de éxito o error

### 4. Redirección
- Vuelve a `mis_datos_personales&id=XX`

---

## 8. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| JSON válido | Todos los campos familiares deben ser JSON válido |
| Un registro por usuario | `usuario_id` es único en la tabla |
| Nivel de estudio | Debe coincidir con el ENUM |
| Fechas | pareja_nacimiento debe ser date válido |
| Seguridad | Solo el usuario dueño puede editar sus datos |

---

## 9. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| JSON inválido | Arrays enviados como strings incorrectos | Usar `normalizarArray()` |
| No guarda datos | usuario_id vacío | Verificar hidden input en formulario |
| Vista en blanco | Falta permiso | Revisar `Auth::check()` |
| Datos duplicados | Insertar en vez de actualizar | Verificar `buscarPorUsuario()` |

---

## 10. Mejoras futuras sugeridas

- Validación de DNI y fechas  
- Subida de documentos (DNI, certificados)  
- Historial de cambios  
- Campos adicionales (domicilio, teléfonos, emergencias)  
- Autocompletado de parentescos  

---

