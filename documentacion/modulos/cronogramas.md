# Módulo: CRONOGRAMAS

## 1. Descripción general

El módulo **Cronogramas** es uno de los más complejos del sistema.  
Permite:

- Crear cronogramas mensuales por objetivo  
- Cargar turnos para vigiladores y referentes  
- Mantener la escala **4×2** automáticamente  
- Validar horas mensuales por usuario  
- Validar coexistencia de turnos  
- Pre-cargar cronogramas del mes anterior  
- Generar simulaciones vacías  
- Calcular jornadas reales trabajadas  
- Generar reportes diarios y por horas  

Este módulo es utilizado por:

- Supervisores  
- Administrativos  
- Coordinadores operativos  

---

## 2. Rutas del módulo

| Ruta | Método | Descripción |
|------|--------|-------------|
| `crear_cronograma` | GET | Vista para crear un cronograma mensual. |
| `listado_cronogramas` | GET | Listado general de cronogramas. |
| `listado_porVigilador` | GET | Cronogramas filtrados por vigilador. |
| `listado_resumen_diario` | GET | Vista de resumen diario de jornadas. |
| `reporte_porVigilador` | GET | Vista de horas por vigilador. |
| `reporte_porHoras` | GET | Vista de horas por objetivo. |
| `buscar_cronogramas` | GET | Búsqueda por rango de fechas. |
| `buscar_porVigilador` | GET | Búsqueda por vigilador. |
| `buscar_resumen_diario` | POST | Procesa resumen diario. |
| `buscar_resumen_horas` | POST | Procesa resumen de horas. |
| `buscar_resumen_horas_por_vigilador` | POST | Horas por vigilador. |
| `validar_turno_global` | GET | Valida la franja contra otros objetivos; requiere sesión y permiso. |

---

## 3. Controladores involucrados

### **ControladorCronogramas**

| Método | Descripción |
|--------|-------------|
| `ctrGuardarCronograma()` | Guarda un cronograma mensual completo. |
| `esLicencia()` | Determina si un código es licencia. |
| `generarSimulacionVacia()` | Genera cronograma 4×2 desde cero. |
| `precargarCronogramaSiExiste()` | Carga mes actual o anterior. |
| `continuar4x2DesdeTurnosAnteriores()` | Mantiene continuidad real del 4×2. |
| `armarPostSimuladoDesdeTurnos()` | Convierte turnos BD → POST simulado. |
| `crtBuscarResumenDiario()` | Calcula jornadas diarias. |
| `crtBuscarResumenHoras()` | Calcula horas reales trabajadas. |

---

## 4. Modelos involucrados

### **ModeloCronograma**

| Método | Descripción |
|--------|-------------|
| `mdlSubirCrono()` | Guarda imagen del cronograma. |
| `mdlResumenDiarioJornadas()` | Calcula jornadas diurnas/nocturnas. |

### **ModeloTurnos** (referenciado)

| Método | Descripción |
|--------|-------------|
| `mdlGuardarTurno()` | Guarda un turno individual. |
| `mdlEliminarTurnosPorMes()` | Elimina turnos previos del mes. |
| `mdlBuscarTurnosPorMes()` | Obtiene turnos del mes. |

### Reglas compartidas

`CronogramaReglas` clasifica códigos, calcula horas y compara intervalos. `ModeloCronogramaValidacion`
resuelve el horario desde el puesto y la rotación. Si una jornada no tiene una franja inequívoca, el
sistema la deja pendiente y no permite guardarla en otro objetivo hasta completar esa configuración.

---

## 5. Tablas de base de datos

### Tabla: `cronogramas`

```sql
CREATE TABLE `cronogramas` (
  `idCrono` int NOT NULL AUTO_INCREMENT,
  `objetivo_id` int NOT NULL,
  `imgCrono` varchar(100) NOT NULL,
  `fechaCarga` date NOT NULL,
  PRIMARY KEY (`idCrono`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
```

### Tabla relacionada: `turnos` (inferida del código)

| Campo | Tipo | Descripción |
|--------|------|-------------|
| idTurno | int | Identificador |
| usuario_id | int | Vigilador o referente |
| objetivo_id | int | Objetivo asignado |
| fecha | date | Día del turno |
| rol | varchar | Vigilador / Referente |
| tipo_turno | varchar | Normal / Licencia |
| codigo_turno | varchar | D, N, F, GP/D, etc. |

La unicidad es `(usuario_id, objetivo_id, fecha)`. Esto permite registrar al mismo usuario en dos
objetivos el mismo día únicamente cuando las reglas de intervalos determinan que no se superponen.
La migración idempotente está en `scripts/migracion_cronogramas_2026_09.php`.

