<?php
$mesActual = date('m');

$db = new Conexion;
$cumples = $db->consultas("
    SELECT nombre, apellido, rol, f_nac 
    FROM usuarios 
    WHERE MONTH(f_nac) = $mesActual
    ORDER BY DAY(f_nac)
");
?>
<div class="card">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">Cumpleaños del mes</h3>
    </div>

    <div class="card-body">
        <?php if (!empty($cumples)): ?>
            <div id="cumplesMesInfo" class="alert alert-secondary ">
                <strong>🎂 Cumpleaños del mes:</strong>
                <ul id="cumplesMesLista" class="mb-0">
                    <?php foreach ($cumples as $c): ?>
                        <li>
                            <?= date('d/m', strtotime($c['f_nac'])) ?> —
                            <?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido']) ?>
                            <small class="text-muted">(<?= $c['rol'] ?>)</small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <div class="alert alert-light small text-muted">
                No hay cumpleaños registrados para este mes.
            </div>
        <?php endif; ?>

    </div>
</div>