<?php

$db = new Conexion;
$sql = "SELECT * FROM objetivos ORDER BY nombre";
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
                        <?php
                        if (isset($_SESSION['success_message'])) {
                            echo '<div class="alert alert-success alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <p><i class="icon fas fa-check"></i>' . $_SESSION['success_message'] .'</p>
                                </div>';
                            // Elimina el mensaje después de mostrarlo
                            unset($_SESSION['success_message']);
                        };
                        ?>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
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
                                            <div class="row d-flex justify-content-around">
                                                <a href="?r=editar_objetivo&id=<?php echo $valor["idObjetivo"]; ?>" class="btn btn-success btn-sm"><i class="fas fa-edit"></i></a>
                                                <form method="post">
                                                    <input type="hidden" value="<?php echo $valor["idObjetivo"]; ?>" name="idEliminar">
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