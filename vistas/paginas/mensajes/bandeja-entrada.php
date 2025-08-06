<?php

$mensajes = ControladorMensajes::crtMostrarMensajes('destinatario_id', $_SESSION['idUsuario']);
$mensajesNoLeidos = array_filter($mensajes, fn($m) => $m['leido'] == 0);
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
              <!-- /.card-body -->
            </div>

          </div>
          <!-- /.col -->
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
                    <tr>
                      <?php foreach ($mensajes as $mensaje) : ?>
                    <tr class="<?php echo $mensaje['leido'] == 0 ? 'font-weight-bold bg-light' : ''; ?>">
                      <td>
                        <div class="icheck-primary">
                          <input type="checkbox" value="" id="check<?php echo $mensaje['idMensaje']; ?>">
                          <label for="check<?php echo $mensaje['idMensaje']; ?>"></label>
                        </div>
                      </td>
                      <td class="mailbox-name">
                        De:
                        <a href="index.php?r=nuevo-mensaje&idMsj=<?php echo $mensaje['idMensaje']; ?>&t=reply">
                          <?php echo $mensaje['nombre'] . " " . $mensaje['apellido']; ?>
                        </a>
                      </td>
                      <td class="mailbox-subject"><?php echo $mensaje['contenido']; ?></td>
                      <td class="mailbox-date"><?php echo $mensaje['fMensaje']; ?></td>
                      <td class="mailbox-date"><?php echo $mensaje['horaMensaje']; ?></td>
                      <td>
                        <div class="btn-group">
                          <!-- botones eliminar, responder, reenviar, ver -->
                          <button
                            type="button"
                            class="btn btn-default btn-sm ver-mensaje"
                            data-id="<?php echo $mensaje['idMensaje']; ?>"
                            data-toggle="modal"
                            data-target="#modalVerMensaje"
                            title="Ver mensaje">
                            <i class="fas fa-eye"></i>
                          </button>
                          <button type="button" class="btn btn-default btn-sm" title="Responder">
                            <a href="index.php?r=nuevo-mensaje&idMsj=<?php echo $mensaje['idMensaje']; ?>&t=reply" class="text-dark">
                              <i class="fas fa-reply"></i>
                            </a>
                          </button>

                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>



                  </tbody>
                </table>
                <!-- /.table -->
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
<!-- Modal para ver mensaje -->
<div class="modal fade" id="modalVerMensaje" tabindex="-1" role="dialog" aria-labelledby="modalVerMensajeLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-info">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="modalVerMensajeLabel">Mensaje recibido</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="contenido-mensaje">
        <p class="text-muted">Cargando mensaje...</p>
      </div>
    </div>
  </div>
</div>

<script>
  function actualizar() {
    location.reload();

  }

  $(document).on('click', '.ver-mensaje', function() {
    const idMensaje = $(this).data('id');
    console.log("🧪 ID que se enviará:", idMensaje);

    $('#contenido-mensaje').html('<p class="text-muted">Cargando mensaje...</p>');

    $.ajax({
      url: 'ajax/ver_mensaje.php',
      type: 'POST',
      data: {
        idMensaje: idMensaje
      },
      dataType: 'json',
      success: function(respuesta) {
        if (respuesta && respuesta.exito) {
          let html = `
        <p><strong>De:</strong> ${respuesta.nombre} ${respuesta.apellido}</p>
        <p><strong>Fecha:</strong> ${respuesta.fecha} ${respuesta.hora}</p>
        <hr>
        <p>${respuesta.contenido}</p>
      `;
          $('#contenido-mensaje').html(html);
          $('#modalVerMensaje').modal('show'); // 👈 Abrir el modal
        } else {
          $('#contenido-mensaje').html(`<p class="text-danger">${respuesta.error ?? 'Error al cargar el mensaje.'}</p>`);
        }
      },
      error: function(xhr, status, error) {
        console.error("❌ Error en AJAX:", status, error);
        $('#contenido-mensaje').html('<p class="text-danger">Error de conexión con el servidor.</p>');
      }
    });

  });
</script>
<!-- Carga explícita de Bootstrap JS si no está definido -->
<script>
  if (typeof $.fn.modal === 'undefined') {
    console.warn('⚠️ Bootstrap modal no está definido. Se intenta cargar manualmente...');
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js';
    script.onload = () => console.log('✅ Bootstrap JS cargado manualmente');
    document.body.appendChild(script);
  }
</script>