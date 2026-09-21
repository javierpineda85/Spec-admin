<?php
// Solo servidor de pruebas: php -S 127.0.0.1:8765 tests/cronogramas/navegador.php
if (PHP_SAPI !== 'cli-server' || !in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1','::1'], true)) { http_response_code(404); exit; }
$root = dirname(__DIR__,2);
$route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($route === '/cronograma.js') { header('Content-Type: application/javascript'); readfile($root.'/js/cronograma.js'); exit; }
if ($route === '/siglas') { header('Content-Type: application/json'); echo '[]'; exit; }
if ($route === '/validar') { header('Content-Type: application/json'); echo json_encode(['estado'=>($_GET['codigo']==='N'?'conflicto':($_GET['codigo']==='8H'?'pendiente':'libre')),'mensaje'=>'Respuesta controlada de prueba']); exit; }
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $data=json_decode($_POST['cronograma_json']??'',true);
    $rows=count($data['vigilador']??[])+count($data['referente']??[]);
    $cells=0; foreach($data??[] as $role) foreach($role as $row) $cells+=count($row)-1;
    header('Content-Type: text/html; charset=UTF-8');
    echo '<h1>Recepción de prueba</h1><pre id="recepcion">'.htmlspecialchars(json_encode(['filas'=>$rows,'celdas'=>$cells,'variables_post'=>count($_POST),'max_input_vars'=>ini_get('max_input_vars')])).'</pre>'; exit;
}
$sample=json_decode(file_get_contents($root.'/output/cronogramas_mayo_2025/muestra_mayo_2025.json'),true);
$posts=json_decode(file_get_contents($root.'/output/cronogramas_mayo_2025/pruebas_armado_php.json'),true)['posts_mayo'];
$staff=[]; foreach($sample['nomina_por_objetivo'] as $r) $staff[]=['idUsuario'=>10000+abs($r['usuario_id']),'objetivo_id'=>5000+abs($r['objetivo_id']),'apellido'=>'Prueba','nombre'=>(string)abs($r['usuario_id'])];
?>
<!doctype html><html lang="es"><meta charset="UTF-8"><title>Regresión de cronogramas</title>
<style>body{font:14px sans-serif} .table-responsive{overflow:auto;max-height:500px} td{padding:2px} .d-none,[hidden]{display:none} .is-invalid{outline:2px solid red} #resultados{white-space:pre-wrap}</style>
<h1>Pruebas de tabla mensual</h1><pre id="resultados">Ejecutando…</pre>
<form id="form" method="post"><label>Objetivo <select id="objetivo" name="objetivo"><option value="5001">5001</option><option value="5002">5002</option></select></label>
<input id="mes" name="mes" type="month" value="2025-05"><button id="guardar" name="cargar" type="submit">Cargar</button><button id="btnGuardarCronograma" type="submit">Guardar prueba</button><button type="button" id="vaciar">Vaciar</button>
<div id="cronogramaEstado"></div><div id="feriados"><ul id="lista"></ul></div><div id="tabla"></div>
<input id="cronogramaObjetivo" name="cronograma_objetivo" type="hidden"><input id="cronogramaMes" name="cronograma_mes" type="hidden"><input id="cronogramaFilas" name="cronograma_filas" type="hidden"><input name="cronograma_completo" value="1" type="hidden"></form>
<script>const API_SIGLAS_URL='/siglas',API_VALIDAR_URL='/validar';</script><script src="/cronograma.js"></script>
<script>
const posts=<?=json_encode($posts,JSON_HEX_TAG)?>,staff=<?=json_encode($staff)?>;
const boot={vigiladores:staff,referentes:[],todosFeriados:[{fecha:'2025-05-01',motivo:'Prueba'}],datosPrevios:{}};
const results=[]; const check=(caso,ok)=>results.push({caso,correcto:!!ok});
(async()=>{
 await Cronograma.init({formId:'form',contenedorTablaId:'tabla',objetivoId:'objetivo',mesId:'mes',btnVaciarId:'vaciar',feriadosInfoId:'feriados',feriadosListaId:'lista'},boot);
 let totalRows=0,totalCells=0;
 for(const [obj,post] of Object.entries(posts)){
  boot.datosPrevios=post; await Cronograma.renderTabla(obj,'2025-05');
  const rows=[...document.querySelectorAll('#tabla tbody tr')],cells=[...document.querySelectorAll('.celda-turno')];
  check('nomina_'+obj,rows.length===staff.filter(p=>p.objetivo_id===Number(obj)).length);
  check('celdas_'+obj,cells.length===rows.length*31 && cells.every(s=>{const m=s.name.match(/vigilador\[(\d+)\]\[(\d+)\]/);return s.value===(post.vigilador?.[m[1]]?.[m[2]]||'');}));
  totalRows+=rows.length; totalCells+=cells.length;
 }
 const largest=Object.keys(posts).sort((a,b)=>Object.keys(posts[b].vigilador).length-Object.keys(posts[a].vigilador).length)[0];
 boot.datosPrevios=posts[largest]; await Cronograma.renderTabla(largest,'2025-05');
 check('feriado_visible',document.querySelector('#lista').textContent.includes('2025-05-01'));
 document.querySelector('#resultados').textContent=JSON.stringify({casos:results.length,correctos:results.filter(r=>r.correcto).length,filas:totalRows,celdas:totalCells,casos_detalle:results},null,2);
})().catch(e=>document.querySelector('#resultados').textContent=e.stack);
</script></html>
