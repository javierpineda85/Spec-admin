<?php
require_once 'modelos/Conexion.php'; 

echo "🧹 Limpieza de alertas iniciada...\n";

// Conexión
$db = new Conexion();

try {
    $sql = "DELETE FROM alertas 
            WHERE leida = 1 
              AND creada_en < NOW() - INTERVAL 30 DAY";
    $stmt = $db->consultas($sql);

    echo "✅ Alertas eliminadas correctamente.\n";
} catch (Exception $e) {
    echo "❌ Error al eliminar alertas: " . $e->getMessage() . "\n";
}
