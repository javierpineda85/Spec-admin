<?php
declare(strict_types=1);
// Ejecuta los controladores/modelos originales contra un adaptador de lectura en memoria.
// No incluye conexion.php, no abre PDO y rechaza cualquier SQL que no sea SELECT.
final class Conexion {
    public static array $turnos = [];
    public static array $nomina = [];
    public static array $consultas = [];
    public static function conectar(): self { return new self(); }
    public function prepare(string $sql): ConsultaMemoria {
        if (!preg_match('/^\s*SELECT\b/i', $sql)) throw new RuntimeException('SQL no autorizado en pruebas');
        return new ConsultaMemoria($sql);
    }
    public function consultas(string $sql): array {
        if (!preg_match('/^\s*SELECT\b/i', $sql)) throw new RuntimeException('SQL no autorizado en pruebas');
        if (!preg_match('/(?:ov|orf)\.objetivo_id\s*=\s*(-?\d+)/', $sql, $m)) throw new RuntimeException('Consulta de nómina desconocida');
        $rol = str_contains($sql, 'objetivo_referentes') ? 'Referente' : 'Vigilador';
        return array_values(array_map(fn($x) => ['idUsuario' => $x['usuario_id']], array_filter(self::$nomina,
            fn($x) => $x['objetivo_id'] === (int)$m[1] && $x['rol'] === $rol && $x['activo'])));
    }
}
final class ConsultaMemoria {
    private array $rows = [];
    public function __construct(private string $sql) {}
    public function execute(array $params): bool {
        Conexion::$consultas[] = ['sql' => $this->sql, 'parametros' => $params];
        if (str_contains($this->sql, 'fecha LIKE')) {
            [$obj, $month] = $params;
            $month = rtrim($month, '%');
            $this->rows = array_values(array_filter(Conexion::$turnos, fn($t) => $t['objetivo_id'] === $obj && str_starts_with($t['fecha'], $month)));
            usort($this->rows, fn($a,$b) => [$a['fecha'],$a['usuario_id']] <=> [$b['fecha'],$b['usuario_id']]);
        } elseif (str_contains($this->sql, 'ORDER BY fecha DESC LIMIT 1')) {
            $this->rows = array_values(array_filter(Conexion::$turnos, fn($t) => $t['objetivo_id'] === $params[':obj'] && $t['usuario_id'] === $params[':uid'] && $t['fecha'] >= $params[':ini'] && $t['fecha'] <= $params[':fin']));
            usort($this->rows, fn($a,$b) => strcmp($b['fecha'],$a['fecha']));
            $this->rows = array_slice($this->rows,0,1);
        } else throw new RuntimeException('Consulta no modelada: '.$this->sql);
        return true;
    }
    public function fetchAll(int $mode): array { return $this->rows; }
    public function fetch(int $mode): array|false { return $this->rows[0] ?? false; }
}
$root = dirname(__DIR__,2);
chdir($root);
require 'controladores/cronograma.controller.php';
date_default_timezone_set('America/Argentina/Buenos_Aires');
$_SESSION = [];
$sample = json_decode(file_get_contents('output/cronogramas_mayo_2025/muestra_mayo_2025.json'),true,512,JSON_THROW_ON_ERROR);
$uid = fn(int $id): int => 10000 + abs($id);
$oid = fn(int $id): int => 5000 + abs($id);
$tests = [];
$observations = [];
function checkCase(string $id, bool $ok, array $details=[]): void {
    global $tests;
    $tests[] = ['caso'=>$id,'resultado'=>$ok?'correcto':'fallo','detalle'=>$details];
}
function postTurns(array $post): array {
    $out = [];
    foreach (['vigilador','referente'] as $rol) foreach ($post[$rol] ?? [] as $u=>$days) foreach ($days as $day=>$code) {
        if ($day === 'usuario') continue;
        $out[] = ['usuario_id'=>(int)$u,'objetivo_id'=>(int)$post['objetivo'],'fecha'=>$post['mes'].'-'.str_pad((string)$day,2,'0',STR_PAD_LEFT),'codigo_turno'=>$code,'rol'=>ucfirst($rol)];
    }
    return $out;
}
function days(array $post, string $rol, int $id, int $n=6): array {
    $row=$post[$rol][$id] ?? []; unset($row['usuario']); return array_slice(array_values($row),0,$n);
}
function seed(array $sample, string $rol='Vigilador'): void {
    global $uid,$oid;
    Conexion::$turnos = array_map(fn($a)=>['usuario_id'=>$uid($a['usuario_id']),'objetivo_id'=>$oid($a['objetivo_id']),'fecha'=>$a['fecha'],'codigo_turno'=>$a['codigo_turno'],'rol'=>$rol],$sample['asignaciones']);
    Conexion::$nomina = array_map(fn($r)=>['usuario_id'=>$uid($r['usuario_id']),'objetivo_id'=>$oid($r['objetivo_id']),'rol'=>$rol,'activo'=>true],$sample['nomina_por_objetivo']);
}
seed($sample);
$postsMay = [];
$postsJune = [];
foreach ($sample['objetivos'] as $obj) {
    $id=$oid($obj['idObjetivo']);
    $loaded=ControladorCronogramas::precargarCronogramaSiExiste($id,'2025-05');
    $post=$loaded['postSimulado'];
    $actual=postTurns($post);
    $expected=array_values(array_filter(Conexion::$turnos,fn($t)=>$t['objetivo_id']===$id));
    $sort=function(&$rows) { usort($rows,fn($a,$b)=>[$a['usuario_id'],$a['fecha']]<=>[$b['usuario_id'],$b['fecha']]); };
    $sort($actual); $sort($expected);
    checkCase('mes_existente_'.$id,$loaded['origen']==='actual' && $actual===$expected,['objetivo'=>$obj['nombre'],'celdas'=>count($actual)]);
    $postsMay[$id]=$post;
    $loaded=ControladorCronogramas::precargarCronogramaSiExiste($id,'2025-06');
    $post=$loaded['postSimulado'];
    $roster=array_values(array_filter(Conexion::$nomina,fn($r)=>$r['objetivo_id']===$id));
    checkCase('estructura_junio_'.$id,$loaded['origen']==='anterior' && count($post['vigilador'])===count($roster) && count(postTurns($post))===count($roster)*30,['objetivo'=>$obj['nombre'],'filas'=>count($roster)]);
    $postsJune[$id]=$post;
}

