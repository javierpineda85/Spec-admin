<?php
/*
$db = new Conexion();
$sql = "SELECT n.idNovedad, o.nombre AS objetivo, CONCAT(u.apellido, ' ', u.nombre) AS creado_por, n.fecha, n.hora, n.detalle, n.created_at,n.adjunto FROM novedades n LEFT JOIN objetivos o ON n.objetivo_id = o.idObjetivo LEFT JOIN usuarios u ON n.vigilador_id = u.idUsuario ORDER BY n.fecha DESC, n.hora DESC ";

$novedades = $db->consultas($sql);*/
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title">Listado de Novedades</h3>
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
                                <th style="text-align:center;">Objetivo</th>
                                <th style="text-align:center;">Creado por</th>
                                <th style="text-align:center;">Fecha</th>
                                <th style="text-align:center;">Hora</th>
                                <th style="text-align:center;">Adjunto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($novedades as $n): ?>
                                <tr>
                                    <td style="vertical-align:middle; text-align:center;">
                                        <?= htmlspecialchars($n['objetivo'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td style="vertical-align:middle; text-align:center;">
                                        <?= htmlspecialchars($n['creado_por'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td style="vertical-align:middle; text-align:center;">
                                        <?= htmlspecialchars(date_format(date_create($n['fecha']), 'd-m-Y'), ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td style="vertical-align:middle; text-align:center;">
                                        <?= htmlspecialchars($n['hora'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td style="vertical-align:middle; text-align:center;">
                                        <?php if (!empty($n['adjunto'])): ?>
                                            <a href="<?= htmlspecialchars($n['adjunto'], ENT_QUOTES, 'UTF-8') ?>"
                                                target="_blank" class="btn btn-info btn-sm" title="Ver adjunto">
                                                <i class="fas fa-paperclip"></i>
                                            </a>
                                        <?php else: ?>
                                            &mdash;
                                        <?php endif; ?>
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