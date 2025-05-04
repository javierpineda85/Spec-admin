<?php
$db = new Conexion;
$sql = "SELECT * FROM objetivos ORDER BY nombre ";
$objetivos = $db->consultas($sql);

?>
<!-- Default box -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Completa el formulario para subir un nuevo cronograma</h3>

        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                <i class="fas fa-minus"></i>
            </button>

        </div>
    </div>
    <div class="card-body">
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Subir cronograma</h3>
                </div>

                <form class="form-horizontal" method="POST" enctype="multipart/form-data">
                    <div class="card-body">

                        <div class="row">
                            <div class="form-group col-sm-12 col-md-4">
                                <label for="objetivo" class="form-label fw-bold">Objetivo</label>
                                <select class="form-control" id="id_objetivo" name="id_objetivo" required>
                                    <option value="" disabled selected>Selecciona un objetivo</option>
                                    <?php foreach ($objetivos as $objetivo): ?>
                                        <option value="<?php echo $objetivo['idObjetivo'] ?>"><?php echo $objetivo['nombre'] ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-12 col-md-3">
                                <label for="inputGroupFile01" class="form-label">Subir cronograma</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="inputGroupFile01" name="imgCrono" required>
                                        <label class="custom-file-label" for="inputGroupFile01">Selecciona un archivo</label>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Solo formatos .png, .jpg o .jpeg</small>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="reset" class="btn btn-default float-right">Borrar campos</button>
                            <?php

                            $registro = ControladorCronograma::crtSubirCrono();
                            ?>
                            <?php
                            if (isset($_SESSION['success_message'])) {
                                echo '<div class="alert alert-success alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                        <p><i class="icon fas fa-check"></i>' . $_SESSION['success_message'] .
                                    '</p></div>';
                                // Elimina el mensaje después de mostrarlo
                                unset($_SESSION['success_message']);
                            };
                            ?>
                            <input type="submit" class="btn btn-success" value="Registrar">

                        </div>
                    </div>
                </form>
            </div>

        </form>
    </div>

</div>