$continue=new ReflectionMethod(ControladorCronogramas::class,'continuar4x2DesdeTurnosAnteriores');
foreach (['Vigilador','Referente'] as $rol) {
    foreach ([['F','D','DNNFFD'],['D','D','NNFFDD'],['D','N','NFFDDN'],['N','N','FFDDNN'],['N','F','FDDNNF'],['F','F','DDNNFF']] as [$a,$b,$want]) {
        Conexion::$nomina=[['usuario_id'=>10001,'objetivo_id'=>5001,'rol'=>$rol,'activo'=>true]];
        $history=[['usuario_id'=>10001,'objetivo_id'=>5001,'fecha'=>'2025-05-30','rol'=>$rol,'codigo_turno'=>$a],['usuario_id'=>10001,'objetivo_id'=>5001,'fecha'=>'2025-05-31','rol'=>$rol,'codigo_turno'=>$b]];
        $post=$continue->invoke(null,$history,5001,'2025-06');
        $got=implode('',days($post,strtolower($rol),10001));
        checkCase('ciclo_'.$rol.'_'.$a.$b,$got===$want,['esperado'=>$want,'obtenido'=>$got]);
    }
}
foreach (['2025-02'=>28,'2028-02'=>29,'2025-04'=>30,'2025-05'=>31] as $month=>$length) {
    Conexion::$nomina=[['usuario_id'=>10001,'objetivo_id'=>5001,'rol'=>'Vigilador','activo'=>true],['usuario_id'=>10002,'objetivo_id'=>5001,'rol'=>'Referente','activo'=>true]];
    Conexion::$turnos=[];
    $r=ControladorCronogramas::precargarCronogramaSiExiste(5001,$month);
    $p=$r['postSimulado'];
    checkCase('mes_nuevo_'.$month,$r['origen']==='vacio' && count(postTurns($p))===$length*2 && days($p,'vigilador',10001)===['D','D','N','N','F','F'] && days($p,'referente',10002)===['D','D','N','N','F','F']);
}
// Cambio de año: se debe consultar diciembre y no otro mes/año.
Conexion::$turnos=[['usuario_id'=>10001,'objetivo_id'=>5001,'fecha'=>'2025-12-30','rol'=>'Vigilador','codigo_turno'=>'N'],['usuario_id'=>10001,'objetivo_id'=>5001,'fecha'=>'2025-12-31','rol'=>'Vigilador','codigo_turno'=>'N']];
$p=ControladorCronogramas::precargarCronogramaSiExiste(5001,'2026-01');
checkCase('cambio_anio',$p['origen']==='anterior' && days($p['postSimulado'],'vigilador',10001)===['F','F','D','D','N','N']);
// Alta de una persona sin historial, y exclusión de una persona inactiva en el mes nuevo.
Conexion::$nomina[]=['usuario_id'=>10003,'objetivo_id'=>5001,'rol'=>'Vigilador','activo'=>true];
Conexion::$nomina[]=['usuario_id'=>10004,'objetivo_id'=>5001,'rol'=>'Vigilador','activo'=>false];
$p=ControladorCronogramas::precargarCronogramaSiExiste(5001,'2026-01')['postSimulado'];
checkCase('alta_sin_historial',days($p,'vigilador',10003)===['D','D','N','N','F','F']);
checkCase('inactivo_no_generado',!isset($p['vigilador'][10004]));

