<?php
$fecha = date('Y-m-d');
$hora = date('H:i');

?>
<!-- Default box -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Completa el formulario para registrar entrada o salida</h3>

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
                    <h3 class="card-title">Registrar entrada o salida del servicio</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form class="form-horizontal" method="POST">
                    <div class="card-body">
                        <?php
                        if (isset($_SESSION['success_message'])) {
                            echo '<div class="alert alert-success alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <p><i class="icon fas fa-check"></i>' . $_SESSION['success_message'] . '</p>
                                </div>';
                            // Elimina el mensaje después de mostrarlo
                            unset($_SESSION['success_message']);
                        };
                        ?>
                        <div class="row">
                            <div class="form-group col-sm-12 col-md-2">
                                <label class="form-label fw-bold">Usuario</label>
                                <input type="text" class="form-control" name="idUsuario" value="<?php echo $_SESSION['idUsuario']; ?>" hidden>
                                <input type="text" class="form-control" name="usuario" value="<?php echo $_SESSION['apellido'] . " " . $_SESSION['nombre']; ?>" readonly>
                            </div>
                            <div class="form-group col-sm-12 col-md-2">
                                <label class="form-label fw-bold">Fecha</label>
                                <input type="date" class="form-control mb-2" value="<?= $fecha ?>" readonly>
                            </div>
                            <div class="form-group col-sm-12 col-md-2">
                                <label class="form-label fw-bold">Hora</label>
                                <input type="time" class="form-control" value="<?= $hora ?>" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-12 col-md-3">
                                <label class="form-label fw-bold">Objetivo:</label>
                                <input type="text" class="form-control" value="" readonly>
                            </div>
                            <div class="form-group col-sm-12 col-md-2">
                                <label class="form-label fw-bold">Presiona para cambiar</label>
                                <div class="custom-control custom-switch ms-2">
                                    <input type="checkbox" name="tipo_registro" class="custom-control-input entrada-salida-switch switch-danger" id="entradaSalidaSwitch">
                                    <label class="custom-control-label" for="entradaSalidaSwitch" id="switchLabel">Registrar Entrada</label>
                                </div>
                                <small class="form-text text-muted" id="switchText">Se registrará la entrada</small>
                            </div>

                        </div>
                        <div class="row">
                            <div class="form-group col-sm-12 col-md-6">
                                <label for="detalle" class="form-label fw-bold">Novedades</label>
                                <textarea class="form-control" id="detalle" name="detalle" rows="8" placeholder="Escribe aquí las novedades reportadas" required></textarea>
                                </select>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer col-sm-12 col-md-6">
                        <input type="submit" class="btn btn-success" value="Registrar">
                        <button type="reset" class="btn btn-default float-right">Borrar campos</button>
                        <?php
                            $registro = NovedadesController::crtRegistrar();
                        ?>
                        <?php
                        if (isset($_SESSION['success_message'])) {
                            echo '<div class="alert alert-success alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <p><i class="icon fas fa-check"></i>' . $_SESSION['success_message'] . '</p>
                                </div>';
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
<script>
    /////////////////////////////////////////////////////////////////////////////////////////////////////
    //                Funcion para cambiar los colores del switch en novedades/entradas-salidas
    /////////////////////////////////////////////////////////////////////////////////////////////////////
    const switchInput = document.getElementById('entradaSalidaSwitch');
    const switchLabel = document.getElementById('switchLabel');
    const switchText = document.getElementById('switchText');

    switchInput.addEventListener('change', function() {
        switchLabel.textContent = this.checked ? 'Registrar Salida' : 'Registrar Entrada';
        switchText.textContent = this.checked ? 'Ahora se registrará la salida' : 'Se registrará la entrada';
    });
</script>