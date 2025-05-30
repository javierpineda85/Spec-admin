<!-- Default box -->
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Completa el formulario para dar de alta a un usuario</h3>

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
          <h3 class="card-title">Crear Usuario</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form class="form-horizontal" method="POST" enctype="multipart/form-data">
          <div class="card-body">
            <div class="row">
              <div class="form-group col-sm-12 col-md-3">
                <label class="form-label">Nombre</label>
                <input type="text" class="form-control" placeholder="Juan Carlos" name="nombre" required>
              </div>
              <div class="form-group col-sm-12 col-md-3">
                <label class="form-label">Apellido</label>
                <input type="text" class="form-control" placeholder="Perez" name="apellido" required>
              </div>
              <div class="form-group col-sm-12 col-md-2">
                <label class="form-label">DNI</label>
                <input type="text" class="form-control" placeholder="12345678" name="dni" id="inputDNI" maxlength="8" required>
                <small id="caracteresRestantes" class="form-text text-muted">Caracteres restantes: 8</small>
              </div>
              <div class="form-group col-sm-12 col-md-2">
                <label class="form-label">Contraseña</label>
                <input type="password" class="form-control" placeholder="DNI como contraseña" name="pass" id="inputPass" readonly>
              </div>

              <div class="form-group col-sm-12 col-md-2">
                <label class="form-label">Fecha Nac</label>
                <input type="date" class="form-control" name="f_nac" required>
              </div>

              <div class="form-group col-sm-12 col-md-2">
                <label class="form-label">Teléfono</label>
                <input type="text" class="form-control" placeholder="2612223333" name="telefono" required>
              </div>


              <div class="form-group col-sm-12 col-md-2">
                <label class="form-label">Teléfono de Emergencia</label>
                <input type="text" class="form-control" placeholder="2612223333" name="tel_emergencia" required>
              </div>
              <div class="form-group col-sm-12 col-md-2">
                <label class="form-label">Nombre de contacto</label>
                <input type="text" class="form-control" placeholder="Juan Perez" name="nombre_contacto" required>
              </div>
              <div class="form-group col-sm-12 col-md-2">
                <label class="form-label">Parentesco</label>
                <input type="text" class="form-control" placeholder="Parentesco" name="parentesco" required>
              </div>
              <div class="form-group col-sm-12 col-md-3">
                <label class="form-label">Rol de Usuario</label>
                <select class="custom-select" name="rol" required>
                  <option value="" disabled selected> Rol de usuario</option>
                  <option value="Vigilador">Vigilador</option>
                  <option value="Supervisor">Supervisor</option>
                  <option value="Gerencia">Gerencia</option>
                  <option value="Administrador">Administrador</option>
                </select>
              </div>
              <div class="form-group col-sm-12 col-md-4">
                <label class="form-label">Domicilio</label>
                <input type="text" class="form-control" placeholder="Av San Martin 123 Ciudad" name="domicilio" required>
              </div>
              <div class="form-group col-sm-12 col-md-2">
                <label class="form-label">Provincia</label>
                <select id="provincia" name="provincia" class="form-control" required>
                  <option value="" disabled selected>Elige una provincia</option>
                </select>
              </div>
              <div class="form-group col-sm-12 col-md-3">
                <label for="inputGroupFile01" class="form-label">Foto de Perfil</label>
                <div class="input-group">
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" id="inputGroupFile01" name="imgPerfil" required>
                    <label class="custom-file-label" for="inputGroupFile01">Selecciona un archivo</label>
                  </div>
                </div>
                <small class="form-text text-muted">Solo formatos .png, .jpg o .jpeg</small>
              </div>

              <div class="form-group col-sm-12 col-md-3">
                <label for="inputGroupFile02" class="form-label">Carnet de REPRIV</label>
                <div class="input-group">
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" id="inputGroupFile02" name="imgRepriv" required>
                    <label class="custom-file-label" for="inputGroupFile02">Selecciona un archivo</label>
                  </div>
                </div>
                <small class="form-text text-muted">Solo formatos .png, .jpg o .jpeg</small>
              </div>

            </div>

            <!-- /.card-body -->
            <div class="card-footer">
              <button type="reset" class="btn btn-default float-right">Borrar campos</button>
              <?php
              $registro =  ControladorUsuarios::crtGuardarUsuario();
              ?>

              <input type="submit" class="btn btn-success" value="Registrar">
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
      <!-- /.card -->
    </form>
  </div>
  <!-- /.card-body -->

</div>
<!-- /.card -->

</section>
<!-- /.content -->

