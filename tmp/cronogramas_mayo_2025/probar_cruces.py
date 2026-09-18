from pathlib import Path
from collections import defaultdict, Counter
from datetime import datetime, timedelta
import json
import subprocess
import shutil

ROOT=Path(__file__).resolve().parents[2]
OUT=ROOT/'output/cronogramas_mayo_2025'
TMP=Path(__file__).parent
read=lambda name:json.loads((OUT/name).read_text(encoding='utf-8'))
sample=read('muestra_mayo_2025.json')
mapping=read('correspondencias_mayo_2025.json')
codes={c['codigo']:c for c in mapping['codigos']}
people={p['idUsuario']:p for p in sample['empleados']}
objectives={o['idObjetivo']:o['nombre'] for o in sample['objetivos']}
provenance={p['indice_asignacion']:p['celda'] for p in sample['trazabilidad_asignaciones']}

# Clasificar coincidencias de fecha; no deducir franjas a partir de duración.
by_date=defaultdict(list)
references=[]
for i,a in enumerate(sample['asignaciones']):
    category=codes[a['codigo_turno']]['categoria']
    row={**a,'objetivo':objectives[a['objetivo_id']],'celda':provenance[i]}
    if category in {'turno','turno_especial','servicio_con_destino'}:
        by_date[(a['usuario_id'],a['fecha'])].append(row)
    elif category=='referencia_otro_objetivo':
        references.append(row)
coincidences=[]
for (uid,date),rows in by_date.items():
    if len({r['objetivo_id'] for r in rows})>1:
        coincidences.append({'usuario_id':uid,'nombre':people[uid]['nombre_fuente'],'fecha':date,
                             'asignaciones':rows,'estado':'coincidencia_de_fecha_horarios_pendientes'})

# Una noche seguida de un servicio distinto al día siguiente exige conocer franjas.
# No constituye conflicto confirmado: el segundo servicio puede empezar al terminar la noche.
night_candidates=[]
for (uid,date),rows in list(by_date.items()):
    following=(datetime.fromisoformat(date)+timedelta(days=1)).date().isoformat()
    for night in rows:
        if night['codigo_turno'] not in {'N','N15','N/AR'}:
            continue
        for next_shift in by_date.get((uid,following),[]):
            if next_shift['objetivo_id'] != night['objetivo_id'] and next_shift['codigo_turno'] not in {'N','N15','N/AR'}:
                night_candidates.append({'usuario_id':uid,'nombre':people[uid]['nombre_fuente'],
                                         'noche':night,'dia_siguiente':next_shift,'estado':'horarios_pendientes_no_es_conflicto_confirmado'})

ref_checks=[]
for ref in references:
    targets=codes[ref['codigo_turno']]['objetivos_muestra_sugeridos']
    found=[r for r in by_date[(ref['usuario_id'],ref['fecha'])] if r['objetivo_id'] in targets]
    ref_checks.append({**ref,'destinos_sugeridos':targets,'presencias_en_destinos_sugeridos':found,
                       'estado':'referencia_con_asignacion_en_destino_sugerido' if found else 'referencia_sin_correspondencia_presencial_confirmable'})

# Oracle horario independiente: intervalos semiabiertos [entrada,salida).
def overlap(a,b):
    return max(datetime.fromisoformat(a[0]),datetime.fromisoformat(b[0])) < min(datetime.fromisoformat(a[1]),datetime.fromisoformat(b[1]))

tests=[]
def add(name,old,new,old_interval,new_interval,old_date='2025-05-10',new_date='2025-05-10',same_objective=False,other_user=False):
    obj=8001 if same_objective else 8002
    old_user=9002 if other_user else 9001
    expected=bool(old_interval and new_interval and not same_objective and not other_user and overlap(old_interval,new_interval))
    day=datetime.fromisoformat(new_date)
    payload={'consulta':{'usuario':9001,'dia':day.day,'mes':day.month,'anio':day.year,'codigo':new,'objetivo_actual':8001},
             'existentes':[{'usuario_id':old_user,'objetivo_id':obj,'objetivo':'Objetivo B simulado','fecha':old_date,'codigo_turno':old}]}
    casefile=TMP/'caso_validador.json'
    casefile.write_text(json.dumps(payload),encoding='utf-8')
    proc=subprocess.run([shutil.which('php') or 'php',str(TMP/'ejecutar_validador.php'),str(casefile)],capture_output=True,text=True,encoding='utf-8',errors='replace')
    if proc.returncode:
        raise RuntimeError(proc.stderr)
    answer=json.loads(proc.stdout)
    got=answer['conflicto']
    tests.append({'caso':name,'codigo_existente':old,'codigo_nuevo':new,'franja_existente':old_interval,'franja_nueva':new_interval,
                  'conflicto_horario_esperado':expected,'respuesta_endpoint':answer,'resultado':'correcto' if expected==got else 'fallo',
                  'origen':'horarios sinteticos explicitos; no corresponden al Excel'})

