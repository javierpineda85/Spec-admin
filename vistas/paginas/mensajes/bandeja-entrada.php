<?php
$mensajes = ControladorMensajes::crtMostrarMensajesRecibidos('destinatario_id', $_SESSION['idUsuario']);
$mensajesNoLeidos = array_filter($mensajes, fn($m) => isset($m['leido']) && $m['leido'] == 0);
$cantidadNoLeidos = count($mensajesNoLeidos);

?>


<section class="content">
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
      <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
          <a href="index.php?r=nuevo-mensaje" class="btn btn-primary btn-block mb-3">Enviar nuevo mensaje</a>

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
                      <span class="badge bg-danger float-right" id="badge-mensajes-entrada"><?= $cantidadNoLeidos; ?></span>
                    <?php endif; ?>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="index.php?r=mensajes-enviados" class="nav-link">
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
          </div>
        </div>

        <!-- Bandeja -->
        <div class="col-md-9">
          <div class="card card-primary card-outline">
            <div class="card-header">
              <h3 class="card-title">Bandeja de entrada</h3>
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
            </div>

            <div class="card-body p-0">
              <div class="mailbox-controls">
                <button type="button" class="btn btn-default btn-sm" onclick="actualizar()">
                  <i class="fas fa-sync-alt"></i>
                </button>
              </div>

              <div class="table-responsive mailbox-messages">
                <table class="table table-hover table-striped">
                  <tbody>
                    <?php foreach ($mensajes as $mensaje): ?>
                      <tr id="mensaje-<?= $mensaje['idMensaje']; ?>" class="<?= (isset($mensaje['leido']) && $mensaje['leido'] == 0) ? 'font-weight-bold bg-light no-leido' : 'leido'; ?>">
                        <td>
                          <div class="icheck-primary">
                            <input type="checkbox" value="" id="check<?= $mensaje['idMensaje']; ?>">
                            <label for="check<?= $mensaje['idMensaje']; ?>"></label>
                          </div>
                        </td>
                        <td class="mailbox-name">
                          De:
                          <a href="index.php?r=nuevo-mensaje&idMsj=<?= $mensaje['idMensaje']; ?>&t=reply">
                            <?= $mensaje['nombre'] . " " . $mensaje['apellido']; ?>
                          </a>
                        </td>
                        <td class="mailbox-subject"><?= htmlspecialchars($mensaje['contenido']); ?></td>
                        <td class="mailbox-date"><?= $mensaje['fMensaje']; ?></td>
                        <td class="mailbox-date"><?= $mensaje['horaMensaje']; ?></td>
                        <td>
                          <div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm ver-mensaje"
                              data-id="<?= $mensaje['idMensaje']; ?>"
                              data-toggle="modal"
                              data-target="#modalVerMensaje"
                              title="Ver mensaje">
                              <i class="fas fa-eye"></i>
                            </button>
                            <a href="index.php?r=nuevo-mensaje&idMsj=<?= $mensaje['idMensaje']; ?>&t=reply"
                              class="btn btn-default btn-sm text-dark" title="Responder">
                              <i class="fas fa-reply"></i>
                            </a>
                            <button type="button" class="btn btn-default btn-sm marcar-no-leido"
                              data-id="<?= $mensaje['idMensaje']; ?>" title="Marcar como no leído">
                              <i class="fas <?= ($mensaje['leido'] == 0) ? 'fa-envelope' : 'fa-envelope-open'; ?> icono-leido" id="icono-<?= $mensaje['idMensaje']; ?>"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>

                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>



<script>
  function actualizar() {
    location.reload();
  }


  if (typeof $.fn.modal === 'undefined') {
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js';
    //script.onload = () => console.log('✅ Bootstrap JS cargado manualmente');
    document.body.appendChild(script);
  }
</script>