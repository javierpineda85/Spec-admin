<?php
if (isset($_POST['idReactivar'])) {
    ControladorUsuarios::crtReactivarUsuario();
}
$db = new Conexion;
$sql = "SELECT u.idUsuario, CONCAT(u.apellido, ' ', u.nombre) AS empleado, b.motivo, b.fecha, CONCAT(e.apellido, ' ', e.nombre) AS eliminado_por
                FROM usuarios u
                JOIN bajas b ON u.idUsuario = b.usuario_id
                JOIN usuarios e ON b.eliminado_por = e.idUsuario
                WHERE u.activo = 0
                ORDER BY empleado ";
$usuarios = $db->consultas($sql);


?>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title">Listado de usuarios inactivos</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <?php if (!empty($_SESSION['success_message'])): ?>
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <i class="icon fas fa-check"></i>
                                <?= $_SESSION['success_message'];
                                unset($_SESSION['success_message']); ?>
                            </div>
                        <?php endif; ?>
                        <table id="example1" class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">Empleado</th>
                                    <th style="text-align: center;">Motivo</th>
                                    <th style="text-align: center;">Fecha de baja</th>
                                    <th style="text-align: center;">Responsable</th>
                                    <th style="text-align: center;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $campo => $valor) : ?>
                                    <tr>
                                        <td> <?php echo $valor['empleado']; ?></td>
                                        <td> <?php echo $valor['motivo']; ?></td>
                                        <td> <?php echo $valor['fecha']; ?></td>
                                        <td> <?php echo $valor['eliminado_por']; ?></td>
                                        <td style="vertical-align: middle; text-align: center;">
                                            <div class="d-flex justify-content-center">
                                                <form method="post">
                                                    <input type="hidden" name="idReactivar" value="<?= $valor['idUsuario'] ?>" title="Reactivar">
                                                    <button class="btn btn-success btn-sm"><i class="fas fa-check-circle"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach ?>

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
    <!-- /.container-fluid -->
</section>
<!-- /.content -->