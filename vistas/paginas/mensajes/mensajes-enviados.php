<?php

$mensajes = ControladorMensajes::crtMostrarMensajesEnviados('remitente_id', $_SESSION['idUsuario']);
$mensajesNoLeidos = array_filter($mensajes, fn($m) => isset($m['leido']) && $m['leido'] == 0);
$cantidadNoLeidos = count($mensajesNoLeidos);

?>

<!-- Main content -->
<section class="content">

  <!-- Default box -->
  <div class="card">
    <div class="card-header bg-info">
      <h3 class="card-title">Mensajes</h3>

      <div class="card-tools">
        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
          <i class="fas fa-minus"></i>
        </button>

      </div>
    </div>
    <div class="card-body">
      <section class="content">
        <div class="row">
          <div class="col-md-3">
            <a href="index.php?r=nuevo-mensaje&c=mensajes&t=" class="btn btn-primary btn-block mb-3">Enviar nuevo mensaje</a>

            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Carpetas</h3>

                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body p-0">
                <ul class="nav nav-pills flex-column">
                  <li class="nav-item active">
                    <a href="index.php?r=bandeja-entrada" class="nav-link">
                      <i class="fas fa-inbox"></i> Bandeja de entrada
                      <?php if ($cantidadNoLeidos > 0): ?>
                        <span class="badge bg-danger float-right"><?php echo $cantidadNoLeidos; ?></span>
                      <?php endif; ?>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="index.php?r=mensajes-enviados&c=mensajes" class="nav-link">
                      <i class="far fa-envelope"></i> Enviados
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="far fa-trash-alt"></i> Papelera
                    </a>
                  </li>
                </ul>
              </div>
              <!-- /.card-body -->
            </div>

          </div>
          <!-- /.col -->
          <div class="col-md-9">
            <div class="card card-primary card-outline">
              <div class="card-header">
                <h3 class="card-title">Mensajes enviados</h3>

                <div class="card-tools">
                  <div class="input-group input-group-sm">
                    <input type="text" class="form-control" placeholder="Buscar correo">
                    <div class="input-group-append">
                      <div class="btn btn-primary">
                        <i class="fas fa-search"></i>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- /.card-tools -->
              </div>
              <!-- /.card-header -->
              <div class="card-body p-0">
                <div class="mailbox-controls">
                  <!-- /.btn-group -->
                  <button type="button" class="btn btn-default btn-sm" onclick="actualizar()">
                    <i class="fas fa-sync-alt"></i>
                  </button>
                  <!-- /.btn-group -->
                </div>
                <!-- /.float-right -->
              </div>

              <!-- TABLA DE MENSAJES -->
              <div class="table-responsive mailbox-messages">
                <table class="table table-hover table-striped">
                  <tbody>
                    <?php if (!empty($mensajes)): ?>
                      <?php foreach ($mensajes as $mensaje): ?>
                        <tr>
                          <td>
                            <div class="icheck-primary">
                              <input type="checkbox" value="" id="check<?= $mensaje['idMensaje']; ?>">
                              <label for="check<?= $mensaje['idMensaje']; ?>"></label>
                            </div>
                          </td>
                          <td class="mailbox-name">
                            Para:
                            <a href="index.php?r=nuevo-mensaje&c=mensajes&idMsj=<?= $mensaje['idMensaje']; ?>&t=reply">
                              <?= htmlspecialchars($mensaje['nombre'] . " " . $mensaje['apellido']); ?>
                            </a>
                          </td>
                          <td class="mailbox-subject"><?= htmlspecialchars($mensaje['contenido']); ?></td>
                          <td class="mailbox-date"><?= $mensaje['fMensaje']; ?></td>
                          <td class="mailbox-date"><?= $mensaje['horaMensaje']; ?></td>
                          <td>
                            <div class="btn-group">
                              <a href="index.php?r=eliminar-mensaje&id=<?= $mensaje['idMensaje']; ?>" class="btn btn-default btn-sm text-dark" title="Eliminar">
                                <i class="far fa-trash-alt"></i>
                              </a>
                              <a href="index.php?r=nuevo-mensaje&c=mensajes&idMsj=<?= $mensaje['idMensaje']; ?>&t=reply" class="btn btn-default btn-sm text-dark" title="Responder">
                                <i class="fas fa-reply"></i>
                              </a>
                              <a href="index.php?r=nuevo-mensaje&c=mensajes&idMsj=<?= $mensaje['idMensaje']; ?>&t=share" class="btn btn-default btn-sm text-dark" title="Reenviar">
                                <i class="fas fa-share"></i>
                              </a>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="6" class="text-center text-muted">No se encontraron mensajes enviados.</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>

              <!-- /.mail-box-messages -->
            </div>

          </div>
          <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</section>
</div>
<!-- /.card-body -->

</div>
<!-- /.card -->

</section>
<!-- /.content -->

<script>
  function actualizar() {
    location.reload();

  }
</script>
