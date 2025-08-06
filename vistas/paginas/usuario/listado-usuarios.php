<?php
/*
$db = new Conexion;
$sql = "SELECT * FROM usuarios WHERE activo = 1 ORDER BY rol ";
$usuarios = $db->consultas($sql);
*/
?>

<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">

        <div class="card">
          <div class="card-header bg-info text-white">
            <h3 class="card-title">Listado de usuarios</h3>
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
                  <th style="text-align: center;">Apellido</th>
                  <th style="text-align: center;">Nombre</th>
                  <th style="text-align: center;">Celular</th>
                  <th style="text-align: center;">Celular emergencia</th>
                  <th style="text-align: center;">Contacto</th>
                  <th style="text-align: center;">Parentesco</th>
                  <th style="text-align: center;">Rol</th>
                  <?php if (in_array($_SESSION['rol'], ['Gerencia', 'Administrador'], true)): ?>
                  <th style="text-align: center;">Acciones</th>
                  <?php endif ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($usuarios as $campo => $valor) : ?>
                  <tr>
                    <td> <?php echo $valor['apellido']; ?></td>
                    <td> <?php echo $valor['nombre']; ?></td>
                    <td> <?php echo $valor['telefono']; ?></td>
                    <td> <?php echo $valor['tel_emergencia']; ?></td>
                    <td> <?php echo $valor['nombre_contacto']; ?></td>
                    <td> <?php echo $valor['parentesco']; ?></td>
                    <td> <?php echo $valor['rol']; ?></td>
                    <?php if (in_array($_SESSION['rol'], ['Gerencia', 'Administrador'], true)): ?>
                    <td>
                      <div class="row d-flex justify-content-around">
                        <a href="?r=perfil-usuario&id=<?php echo $valor["idUsuario"]; ?>" class="btn btn-success btn-sm"><i class="fas fa-eye"></i></a>
                        <form method="post">
                          <input type="hidden" value="<?php echo $valor["idUsuario"]; ?>" name="idEliminar">
                          <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></button>
                        </form>
                      </div>
                    </td>
                    <?php endif ?>
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