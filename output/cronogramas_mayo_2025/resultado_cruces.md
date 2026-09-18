# Pruebas de cruces entre objetivos

**El control actual no garantiza que un vigilador esté libre de superposiciones.** Consulta usuario, fecha y códigos; no consulta entrada/salida ni puestos.

## Prueba del control actual

Se ejecutaron 18 casos con horarios sintéticos explícitos: 5 coinciden con el criterio de solape físico y 13 no.

| Caso | Conflicto horario esperado | Respuesta actual | Resultado |
|---|---|---|---|
| D_superpuesto | Sí | No | fallo |
| N_superpuesto | Sí | No | fallo |
| 8H_superpuesto | Sí | Sí | correcto |
| 10H_superpuesto | Sí | No | fallo |
| 12H_superpuesto | Sí | No | fallo |
| 8H_consecutivos_sin_solape | No | Sí | fallo |
| 8H_separados_sin_solape | No | Sí | fallo |
| nocturno_dia_anterior_superpuesto | Sí | No | fallo |
| nocturno_cambio_mes_superpuesto | Sí | No | fallo |
| F_no_es_presencia | No | Sí | fallo |
| GP_D_no_es_presencia | No | Sí | fallo |
| GP_N_no_es_presencia | No | Sí | fallo |
| referencia_NOTT_no_duplica | No | No | correcto |
| referencia_PERR_no_duplica | No | Sí | fallo |
| referencia_BERM_no_duplica | No | Sí | fallo |
| mismo_objetivo_excluido | No | No | correcto |
| persona_distinta_excluida | No | No | correcto |
| D_y_N_consecutivos | No | No | correcto |

Los intervalos se comparan como [entrada, salida): finalizar a las 15:00 e iniciar a las 15:00 no es simultaneidad. Esto no evalúa traslados o descansos.

## Causas reproducidas

- D, N, 10H y 12H no figuran en el catálogo de jornadas del endpoint: no reconoce el conflicto cuando el registro existente lleva esos códigos.
- La consulta compara solo la misma fecha. No encuentra un turno de la víspera que continúe durante la mañana siguiente, incluso al cambiar de mes.
- 8H sí se reconoce, pero se bloquea todo el día: dos turnos consecutivos o separados se consideran conflicto aunque no se superpongan.
- F y GP se tratan como registros que bloquean. GP sigue computando 12 h según la decisión actual, pero no representa presencia por sí misma.
- Algunas referencias están en la lista histórica y otras no: NOTT evita un falso duplicado, PERR y BERM no.

## Coincidencias en mayo de 2025

Se encontraron 0 combinaciones persona–fecha con códigos de presencia en varios cuadros, que corresponden a 0 nombres normalizados.
**Son candidatos a revisión, no superposiciones confirmadas.** Faltan horas de entrada/salida y puestos históricos. No se unieron nombres dudosos.
Se excluyeron como segunda presencia 175 referencias y 212 guardias pasivas; el cálculo de horas GP no se modificó.
De las referencias, 146 tienen una asignación de presencia del mismo nombre y fecha en el cuadro de destino sugerido. Las restantes requieren revisar destino, grafía o celdas vacías; no implican automáticamente un error.

| Persona | Fecha | Cuadros y códigos | Celdas |
|---|---|---|---|
| No se encontraron coincidencias en la misma fecha para los nombres normalizados | — | — | — |

## Noches seguidas de otro objetivo

Hay 3 transiciones desde un código nocturno hacia un código no nocturno en otro cuadro al día siguiente. Requieren horarios: no son conflictos confirmados.
El archivo solo contiene mayo: no permite revisar la noche del 30 de abril contra el 1 de mayo ni la noche del 31 de mayo contra el 1 de junio.

| Persona | Noche | Siguiente día | Celdas |
|---|---|---|---|
| Allende Fernando | 2025-05-05 HOSPITAL PERRUPATO (N) | 2025-05-06 SALA DE PALMIRA Y GUEMES (8H) | H62, I197 |
| Allende Fernando | 2025-05-11 HOSPITAL PERRUPATO (N) | 2025-05-12 SALA DE PALMIRA Y GUEMES (8H) | N62, O197 |
| Allende Fernando | 2025-05-21 HOSPITAL PERRUPATO (N) | 2025-05-22 SALA DE PALMIRA Y GUEMES (8H) | X62, Y197 |

## Límites y siguiente comprobación

- Se ejecutó el endpoint original en procesos PHP aislados, sustituyendo únicamente su conexión por un adaptador SELECT en memoria. No hubo escrituras en MySQL.
- Los horarios de los casos están inventados explícitamente para comprobar el control; no se presentan como horarios de mayo.
- La base local tiene una restricción única usuario–fecha: puede rechazar un segundo registro del día aunque no haya solape. Esa restricción no detecta cruces desde la víspera. Queda pendiente probar el guardado completo.
- El controlador actual no incorpora una validación temporal de solapes al guardar. La verificación en navegador y el recorrido de guardado aún están pendientes.
- No se aplicaron correcciones funcionales. Los resultados de esta etapa quedan separados del commit anterior.

Evidencia detallada: `pruebas_cruces.json`. Ejecutor: `tmp/cronogramas_mayo_2025/probar_cruces.py` con `ejecutar_validador.php`.
