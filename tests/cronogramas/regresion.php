<?php
declare(strict_types=1);
// Integra el código real con MySQL en una base desechable creada por este proceso.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
chdir(dirname(__DIR__, 2));
require 'modelos/conexion.php';
require 'controladores/cronograma.controller.php';
$db = Conexion::conectar();
if (!$db) throw new RuntimeException('MySQL no disponible.');
$original = $db->query('SELECT DATABASE()')->fetchColumn();
$testDb = 'spec_test_cronogramas_' . bin2hex(random_bytes(6));
$cases = [];
function check(string $name, bool $condition, array $detail = []): void {
    global $cases;
    $cases[] = ['caso' => $name, 'correcto' => $condition, 'detalle' => $detail];
    if (!$condition) throw new RuntimeException('Fallo: ' . $name . ' ' . json_encode($detail));
}
function postMes(int $objetivo, array $codes, string $mes = '2025-05', int $uid = 1, string $rol = 'vigilador'): array {
    $days = ['usuario' => $uid];
    for ($d = 1; $d <= (int)date('t', strtotime($mes . '-01')); $d++) $days[$d] = $codes[$d] ?? '';
    return ['objetivo' => $objetivo, 'mes' => $mes, 'cronograma_objetivo' => $objetivo, 'cronograma_mes' => $mes,
        'cronograma_filas' => 1, 'cronograma_completo' => '1', $rol => [$uid => $days]];
}
function resetTurnos(): void { global $db; $db->exec('DELETE FROM turnos'); $db->exec('DELETE FROM rotaciones_puestos'); }
function horarios(string $a = '07:00:00', string $b = '19:00:00', string $c = '07:00:00', string $d = '19:00:00'): void {
    global $db;
    $db->exec('DELETE FROM puestos_turnos');
    $s = $db->prepare('INSERT INTO puestos_turnos (puesto_id,numero_turno,hora_entrada,hora_salida) VALUES (?,?,?,?)');
    $s->execute([1,1,$a,$b]); $s->execute([2,1,$c,$d]);
    $s->execute([1,2,'19:00:00','07:00:00']); $s->execute([2,2,'19:00:00','07:00:00']);
}
function rejected(string $name, array $post): void {
    global $db;
    $before = $db->query('SELECT * FROM turnos ORDER BY idTurno')->fetchAll(PDO::FETCH_ASSOC);
    $error = '';
    try { ControladorCronogramas::guardarDatos($post); } catch (Throwable $e) { $error = $e->getMessage(); }
    check($name, $error !== '' && !$db->inTransaction() && $before === $db->query('SELECT * FROM turnos ORDER BY idTurno')->fetchAll(PDO::FETCH_ASSOC), ['mensaje' => $error]);
}
try {
    $db->exec("CREATE DATABASE `$testDb` CHARACTER SET utf8mb4");
    $db->exec("USE `$testDb`");
    foreach ([
        'roles (id INT PRIMARY KEY, categoria VARCHAR(30))',
        'usuarios (idUsuario INT PRIMARY KEY, apellido VARCHAR(80), nombre VARCHAR(80), rol_id INT, activo INT)',
        'objetivos (idObjetivo INT PRIMARY KEY, nombre VARCHAR(80), activo INT)',
        'objetivo_vigiladores (objetivo_id INT, vigilador_id INT)',
        'objetivo_referentes (objetivo_id INT, referente_id INT)',
        'objetivo_siglas (objetivo_id INT, sigla VARCHAR(5), horas DECIMAL(5,2), activo INT)',
        'puestos (idPuesto INT PRIMARY KEY, objetivo_id INT, activo INT)',
        'puestos_turnos (puesto_id INT, numero_turno INT, hora_entrada TIME, hora_salida TIME)',
        'rotaciones_puestos (usuario_id INT, objetivo_id INT, puesto_id INT, fecha DATE, codigo_turno VARCHAR(5))',
        "turnos (idTurno INT AUTO_INCREMENT PRIMARY KEY, usuario_id INT, objetivo_id INT, fecha DATE, rol VARCHAR(15), tipo_turno VARCHAR(15), codigo_turno VARCHAR(5), UNIQUE KEY idx_usuario_objetivo_fecha(usuario_id,objetivo_id,fecha))"
    ] as $schema) $db->exec('CREATE TABLE ' . $schema . ' ENGINE=InnoDB');
    $db->exec("INSERT INTO roles VALUES (1,'operativo'),(2,'referente')");
    $db->exec("INSERT INTO usuarios VALUES (1,'Prueba','Uno',1,1),(2,'Prueba','Dos',1,1),(3,'Prueba','Referente',2,1)");
    $db->exec("INSERT INTO objetivos VALUES (1,'Objetivo A',1),(2,'Objetivo B',1)");
    $db->exec('INSERT INTO objetivo_vigiladores VALUES (1,1),(2,1),(1,2),(2,2)');
    $db->exec('INSERT INTO objetivo_referentes VALUES (1,3),(2,3)');
    $db->exec('INSERT INTO puestos VALUES (1,1,1),(2,2,1)');
    horarios();
    $_SESSION = [];
    $stored = json_decode(file_get_contents('output/cronogramas_mayo_2025/pruebas_cruces.json'), true);
    foreach ($stored['casos'] as $c) {
        $same = $c['caso'] === 'mismo_objetivo_excluido'; $otherUser = $c['caso'] === 'persona_distinta_excluida';
        $a = ['usuario_id'=>1,'objetivo_id'=>1,'fecha'=>substr($c['franja_nueva'][0] ?? '2025-05-10',0,10),'codigo_turno'=>$c['codigo_nuevo'],
            'categoria'=>CronogramaReglas::categoria($c['codigo_nuevo'],1),'intervalo'=>$c['franja_nueva'] ? array_map('strtotime',$c['franja_nueva']) : null];
        $b = ['usuario_id'=>$otherUser?2:1,'objetivo_id'=>$same?1:2,'fecha'=>substr($c['franja_existente'][0] ?? '2025-05-10',0,10),'codigo_turno'=>$c['codigo_existente'],
            'categoria'=>CronogramaReglas::categoria($c['codigo_existente'],2),'intervalo'=>$c['franja_existente'] ? array_map('strtotime',$c['franja_existente']) : null];
        $result= CronogramaReglas::comparar($a,[$b]);
        check('cruce_' . $c['caso'], $result['conflicto'] === $c['conflicto_horario_esperado'], $result);
    }
    ControladorCronogramas::guardarDatos(postMes(1,[10=>'D']));
    check('guardar_simple', (int)$db->query('SELECT COUNT(*) FROM turnos')->fetchColumn() === 1);
    $db->exec("INSERT INTO rotaciones_puestos VALUES (1,1,1,'2025-05-10','D')");
    $esperado = ModeloTurnos::obtenerHorarioEsperado(1, 1, '2025-05-10');
    check('horario_esperado_usa_rotacion_y_turno_correcto', $esperado === [
        'hora_entrada'=>'07:00:00','hora_salida'=>'19:00:00','numero_turno'=>1
    ], ['horario'=>$esperado]);
    rejected('bloquear_D_superpuesto',postMes(2,[10=>'D']));
    resetTurnos(); horarios('07:00:00','15:00:00','15:00:00','23:00:00');
    ControladorCronogramas::guardarDatos(postMes(1,[10=>'8H']));
    ControladorCronogramas::guardarDatos(postMes(2,[10=>'8H']));
    check('permitir_turnos_consecutivos', (int)$db->query('SELECT COUNT(*) FROM turnos')->fetchColumn() === 2);
    resetTurnos(); horarios();
    ControladorCronogramas::guardarDatos(postMes(1,[31=>'N']));
    horarios('07:00:00','19:00:00','06:00:00','12:00:00');
    rejected('bloquear_cruce_entre_meses',postMes(2,[1=>'6H'],'2025-06'));
    resetTurnos(); horarios();
    ControladorCronogramas::guardarDatos(postMes(1,[10=>'GP/D']));
    ControladorCronogramas::guardarDatos(postMes(2,[10=>'D']));
    check('GP_sin_presencia_y_12horas', (int)$db->query('SELECT COUNT(*) FROM turnos')->fetchColumn()===2 && CronogramaReglas::horas('GP/D')===12.0);
    resetTurnos();
    ControladorCronogramas::guardarDatos(postMes(1,[10=>'PERR']));
    ControladorCronogramas::guardarDatos(postMes(2,[10=>'D']));
    check('referencia_no_duplica_presencia',(int)$db->query('SELECT COUNT(*) FROM turnos')->fetchColumn()===2);
    resetTurnos();
    ControladorCronogramas::guardarDatos(postMes(1,[10=>'D']));
    $db->exec('DELETE FROM puestos_turnos WHERE puesto_id=2');
    rejected('horario_desconocido_no_se_aprueba',postMes(2,[10=>'D']));
    rejected('vacio_no_borra',postMes(1,[]));
    $p=postMes(1,[10=>'N']); unset($p['vigilador'][1][31]); rejected('envio_truncado_no_borra',$p);
    $p=postMes(1,[10=>'N']); $p['mes']='2025-06'; rejected('cambio_mes_sin_cargar_no_borra',$p);
    $p=postMes(1,[10=>'N']); unset($p['cronograma_completo']); rejected('falta_marcador_no_borra',$p);
    $p=postMes(1,[10=>'N']); $p['referente'][1]=$p['vigilador'][1]; $p['cronograma_filas']=2; rejected('usuario_doble_fila_no_borra',$p);
    rejected('usuario_fuera_de_nomina',postMes(1,[10=>'N'],'2025-05',999));
    rejected('codigo_nuevo_desconocido',postMes(1,[10=>'ZZZ']));
    $p=postMes(1,[10=>'N']); $p['vigilador'][1][32]='D'; rejected('dia_invalido',$p);
    horarios();
    $db->exec("CREATE TRIGGER fallo_prueba BEFORE INSERT ON turnos FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Fallo inducido para rollback'");
    rejected('rollback_despues_del_delete',postMes(1,[10=>'N']));
    $db->exec('DROP TRIGGER fallo_prueba');
    $p=postMes(1,[10=>'N']); $p['cronograma_json']=json_encode(['vigilador'=>$p['vigilador']]); unset($p['vigilador']);
    ControladorCronogramas::guardarDatos($p);
    check('envio_json_conserva_filas',$db->query('SELECT codigo_turno FROM turnos')->fetchColumn()==='N');
    resetTurnos();
    $db->exec("INSERT INTO turnos (usuario_id,objetivo_id,fecha,rol,tipo_turno,codigo_turno) VALUES (1,1,'2025-05-10','Vigilador','Normal','ZZZ')");
    ControladorCronogramas::guardarDatos(postMes(1,[10=>'ZZZ']));
    check('codigo_historico_no_desaparece',$db->query('SELECT codigo_turno FROM turnos')->fetchColumn()==='ZZZ');
    resetTurnos();
    foreach (['2025-02'=>28,'2028-02'=>29,'2025-04'=>30,'2025-05'=>31] as $mes=>$days) {
        $r=ControladorCronogramas::precargarCronogramaSiExiste(1,$mes)['postSimulado'];
        check('calendario_'.$mes,count($r['vigilador'][1])===$days+1);
        check('escalonar_'.$mes,[$r['vigilador'][1][1],$r['vigilador'][2][1],$r['referente'][3][1]]===['D','N','F']);
    }
    foreach (['D','N','F'] as $code) foreach ([1,2] as $tail) {
        resetTurnos();
        $days=[31=>$code]; if ($tail===2) $days[30]=$code;
        ControladorCronogramas::guardarDatos(postMes(1,$days));
        $r=ControladorCronogramas::precargarCronogramaSiExiste(1,'2025-06')['postSimulado'];
        $expected=$tail===1?$code:['D'=>'N','N'=>'F','F'=>'D'][$code];
        check('continuidad_'.$code.$tail,$r['vigilador'][1][1]===$expected);
    }
    resetTurnos(); ControladorCronogramas::guardarDatos(postMes(1,[20=>'D']));
    $r=ControladorCronogramas::precargarCronogramaSiExiste(1,'2025-06')['postSimulado'];
    check('historial_incompleto_sin_fase_inventada',$r['vigilador'][1][1]==='' && !empty($_SESSION['cronograma_avisos']));
    resetTurnos(); ControladorCronogramas::guardarDatos(postMes(1,[31=>'12H']));
    $r=ControladorCronogramas::precargarCronogramaSiExiste(1,'2025-06')['postSimulado'];
    check('12H_no_se_convierte_en_franco',$r['vigilador'][1][1]==='' && !empty($_SESSION['cronograma_avisos']));
    check('mes_invalido_rechazado', (function(){ try { CronogramaReglas::mes('2025-13'); return false; } catch (InvalidArgumentException $e) { return true; } })());
} catch (Throwable $e) {
    $cases[]=['caso'=>'ejecutor','correcto'=>false,'detalle'=>['error'=>$e->getMessage()]];
} finally {
    if ($db->inTransaction()) $db->rollBack();
    $db->exec('USE `' . str_replace('`','``',$original) . '`');
    // Nombre generado por este proceso, nunca recibido del usuario o del archivo fuente.
    if (preg_match('/^spec_test_cronogramas_[a-f0-9]{12}$/D',$testDb)) $db->exec("DROP DATABASE IF EXISTS `$testDb`");
}
$summary=['casos'=>count($cases),'correctos'=>count(array_filter($cases,fn($c)=>$c['correcto'])),'base_prueba_eliminada'=>$testDb,'base_original_sin_datos_de_prueba'=>true];
file_put_contents('output/cronogramas_mayo_2025/regresion_corregida_php.json',json_encode(['resumen'=>$summary,'casos'=>$cases],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo json_encode($summary,JSON_UNESCAPED_UNICODE),PHP_EOL;
foreach ($cases as $c) if (!$c['correcto']) echo json_encode($c,JSON_UNESCAPED_UNICODE),PHP_EOL;
exit($summary['correctos']===count($cases)?0:1);
