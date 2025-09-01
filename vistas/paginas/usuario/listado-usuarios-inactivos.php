<?php
if (isset($_POST['idReactivar'])) {
    ControladorUsuarios::crtReactivarUsuario();
}

// Nivel del usuario logueado
$nivelSesion   = isset($_SESSION['nivel']) ? (float) $_SESSION['nivel'] : 1.0;
// Solo niveles 3.0, 4.0, 5.0 y 99.0 pueden reactivar
$puedeGestionar = in_array($nivelSesion, [3.0, 4.0, 5.0, 99.0], true);
?>

<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">

        <div class="card">
          <div class="card-header bg-warning text-white">
            <h3 class="card-title">Listado de Usuarios Inactivos</h3>
          </div>
          <!-- /.card-header -->

          <div class="card-body">
            <table id="example1" class="table table-bordered table-striped table-sm">
              <thead>
                <tr>
                  <th style="text-align: center;">Empleado</th>
                  <th style="text-align: center;">Motivo</th>
                  <th style="text-align: center;">Fecha de baja</th>
                  <th style="text-align: center;">Responsable</th>
                  <?php if ($puedeGestionar): ?>
                    <th style="text-align: center;">Acciones</th>
                  <?php endif; ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($usuarios as $valor): ?>
                  <tr>
                    <td class="text-center"><?= htmlspecialchars($valor['empleado']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($valor['motivo']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($valor['fecha']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($valor['eliminado_por']) ?></td>

                    <?php if ($puedeGestionar): ?>
                      <td class="text-center">
                        <form method="post">
                          <input
                            type="hidden"
                            name="idReactivar"
                            value="<?= (int) $valor['idUsuario'] ?>"
                          >
                          <button
                            class="btn btn-success btn-sm"
                            title="Reactivar"
                          >
                            <i class="fas fa-check-circle"></i>
                          </button>
                        </form>
                      </td>
                    <?php endif; ?>
                  </tr>
                <?php endforeach; ?>
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
