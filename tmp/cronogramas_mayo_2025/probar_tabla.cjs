// Ejecuta cronograma.js original en VM. DOM/jQuery mínimos solo para capturar HTML.
// No es una prueba E2E: no reproduce layout, Select2, red ni interacción real.
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const crypto = require('node:crypto');
const root = path.resolve(__dirname, '../..');
const out = path.join(root, 'output/cronogramas_mayo_2025');
const read = name => JSON.parse(fs.readFileSync(path.join(out, name), 'utf8'));
const sample = read('muestra_mayo_2025.json');
const php = read('pruebas_armado_php.json');
const catalog = JSON.parse(fs.readFileSync(path.join(__dirname, 'catalogos.json'), 'utf8'));
const source = fs.readFileSync(path.join(root, 'js/cronograma.js'), 'utf8');
const originalReturn = 'return { init, renderTabla };';
if (source.split(originalReturn).length !== 2) throw Error('No se identificó la API pública esperada');
const instrumented = source.replace(originalReturn, 'return { init, renderTabla, __test: { horasCodigo, esCodigoValido, opcionesTurnoHTML } };');
const uid = id => 10000 + Math.abs(id);
const oid = id => 5000 + Math.abs(id);
const people = new Map(sample.empleados.map(p => [p.idUsuario,p]));
const staff = sample.nomina_por_objetivo.map(r => ({idUsuario:uid(r.usuario_id), objetivo_id:oid(r.objetivo_id), apellido:people.get(r.usuario_id).nombre_fuente, nombre:''}));
const tests = [];
const details = [];
function check(caso, expected, got) { tests.push({caso, esperado:expected, obtenido:got, resultado:JSON.stringify(expected)===JSON.stringify(got)?'correcto':'fallo'}); }

