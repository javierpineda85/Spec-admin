<?php

$db = new Conexion;
$sql = "SELECT p.idPuesto, p.puesto, p.objetivo_id, p.tipo, o.nombre as objetivo FROM puestos p JOIN objetivos o ON p.objetivo_id = o.idObjetivo ORDER BY p.objetivo_id ";
$objetivos = $db->consultas($sql);

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
                        <table id="example1" class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">Puesto</th>
                                    <th style="text-align: center;">Objetivo</th>
                                    <th style="text-align: center;">tipo</th>
                                    <th style="text-align: center;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($objetivos as $campo => $valor) : ?>
                                    <tr>
                                        <td> <?= $valor['puesto'] ?></td>
                                        <td> <?= $valor['objetivo'] ?></td>
                                        <td> <?= $valor['tipo'] ?></td>
                                        <td>
                                            <div class="row d-flex justify-content-around">
                                                <a href="?r=editar_puesto&id=<?php echo $valor["idPuesto"]; ?>" class="btn btn-success btn-sm"><i class="fas fa-edit"></i></a>
                                                <form method="post">
                                                    <input type="hidden" value="<?php echo $valor["idPuesto"]; ?>" name="idEliminar">
                                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></button>
                                                    <?php

                                                    /* $eliminar = new ControladorFormularios();
                                            $eliminar->ctrEliminarVisita();*/

                                                    ?>

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