### Tabla relacionada: `marcaciones_servicio`

Usada para calcular horas reales.

| Campo | Tipo | Descripción |
|--------|------|-------------|
| vigilador_id | int | Usuario |
| objetivo_id | int | Objetivo |
| tipo_evento | entrada/salida | Marca de jornada |
| fecha_hora | datetime | Fecha/hora del evento |

---

## 6. Vistas del módulo

| Vista | Ubicación | Descripción |
|--------|-----------|-------------|
| crear_cronograma.php | `vistas/paginas/cronogramas/` | Formulario principal |
| listado_cronogramas.php | `vistas/paginas/cronogramas/` | Listado general |
| por_vigilador.php | `vistas/paginas/cronogramas/` | Filtro por vigilador |
| resumen_diario.php | `vistas/paginas/cronogramas/` | Jornadas diarias |
| horas_vigilador.php | `vistas/paginas/cronogramas/` | Horas por vigilador |
| horas_objetivo.php | `vistas/paginas/cronogramas/` | Horas por objetivo |

---

## 7. Flujo de trabajo del módulo

### 🔹 1. Selección de objetivo y mes
El usuario selecciona:

- Objetivo  
- Mes (YYYY-MM)

El sistema:

- Busca si existe cronograma del mes actual  
- Si no, busca mes anterior  
- Si no, genera simulación vacía 4×2  

---

### 🔹 2. Render del cronograma (JS)
El archivo `cronograma.js`:

- Genera tabla dinámica  
- Pinta feriados  
- Clasifica códigos  
- Calcula horas por usuario  
- Valida coexistencia de turnos  
- Valida conflictos globales vía AJAX  

---

### 🔹 3. Guardado del cronograma
`ctrGuardarCronograma()`:

1. Valida objetivo y mes  
2. Comprueba que la tabla enviada esté completa y corresponda al objetivo y mes seleccionados
3. Procesa vigiladores y referentes, incluidos los históricos del cronograma existente
4. Clasifica códigos (D, N, horas, francos, guardias pasivas, licencias y referencias)
5. Valida solapamientos por intervalo contra otros objetivos
6. Reemplaza el mes dentro de una transacción; ante cualquier error conserva el cronograma anterior
7. Informa como aviso las horas mensuales fuera del rango de referencia 200–240
8. Redirige a la misma vista  

La tabla se envía como un único JSON para no quedar truncada por `max_input_vars` en objetivos con
muchas personas. Vaciar la tabla en pantalla no borra datos; un cronograma vacío es rechazado.

---

### 🔹 4. Reportes

#### **Resumen diario**
- Cuenta jornadas diurnas y nocturnas por día.

#### **Resumen de horas**
- Empareja entradas y salidas reales  
- Ajusta horas según turno teórico  
- Calcula horas por objetivo  

---

## 8. Validaciones y reglas de negocio

| Regla | Descripción |
|--------|-------------|
| Escala 4×2 | D, D, N, N, F, F |
| Continuidad | Solo se continúa 4×2 cuando el mes anterior está completo y la fase final es inequívoca |
| Horas mensuales | 200–240 hs por usuario |
| Coexistencia | Se permiten jornadas consecutivas; se rechazan intervalos superpuestos, incluso entre meses |
| Referencias | Pueden coexistir con cualquier turno |
| GP/D y GP/N | Conservan el cálculo actual de 12 horas y no representan presencia para cruces |
| Licencias | Se tratan como franco para continuidad |
| Horario desconocido | Bloquea el cruce como pendiente hasta configurar una franja inequívoca |

---

## 9. Errores comunes y soluciones

| Error | Causa | Solución |
|--------|--------|----------|
| Turno duplicado | Ya existe turno en BD | Revisar cronograma previo |
| Horas fuera de rango | <200 o >240 | Ajustar turnos |
| No carga cronograma | Falta objetivo o mes | Validar formulario |
| No continúa 4×2 | Mes anterior incompleto o código sin fase | Completar manualmente la fila indicada |
| Conflicto global | Las franjas del usuario se superponen entre objetivos | Ajustar uno de los turnos |
| Validación pendiente | Falta un único horario de puesto aplicable | Configurar puesto, rotación u horario |

---

## 10. Mejoras futuras sugeridas

- Paginación en reportes  
- Editor visual más rápido  
- Exportación a PDF/Excel  
- Historial de cronogramas por usuario  
- Auditoría de cambios  
- Integración con IA para sugerir turnos óptimos  

---

