# # Módulo: TURNOS

## 1. Descripción general

El módulo **Turnos** administra:

- La carga masiva de turnos (planillas)  
- La consulta de turnos por rango de fechas  
- La consulta de turnos por vigilador  
- La integración con feriados  
- La compatibilidad con cronogramas y puestos  
- La validación de turnos para rotaciones y marcaciones  

Este módulo es uno de los pilares operativos del sistema.

---

# 2. Rutas del módulo

Estas rutas deben estar en:

```
/rutas/rutas_turnos.php
```

### ✔ Rutas recomendadas

```php
<?php

// Registrar planilla completa
if (isset($_GET['r']) && $_GET['r'] === 'registrar_planilla') {
    ControladorTurnos::ctrRegistrarPlanilla();
    return;
}

// Buscar turnos por rango
if (isset($_GET['r']) && $_GET['r'] === 'buscar_turnos_rango') {
    ControladorTurnos::crtBuscarTurnosPorRango();
    return;
}

// Buscar turnos por vigilador
if (isset($_GET['r']) && $_GET['r'] === 'buscar_turnos_vigilador') {
    ControladorTurnos::crtBuscarPorVigilador();
    return;
}
```

---

# 3. Controladores involucrados

### **ControladorTurnos**

| Método | Descripción |
|--------|-------------|
| `ctrRegistrarPlanilla()` | Guarda todos los turnos cargados en sesión. |
| `crtBuscarTurnosPorRango()` | Busca turnos por objetivo y rango de fechas. |
| `crtBuscarPorVigilador()` | Busca turnos por vigilador y rango de fechas. |

---

# 4. Submódulo: Registrar planilla

## ✔ ctrRegistrarPlanilla()

Este método guarda **todos los turnos cargados previamente en `$_SESSION['turnos']`**.

### Flujo:

1. Valida permisos  
2. Verifica que exista `guardar_todos` y que haya turnos en sesión  
3. Inicia transacción  
4. Recorre cada turno y arma `$datos`:

```php
$datos = [
    "objetivo_id"   => $t['objetivo'],
    "fecha"         => $t['fecha'],
    "turno"         => $t['turno'],
    "vigilador_id"  => $t['vigilador'],
    "tipo_jornada"  => $t['tipo_jornada'],
    "is_referente"  => 1/0,
    "entrada"       => $t['entrada'],
    "salida"        => $t['salida'],
    "color"         => $t['color'],
];
```

5. Guarda cada turno con:

```php
ModeloTurnos::mdlGuardarTurno("turnos", $datos)
```

6. Si todo sale bien:
   - Commit  
   - Limpia `$_SESSION['turnos']`  
   - Toastify de éxito  
   - Redirige  

7. Si falla:
   - Rollback  
   - Toastify de error  

---

# 5. Submódulo: Buscar turnos por rango

## ✔ crtBuscarTurnosPorRango()

Usado en:

```
listado_cronogramas.php
```

### Flujo:

1. Valida permisos  
2. Guarda filtros en sesión:

```php
$_SESSION['filtros'] = [
    'objetivo' => $_POST['objetivo'],
    'desde'    => $_POST['desde'],
    'hasta'    => $_POST['hasta']
];
```

3. Obtiene turnos con:

```php
ModeloTurnos::mdlObtenerTurnosConPuestos('turnos', $_SESSION['filtros']);
```

4. Guarda resultado en sesión:

```
$_SESSION['turnos']
```

5. Redirige a `listado_cronogramas`

---

# 6. Submódulo: Buscar turnos por vigilador

## ✔ crtBuscarPorVigilador()

Usado en:

```
listado_porVigilador.php
```

### Flujo:

1. Valida permisos  
2. Recibe vigilador + rango de fechas  
3. Guarda filtros en sesión  
4. Genera array de días del rango  
5. Obtiene turnos con:

```php
ModeloTurnos::mdlObtenerTurnos('turnos', $_SESSION['filtros_vigilador']);
```

6. Ajusta campos para compatibilidad con vistas antiguas:

```php
$t['tipo_jornada'] = $t['tipo_turno'];
$t['turno'] = $t['codigo_turno'];
```

7. Consulta feriados del rango  
8. Guarda en sesión:

- `dias_rango`  
- `feriados_rango`  
- `turnos_porVigilador`  

9. Redirige a `listado_porVigilador`

---

# 7. Modelo involucrado

### **ModeloTurnos**

> *No lo pasaste aún, pero el controlador lo usa así:*

| Método | Descripción |
|--------|-------------|
| `mdlGuardarTurno()` | Inserta un turno. |
| `mdlObtenerTurnos()` | Devuelve turnos por vigilador. |
| `mdlObtenerTurnosConPuestos()` | Devuelve turnos con información de puestos. |

Si querés, cuando me pases el modelo completo, lo documento también.

---

# 8. Integración con otros módulos

### ✔ Con Puestos
- `mdlObtenerTurnosConPuestos()` devuelve puesto asignado  
- Se usa en cronogramas y reportes  

### ✔ Con Rotaciones
- Para validar si un vigilador puede ocupar un puesto en un turno  
- El módulo de rotaciones usa:

```php
mdlObtenerTurnosMesObjetivo()
```

### ✔ Con Novedades (entradas/salidas)
- Se usa para determinar si hay turno calendarizado  
- Se usa para calcular hora esperada  

### ✔ Con Feriados
- Se consulta tabla `feriados` para marcar días especiales  

---

# 9. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| listado_cronogramas.php | `vistas/paginas/turnos/` | Turnos por rango |
| listado_porVigilador.php | `vistas/paginas/turnos/` | Turnos por vigilador |
| planilla_turnos.php | `vistas/paginas/turnos/` | Carga masiva |

---

# 10. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| Un turno por vigilador por día/turno | Evita superposiciones |
| Tipo guardia → guardia pasiva | Normalización automática |
| Filtros obligatorios | objetivo/fecha o vigilador/fecha |
| Sesión | Turnos se cargan en `$_SESSION['turnos']` |
| Seguridad | Todos los métodos usan `Auth::check()` |

---

# 11. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| “Error al guardar turno” | Datos incompletos o error SQL | Revisar planilla |
| No aparecen turnos | Filtros incorrectos | Revisar POST |
| Turnos duplicados | Carga repetida | Limpiar sesión antes |
| Feriados no marcados | Rango incorrecto | Revisar fechas |

---

# 12. Mejoras futuras sugeridas

- Editor visual de turnos (drag & drop)  
- Validación automática de superposiciones  
- Exportación a Excel/PDF  
- Historial de cambios de turnos  
- Integración con IA para sugerir dotación  
- Vista semanal/mensual tipo calendario  


