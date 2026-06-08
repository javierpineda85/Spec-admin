Perfecto, Javier.  
Con lo que me pasaste ya puedo generar **el archivo completo del módulo NOTICIAS**, y además dejarlo preparado para que en el futuro pueda manejar **el newsletter interno de la empresa**, que claramente es la evolución natural de este módulo.

Este archivo está **listo para copiar/pegar** en:

```
/documentacion/modulos/noticias.md
```

---

# 📘 **noticias.md — Documentación del Módulo NOTICIAS**

---

# # Módulo: NOTICIAS

## 1. Descripción general

El módulo **Noticias** es un módulo liviano que actualmente cumple una única función:

- Mostrar los **cumpleaños del mes** de todos los usuarios activos del sistema.

Este módulo está pensado para:

- Publicar información interna  
- Mostrar novedades en el panel administrativo  
- Servir como base para un futuro **newsletter corporativo**  

Actualmente solo tiene una vista y una consulta SQL simple, pero está diseñado para crecer.

---

## 2. Rutas del módulo

Estas rutas deben estar en:

```
/rutas/rutas_noticias.php
```

### ✔ Rutas actuales

```php
<?php

if (isset($_GET['r']) && $_GET['r'] === 'cumpleanos') {
    NoticiasController::vistaCumple();
    return;
}
```

> Si en el futuro agregás newsletter, se agregarán nuevas rutas aquí.

---

## 3. Controladores involucrados

### **NoticiasController**

| Método | Descripción |
|--------|-------------|
| `vistaCumple()` | Muestra los cumpleaños del mes actual. |

---

### ✔ vistaCumple()

Flujo:

1. Valida permisos:  
   `Auth::check('noticias', 'vistaCumple')`

2. Obtiene el mes actual:

```php
$mesActual = date('m');
```

3. Consulta todos los usuarios cuyo cumpleaños cae en ese mes:

```sql
SELECT u.nombre, u.apellido, r.nombre AS rol, u.f_nac
FROM usuarios u
JOIN roles r ON u.rol_id = r.id
WHERE MONTH(u.f_nac) = ?
ORDER BY DAY(u.f_nac)
```

4. Renderiza la vista:

```
vistas/paginas/admin/noticias/cumpleanos.php
```

---

## 4. Modelos involucrados

Este módulo **no utiliza un modelo dedicado**.  
La consulta se realiza directamente desde el controlador usando:

```php
$db = new Conexion;
$db->consultas(...)
```

---

## 5. Tablas de base de datos utilizadas

### Tabla: `usuarios`

El módulo utiliza:

- nombre  
- apellido  
- f_nac  
- rol_id  

Y hace JOIN con:

### Tabla: `roles`

Para obtener:

- nombre del rol  

---

## 6. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| cumpleanos.php | `vistas/paginas/admin/noticias/` | Lista de cumpleaños del mes |

La vista suele mostrar:

- Nombre y apellido  
- Rol  
- Fecha de nacimiento  
- Ordenado por día del mes  

---

## 7. Flujo de trabajo del módulo

### 1. Usuario accede a `?r=cumpleanos`
- Se valida permiso  
- Se obtiene el mes actual  
- Se consultan los usuarios que cumplen años  
- Se renderiza la vista  

### 2. La vista muestra:
- Lista ordenada por día  
- Nombre completo  
- Rol  
- Fecha  

---

## 8. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| Permisos | Requiere `Auth::check('noticias', 'vistaCumple')` |
| Usuarios activos | No se filtra por activo, pero se recomienda hacerlo |
| Orden | Ordenado por día del mes |

---

## 9. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| Lista vacía | No hay cumpleaños este mes | No es un error |
| Vista en blanco | Falta permiso | Revisar Auth |
| Error SQL | roles o usuarios sin datos | Revisar integridad |

---

## 10. Mejoras futuras sugeridas (Newsletter)

Este módulo está claramente pensado para crecer.  
Las mejoras recomendadas:

### ✔ 1. Newsletter interno
- Publicar noticias internas  
- Comunicados de dirección  
- Cambios operativos  
- Reconocimientos  
- Cumpleaños del mes  
- Nuevos ingresos  

### ✔ 2. Editor de noticias
- Crear, editar y eliminar noticias  
- Adjuntar imágenes  
- Categorías (RRHH, Operaciones, Dirección)

### ✔ 3. Notificaciones
- Enviar newsletter por email  
- Notificar en el panel principal  
- Integración con Alertas

### ✔ 4. Historial de noticias
- Archivo mensual  
- Buscador  
- Filtros por categoría  

### ✔ 5. API para app móvil
- Endpoint JSON para mostrar noticias en la app

---

