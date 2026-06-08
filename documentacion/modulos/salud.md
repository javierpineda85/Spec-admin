# # Módulo: SALUD

## 1. Descripción general

El módulo **Salud** permite que cada usuario registre y mantenga su información médica relevante, incluyendo:

- Enfermedades crónicas  
- Medicación  
- Grupo sanguíneo  
- Obra social  
- Beneficiario y número de afiliado  
- Vigencia de la obra social  

Este módulo es utilizado principalmente por:

- El propio usuario (autogestión)  
- Supervisores o administrativos (según permisos)  
- Áreas de RRHH o Seguridad e Higiene  

La información es **confidencial** y se protege mediante permisos específicos.

---

# 2. Rutas del módulo

Estas rutas deben estar en:

```
/rutas/rutas_salud.php
```

### ✔ Rutas recomendadas

```php
<?php

// Vista principal de salud del usuario
if (isset($_GET['r']) && $_GET['r'] === 'mi_salud') {
    SaludController::vistaMiSalud();
    return;
}

// Guardar datos de salud
if (isset($_GET['r']) && $_GET['r'] === 'guardar_salud') {
    SaludController::guardarSalud();
    return;
}
```

---

# 3. Controladores involucrados

### **SaludController**

| Método | Descripción |
|--------|-------------|
| `vistaMiSalud()` | Muestra el formulario con los datos de salud del usuario. |
| `guardarSalud()` | Inserta o actualiza los datos de salud. |

---

## ✔ vistaMiSalud()

```php
public static function vistaMiSalud()
{
    Auth::check('salud', 'vistaMiSalud');
    require 'vistas/paginas/datos/mi_salud.php';
}
```

### Comportamiento

- Valida permisos  
- Carga la vista `mi_salud.php`  
- La vista debe consultar el modelo para mostrar datos existentes  

---

## ✔ guardarSalud()

Flujo:

1. Valida permisos  
2. Recibe datos del formulario  
3. Construye array `$datos` con todos los campos  
4. Verifica si ya existe un registro para el usuario:

```php
$existe = ModeloSalud::buscarPorUsuario($usuario_id);
```

5. Si existe → **actualiza**  
6. Si no existe → **inserta**  
7. Muestra Toastify de éxito o error  
8. Redirige a `?r=mi_salud`  

---

# 4. Modelo involucrado

### **ModeloSalud**

| Método | Descripción |
|--------|-------------|
| `buscarPorUsuario()` | Devuelve datos de salud del usuario. |
| `insertar()` | Inserta un nuevo registro. |
| `actualizar()` | Actualiza datos existentes. |
| `bindCampos()` | Método interno para bindear parámetros. |

---

## ✔ buscarPorUsuario()

Consulta:

```sql
SELECT * FROM salud WHERE usuario_id = :uid LIMIT 1
```

Devuelve:

- Array asociativo con los datos  
- `false` si no existe  

---

## ✔ insertar()

Inserta:

- usuario_id  
- enfermedad_cronica  
- medicacion  
- grupo_sanguineo  
- tiene_obra_social  
- obra_social_nombre  
- beneficiario  
- nro_afiliado  
- vigencia_obra_social  

---

## ✔ actualizar()

Actualiza los mismos campos excepto el ID.

---

# 5. Tabla de base de datos

### Tabla: `salud`

```sql
CREATE TABLE `salud` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `enfermedad_cronica` text,
  `medicacion` text,
  `grupo_sanguineo` varchar(10),
  `tiene_obra_social` tinyint(1),
  `obra_social_nombre` varchar(100),
  `beneficiario` varchar(100),
  `nro_afiliado` varchar(50),
  `vigencia_obra_social` date,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
);
```

### Campos

| Campo | Descripción |
|--------|-------------|
| usuario_id | Relación con usuarios |
| enfermedad_cronica | Texto libre |
| medicacion | Texto libre |
| grupo_sanguineo | A+, O-, etc. |
| tiene_obra_social | 0/1 |
| obra_social_nombre | Nombre de la obra social |
| beneficiario | Nombre del titular |
| nro_afiliado | Número de afiliado |
| vigencia_obra_social | Fecha de vencimiento |

---

# 6. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| mi_salud.php | `vistas/paginas/datos/` | Formulario de salud del usuario |

La vista debe:

- Mostrar datos existentes  
- Permitir editar  
- Enviar POST a `?r=guardar_salud`  

---

# 7. Flujo de trabajo del módulo

### 1. Usuario accede a `?r=mi_salud`
- Se cargan datos existentes  
- Se muestra formulario  

### 2. Usuario edita y guarda
- Se valida permiso  
- Se inserta o actualiza registro  

### 3. Se muestra Toastify
- Éxito o error  

---

# 8. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| Permisos | Requiere `Auth::check('salud', ...)` |
| Un registro por usuario | Se actualiza si ya existe |
| Datos sensibles | No se muestran a otros usuarios sin permiso |
| Obra social | `tiene_obra_social` controla campos adicionales |

---

# 9. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| No se guardan datos | POST incompleto | Revisar formulario |
| Datos duplicados | No debería ocurrir | Revisar integridad |
| No carga vista | Falta permiso | Revisar Auth |
| Vigencia inválida | Formato incorrecto | Usar input type="date" |

---

# 10. Mejoras futuras sugeridas

- Adjuntar certificados médicos  
- Historial de medicación  
- Alertas por vencimiento de obra social  
- Integración con emergencias médicas  
- Exportación para RRHH  

---

