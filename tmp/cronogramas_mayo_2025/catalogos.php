<?php
// Lectura exclusiva de catálogos locales. No obtiene credenciales ni datos de contacto.
require dirname(__DIR__, 2) . '/modelos/conexion.php';
$db = Conexion::conectar();
if (!$db) { fwrite(STDERR, "No se pudo leer la base local.\n"); exit(1); }
$queries = [
    'objetivos' => 'SELECT idObjetivo,nombre,activo FROM objetivos ORDER BY idObjetivo',
    'usuarios' => 'SELECT u.idUsuario,u.apellido,u.nombre,u.activo,r.nombre AS rol,r.categoria FROM usuarios u LEFT JOIN roles r ON r.id=u.rol_id ORDER BY u.idUsuario',
    'siglas' => 'SELECT objetivo_id,sigla,descripcion,horas,activo FROM objetivo_siglas ORDER BY objetivo_id,sigla',
    'vigiladores' => 'SELECT objetivo_id,vigilador_id AS usuario_id FROM objetivo_vigiladores',
    'referentes' => 'SELECT objetivo_id,referente_id AS usuario_id FROM objetivo_referentes',
    'puestos' => 'SELECT idPuesto,puesto,objetivo_id,activo FROM puestos ORDER BY objetivo_id,idPuesto',
    'horarios' => 'SELECT puesto_id,numero_turno,hora_entrada,hora_salida FROM puestos_turnos ORDER BY puesto_id,numero_turno',
    'rotaciones_mayo' => "SELECT objetivo_id,fecha,puesto_id,usuario_id,codigo_turno FROM rotaciones_puestos WHERE fecha >= '2025-05-01' AND fecha < '2025-06-01'",
];
$out = ['capturado_en' => date(DATE_ATOM), 'alcance' => 'Catalogos locales actuales, no prueba de configuracion historica'];
foreach ($queries as $key => $sql) $out[$key] = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
file_put_contents(__DIR__ . '/catalogos.json', json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
echo json_encode(array_map('count', array_intersect_key($out, $queries)), JSON_UNESCAPED_UNICODE), PHP_EOL;
