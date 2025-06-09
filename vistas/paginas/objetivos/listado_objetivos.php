<?php
if (isset($_POST['idEliminar'])) {
    ControladorObjetivos::crtDesactivarObjetivo();
}
/*
$db = new Conexion;
$sql = "SELECT * FROM objetivos WHERE activo = 1 ORDER BY nombre";
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
                                    <th style="text-align: center;">Nombre</th>
                                    <th style="text-align: center;">Localidad</th>
                                    <th style="text-align: center;">tipo</th>
                                    <th style="text-align: center;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($objetivos as $campo => $valor) : ?>
                                    <tr>
                                        <td> <?= $valor['nombre'] ?></td>
                                        <td> <?= $valor['localidad'] ?></td>
                                        <td> <?= $valor['tipo'] ?></td>
                                        <td>
                                            <div class="d-flex justify-content-center">
                                                <!-- Botón Editar -->
                                                <a href="?r=editar_objetivo&id=<?php echo $valor["idObjetivo"]; ?>"class="btn btn-success btn-sm mr-1" title="Editar objetivo"><i class="fas fa-edit"></i>
                                                </a>

                                                <!-- Botón Eliminar -->
                                                <form method="post" style="display:inline-block;">
                                                    <input type="hidden" name="idEliminar" value="<?php echo $valor["idObjetivo"]; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Eliminar objetivo"  onclick="return confirm('¿Desea desactivar este objetivo?');"> <i class="fas fa-trash-alt"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th style="text-align: center;">Nombre</th>
                                    <th style="text-align: center;">Localidad</th>
                                    <th style="text-align: center;">tipo</th>
                                    <th style="text-align: center;">Acciones</th>
                                </tr>
                            </tfoot>
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