"""Contraste independiente del JSON con XML del XLSX, sin usar el extractor."""
from pathlib import Path
from collections import Counter
import hashlib
import json
import re
import zipfile
import xml.etree.ElementTree as ET

root = Path(__file__).resolve().parents[2]
out = root / 'output/cronogramas_mayo_2025'
sample = json.loads((out/'muestra_mayo_2025.json').read_text(encoding='utf-8'))
mapping = json.loads((out/'correspondencias_mayo_2025.json').read_text(encoding='utf-8'))
ns = {'s': 'http://schemas.openxmlformats.org/spreadsheetml/2006/main'}
path = Path(sample['fuente']['archivo'])
with zipfile.ZipFile(path) as z:
    strings = [''.join(si.itertext()) for si in ET.fromstring(z.read('xl/sharedStrings.xml')).findall('s:si', ns)]
    xml = ET.fromstring(z.read('xl/worksheets/sheet1.xml'))
    values = {}
    for c in xml.findall('.//s:sheetData/s:row/s:c', ns):
        v = c.find('s:v', ns)
        value = '' if v is None else v.text or ''
        if c.attrib.get('t') == 's':
            value = strings[int(value)]
        elif c.attrib.get('t') == 'inlineStr':
            value = ''.join(c.find('s:is', ns).itertext())
        values[c.attrib['r']] = value

checks = {}
checks['hash_original_intacto'] = hashlib.sha256(path.read_bytes()).hexdigest() == sample['fuente']['sha256']
checks['13_cuadros'] = len(sample['objetivos']) == 13
checks['173_filas'] = len(sample['nomina_por_objetivo']) == 173
coords = []
counts = Counter()
for prov in sample['trazabilidad_asignaciones']:
    a = sample['asignaciones'][prov['indice_asignacion']]
    value = values[prov['celda']]
    assert value == prov['valor_original'], (prov, value)
    assert ' '.join(value.split()).upper() == a['codigo_turno']
    coords.append(prov['celda'])
    counts[a['codigo_turno']] += 1
for blank in sample['sin_asignacion']:
    assert not values.get(blank['celda'], '').strip(), blank
    coords.append(blank['celda'])
expected = set()
for row in sample['nomina_por_objetivo']:
    row_num = re.search(r'\d+',row['celda_nombre'])[0]
    assert values.get(row['celda_nombre'], '').strip()
    for col in ['D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH']:
        expected.add(col+row_num)
checks['celdas_diarias_completas_sin_duplicados'] = set(coords) == expected and len(coords) == len(expected) == 5363
checks['codigos_conteos_coinciden'] = counts == Counter({c['codigo']: c['cantidad'] for c in mapping['codigos']})
checks['gp_12_horas'] = all(c['horas_muestra'] == 12 and c['horas_codigo_actual_sin_sigla_configurada'] == 12 for c in mapping['codigos'] if c['codigo'] in ['GP/D','GP/N'])
checks['nota_fuera_nomina_preservada'] = sample['fuente']['anotaciones_fuera_nomina'][0]['texto'] == values['C269']
checks['solo_campos_documentados_en_asignaciones'] = all(set(a) == {'usuario_id','objetivo_id','fecha','codigo_turno'} for a in sample['asignaciones'])
assert all(checks.values()), checks
(out/'verificacion_muestra.json').write_text(json.dumps({'controles':checks,'celdas_con_codigo':len(sample['asignaciones']),'celdas_sin_asignacion':len(sample['sin_asignacion']),'metodo':'XML original frente a JSON extraido; sin escrituras en Excel o BD'},ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
print(json.dumps(checks, ensure_ascii=False, indent=2))
