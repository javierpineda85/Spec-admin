<?php
$modo = $_GET['t'] ?? 'nuevo';
$idMensajeOriginal = ($modo === 'reply' || $modo === 'share') ? ($_GET['idMsj'] ?? null) : null;

$usuarios = ControladorMensajes::obtenerDestinatariosDisponibles($_SESSION['idUsuario'], $idMensajeOriginal);

$mensaje = [];
if ($idMensajeOriginal) {
    $mensaje = ControladorMensajes::crtMostrarUnMensaje($idMensajeOriginal) ?? [];
}

$recibidos = ControladorMensajes::crtMostrarMensajesEnviados('destinatario_id', $_SESSION['idUsuario']);
$mensajesNoLeidos = array_filter($recibidos, fn($m) => isset($m['leido']) && $m['leido'] == 0);
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
            <section class="content">
                <div class="row">
                    <!-- Sidebar -->
                    <div class="col-md-3">
                        <a href="index.php?r=bandeja-entrada&c=mensajes" class="btn btn-primary btn-block mb-3">Volver a bandeja de entrada</a>

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
                                                <span class="badge bg-danger float-right"><?= $cantidadNoLeidos; ?></span>
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
                        </div>
                    </div>

                    <!-- Formulario -->
                    <div class="col-md-7">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <?php
                                    switch ($modo) {
                                        case 'reply': echo "Responder mensaje"; break;
                                        case 'share': echo "Compartir mensaje"; break;
                                        default: echo "Redactar nuevo mensaje";
                                    }
                                    ?>
                                </h3>
                            </div>

                            <div class="card-body">
                                <?php if (empty($usuarios)): ?>
                                    <div class="alert alert-warning">
                                        No tienes destinatarios disponibles para este tipo de mensaje o contexto.
                                    </div>
                                <?php else: ?>
                                    <form action="" method="post">
                                        <input type="hidden" name="id_remitente" value="<?= $_SESSION['idUsuario']; ?>">

                                        <?php if ($modo === 'reply' && !empty($mensaje[0]['remitente_id'])): ?>
                                            <input type="hidden" name="id_destinatario" value="<?= $mensaje[0]['remitente_id']; ?>">
                                        <?php endif; ?>

                                        <div class="form-group">
                                            <select class="form-control select2" name="id_destinatario" <?= ($modo === 'reply') ? 'disabled' : ''; ?> required>
                                                <option value="" disabled selected>Para:</option>
                                                <?php if ($modo === 'reply' && !empty($mensaje[0]['remitente_id'])): ?>
                                                    <option value="<?= $mensaje[0]["remitente_id"]; ?>" selected>
                                                        <?= $mensaje[0]['apellido'] . " " . $mensaje[0]['nombre']; ?>
                                                    </option>
                                                <?php else: ?>
                                                    <?php foreach ($usuarios as $usuario): ?>
                                                        <option value="<?= $usuario["idUsuario"]; ?>">
                                                            <?= $usuario['apellido'] . " " . $usuario['nombre'] . " (" . ucfirst($usuario['categoria']) . ")"; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </div>

                                        <?php if (in_array($modo, ['reply', 'share']) && !empty($mensaje[0]['contenido'])): ?>
                                            <div class="alert alert-light small">
                                                <strong><?= $modo === 'reply' ? 'Mensaje anterior:' : 'Mensaje compartido:' ?></strong><br>
                                                <?= nl2br(htmlspecialchars($mensaje[0]['contenido'])); ?><br>
                                                <em class="text-muted">Enviado el <?= $mensaje[0]['fMensaje']; ?> a las <?= $mensaje[0]['horaMensaje']; ?></em>
                                            </div>
                                        <?php endif; ?>

                                        <div class="form-group">
                                            <textarea id="compose-textarea" class="form-control" style="height: 100px" name="contenidoMensaje" required></textarea>
                                        </div>

                                        <div class="card-footer">
                                            <div class="float-right">
                                                <?php $registro = ControladorMensajes::crtGuardarMensaje(); ?>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="far fa-envelope"></i> Enviar
                                                </button>
                                            </div>
                                            <button type="reset" class="btn btn-default"><i class="fas fa-times"></i> Descartar</button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</section>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: 'Buscar usuario...',
            width: '100%'
        });
    });
</script>
