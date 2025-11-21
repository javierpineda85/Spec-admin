<?php
$nivelSesion = isset($_SESSION['nivel']) ? (float)$_SESSION['nivel'] : 1.0;
$puedeGestionar = in_array($nivelSesion, [3.0, 4.0, 5.0, 99.0], true);

 ?>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">

        <div class="card">
          <div class="card-header bg-info text-white">
            <h3 class="card-title">Listado de usuarios</h3>
          </div>

          <div class="card-body">


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
                  <?php if ($puedeGestionar): ?>
                    <th style="text-align: center;">Acciones</th>
                  <?php endif ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($usuarios as $valor): ?>
                  <tr>
                    <td><?= htmlspecialchars($valor['apellido']) ?></td>
                    <td><?= htmlspecialchars($valor['nombre']) ?></td>
                    <td><?= htmlspecialchars($valor['telefono']) ?></td>
                    <td><?= htmlspecialchars($valor['tel_emergencia']) ?></td>
                    <td><?= htmlspecialchars($valor['nombre_contacto']) ?></td>
                    <td><?= htmlspecialchars($valor['parentesco']) ?></td>
                    <td><?= htmlspecialchars($valor['rol_nombre']) ?></td>

                    <?php if ($puedeGestionar): ?>
                      <td>
                        <div class="row d-flex justify-content-around">
                          <a href="?r=perfil-usuario&id=<?= (int)$valor['idUsuario']; ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-eye"></i>
                          </a>
                          <!--<form method="post">
                            <input type="hidden" value="<?= (int)$valor['idUsuario']; ?>" name="idEliminar">
                            <button type="submit" class="btn btn-danger btn-sm">
                              <i class="fas fa-trash-alt"></i>
                            </button>
                          </form>-->
                        </div>
                      </td>
                    <?php endif ?>
                  </tr>
                <?php endforeach ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>
