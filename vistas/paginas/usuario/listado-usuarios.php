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
                          <a href="?r=perfil-usuario&id=<?= (int)$valor['idUsuario']; ?>" class="btn btn-success btn-sm" title="Ver perfil">
                            <i class="fas fa-eye"></i>
                          </a>
                          <a href="?r=mi_salud&id=<?= (int)$valor['idUsuario']; ?>" class="btn bg-lightblue btn-sm" title="Ver salud">
                            <i class="fas fa-medkit"></i>
                          </a>
                          <a href="?r=mis_datos_personales&id=<?= (int)$valor['idUsuario']; ?>" class="btn bg-lightblue btn-sm" title="Ver datos personales">
                            <i class="fas fa-address-card"></i>
                          </a>
                          <a href="?r=mi_uniforme&id=<?= (int)$valor['idUsuario']; ?>" class="btn bg-info btn-sm" title="Ver uniforme">
                            <i class="fas fa-user-shield"></i>
                          </a>
                          <!--<a href="?r=perfil-usuario&id=<?= (int)$valor['idUsuario']; ?>" class="btn bg-lightblue btn-sm" title="Ver documentos">
                            <i class="fas fa-file-alt"></i>
                          </a>-->
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