<?php
$id = $_GET['id'];
$db = new Conexion;
$sql = "SELECT d.idDirectiva, d.id_objetivo, o.nombre, d.detalle FROM directivas d JOIN objetivos o ON d.id_objetivo = o.idObjetivo WHERE d.idDirectiva = $id";
$directiva = $db->consultas($sql);

$db = new Conexion;
$sql = "SELECT idObjetivo, nombre FROM objetivos";
$objetivos = $db->consultas($sql);

?>

<!-- Default box -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Completa el formulario para crear una nueva directiva</h3>

        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                <i class="fas fa-minus"></i>
            </button>

        </div>
    </div>
    <div class="card-body">
        <form action="" method="POST">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Modificar Directiva</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form class="form-horizontal" method="POST">
                    <div class="card-body">
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
                        <div class="row">
                            <div class="form-group col-sm-12 col-md-6">

                                <input type="number" name="idDirectiva" value="<?php echo $directiva[0]['idDirectiva']; ?>" hidden>

                                <label for="objetivo" class="form-label fw-bold">Objetivo</label>
                                <select class="form-control" id="id_objetivo" name="id_objetivo" required>
                                    <option value="<?php echo $directiva[0]['id_objetivo']; ?>" selected><?php echo $directiva[0]['nombre']; ?></option>
                                    <?php foreach ($objetivos as $objetivo): ?>
                                        <option value="<?php echo $objetivo['idObjetivo'] ?>"><?php echo $objetivo['nombre'] ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-12 col-md-6">
                                <label for="detalle" class="form-label fw-bold">Detalle</label>
                                <textarea class="form-control" id="detalle" name="detalle" rows="8" placeholder="Escribe aquí los detalles..." required><?php echo $directiva[0]['detalle']; ?></textarea>
                                </select>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer col-sm-12 col-md-6">
                        <input type="submit" class="btn btn-success" value="Modificar">
                        <button type="reset" class="btn btn-default float-right">Borrar campos</button>
                        <?php
                            $registro = ControladorDirectivas::crtModificarDirectiva();
                        ?>

                        <?php
                        if (isset($_SESSION['success_message'])) {
                            echo '<div class="alert alert-success alert-dismissible">
                       <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                       <h5><i class="icon fas fa-check"></i></h5>' . $_SESSION['success_message'] .
                                '</div>';
                            // Elimina el mensaje después de mostrarlo
                            unset($_SESSION['success_message']);
                        };
                        ?>


                    </div>
                    <!-- /.card-footer -->
                </form>
            </div>
            <!-- /.card-info -->
        </form>
    </div>
    <!-- /.card-body -->

</div>
<!-- /.card -->