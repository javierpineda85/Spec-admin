# 📘 **MANUAL DE ADMINISTRADOR**

> Destinado a: Programador, Dirección, Administración, Supervisores con permisos especiales.

---

# 0. Pantalla de inicio

Al ingresar al sistema, este perfil ve el panel de control con accesos rápidos y bloques operativos según permisos:

- **Alertas de Hombre Vivo**
- **Guardias en Servicio** con filtro por objetivo
- Accesos de administración, uniformes y módulos de gestión

En celular, los accesos del panel se acomodan en dos columnas para una lectura más cómoda.

---

# 1. Gestión de Usuarios

Acceso:  
**Menú → Usuarios**

### **1.1. Crear usuario**
- Completar datos personales  
- Subir foto de perfil  
- Subir repriv (opcional)  
- Seleccionar rol  
- Guardar  

El usuario queda **activo** y con **resetPass = 0** (debe cambiar contraseña).

### **1.2. Editar usuario**
Podés modificar:

- Datos personales  
- Rol  
- Imágenes  
- Estado (activo/inactivo)  
- Resetear contraseña  

### **1.3. Dar de baja**
- Marcar “Inactivo”  
- Ingresar motivo obligatorio  
- Se registra en tabla **bajas**

### **1.4. Reactivar usuario**
- Desde listado de inactivos  
- Botón “Reactivar”  

---

# 2. Roles y Permisos

Acceso:  
**Menú → Roles**

### **2.1. Crear rol**
- Nombre  
- Alias  
- Categoría (define nivel)  
- Tipo  
- Estado  

### **2.2. Editar rol**
- Cambiar nombre, alias, tipo, categoría  
- Cambiar estado  

### **2.3. Permisos**
Acceso:  
**Menú → Roles → Permisos**

- Seleccionar rol  
- Marcar/desmarcar permisos  
- Guardar  

> ⚠️ El rol **Programador** solo puede ser modificado por usuarios con nivel 99 y reservado = 1.

---

# 3. Objetivos y Puestos

Acceso:  
**Menú → Objetivos / Puestos**

### **3.1. Crear puesto**
- Nombre  
- Objetivo  
- Tipo (Fijo / Rotativo)  
- Turnos del puesto  

### **3.2. Editar puesto**
- Modificar datos  
- Cambiar turnos  
- Guardar  

### **3.3. Desactivar / Reactivar**
Soft-delete con campo `activo`.

---

# 4. Turnos

Acceso:  
**Menú → Turnos**

### **4.1. Cargar planilla**
- Se cargan turnos en `$_SESSION['turnos']`  
- Luego se guardan todos juntos  

### **4.2. Buscar por rango**
- Objetivo  
- Desde  
- Hasta  

### **4.3. Buscar por vigilador**
- Vigilador  
- Rango de fechas  
- Muestra feriados  
- Muestra turnos por fecha  

---

# 5. Rotaciones

Acceso:  
**Menú → Rotaciones**

Funciones:

- Asignar vigiladores a puestos por día  
- Validación automática de turnos  
- Swap entre vigiladores  
- Autollenado equitativo (round-robin)  
- Log de cambios  

---

# 6. Rondas y QR

Acceso:  
**Menú → Rondas**

### **6.1. Crear rondas**
- Puesto  
- Objetivo  
- Tipo  
- Orden  
- Estado = draft  

### **6.2. Generar QR**
- Se guarda en sesión  
- Se imprime desde vista especial  

### **6.3. Editar ronda**
- Actualiza datos  
- Genera nuevo QR  

### **6.4. Desactivar ronda**
- Cambia estado a inactive  

---

# 7. Uniformes

Acceso:  
**Menú → Uniformes**

### **7.1. Administrar ítems**
- Crear ítem  
- Editar ítem  
- Activar/desactivar  
- Categorías  

### **7.2. Registrar entregas**
- Múltiples ítems  
- Talle  
- Cantidad  
- Observaciones  
- Comprobante imprimible  

### **7.3. Registrar devoluciones**
- Estado  
- Observaciones  
- Fecha  

---

# 8. Salud

Acceso:  
**Menú → Salud**

- Ver datos de salud del usuario  
- Editar (según permisos)  

---

# 9. Auditoría y Seguridad

### **9.1. Bajas**
- Registro de motivo  
- Fecha  
- Usuario que dio la baja  

### **9.2. Permisos**
- Cada acción del sistema está protegida por `Auth::check()`  

### **9.3. Contraseñas**
- Hasheadas con `password_hash()`  
- Reset obligatorio si `resetPass = 0`  

---

# 10. Mantenimiento

### **10.1. Limpieza de QR**
- Eliminar PNG antiguos  
- Eliminar logs de librería  

### **10.2. Archivos**
- Control de imágenes  
- Carpeta `/uploads/docs/`  

---