D=('2025-05-10T07:00:00','2025-05-10T19:00:00')
N=('2025-05-10T19:00:00','2025-05-11T07:00:00')
H8=('2025-05-10T07:00:00','2025-05-10T15:00:00')
add('D_superpuesto','D','D',D,D)
add('N_superpuesto','N','N',N,N)
add('8H_superpuesto','8H','8H',H8,H8)
add('10H_superpuesto','10H','10H',('2025-05-10T07:00:00','2025-05-10T17:00:00'),('2025-05-10T08:00:00','2025-05-10T18:00:00'))
add('12H_superpuesto','12H','12H',D,D)
add('8H_consecutivos_sin_solape','8H','8H',H8,('2025-05-10T15:00:00','2025-05-10T23:00:00'))
add('8H_separados_sin_solape','8H','6H',H8,('2025-05-10T17:00:00','2025-05-10T23:00:00'))
add('nocturno_dia_anterior_superpuesto','N','6H',('2025-05-09T19:00:00','2025-05-10T07:00:00'),('2025-05-10T06:00:00','2025-05-10T12:00:00'),old_date='2025-05-09')
add('nocturno_cambio_mes_superpuesto','N','6H',('2025-05-31T19:00:00','2025-06-01T07:00:00'),('2025-06-01T06:00:00','2025-06-01T12:00:00'),old_date='2025-05-31',new_date='2025-06-01')
add('F_no_es_presencia','F','D',None,D)
add('GP_D_no_es_presencia','GP/D','D',None,D)
add('GP_N_no_es_presencia','GP/N','N',None,N)
add('referencia_NOTT_no_duplica','8H','NOTT',H8,None)
add('referencia_PERR_no_duplica','8H','PERR',H8,None)
add('referencia_BERM_no_duplica','8H','BERM',H8,None)
add('mismo_objetivo_excluido','8H','8H',H8,H8,same_objective=True)
add('persona_distinta_excluida','8H','8H',H8,H8,other_user=True)
add('D_y_N_consecutivos','D','N',D,N)

summary={'casos_sinteticos':len(tests),'correctos':sum(t['resultado']=='correcto' for t in tests),'fallos':sum(t['resultado']=='fallo' for t in tests),
         'coincidencias_persona_fecha':len(coincidences),'personas_con_coincidencias':len({c['usuario_id'] for c in coincidences}),
         'noches_seguidas_de_otro_objetivo':len(night_candidates),
         'referencias_excluidas_como_presencia':len(references),'referencias_con_destino_sugerido_y_asignacion':sum(bool(r['presencias_en_destinos_sugeridos']) for r in ref_checks),
         'guardias_pasivas_excluidas_como_presencia':sum(c['cantidad'] for c in mapping['codigos'] if c['categoria']=='guardia_pasiva')}
result={'alcance':'Endpoint original con conexión sustituida en memoria. Oracle de solape horario con horarios sintéticos; no prueba servidor web ni guardado.',
        'reglas':['GP/D y GP/N siguen sumando 12 h en el sistema; disponibilidad no equivale a presencia.',
                  'Un falso positivo aquí significa que no hay solape físico. No descarta otras restricciones operativas o de licencias.',
                  'Cruces del Excel por nombres normalizados sin unir alias dudosos; coincidencia de fecha no demuestra solape.',
                  'No se evalúan descansos mínimos, desplazamientos ni obligaciones legales.'],
        'resumen':summary,'casos':tests,'coincidencias_muestra':coincidences,'noches_hacia_otro_objetivo':night_candidates,'referencias_muestra':ref_checks}
