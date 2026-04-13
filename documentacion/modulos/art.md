# # Módulo: ART

## 1. Descripción general

El módulo **ART** administra la información relacionada con la Aseguradora de Riesgos del Trabajo asociada a la empresa.  
Permite:

- Registrar datos de la ART  
- Editar datos existentes  
- Consultar la credencial ART del vigilador  
- Listar todas las ART registradas  

Este módulo es utilizado principalmente por:

- Administrativos  
- Recursos Humanos  
- Supervisores (solo lectura)  

---

## 2. Rutas del módulo

| Ruta | Método | Descripción |
|------|--------|-------------|
| `credencial_art` | GET | Muestra la credencial ART del usuario. |
| `listado_art` | GET | Lista todas las ART registradas. |
| `crear_art` | GET | Muestra formulario para crear una ART. |
| `editar_art` | GET | Muestra formulario para editar una ART. |
| `ctrGuardarArt` | POST | Guarda una nueva ART. |
| `ctrEditarArt` | POST | Actualiza una ART existente. |

---

## 3. Controladores involucrados

### **ArtController**

| Método | Descripción |
|--------|-------------|
| `ctrGuardarArt()` | Guarda una nueva ART en la base de datos. |
| `ctrEditarArt()` | Actualiza una ART existente. |
| `vistaCredencialArt()` | Muestra la credencial ART del usuario. |
| `vistaListadoArt()` | Muestra listado de ART. |
| `vistaCrearArt()` | Muestra formulario de creación. |
| `vistaEditarArt()` | Muestra formulario de edición. |

---

## 4. Modelos involucrados

### **ModeloArt**

| Método | Descripción |
|--------|-------------|
| `mdlGuardarArt($tabla, $datos)` | Inserta una nueva ART. |
| `mdlObtenerArtPorUsuario($tabla, $usuarioId)` | Obtiene ART asociada a un usuario. |
| `mdlObtenerArtPorId($tabla, $idArt)` | Obtiene ART por ID. |
| `mdlObtenerUltimaArt()` | Obtiene la última ART registrada. |
| `mdlEditarArt($tabla, $datos)` | Actualiza una ART existente. |

---

## 5. Tablas de base de datos

### Tabla: `art`

```sql
CREATE TABLE `art` (
  `idArt` int NOT NULL AUTO_INCREMENT,
  `razon_social` varchar(255) DEFAULT NULL,
  `cuit_empresa` varchar(11) DEFAULT NULL,
  `telefono_empresa` varchar(20) DEFAULT NULL,
  `empresa_aseguradora` varchar(255) DEFAULT NULL,
  `cuit_aseguradora` varchar(11) DEFAULT NULL,
  `nro_poliza` varchar(100) DEFAULT NULL,
  `telefono_aseguradora` varchar(20) DEFAULT NULL,
  `fecha_alta` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idArt`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
```

### Campos

| Campo | Tipo | Descripción |
|--------|------|-------------|
| idArt | int | Identificador único de la ART |
| razon_social | varchar(255) | Razón social de la empresa |
| cuit_empresa | varchar(11) | CUIT de la empresa |
| telefono_empresa | varchar(20) | Teléfono de la empresa |
| empresa_aseguradora | varchar(255) | Nombre de la ART |
| cuit_aseguradora | varchar(11) | CUIT de la ART |
| nro_poliza | varchar(100) | Número de póliza |
| telefono_aseguradora | varchar(20) | Teléfono de la ART |
| fecha_alta | timestamp | Fecha de alta automática |

### Relaciones

Este módulo **no tiene relaciones directas** con otras tablas.  
La ART es un registro global, no por usuario.

---

## 6. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| credencial_art.php | `vistas/paginas/admin/art/credencial_art.php` | Muestra credencial ART del usuario |
| listado_art.php | `vistas/paginas/admin/art/listado_art.php` | Lista todas las ART |
| crear_art.php | `vistas/paginas/admin/art/crear_art.php` | Formulario de creación |
| editar_art.php | `vistas/paginas/admin/art/editar_art.php` | Formulario de edición |

---

## 7. Flujo de trabajo del módulo

### 1. Crear ART
- El usuario accede a `crear_art`
- Completa formulario
- Se envía POST a `ctrGuardarArt`
- Se valida y guarda en BD
- Se recarga la misma vista

### 2. Editar ART
- El usuario accede a `editar_art&id=X`
- Se cargan los datos actuales
- Se envía POST a `ctrEditarArt`
- Se actualiza la BD
- Redirige a `listado_art`

### 3. Ver credencial ART
- El usuario accede a `credencial_art`
- Se muestra la última ART registrada

### 4. Listado de ART
- El usuario accede a `listado_art`
- Se muestran todas las ART registradas

---

## 8. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| Permisos | Todas las vistas requieren `Auth::check('art')` |
| Datos obligatorios | No hay campos obligatorios, pero se recomienda completar todos |
| Redirección | Después de guardar, se recarga la misma vista |
| Integridad | No se permite editar sin `idArt` válido |

---

## 9. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| No guarda ART | Datos incompletos o error SQL | Revisar campos enviados |
| No edita ART | Falta `idArt` en POST | Verificar formulario |
| Vista en blanco | Falta permiso | Revisar `Auth::check()` |
| No carga credencial | No existe ART registrada | Crear una nueva ART |

---

## 10. Mejoras futuras sugeridas

- Asociar ART a usuarios específicos  
- Historial de ART por empresa  
- Adjuntar documentos (PDF, imágenes)  
- Validación estricta de CUIT  
- Auditoría de cambios  

---
