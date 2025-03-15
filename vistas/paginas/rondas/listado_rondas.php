<?php

$db = new Conexion;
$sql = "SELECT r.idRonda, r.puesto, r.objetivo_id, r.tipo, r.orden_escaneo, r.fecha_creacion, o.nombre FROM rondas r JOIN objetivos o ON r.objetivo_id = o.idObjetivo ORDER BY o.nombre ASC, r.orden_escaneo ASC";
$usuarios = $db->consultas($sql);

?>

<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">

        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Listado de rondas</h3>
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
          <!-- /.card-header -->
          <div class="card-body">
            <table id="example1" class="table table-bordered table-striped table-sm">
              <thead>
                <tr>
                  <th style="text-align: center;">Objetivo</th>
                  <th style="text-align: center;">Sector / nombre</th>
                  <th style="text-align: center;">Tipo</th>
                  <th style="text-align: center;">Orden de escaneo</th>
                  <th style="text-align: center;">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($usuarios as $campo => $valor) : ?>
                  <tr>
                    <td> <?php echo $valor['nombre']; ?></td>
                    <td> <?php echo $valor['puesto']; ?></td>
                    <td> <?php echo $valor['tipo']; ?></td>
                    <td> <?php echo $valor['orden_escaneo']; ?></td>

                    <td>
                      <div class="row d-flex justify-content-around">
                        <a href="?r=editar_ronda&id=<?php echo $valor["idRonda"]; ?>" class="btn btn-success btn-sm"><i class="fas fa-eye"></i></a>
                        <form method="post">
                          <input type="hidden" value="<?php echo $valor["idRonda"]; ?>" name="idEliminar">
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
                <th style="text-align: center;">Objetivo</th>
                  <th style="text-align: center;">Sector / nombre</th>
                  <th style="text-align: center;">Tipo</th>
                  <th style="text-align: center;">Orden de escaneo</th>
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