// Matriz de muestra completa con el rol Referente como escenario, no hecho histórico.
seed($sample,'Referente');
$referenceRows=0;
foreach ($sample['objetivos'] as $o) {
    $p=ControladorCronogramas::precargarCronogramaSiExiste($oid($o['idObjetivo']),'2025-06')['postSimulado'];
    $referenceRows+=count($p['referente']);
}
checkCase('muestra_completa_como_referentes',$referenceRows===173);

// Caracterización de finales especiales: no impone una franja D/N aún desconocida.
Conexion::$nomina=[['usuario_id'=>10001,'objetivo_id'=>5001,'rol'=>'Vigilador','activo'=>true]];
foreach (['8H','10H','12H','13H','N15','GP/D','GP/N','NOTT','VAC'] as $code) {
    $h=[['usuario_id'=>10001,'rol'=>'Vigilador','fecha'=>'2025-05-31','codigo_turno'=>$code]];
    $p=$continue->invoke(null,$h,5001,'2025-06');
    $observations['continuidad_codigos'][$code]=days($p,'vigilador',10001);
}
$h=[['usuario_id'=>10001,'rol'=>'Vigilador','fecha'=>'2025-05-19','codigo_turno'=>'D'],['usuario_id'=>10001,'rol'=>'Vigilador','fecha'=>'2025-05-20','codigo_turno'=>'D']];
$observations['historial_terminado_20_mayo']=['historial'=>$h,'junio'=>$continue->invoke(null,$h,5001,'2025-06')['vigilador'][10001]];
seed($sample);
$last=[];
foreach (Conexion::$turnos as $t) {
    $k=$t['objetivo_id'].':'.$t['usuario_id'];
    if (!isset($last[$k]) || $t['fecha']>$last[$k]['fecha']) $last[$k]=$t;
}
$observations['filas_con_historial_sin_31_mayo']=array_values(array_filter($last,fn($t)=>$t['fecha']<'2025-05-31'));
$observations['filas_sin_historial']=array_values(array_filter(Conexion::$nomina,fn($r)=>!isset($last[$r['objetivo_id'].':'.$r['usuario_id']])));
$observations['filas_ultimo_codigo_10H_12H']=array_values(array_filter($last,fn($t)=>in_array($t['codigo_turno'],['10H','12H'],true)));
// Sin historial todos empiezan con D, sin escalonar cobertura ni controlar otros objetivos.
Conexion::$turnos=[];
$new=[];
foreach ($sample['objetivos'] as $o) $new[$oid($o['idObjetivo'])]=ControladorCronogramas::precargarCronogramaSiExiste($oid($o['idObjetivo']),'2025-07')['postSimulado'];
$observations['nuevo_desde_cero_dia_1']=array_count_values(array_map(fn($t)=>$t['codigo_turno'],array_filter(array_merge(...array_map('postTurns',$new)),fn($t)=>str_ends_with($t['fecha'],'-01'))));
$hours=new ReflectionMethod(ControladorCronogramas::class,'horasCodigoCronograma');
checkCase('gp_conserva_12h',$hours->invoke(null,'GP/D',[])===12.0 && $hours->invoke(null,'GP/N',[])===12.0);
checkCase('solo_select_en_adaptador',!array_filter(Conexion::$consultas,fn($q)=>!preg_match('/^\s*SELECT\b/i',$q['sql'])));
$result=['alcance'=>'PHP original y adaptador SELECT en memoria. No prueba MySQL, guardado, autenticación ni navegador real.',
    'roles'=>'Muestra ejecutada como Vigilador y escenario adicional como Referente; el Excel no documenta roles.',
    'ids'=>'IDs sintéticos positivos solo en memoria: usuario=10000+abs(id_muestra), objetivo=5000+abs(id_muestra).',
    'casos'=>$tests,'observaciones'=>$observations,'posts_mayo'=>$postsMay,'posts_junio'=>$postsJune,
    'resumen'=>['casos'=>count($tests),'correctos'=>count(array_filter($tests,fn($t)=>$t['resultado']==='correcto')),'consultas_select'=>count(Conexion::$consultas)]];
file_put_contents('output/cronogramas_mayo_2025/pruebas_armado_php.json',json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));
echo json_encode($result['resumen'],JSON_UNESCAPED_UNICODE),PHP_EOL;
exit($result['resumen']['correctos']===count($tests)?0:1);
