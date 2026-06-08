<?php if (
    Auth::hasPermission('mensajes', 'crtMostrarMensajesEnviados') ||
    Auth::hasPermission('mensajes', 'crtGuardarMensaje')
): ?>
  <div class="col-6 col-md-6 col-lg-3">
      <div class="info-box shadow">
            <span class="info-box-icon bg-info"><i class="far fa-envelope"></i></span>
            <div class="info-box-content">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="info-box-number">Mensajería</span>
                    <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseMensajeria" aria-expanded="false" aria-controls="collapseMensajeria">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div id="collapseMensajeria" class="collapse">
                    <div class="mt-2">
                        <?php if (Auth::hasPermission('mensajes', 'crtMostrarMensajesEnviados')): ?>
                            <a href="?r=bandeja-entrada" class="btn btn-block btn-info btn-sm text-white">Bandeja de entrada</a>
                        <?php endif; ?>
                        <?php if (Auth::hasPermission('mensajes', 'crtMostrarMensajesEnviados')): ?>
                            <a href="?r=mensajes-enviados" class="btn btn-block btn-info btn-sm text-white">Mensajes enviados</a>
                        <?php endif; ?>
                        <?php if (Auth::hasPermission('mensajes', 'crtGuardarMensaje')): ?>
                            <a href="?r=nuevo-mensaje" class="btn btn-block btn-info btn-sm text-white">Enviar mensaje</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
