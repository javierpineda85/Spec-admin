## **Índice General**

### **1. Introducción**
- Objetivo del sistema
- Alcance funcional
- Arquitectura general
- Convenciones de desarrollo

### **2. Módulos funcionales**
- **Usuarios**
- **Roles y Permisos**
- **Turnos**
- **Puestos**
- **Rondas**
- **QR**
- **Uniformes**
- **Salud**
- **Alertas**
- **Objetivos**
- **Marcaciones**

### **Cambios recientes relevantes**
- La portada ahora es sensible al rol: supervisores/admin/gerencia ven alertas de Hombre Vivo y guardias en servicio, mientras que vigiladores/referentes ven directivas en modo lectura.
- Hombre Vivo quedó configurado por turno diurno y nocturno, manteniendo la tolerancia fija de 3 minutos.
- Las alertas del sistema quedaron integradas con notificaciones push web para los usuarios activos.
- El listado general de uniformes quedó expuesto desde Administración y conserva el detalle por talle y su resumen.

### **3. Módulos transversales**
- Autenticación
- Control de acceso (Auth)
- Manejo de archivos
- Toastify (notificaciones)
- Conexión a BD
- Helpers y utilidades

### **4. Base de datos**
- Diagrama ER
- Tablas por módulo
- Relaciones
- Reglas de integridad

### **5. Arquitectura**
- Estructura MVC
- Flujo de request → controlador → modelo → vista
- Rutas
- Sesiones
- Seguridad

### **6. APIs internas**
- Endpoints AJAX
- Respuestas JSON
- Validaciones
- Errores comunes

### **7. Vistas**
- Estructura de plantillas
- Componentes reutilizables
- Estilos

### **8. Anexos**
- Convenciones de código
- Estándares de commit
- Checklist de QA
- Roadmap

---

# 📘 **2. README PRINCIPAL DEL SISTEMA**

Este README está pensado para:

- Nuevos desarrolladores
- Documentación interna
- Auditorías
- Onboarding

---

# 🏢 **Sistema de Gestión Operativa — Documentación General**

## **Descripción general**

Este sistema integra todos los procesos operativos de una empresa de seguridad:

- Gestión de personal
- Turnos y cronogramas
- Rondas y QR
- Uniformes
- Salud del empleado
- Roles y permisos
- Objetivos y puestos
- Rotaciones
- Marcaciones
- Reportes operativos

Está desarrollado en **PHP 7+**, con arquitectura **MVC**, base de datos **MySQL**, y vistas HTML con componentes reutilizables.

---

## **Características principales**

- Autenticación segura con contraseñas hasheadas
- Control de acceso granular por permisos
- Gestión completa de usuarios
- Administración de roles y niveles jerárquicos
- Turnos por objetivo, vigilador y rango
- Rotaciones automáticas (round-robin)
- Rondas con QR dinámicos
- Registro de entregas y devoluciones de uniforme
- Datos de salud del empleado
- Auditoría de bajas
- Notificaciones con Toastify
- Manejo de archivos (perfil, repriv, documentos)

---

## **Tecnologías**

- **Backend:** PHP 7+
- **Base de datos:** MySQL
- **Frontend:** HTML5, CSS3, JS
- **Librerías:**
  - phpqrcode
  - Toastify
- **Arquitectura:** MVC
- **Seguridad:** Auth + permisos por rol

---

## **Estructura del proyecto**

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

---

## **Instalación**

1. Clonar repositorio
2. Configurar conexión en `modelos/conexion.php`
3. Importar base de datos
4. Crear usuario inicial (Programador)
5. Iniciar sesión

---

## **Documentación por módulo**

Cada módulo tiene su archivo en:

```
/documentacion/modulos/
```

---

# 📘 **3. MAPA DE MÓDULOS Y DEPENDENCIAS**

Este mapa muestra cómo se relacionan los módulos entre sí.

---

## **Módulos principales**

```
Usuarios
 ├── Roles
 │    └── Permisos
 ├── Salud
 ├── Uniformes
 │    ├── Items
 │    ├── Entregas
 │    └── Devoluciones
 ├── Turnos
 │    ├── Cronogramas
 │    └── Feriados
 ├── Puestos
 │    ├── Turnos del puesto
 │    ├── Rotaciones
 │    └── Log de rotaciones
 ├── Rondas
 │    ├── QR
 │    └── Escaneos
 └── Objetivos
      └── Asignaciones
```

---

## **Dependencias transversales**

```
Auth → controla acceso a todos los módulos
Toastify → notificaciones globales
ControladorArchivos → manejo de imágenes
Conexion → acceso a BD
```

---

# 📘 **4. DIAGRAMA DE ARQUITECTURA (LÓGICO)**

Representación conceptual del flujo MVC:

```
┌──────────────────────────┐
│        Navegador         │
└─────────────┬────────────┘
              │ Request
              ▼
┌──────────────────────────┐
│         index.php         │
└─────────────┬────────────┘
              │ Router
              ▼
┌──────────────────────────┐
│       Controladores       │
└─────────────┬────────────┘
              │ Lógica
              ▼
┌──────────────────────────┐
│         Modelos           │
└─────────────┬────────────┘
              │ SQL
              ▼
┌──────────────────────────┐
│        Base de datos      │
└─────────────┬────────────┘
              │ Datos
              ▼
┌──────────────────────────┐
│          Vistas           │
└─────────────┬────────────┘
              │ HTML
              ▼
┌──────────────────────────┐
│        Navegador         │
└──────────────────────────┘
```

---

# 📘 **5. DIAGRAMA DE ARQUITECTURA (MÓDULOS)**

```
Usuarios ─────┐
               ├── Roles ─── Permisos
               ├── Turnos ── Cronogramas ── Rotaciones ── Puestos
               ├── Rondas ── QR ── Escaneos
               ├── Uniformes ── Items ── Entregas ── Devoluciones
               ├── Salud
               └── Objetivos ── Asignaciones
```

---

