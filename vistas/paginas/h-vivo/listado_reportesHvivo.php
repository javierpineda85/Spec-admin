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
        $_SESSION['error_message'] = "Fechas inválidas o 'Desde' > 'Hasta'.";
        unset($_SESSION['filtros']);
    } else {
        // Guardar en sesión para rellenar el formulario
        $_SESSION['filtros']['desde'] = $desde;
        $_SESSION['filtros']['hasta'] = $hasta;
    }
}

// Construir y ejecutar la consulta con o sin filtro
$sql = " SELECT r.idReporte, DATE(r.fecha_hora) AS fecha,TIME(r.fecha_hora) AS hora, CONCAT(u.nombre, ' ', u.apellido) AS vigilador FROM reporte_hombre_vivo AS r JOIN usuarios AS u ON r.id_usuario = u.idUsuario ";

// Si tenemos fechas válidas en sesión, aplicamos BETWEEN
if (!empty($_SESSION['filtros']['desde']) && !empty($_SESSION['filtros']['hasta'])) {
    $desde = $_SESSION['filtros']['desde'];
    $hasta = $_SESSION['filtros']['hasta'];
    $sql .= " WHERE DATE(r.fecha_hora) BETWEEN '$desde' AND '$hasta'";
}

// Siempre ordenamos
$sql .= " ORDER BY r.fecha_hora, hora";

// Ejecutamos con tu método habitual
$reportes = $db->consultas($sql);
?>

<!-- Main content -->
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
                        <table id="example1" class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Vigilador</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportes)): ?>
                                    <?php foreach ($reportes as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['vigilador']) ?></td>
                                            <td><?= (new DateTime($row['fecha']))->format('d/m/Y') ?></td>
                                            <td><?= htmlspecialchars($row['hora']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3">No hay reportes para el rango seleccionado.</td>
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
<!-- /.content -->