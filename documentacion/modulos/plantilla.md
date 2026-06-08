# # Módulo: PLANTILLAS

## 1. Descripción general

El módulo **Plantillas** es responsable de cargar:

- La **plantilla principal** del sistema (layout general)
- La **vista de login** cuando el usuario no está autenticado

Este módulo no maneja datos, no interactúa con la base de datos y no tiene lógica de negocio.  
Su única función es **incluir la vista correcta** según el contexto.

Es uno de los módulos más simples, pero es esencial porque:

- Define la estructura HTML base  
- Carga el menú, header, footer y contenedor de contenido  
- Es el punto de entrada del sistema después del login  

---

# 2. Rutas del módulo

Estas rutas suelen estar en:

```
/rutas/rutas_plantilla.php
```

### ✔ Rutas recomendadas

```php
<?php

// Plantilla principal
if (!isset($_GET['r']) || $_GET['r'] === 'inicio') {
    $plantilla = new PlantillaController();
    $plantilla->crtGetPlantilla();
    return;
}

// Vista de login
if (isset($_GET['r']) && $_GET['r'] === 'login') {
    $plantilla = new PlantillaController();
    $plantilla->crtGetLogin();
    return;
}
```

> En la mayoría de los sistemas, la plantilla se carga desde `index.php` directamente.

---

# 3. Controladores involucrados

### **PlantillaController**

| Método | Descripción |
|--------|-------------|
| `crtGetPlantilla()` | Carga la plantilla principal del sistema. |
| `crtGetLogin()` | Carga la vista de login. |

---

## ✔ crtGetPlantilla()

```php
public function crtGetPlantilla()
{
    include "vistas/plantilla.php";
}
```

Carga:

```
vistas/plantilla.php
```

Esta vista contiene:

- Header  
- Sidebar  
- Navbar  
- Contenedor principal  
- Footer  
- Inclusión dinámica de módulos según `$_GET['r']`  

---

## ✔ crtGetLogin()

```php
public function crtGetLogin()
{
    include "vistas/login.php";
}
```

Carga:

```
vistas/login.php
```

Esta vista contiene:

- Formulario de login  
- Estilos propios  
- No incluye menú ni estructura general  

---

# 4. Modelos involucrados

Este módulo **no utiliza modelos**.  
No accede a la base de datos.

---

# 5. Tablas de base de datos

Este módulo **no utiliza tablas**.

---

# 6. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| plantilla.php | `vistas/` | Layout principal del sistema |
| login.php | `vistas/` | Formulario de login |

---

# 7. Flujo de trabajo del módulo

### 1. Usuario accede al sistema
- `index.php` instancia `PlantillaController`
- Se carga `plantilla.php`

### 2. Usuario no autenticado
- Se redirige a `?r=login`
- Se carga `login.php`

### 3. Usuario autenticado
- `plantilla.php` incluye el módulo solicitado según `$_GET['r']`

---

# 8. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| No requiere permisos | Es un módulo base |
| No usa Auth | La plantilla se carga siempre |
| No procesa POST | Solo incluye vistas |
| No usa modelos | No accede a BD |

---

# 9. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| Vista en blanco | Falta include de plantilla.php | Revisar rutas |
| No carga módulos | Error en router | Revisar rutas en `RutasController` |
| Login no aparece | Falta ruta `?r=login` | Agregar rutas_plantilla.php |

---

# 10. Mejoras futuras sugeridas

- Sistema de plantillas múltiples (tema claro/oscuro)  
- Plantillas por rol (operativo vs administrativo)  
- Layout responsive avanzado  
- Carga dinámica de módulos vía AJAX  
- Integración con componentes reutilizables (cards, widgets, dashboards)  

---


