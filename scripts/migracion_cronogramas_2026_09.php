<?php
// php scripts/migracion_cronogramas_2026_09.php [--aplicar]
// No agrega columnas ni cambia turnos: ajusta motor e índice de unicidad.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/modelos/conexion.php';
$db = Conexion::conectar();
if (!$db) throw new RuntimeException('Sin conexión.');
$indices = $db->query('SHOW INDEX FROM turnos')->fetchAll(PDO::FETCH_ASSOC);
$unicos = [];
foreach ($indices as $i) if ((int)$i['Non_unique'] === 0) $unicos[$i['Key_name']][(int)$i['Seq_in_index']] = $i['Column_name'];
$clauses = [];
$hasTarget = false;
foreach ($unicos as $name => $columns) {
    ksort($columns); $columns = array_values($columns);
    if ($columns === ['usuario_id', 'fecha']) $clauses[] = 'DROP INDEX `' . str_replace('`', '``', $name) . '`';
    if ($columns === ['usuario_id', 'objetivo_id', 'fecha']) $hasTarget = true;
}
if (!$hasTarget) $clauses[] = 'ADD UNIQUE INDEX idx_usuario_objetivo_fecha (usuario_id, objetivo_id, fecha)';
$engine = $db->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'turnos'")->fetchColumn();
if (strtoupper((string)$engine) !== 'INNODB') $clauses[] = 'ENGINE=InnoDB';
if (!$clauses) { echo "El esquema de turnos ya está actualizado.\n"; exit; }
$sql = 'ALTER TABLE turnos ' . implode(', ', $clauses);
echo $sql, PHP_EOL;
if (!in_array('--aplicar', $argv, true)) { echo "Sin cambios. Usa --aplicar para ejecutar.\n"; exit; }
$before = $db->query('SELECT COUNT(*) FROM turnos')->fetchColumn();
$db->exec($sql);
$after = $db->query('SELECT COUNT(*) FROM turnos')->fetchColumn();
echo "Migración aplicada. Registros antes=$before; después=$after.\n";
