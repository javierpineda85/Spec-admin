<?php

$db = new Conexion;
$sql = "SELECT * FROM directivas d JOIN objetivos o ON d.id_objetivo = o.idObjetivo ORDER BY d.id_objetivo";
$objetivos = $db->consultas($sql);

?>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Listado de directivas</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="example1" class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th style="text-align: center;" width="250px">Objetivo</th>
                                    <th style="text-align: center;">Detalle</th>
                                    <th style="text-align: center;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($objetivos as $campo => $valor) : ?>
                                    <tr>
                                        <td> <?php echo $valor['nombre']; ?></td>
                                        <td> <?php echo nl2br($valor['detalle']); ?></td><!-- nl2br permite imprimir los saltos de línea-->
                                        <td>
                                            <div class="row d-flex justify-content-around">
                                                <a href="?r=modificar_directiva&id=<?php echo $valor["idDirectiva"]; ?>" class="btn btn-success btn-sm"><i class="fas fa-edit"></i></a>
                                                <form method="post">
                                                    <input type="hidden" value="<?php echo $valor["idDirectiva"]; ?>" name="idEliminar">
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
                                    <th style="text-align: center;">Objetivos</th>
                                    <th style="text-align: center;">Detalles</th>
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