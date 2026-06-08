<?php

$db = new Conexion;

if (isset($_POST['buscar_reportes'])) {
    $desde = $_POST['desde'];   // YYYY-MM-DD
    $hasta = $_POST['hasta'];   // YYYY-MM-DD

    // Validar fechas
    $d1 = DateTime::createFromFormat('Y-m-d', $desde);
    $d2 = DateTime::createFromFormat('Y-m-d', $hasta);
    if (
        !$d1 || $d1->format('Y-m-d') !== $desde ||
        !$d2 || $d2->format('Y-m-d') !== $hasta ||
        $desde > $hasta
    ) {
        ToastifyController::error("Fechas inválidas o 'Desde' > 'Hasta'.");
        unset($_SESSION['filtros']);
    } else {
        $_SESSION['filtros']['desde'] = $desde;
        $_SESSION['filtros']['hasta'] = $hasta;
    }
}

$sql = " SELECT r.idReporte, demora, DATE(r.fecha_hora) AS fecha, TIME(r.fecha_hora) AS hora, CONCAT(u.nombre, ' ', u.apellido) AS vigilador 
         FROM reporte_hombre_vivo AS r 
         JOIN usuarios AS u ON r.id_usuario = u.idUsuario ";

if (!empty($_SESSION['filtros']['desde']) && !empty($_SESSION['filtros']['hasta'])) {
    $desde = $_SESSION['filtros']['desde'];
    $hasta = $_SESSION['filtros']['hasta'];
    $sql .= " WHERE DATE(r.fecha_hora) BETWEEN '$desde' AND '$hasta'";
}

$sql .= " ORDER BY r.fecha_hora, hora";

$reportes = $db->consultas($sql);

// Contadores por color
$conteo = ['azul' => 0, 'verde' => 0, 'rojo' => 0];

// Preprocesar para contar
foreach ($reportes as $row) {
    $demoraStr = trim($row['demora']);
    $signo = '';

    if ($demoraStr[0] === '-') {
        $signo = '-';
        $demoraStr = substr($demoraStr, 1);
    } elseif ($demoraStr[0] === '+') {
        $signo = '+';
        $demoraStr = substr($demoraStr, 1);
    }

    // Normalizar a H:M:S
    $partes = explode(':', $demoraStr);
    while (count($partes) < 3) {
        array_unshift($partes, '00'); // agrega horas si faltan
    }

    list($h, $m, $s) = array_map('intval', $partes);
    $totalSeg = ($h * 3600) + ($m * 60) + $s;

    if ($signo === '-') {
        $totalSeg *= -1;
    }
    if ($totalSeg <= 0) {
        $conteo['azul']++;   // En tiempo o adelantado
    } elseif ($totalSeg <= 180) {
        $conteo['verde']++;  // ≤ 3 min tarde
    } else {
        $conteo['rojo']++;   // > 3 min tarde
    }
}
?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title">Listado de Reportes de Hombre Vivo</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($_SESSION['success_message'])): ?>
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <i class="icon fas fa-check"></i>
                                <?= $_SESSION['success_message'];
                                unset($_SESSION['success_message']); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Formulario de búsqueda -->
                        <form action="" method="POST" class="card p-4 shadow-sm mb-4">
                            <div class="form-row">
                                <div class="form-group col-md-2">
                                    <label>Desde</label>
                                    <input type="date" name="desde" class="form-control"
                                        value="<?= $_SESSION['filtros']['desde'] ?? '' ?>" required>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Hasta</label>
                                    <input type="date" name="hasta" class="form-control"
                                        value="<?= $_SESSION['filtros']['hasta'] ?? '' ?>" required>
                                </div>
                                <div class="form-group mt-4 col-md-2">
                                    <button type="submit" name="buscar_reportes" class="btn btn-primary mt-2">
                                        Buscar Reportes
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Resumen por color -->
                        <div class="mb-3">
                            <span class="badge badge-primary px-3 py-1">En tiempo: <?= $conteo['azul'] ?></span>
                            <span class="badge badge-success px-3 py-1">≤ 3 min: <?= $conteo['verde'] ?></span>
                            <span class="badge badge-danger px-3 py-1">> 3 min: <?= $conteo['rojo'] ?></span>
                        </div>

                        <!-- Botones de filtro -->
                        <div class="btn-group mb-3">
                            <button class="btn btn-sm btn-outline-primary" onclick="filtrarColor('badge-primary')">En tiempo</button>
                            <button class="btn btn-sm btn-outline-success" onclick="filtrarColor('badge-success')">≤ 3 min</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="filtrarColor('badge-danger')">> 3 min</button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="filtrarColor('')">Todos</button>
                        </div>

                        <!-- Tabla -->
                        <table id="example1" class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Vigilador</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Demora</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportes)): ?>
                                    <?php foreach ($reportes as $row): ?>
                                        <?php
                                        $demoraStr = trim($row['demora']);
                                        $signo = '';

                                        // Detectar signo
                                        if ($demoraStr[0] === '-') {
                                            $signo = '-';
                                            $demoraStr = substr($demoraStr, 1);
                                        } elseif ($demoraStr[0] === '+') {
                                            $signo = '+';
                                            $demoraStr = substr($demoraStr, 1);
                                        }

                                        // Normalizar a H:M:S (si faltan partes, se completan con ceros)
                                        $partes = explode(':', $demoraStr);
                                        while (count($partes) < 3) {
                                            array_unshift($partes, '00');
                                        }
                                        list($h, $m, $s) = array_map('intval', $partes);

                                        // Calcular segundos totales
                                        $totalSeg = ($h * 3600) + ($m * 60) + $s;
                                        if ($signo === '-') {
                                            $totalSeg *= -1;
                                        }

                                        // Formato para mostrar (mm:ss)
                                        $demoraFmt = sprintf('%02d:%02d', $m, $s);

                                        // Clasificación
                                        if ($totalSeg <= 0) {
                                            $badgeClass = 'badge-primary';
                                            $label = 'En tiempo';
                                        } elseif ($totalSeg <= 180) {
                                            $badgeClass = 'badge-success';
                                            $label = '≤ 3 min';
                                        } else {
                                            $badgeClass = 'badge-danger';
                                            $label = '> 3 min';
                                        }
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['vigilador']) ?></td>
                                            <td><?= (new DateTime($row['fecha']))->format('d/m/Y') ?></td>
                                            <td><?= htmlspecialchars($row['hora']) ?></td>
                                            <td><span class="badge <?= $badgeClass ?>"><?= $demoraFmt ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4">No hay reportes para el rango seleccionado.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>



                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    function filtrarColor(clase) {
        document.querySelectorAll('#example1 tbody tr').forEach(tr => {
            if (!clase) {
                tr.style.display = '';
            } else {
                const badge = tr.querySelector('.badge');
                tr.style.display = badge && badge.className.includes(clase) ? '' : 'none';
            }
        });
    }
</script>