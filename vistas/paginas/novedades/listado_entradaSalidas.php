<?php

$db = new Conexion();
$sql = " SELECT m.idMarcacion,CONCAT(u.apellido, ' ', u.nombre) AS vigilador, o.nombre AS objetivo, m.tipo_evento, m.fecha_hora, m.latitud, m.longitud, m.created_at FROM marcaciones_servicio m JOIN usuarios u ON m.vigilador_id = u.idUsuario LEFT JOIN objetivos o ON m.objetivo_id = o.idObjetivo ORDER BY m.fecha_hora DESC ";

$marcaciones = $db->consultas($sql);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title">Reporte de Ingresos y Salidas a los Servicios</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <?php if (!empty($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible mt-3">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="icon fas fa-check"></i>
                            <?= $_SESSION['success_message'];
                            unset($_SESSION['success_message']); ?>
                        </div>
                    <?php endif; ?>
                    <table id="example1" class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th style="text-align:center;">Vigilador</th>
                                <th style="text-align:center;">Objetivo</th>
                                <th style="text-align:center;">Evento</th>
                                <th style="text-align:center;">Fecha</th>
                                <th style="text-align:center;">Hora</th>
                                <th style="text-align:center;">Latitud</th>
                                <th style="text-align:center;">Longitud</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($marcaciones as $m): ?>
                                <tr>
                                    <td style="vertical-align:middle; text-align:center;">
                                        <?= htmlspecialchars($m['vigilador'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td style="vertical-align:middle; text-align:center;">
                                        <?= htmlspecialchars($m['objetivo'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td style="vertical-align:middle; text-align:center;">
                                        <?= htmlspecialchars($m['tipo_evento'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td style="vertical-align:middle; text-align:center;">
                                        <?= htmlspecialchars(
                                            date_format(date_create($m['fecha_hora']), 'd-m-Y'),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>
                                    <td style="vertical-align:middle; text-align:center;">
                                        <?= htmlspecialchars(
                                            date_format(date_create($m['fecha_hora']), 'H:i'),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>
                                    <td style="vertical-align:middle; text-align:center;">
                                        <?= htmlspecialchars($m['latitud'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td style="vertical-align:middle; text-align:center;">
                                        <?= htmlspecialchars($m['longitud'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</div>