async function simulate(objective, data, siglas=catalog.siglas, yearMonth='2025-05', role='vigilador') {
  const elements = new Map();
  const node = id => {
    if (!elements.has(id)) elements.set(id,{innerHTML:'',value:'',addEventListener(){},querySelector(){return null},querySelectorAll(){return []}});
    return elements.get(id);
  };
  node('objetivo').value=String(objective);
  node('mes').value=yearMonth;
  const jq=()=>({on(){},empty(){},append(){},addClass(){},removeClass(){},text(){return this}});
  jq.getJSON=()=>{throw Error('No se permite red en este ensayo');};
  const boot={puestos:[],vigiladores:role==='vigilador'?structuredClone(staff):[],referentes:role==='referente'?structuredClone(staff):[],todosFeriados:[],datosPrevios:structuredClone(data),horasPorUsuario:{}};
  const sandbox={window:{CRONOGRAMA_BOOT:boot},document:{getElementById:node,querySelectorAll(){return []},querySelector(){return null}},$:jq,console,API_SIGLAS_URL:'memoria://siglas',fetch:async()=>({text:async()=>JSON.stringify(siglas)})};
  vm.createContext(sandbox);
  new vm.Script(instrumented,{filename:'cronograma.js'}).runInContext(sandbox);
  await sandbox.window.Cronograma.init({formId:'form',contenedorTablaId:'tablaCronogramaContainer',objetivoId:'objetivo',mesId:'mes',btnCargarId:'cargar',btnVaciarId:'vaciar',feriadosInfoId:'feriadosMesInfo',feriadosListaId:'feriadosMesLista'},boot);
  const html=node('tablaCronogramaContainer').innerHTML;
  const rows=[...html.matchAll(/<tr data-rol="(Vigilador|Referente)" data-usuario="(\d+)"/g)].map(m=>Number(m[2]));
  const cells=[...html.matchAll(/<select name="(vigilador|referente)\[(\d+)\]\[(\d+)\]"[^>]*>([\s\S]*?)<\/select>/g)].map(m=>({usuario_id:Number(m[2]),dia:Number(m[3]),codigo:m[4].match(/<option value="([^"]*)" selected>/)?.[1]??''}));
  return {html,rows,cells,api:sandbox.window.Cronograma};
}

(async()=>{
  for (const obj of sample.objetivos) {
    const id=oid(obj.idObjetivo);
    const expectedStaff=staff.filter(p=>p.objetivo_id===id).map(p=>p.idUsuario);
    const data=php.posts_mayo[String(id)];
    const got=await simulate(id,data);
    const expected=sample.asignaciones.filter(a=>a.objetivo_id===obj.idObjetivo);
    const rowsLost=expectedStaff.filter(u=>!got.rows.includes(u));
    const lostByRoster=expected.filter(a=>rowsLost.includes(uid(a.usuario_id)));
    const different=[];
    for (const a of expected.filter(a=>got.rows.includes(uid(a.usuario_id)))) {
      const cell=got.cells.find(c=>c.usuario_id===uid(a.usuario_id) && c.dia===Number(a.fecha.slice(-2)));
      if (!cell || cell.codigo!==a.codigo_turno) different.push({usuario_id:a.usuario_id,fecha:a.fecha,codigo_original:a.codigo_turno,codigo_visible:cell?.codigo??null});
    }
    check('nomina_visible_'+id,expectedStaff,got.rows);
    check('codigos_visibles_en_filas_presentes_'+id,0,different.length);
    check('dias_tabla_'+id,got.rows.length*31,got.cells.length);
    details.push({objetivo:obj.nombre,objetivo_id_muestra:obj.idObjetivo,filas_esperadas:expectedStaff.length,filas_visibles:got.rows.length,usuarios_omitidos:rowsLost.map(n=>-(n-10000)),celdas_omitidas_por_nomina:lostByRoster.length,celdas_con_codigo_perdido:different.length,diferencias: different});
  }
  const isolated=await simulate(5001,php.posts_mayo['5001']);
  check('horas_gp_d',12,isolated.api.__test.horasCodigo('GP/D'));
  check('horas_gp_n',12,isolated.api.__test.horasCodigo('GP/N'));
  check('horas_10h',10,isolated.api.__test.horasCodigo('10H'));
  check('horas_12h',12,isolated.api.__test.horasCodigo('12H'));
  check('horas_franco',0,isolated.api.__test.horasCodigo('F'));
  const known=await simulate(5001,{objetivo:5001,mes:'2025-05',vigilador:{10001:{usuario:10001,1:'NOTT'}},referente:[]},[{objetivo_id:5001,sigla:'NOTT',descripcion:'Notti',horas:12}]);
  check('sigla_configurada_se_muestra','NOTT',known.cells.find(c=>c.usuario_id===10001&&c.dia===1)?.codigo);
  const roleData={objetivo:5001,mes:'2025-05',vigilador:[],referente:php.posts_mayo['5001'].vigilador};
  const refs=await simulate(5001,roleData,[], '2025-05','referente');
  check('tabla_referentes_objetivo_1',45,refs.rows.length);
  const june=await simulate(5001,php.posts_junio['5001'],[],'2025-06');
  check('junio_30_dias',45*30,june.cells.length);
  const incompatible=[...new Set(details.flatMap(d=>d.diferencias.map(c=>c.codigo_original)))].sort();
  const result={alcance:'Ejecución del JS original con DOM mínimo; se verifica HTML generado, filtrado de nómina, selección de códigos y cálculo unitario. No se verifican interacción real, AJAX, Select2 ni totales visuales.',
    sha256_js:crypto.createHash('sha256').update(source).digest('hex'),casos:tests,por_objetivo:details,
    resumen:{casos:tests.length,correctos:tests.filter(t=>t.resultado==='correcto').length,fallos:tests.filter(t=>t.resultado==='fallo').length,
      filas_omitidas:details.reduce((n,d)=>n+d.filas_esperadas-d.filas_visibles,0),celdas_omitidas_por_nomina:details.reduce((n,d)=>n+d.celdas_omitidas_por_nomina,0),
      celdas_con_codigo_perdido:details.reduce((n,d)=>n+d.celdas_con_codigo_perdido,0),codigos_perdidos:incompatible}};
  fs.writeFileSync(path.join(out,'pruebas_armado_js.json'),JSON.stringify(result,null,2)+'\n');
  console.log(JSON.stringify(result.resumen,null,2));
  // Los fallos de producto quedan en el informe. Excepción => fallo del propio ejecutor.
})().catch(e=>{console.error(e);process.exitCode=1});
