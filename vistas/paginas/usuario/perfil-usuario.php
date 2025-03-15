<?php

$idUsuario = $_GET['id'];

$db = new Conexion;
$sql = "SELECT * FROM usuarios WHERE idUsuario =" . $idUsuario;
$usuario = $db->consultas($sql);

?>
<style>
    /* Cambiar color cuando está activado */
    .form-check-input:checked {
        background-color: var(--bs-success) !important;
        /* Color verde de Bootstrap */
        border-color: var(--bs-success) !important;
    }

    /* Alternativa para otros colores de Bootstrap */
    .switch-danger:checked {
        background-color: var(--bs-danger) !important;
        /* Rojo */
        border-color: var(--bs-danger) !important;
    }

    .switch-warning:checked {
        background-color: var(--bs-warning) !important;
        /* Amarillo */
        border-color: var(--bs-warning) !important;
    }
</style>
<!-- Default box -->
<div class="card">
    <div class="card-header bg-primary">
        <h3 class="card-title"><?php echo $usuario[0]['nombre'] . " " . $usuario[0]['apellido'] ?></h3>

        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                <i class="fas fa-minus"></i>
            </button>

        </div>
    </div>
    <div class="card-body">
        <!-- Main content -->
        <form class="form-horizontal" method="POST" enctype="multipart/form-data">
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-sm-12 col-md-2">
                        <div class="text-center">
                            <img class="profile-user-img img-fluid img-circle" src="<?php echo $usuario[0]['imgPerfil']; ?>" alt="Foto de perfil"                             alt="Carnet de REPRIV"
                            data-toggle="modal"
                            data-target="#imagenModal"
                            style="cursor:pointer;">
                        </div>
                    </div>
                    <div class="form-group col-sm-12 col-md-2">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" placeholder="Juan Carlos" name="nombre" value="<?php echo $usuario[0]['nombre'] ?>" required>
                    </div>
                    <div class="form-group col-sm-12 col-md-2">
                        <label class="form-label">Apellido</label>
                        <input type="text" class="form-control" placeholder="Perez" name="apellido" value="<?php echo  $usuario[0]['apellido'] ?>" required>
                    </div>
                    <div class="form-group col-sm-12 col-md-2">
                        <label class="form-label">DNI</label>
                        <input type="text" class="form-control" placeholder="12345678" name="dni" id="inputDNI" maxlength="8" value="<?php echo  $usuario[0]['dni'] ?>" required>
                        <small id="caracteresRestantes" class="form-text text-muted">Caracteres restantes: 8</small>
                    </div>
                    <div class="form-group col-sm-12 col-md-2">
                        <label class="form-label">Fecha Nac</label>
                        <input type="date" class="form-control" name="f_nac" value="<?php echo  $usuario[0]['f_nac'] ?>" required>
                    </div>
                    <div class="form-group col-sm-12 col-md-2">
                        <label class="form-label">Rol de Usuario</label>
                        <select class="custom-select" name="rol" required>
                            <option value="<?php echo  $usuario[0]['rol'] ?>" selected><?php echo $usuario[0]['rol'] ?></option>
                            <option value="Vigilador">Vigilador</option>
                            <option value="Referente">Referente</option>
                            <option value="Supervisor">Supervisor</option>
                            <option value="Administrador">Administrador</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-12 col-md-2">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control" placeholder="2612223333" name="telefono" value="<?php echo  $usuario[0]['telefono'] ?>" required>
                    </div>

                    <div class="form-group col-sm-12 col-md-2 text">
                        <label class="form-label">Teléfono de Emergencia</label>
                        <input type="text" class="form-control" placeholder="2612223333" name="tel_emergencia" value="<?php echo  $usuario[0]['tel_emergencia'] ?>" required>
                    </div>
                    <div class="form-group col-sm-12 col-md-5">
                        <label class="form-label">Domicilio</label>
                        <input type="text" class="form-control" placeholder="Av San Martin 123 Ciudad" name="domicilio" value="<?php echo  $usuario[0]['domicilio'] ?>" required>
                    </div>
                    <div class="form-group col-sm-12 col-md-2">
                        <label class="form-label">Provincia</label>
                        <select id="provincia" name="provincia" class="form-control" required>
                            <option value="<?php echo  $usuario[0]['provincia'] ?>" selected><?php echo  $usuario[0]['provincia'] ?></option>
                        </select>
                    </div>
                </div>
                <div class="row parent border-top border-secondary pt-3">
                    <div class="form-group col-sm-12 col-md-6 parent1 border m-3">
                        <label for="fotorepriv" class="form-label">Carnet de REPRIV</label>
                        <img src="<?php echo  $usuario[0]['imgRepriv']; ?>"
                            alt="Carnet de REPRIV"
                            width="250"
                            data-toggle="modal"
                            data-target="#imagenModal"
                            style="cursor:pointer;">
                    </div>

                    <div class="form-group col-sm-12 col-md-12 parent2">
                        <label for="inputGroupFile01" class="form-label">Cambiar Foto de Perfil</label>
                        <div class="input-group">
                            <input type="file" class="form-control" id="inputGroupFile01" name="imgPerfil" required>
                        </div>
                        <small class="form-text text-muted">Solo formatos .png, .jpg o .jpeg</small>
                    </div>
                    <div class="form-group col-sm-12 col-md-12 parent3">
                        <label for="inputGroupFile02" class="form-label">Cambiar Carnet de Repiv</label>
                        <div class="input-group">
                            <!--<label class="input-group-text" for="inputGroupFile02">cargar</label>-->
                            <input type="file" class="form-control" id="inputGroupFile02" name="imgRepriv" required>
                        </div>
                        <small class="form-text text-muted">Solo formatos .png, .jpg o .jpeg</small>
                    </div>

                    <div class="form-group col-sm-12 col-md-12 parent4 p-3 border border-secondary">
                        <label for="" class="form-label">Estado y seguridad</label>
                        <div class="custom-control custom-switch my-1">
                            <input type="checkbox" name="resetPass" class="custom-control-input switch-succss" id="customSwitch1">
                            <label class="custom-control-label text-secondary" for="customSwitch1">Restaurar contraseña</label>
                        </div>
                        <div class="custom-control custom-switch ">
                            <input type="checkbox" name="activo" class="custom-control-input switch-danger" id="customSwitch2">
                            <label class="custom-control-label text-secondary" for="customSwitch2">Dar de baja</label>
                        </div>
                    </div>

                </div>

                <!-- /.card-body -->
                <div class="card-footer">
                    <button type="reset" class="btn btn-default float-right">Borrar campos</button>
                    <?php
                    //$registro =  ControladorUsuarios::crtGuardarUsuario();
                    ?>

                    <input type="submit" class="btn btn-success" value="Modificar Datos">
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

</div>

