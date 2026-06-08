# 📘 **MANUAL TÉCNICO PARA DESARROLLADORES**

> Destinado a: Programadores, arquitectos, analistas y personal técnico encargado del mantenimiento y evolución del sistema.

---

# 1. Introducción técnica

El sistema está desarrollado en **PHP 7+**, con arquitectura **MVC**, base de datos **MySQL**, y vistas HTML con componentes reutilizables.

Se prioriza:

- Simplicidad  
- Escalabilidad  
- Modularidad  
- Seguridad  
- Mantenibilidad  

Cada módulo está completamente documentado en `/documentacion/modulos/`.

---

# 2. Arquitectura general

## 2.1. Patrón MVC

```
/controladores   → Lógica de negocio
/modelos         → Acceso a base de datos
/vistas          → Renderizado HTML
/rutas           → Enrutamiento manual
```

## 2.2. Flujo de ejecución

1. `index.php` recibe la petición  
2. El router identifica `?r=...`  
3. Llama al controlador correspondiente  
4. El controlador usa modelos para obtener datos  
5. Renderiza una vista  
6. Devuelve HTML al navegador  

---

# 3. Estructura del proyecto

```
/controladores
/modelos
/vistas
/rutas
/documentacion
/img
/uploads
/libraries
```

### Carpetas clave

| Carpeta | Descripción |
|--------|-------------|
| `/controladores` | Lógica de cada módulo |
| `/modelos` | Acceso a BD, consultas SQL |
| `/vistas` | HTML + PHP |
| `/rutas` | Enrutamiento manual |
| `/libraries` | Librerías externas (phpqrcode, etc.) |
| `/uploads` | Archivos subidos por usuarios |
| `/img` | Imágenes del sistema |

---

# 4. Base de datos

## 4.1. Motor

- **MySQL / MariaDB**
- Codificación: `utf8mb4_general_ci`
- Engine: `MyISAM` (actual)  
  > Recomendación futura: migrar a **InnoDB** para integridad referencial.

## 4.2. Convenciones

- Tablas en **singular**  
- Clave primaria: `id` o `idNombreTabla`  
- Campos booleanos: `0/1`  
- Campos de estado: `activo`, `status`, `resetPass`  
- Fechas: `date`, `timestamp`  

## 4.3. Relaciones principales

- `usuarios.rol_id → roles.id`  
- `turnos.usuario_id → usuarios.idUsuario`  
- `uniformes.usuario_id → usuarios.idUsuario`  
- `uniforme_entregas.usuario_id → usuarios.idUsuario`  
- `uniforme_devoluciones.entrega_id → uniforme_entregas.id`  
- `rondas.objetivo_id → objetivos.idObjetivo`  

---

# 5. Control de acceso (Auth)

Cada acción del sistema está protegida por:

```php
Auth::check('modulo', 'accion');
```

Esto valida:

- Usuario logueado  
- Rol asignado  
- Permisos del rol  
- Nivel jerárquico  

Los permisos se administran desde:

```
/rutas/rutas_roles.php
```

---

# 6. Manejo de archivos

Se utiliza:

```
ControladorArchivos::guardarArchivo()
```

Características:

- Normaliza nombres  
- Evita espacios  
- Guarda en carpetas específicas  
- Devuelve ruta relativa  
- Maneja errores de subida  

Archivos comunes:

- `img/perfil/`  
- `img/repriv/`  
- `uploads/docs/`  

---

# 7. Notificaciones (Toastify)

El sistema usa:

```
ToastifyController::success()
ToastifyController::error()
ToastifyController::warning()
```

Para:

- Confirmaciones  
- Errores  
- Advertencias  

---

# 8. Sesiones

El sistema usa `$_SESSION` para:

- Datos del usuario logueado  
- Permisos cacheados  
- Turnos cargados temporalmente  
- QR generados  
- Filtros de búsqueda  

Reglas:

- Siempre validar `session_status()`  
- Limpiar sesiones temporales después de commit  
- No almacenar objetos grandes  

---

# 9. Rutas del sistema

Las rutas están en:

```
/rutas/
```

Cada archivo contiene:

- Condiciones `if (isset($_GET['r']) && ...)`
- Llamadas a controladores
- Separación por módulo

El mapa completo está en `/documentacion/rutas.md`.

---

# 10. Estándares de desarrollo

## 10.1. PHP

- PSR-12 (adaptado)  
- Clases en PascalCase  
- Métodos en camelCase  
- Variables en snake_case  
- Evitar lógica en vistas  
- Evitar SQL en controladores  

## 10.2. SQL

- Consultas preparadas  
- Evitar SELECT *  
- Manejar errores con `try/catch`  
- Transacciones para operaciones múltiples  

## 10.3. Seguridad

- Contraseñas con `password_hash()`  
- Validación de archivos subidos  
- Sanitización de inputs  
- Permisos por rol  
- Evitar exponer rutas internas  

---

# 11. Transacciones

Se usa:

```php
$db = Conexion::conectar();
if (!$db->inTransaction()) $db->beginTransaction();
```

Reglas:

- Siempre hacer commit al final  
- Rollback ante cualquier excepción  
- No mezclar transacciones anidadas  

---

# 12. Módulos principales (visión técnica)

## 12.1. Usuarios

- CRUD completo  
- Manejo de imágenes  
- Bajas y reactivación  
- Reset de contraseña  
- Integración con roles  

## 12.2. Roles y permisos

- Roles con niveles  
- Permisos por acción  
- Rol Programador protegido  

## 12.3. Turnos

- Carga masiva desde sesión  
- Búsqueda por rango  
- Búsqueda por vigilador  
- Integración con feriados  

## 12.4. Rondas y QR

- Generación de QR  
- Escaneo  
- Estados: draft, active, inactive  

## 12.5. Uniformes

- Talles  
- Catálogo de ítems  
- Entregas múltiples  
- Devoluciones  
- Comprobantes  

## 12.6. Salud

- Datos médicos  
- Obra social  
- Información sensible  

---

# 13. Logs y auditoría

Actualmente:

- Bajas registran motivo y usuario  
- Rondas registran escaneos  
- Turnos registran asignaciones  

Recomendación futura:

- Log global de acciones  
- Auditoría de cambios de usuario  
- Auditoría de permisos  

---

# 14. Errores comunes y soluciones

| Problema | Causa | Solución |
|---------|--------|----------|
| No carga imagen | Permisos de carpeta | Dar permisos 755/775 |
| No guarda usuario | Falta campo obligatorio | Revisar POST |
| No aparecen permisos | Rol sin permisos asignados | Revisar tabla role_permissions |
| QR no se genera | Sesión vacía | Revisar controlador |
| Turnos duplicados | Carga repetida | Limpiar sesión antes |

---

# 15. Roadmap técnico sugerido

- Migrar a InnoDB  
- Implementar ORM ligero  
- Crear API REST para app móvil  
- Implementar logs de auditoría  
- Mejorar sistema de permisos (RBAC avanzado)  
- Migrar vistas a un motor templating (Twig/Blade)  
- Implementar colas para tareas pesadas  

---
