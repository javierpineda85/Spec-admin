<?php
require __DIR__ . '/../../../modelos/conexion.php'; // ajusta la ruta a tu conexión
$pdo = Conexion::conectar();
$usuario = intval($_GET['usuario']);
$dia     = intval($_GET['dia']);
$mes     = intval($_GET['mes']);
$anio    = intval($_GET['anio']);
$codigo  = strtoupper(trim($_GET['codigo']));
$objetivoActual = isset($_GET['objetivo_actual']) ? intval($_GET['objetivo_actual']) : 0; // 👈 nuevo

// Constantes
$jornadaNormalHoras = [
    '6H' => 6,
    '7H' => 7,
    '8H' => 8,
    '9H' => 9,
    '9RF' => 9,
    '9HEX' => 9,
    '13H' => 13,
    '14H' => 14,
    'N15' => 15,
    'D/LEM' => 8,
    'D/GU' => 8,
    'D/AR' => 8,
    'D/LUJ' => 8,
    'D/LH' => 8,
    'D/GC' => 8,
    'D/MA' => 8,
    'BE' => 8
];
$referencias = ['SALA', 'MIC', 'F/JUS', 'NOTT', 'GUE', 'PER', 'PAL', 'BOS', 'OFI'];
$licencias = ['F', 'GP/D', 'GP/N', 'E', 'P', 'S.'];

function tipoDeCodigo($code, $jornadaNormalHoras, $referencias, $licencias)
{
    if (isset($jornadaNormalHoras[$code])) return 'jornada';
    if (in_array($code, $referencias)) return 'referencia';
    if (in_array($code, $licencias)) return 'licencia';
    return 'otro';
}

$tipoNuevo = tipoDeCodigo($codigo, $jornadaNormalHoras, $referencias, $licencias);

// Si es referencia, no hay conflicto
if ($tipoNuevo === 'referencia') {
    echo json_encode(['conflicto' => false]);
    exit;
}

// Buscar en otros objetivos para ese usuario y fecha
$sql = "SELECT o.nombre AS objetivo, t.codigo_turno
        FROM turnos t
        INNER JOIN objetivos o ON o.idObjetivo = t.objetivo_id
        WHERE t.usuario_id = ?
          AND DATE(t.fecha) = ?
          AND t.objetivo_id <> ?"; // 👈 excluye el actual

$fecha = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);

$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario, $fecha, $objetivoActual]);

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
// 🔍 Debug no intrusivo: queda en el log de PHP
error_log("validar-turno-global → usuario=$usuario, fecha=$fecha, objetivoActual=$objetivoActual, filas=".count($rows));
if ($rows) {
    error_log(print_r($rows, true));
}
foreach ($rows as $row) {
    $tipoExistente = tipoDeCodigo($row['codigo_turno'], $jornadaNormalHoras, $referencias, $licencias);
    if ($tipoExistente === 'jornada' || $tipoExistente === 'licencia') {
        echo json_encode([
            'conflicto' => true,
            'objetivo' => $row['objetivo'],
            'codigo_existente' => $row['codigo_turno']
        ]);
        exit;
    }
}

echo json_encode(['conflicto' => false]);
