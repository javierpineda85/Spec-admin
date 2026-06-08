<?php
if (isset($_POST['idEliminar'])) {
    ControladorPuestos::crtDesactivarPuesto();
}
/*
$db = new Conexion;
$sql = "SELECT p.idPuesto, p.puesto, p.objetivo_id, p.tipo, o.nombre as objetivo FROM puestos p JOIN objetivos o ON p.objetivo_id = o.idObjetivo WHERE p.activo = 1 ORDER BY p.objetivo_id ";
$objetivos = $db->consultas($sql);
*/
?>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title">Listado de objetivos</h3>
                    </div>
                    <?php if (!empty($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible mt-3">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="icon fas fa-check"></i>
                            <?= $_SESSION['success_message'];
                            unset($_SESSION['success_message']); ?>
                        </div>
                    <?php endif; ?>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <?php $esRestringido = in_array(($_SESSION['categoria'] ?? ''), ['operativo', 'referente'], true); ?>
                        <table id="example1" class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">Puesto</th>
                                    <th style="text-align: center;">Objetivo</th>
                                    <th style="text-align: center;">tipo</th>
                                    <?php if ($_SESSION['nivel'] > 2 && !$esRestringido): ?>
                                        <th style="text-align: center;">Acciones</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($objetivos as $campo => $valor) : ?>
                                    <tr>
                                        <td> <?= $valor['puesto'] ?></td>
                                        <td> <?= $valor['objetivo'] ?></td>
                                        <td> <?= $valor['tipo'] ?></td>
                                        <?php if ($_SESSION['nivel'] > 2 && !$esRestringido): ?>
                                            <td style="vertical-align: middle; text-align: center;">
                                                <div class="d-flex justify-content-center">
                                                    <!-- Editar -->
                                                    <a href="?r=editar_puesto&id=<?= $valor['idPuesto']; ?>"
                                                        class="btn btn-success btn-sm mr-1"
                                                        title="Editar puesto">
                                                        <i class="fas fa-edit"></i>
                                                    </a>

                                                    <!-- Desactivar -->
                                                    <form method="post" style="display:inline-block;">
                                                        <input type="hidden" name="idEliminar" value="<?= $valor['idPuesto']; ?>">
                                                        <button type="submit" class="btn btn-warning btn-sm"
                                                            title="Desactivar puesto"
                                                            onclick="return confirm('¿Desea desactivar este puesto?');">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        <?php endif; ?>
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