(OUT/'pruebas_cruces.json').write_text(json.dumps(result,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
lines=['# Pruebas de cruces entre objetivos','',
       '**El control actual no garantiza que un vigilador esté libre de superposiciones.** Consulta usuario, fecha y códigos; no consulta entrada/salida ni puestos.', '',
       '## Prueba del control actual','',
       f"Se ejecutaron {len(tests)} casos con horarios sintéticos explícitos: {summary['correctos']} coinciden con el criterio de solape físico y {summary['fallos']} no.", '',
       '| Caso | Conflicto horario esperado | Respuesta actual | Resultado |','|---|---|---|---|']
for t in tests:
    lines.append(f"| {t['caso']} | {'Sí' if t['conflicto_horario_esperado'] else 'No'} | {'Sí' if t['respuesta_endpoint']['conflicto'] else 'No'} | {t['resultado']} |")
lines+=['','Los intervalos se comparan como [entrada, salida): finalizar a las 15:00 e iniciar a las 15:00 no es simultaneidad. Esto no evalúa traslados o descansos.', '',
        '## Causas reproducidas','',
        '- D, N, 10H y 12H no figuran en el catálogo de jornadas del endpoint: no reconoce el conflicto cuando el registro existente lleva esos códigos.',
        '- La consulta compara solo la misma fecha. No encuentra un turno de la víspera que continúe durante la mañana siguiente, incluso al cambiar de mes.',
        '- 8H sí se reconoce, pero se bloquea todo el día: dos turnos consecutivos o separados se consideran conflicto aunque no se superpongan.',
        '- F y GP se tratan como registros que bloquean. GP sigue computando 12 h según la decisión actual, pero no representa presencia por sí misma.',
        '- Algunas referencias están en la lista histórica y otras no: NOTT evita un falso duplicado, PERR y BERM no.', '',
        '## Coincidencias en mayo de 2025','',
        f"Se encontraron {summary['coincidencias_persona_fecha']} combinaciones persona–fecha con códigos de presencia en varios cuadros, que corresponden a {summary['personas_con_coincidencias']} nombres normalizados.",
        '**Son candidatos a revisión, no superposiciones confirmadas.** Faltan horas de entrada/salida y puestos históricos. No se unieron nombres dudosos.',
        f"Se excluyeron como segunda presencia {len(references)} referencias y {summary['guardias_pasivas_excluidas_como_presencia']} guardias pasivas; el cálculo de horas GP no se modificó.",
        f"De las referencias, {summary['referencias_con_destino_sugerido_y_asignacion']} tienen una asignación de presencia del mismo nombre y fecha en el cuadro de destino sugerido. Las restantes requieren revisar destino, grafía o celdas vacías; no implican automáticamente un error.", '',
        '| Persona | Fecha | Cuadros y códigos | Celdas |','|---|---|---|---|']
for c in coincidences:
    lines.append(f"| {c['nombre']} | {c['fecha']} | {'; '.join(r['objetivo']+': '+r['codigo_turno'] for r in c['asignaciones'])} | {', '.join(r['celda'] for r in c['asignaciones'])} |")
if not coincidences:
    lines.append('| No se encontraron coincidencias en la misma fecha para los nombres normalizados | — | — | — |')
lines+=['','## Noches seguidas de otro objetivo','',
        f"Hay {len(night_candidates)} transiciones desde un código nocturno hacia un código no nocturno en otro cuadro al día siguiente. Requieren horarios: no son conflictos confirmados.",
        'El archivo solo contiene mayo: no permite revisar la noche del 30 de abril contra el 1 de mayo ni la noche del 31 de mayo contra el 1 de junio.', '',
        '| Persona | Noche | Siguiente día | Celdas |','|---|---|---|---|']
for n in night_candidates:
    a,b=n['noche'],n['dia_siguiente']
    lines.append(f"| {n['nombre']} | {a['fecha']} {a['objetivo']} ({a['codigo_turno']}) | {b['fecha']} {b['objetivo']} ({b['codigo_turno']}) | {a['celda']}, {b['celda']} |")
if not night_candidates:
    lines.append('| No se encontraron transiciones de este tipo dentro de mayo | — | — | — |')
lines+=['','## Límites y siguiente comprobación','',
        '- Se ejecutó el endpoint original en procesos PHP aislados, sustituyendo únicamente su conexión por un adaptador SELECT en memoria. No hubo escrituras en MySQL.',
        '- Los horarios de los casos están inventados explícitamente para comprobar el control; no se presentan como horarios de mayo.',
        '- La base local tiene una restricción única usuario–fecha: puede rechazar un segundo registro del día aunque no haya solape. Esa restricción no detecta cruces desde la víspera. Queda pendiente probar el guardado completo.',
        '- El controlador actual no incorpora una validación temporal de solapes al guardar. La verificación en navegador y el recorrido de guardado aún están pendientes.',
        '- No se aplicaron correcciones funcionales. Los resultados de esta etapa quedan separados del commit anterior.', '',
        'Evidencia detallada: `pruebas_cruces.json`. Ejecutor: `tmp/cronogramas_mayo_2025/probar_cruces.py` con `ejecutar_validador.php`.']
(OUT/'resultado_cruces.md').write_text('\n'.join(lines)+'\n',encoding='utf-8')
print(json.dumps(summary,ensure_ascii=False,indent=2))
