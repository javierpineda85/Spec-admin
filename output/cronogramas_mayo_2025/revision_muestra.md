# Muestra de cronogramas — mayo de 2025

Primera parte: preparación y correspondencias. No se modificó el Excel, el código funcional ni la base de datos.

## Alcance y reglas

- Se preservan los 13 cuadros, sus nóminas y los 31 días. Las referencias permanecen diferenciadas en el diccionario de códigos.
- GP/D y GP/N conservan **12 horas** por decisión del usuario. No se confunde esa suma con presencia efectiva.
- Las columnas de liquidación, numeración y totales del Excel no se trasladan a la aplicación.
- Los identificadores negativos pertenecen solo a esta muestra. No son IDs de la base ni autorizan una importación directa.
- No se asignan roles o puestos históricos por suposición; los horarios actuales no prueban los de mayo de 2025.

## Inventario

- 173 filas de nómina; 133 nombres distintos tras normalizar espacios, tildes y mayúsculas.
- 31 identidades textuales aparecen en varios cuadros; esto por sí solo no demuestra superposición.
- 5363 celdas diarias: 4019 con código y 1344 sin asignación.
- 36 códigos distintos; 25 no están disponibles en el selector actual con el catálogo local leído.

## Objetivos

| Encabezado original | Personas | Correspondencia local | Estado |
|---|---:|---|---|
| HOSPITAL NOTTI (C2) | 45 | Hospital Pediatrico Dr. Humberto Notti (ID 18) | equivalencia_por_nombre |
| HOSPITAL PERRUPATO (C58) | 36 | HOSPITAL PERRUPATO (ID 29) | equivalencia_por_nombre |
| HOSPITAL SAPORITI (C107) | 22 | HOSPITAL SAPORITTI (ID 27) | equivalencia_por_nombre |
| HOPITAL TAGARELLI (C141) | 11 | Hospital Victorino Tagarelli (ID 19) | equivalencia_por_nombre |
| DINAF-ETI (C162) | 11 | ETI Lujan (ID 2); ETI Maipu (ID 3); ETI Godoy Cruz (ID 5); ETI Guaymallen (ID 8); ETI Lemos (ID 9); ETI Belgrano (ID 10); ETI Las Heras (ID 11); ETI Armani (ID 30) | cuadro_agrupado_pendiente |
| SALA DE PALMIRA Y GUEMES (C191) | 3 | Sala Guemes (ID 16); Sala Palmira (ID 17) | cuadro_agrupado_pendiente |
| BOSQUE (C208) | 3 | El Bosque (ID 6) | equivalencia_por_nombre |
| METRAUX (C219) | 10 | HOSPITAL ALFREDO METRAUX (ID 20) | equivalencia_por_nombre |
| ETI BERMEJO (C236) | 11 | Sin coincidencia segura | sin_equivalencia_segura |
| HOGAR 8 MAIPU (C256) | 6 | Hogar 8 (ID 4) | equivalencia_por_nombre |
| CASA MC DONALDS (C274) | 2 | La Casa De Ronald McDonald (ID 15) | equivalencia_por_nombre |
| CASA 5,6 GODOY CRUZ (C285) | 6 | Sin coincidencia segura | sin_equivalencia_segura |
| ETI TUPUNGATO (C300) | 7 | ETI Tupungato (ID 14) | equivalencia_por_nombre |

Las equivalencias por nombre son correspondencias de catálogo, no una confirmación de asignaciones históricas. DINAF-ETI necesita distinguir los destinos por servicio; Palmira/Güemes necesita separar los dos destinos. No se crearon objetivos faltantes.

## Empleados

- sin_coincidencia_textual: 127.
- coincidencia_textual_unica: 6.

Los candidatos similares quedan registrados para revisión y no se fusionan. Tampoco se da de alta a personal ausente del catálogo.

| Nombre A | Nombre B | Tratamiento |
|---|---|---|
| Candido Facaundo | Candido Facundo | Identificadores separados; pendiente confirmar |
| Salinas Karin | Salinas Karim | Identificadores separados; pendiente confirmar |

De los nombres sin coincidencia exacta, 105 tienen candidatos por nombres abreviados o similitud. No debe interpretarse una falta de coincidencia exacta como ausencia del empleado en la base.
La nota `GONZALO RODRIGO 8H` (C269) queda preservada por separado: no tiene fechas ni celdas de turno y no se convirtió en una asignación.

Se pudieron contrastar 6 vínculos persona–objetivo por coincidencia textual y objetivo individual. 4 tienen vínculo según la categoría actual; 2 no. Estos datos actuales no invalidan el historial del Excel.

## Diccionario de códigos

| Código | Celdas | Clasificación | Horas muestra | Selector actual | Ejemplo |
|---|---:|---|---:|---|---|
| 10H | 51 | turno | 10 | Sí | M66 |
| 12H | 67 | turno | 12 | Sí | D62 |
| 13H | 36 | turno | 13 | Sí | I111 |
| 6H | 5 | turno | 6 | Sí | AH149 |
| 8H | 81 | turno | 8 | Sí | AD86 |
| 9H | 20 | turno | 9 | Sí | P63 |
| 9HEX | 20 | turno_especial | 9 | No | K20 |
| 9RF | 31 | turno_especial | 9 | No | T6 |
| BE | 20 | servicio_con_destino | Por confirmar / no aplica | No | H171 |
| BERM | 29 | referencia_otro_objetivo | Por confirmar / no aplica | No | D7 |
| C/5,6 | 4 | referencia_otro_objetivo | Por confirmar / no aplica | No | R25 |
| C5,6 | 14 | referencia_otro_objetivo | Por confirmar / no aplica | No | K25 |
| D | 1074 | turno | 12 | Sí | G6 |
| D/GC | 20 | servicio_con_destino | Por confirmar / no aplica | No | H166 |
| D/GU | 19 | servicio_con_destino | Por confirmar / no aplica | No | H168 |
| D/LEM | 20 | servicio_con_destino | Por confirmar / no aplica | No | H167 |
| D/LH | 20 | servicio_con_destino | Por confirmar / no aplica | No | H175 |
| D/LUJ | 20 | servicio_con_destino | Por confirmar / no aplica | No | V172 |
| D/MA | 20 | servicio_con_destino | Por confirmar / no aplica | No | H176 |
| ETI | 7 | referencia_otro_objetivo | Por confirmar / no aplica | No | E8 |
| F | 842 | franco_o_ausencia | 0 | Sí | F6 |
| F/INJ | 5 | franco_o_ausencia | 0 | No | P65 |
| GP/D | 118 | guardia_pasiva | 12 | Sí | X6 |
| GP/N | 94 | guardia_pasiva | 12 | Sí | S10 |
| H/8 | 18 | referencia_otro_objetivo | Por confirmar / no aplica | No | G27 |
| METR | 18 | referencia_otro_objetivo | Por confirmar / no aplica | No | Q79 |
| N | 1019 | turno | 12 | Sí | D6 |
| N/AR | 62 | servicio_con_destino | Por confirmar / no aplica | No | E169 |
| N15 | 26 | turno_especial | 15 | No | G278 |
| NOTT | 34 | referencia_otro_objetivo | Por confirmar / no aplica | No | K83 |
| OFI | 1 | referencia_otro_objetivo | Por confirmar / no aplica | No | Y152 |
| P/EN | 28 | franco_o_ausencia | 0 | No | N74 |
| PERR | 19 | referencia_otro_objetivo | Por confirmar / no aplica | No | P114 |
| SALA | 16 | referencia_otro_objetivo | Por confirmar / no aplica | No | F62 |
| TUP | 15 | referencia_otro_objetivo | Por confirmar / no aplica | No | P145 |
| VAC | 126 | franco_o_ausencia | 0 | No | F8 |

La duración se conserva para D/N, guardias pasivas y códigos numéricos. VAC, P/EN y F/INJ conservan su texto histórico. No se transforman automáticamente a códigos modernos ni se crean siglas en la base.
Las referencias NOTT/PERR/BERM/METR/TUP/H/8/C5,6/C/5,6/SALA/ETI/OFI no se cuentan como una segunda presencia. Sus destinos sugeridos y los códigos de servicio están detallados en correspondencias_mayo_2025.json.

## Horarios disponibles y límites

El catálogo local contiene 3 puestos y 5 horarios, y no contiene rotaciones de puestos para mayo de 2025. Su detalle se guardó en las correspondencias.
Los puestos actuales pertenecen a Parque Central y Empresa Marsan, fuera de los cuadros de la muestra; no aportan franjas horarias aplicables a estos objetivos.
El Excel no identifica entrada/salida ni puesto por celda. No se puede confirmar ausencia o presencia de superposición horaria con la duración únicamente.

## Controles de preparación

- Se verificaron las 13 cabeceras de fechas: días 1 a 31 en D:AH.
- Se incluyeron empleados sin numeración y filas cuya numeración contiene #REF!. La matriz diaria no contiene errores de Excel ni fórmulas.
- Cada asignación tiene celda de origen y valor original; cada blanco se conserva como ausencia de asignación.
- Se verificó la unicidad usuario–cuadro–fecha, las referencias internas y la suma celdas con código + celdas vacías.
- Los datos de asignación usan solo usuario_id, objetivo_id, fecha y codigo_turno. Los datos de trazabilidad pertenecen al material de prueba; no son columnas nuevas del sistema.

## Archivos

- `muestra_mayo_2025.json`: fuente, nóminas, asignaciones y trazabilidad.
- `correspondencias_mayo_2025.json`: códigos, candidatos pendientes, vínculos y horarios actuales.

## Pendientes para las siguientes partes

- Confirmar identidades de los nombres parecidos antes de unirlos en pruebas entre objetivos.
- Resolver los cuadros agrupados y las referencias sin destino inequívoco; conservar C5,6 y C/5,6 como variantes pendientes.
- Elegir puestos/horarios explícitos en los escenarios de prueba. No usar horarios inventados como evidencia histórica.
- Validar compatibilidad de los códigos históricos antes de cargar la muestra mediante la interfaz.

Fuente: C:\Users\Usuario\Desktop\mayo 2025.xlsx — hoja `MAYO 2025 (2)`. SHA-256: `9d2709509431fd038e91a9bc23e66831822a8babb7ff32bfc0247d50fb9bd3d9`.
Catálogos locales consultados: 2026-09-18T03:20:57+00:00. Sin escrituras en base de datos.
