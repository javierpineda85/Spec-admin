<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    NovedadesController::crtRegistrar();
    exit;
}

// Datos de sesión y fecha/hora
$fecha        = date('Y-m-d');
$hora         = date('H:i');
$vigilador_id = $_SESSION['idUsuario'];
$rol_usuario  = $_SESSION['rol'] ?? null;

// Conexión
$db = new Conexion();

// Objetivo "Base" para roles genéricos
$sqlBase      = "SELECT idObjetivo, nombre FROM objetivos WHERE nombre = 'Base' LIMIT 1";
$resBase      = $db->consultas($sqlBase);
$baseObjetivo = $resBase[0] ?? ['idObjetivo' => null, 'nombre' => 'Sin base definida'];

// Decidir objetivo inicial
$objetivoAsignado = in_array($rol_usuario, ['Vigilador', 'Referente'])
    ? ($turnoHoy ?? $baseObjetivo)
    : $baseObjetivo;

// Para roles distintos, cargamos todos los objetivos activos
$listaObjetivos = [];
if (!in_array($rol_usuario, ['Vigilador', 'Referente'])) {
    $sqlAct = "SELECT idObjetivo, nombre FROM objetivos WHERE activo = 1 ORDER BY nombre";
    $listaObjetivos = $db->consultas($sqlAct);
}
?>

<div class="card">
    <div class="card-header bg-info text-white">
        <h3 class="card-title">Registrar novedades</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="card card-info">
            <?php if (!empty($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible mt-3">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="icon fas fa-check"></i>
                    <?= $_SESSION['success_message'];
                    unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible mt-3">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="icon fas fa-exclamation-triangle"></i>
                    <?= $_SESSION['error_message'];
                    unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <form class="form-horizontal" method="POST" action="?r=crear_novedad" enctype="multipart/form-data">
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-2">
                            <label class="form-label fw-bold">Usuario</label>
                            <input type="hidden" name="vigilador_id" value="<?= $vigilador_id ?>">
                            <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['apellido'] . ' ' . $_SESSION['nombre']) ?>" readonly>
                        </div>
                        <div class="form-group col-md-2">
                            <label class="form-label fw-bold">Fecha</label>
                            <input type="date" class="form-control" value="<?= $fecha ?>" readonly>
                        </div>
                        <div class="form-group col-md-2">
                            <label class="form-label fw-bold">Hora</label>
                            <input type="time" class="form-control" value="<?= $hora ?>" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="form-label fw-bold">Objetivo</label>
                            <?php if (in_array($rol_usuario, ['Vigilador', 'Referente'])): ?>
                                <input type="hidden" name="objetivo_id" value="<?= $objetivoAsignado['idObjetivo'] ?>">
                                <input type="text" class="form-control" value="<?= htmlspecialchars($objetivoAsignado['nombre']) ?>" readonly>
                            <?php else: ?>
                                <select name="objetivo_id" class="form-control" required>
                                    <option value="" disabled selected>-- Selecciona un objetivo --</option>
                                    <?php foreach ($listaObjetivos as $o): ?>
                                        <option value="<?= $o['idObjetivo'] ?>"><?= htmlspecialchars($o['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="form-label fw-bold" for="detalle">Novedades</label>
                            <textarea class="form-control" id="detalle" name="detalle" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="row">

                        <div class="form-group col-sm-12 col-md-6">
                            <label for="inputGroupFile01" class="form-label">Adjuntar imagen o PDF</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="inputGroupFile01" name="adjunto" accept=".jpg,.jpeg,.png,.webp,.avif,.pdf">
                                    <label class="custom-file-label" for="inputGroupFile01">Selecciona un archivo</label>
                                </div>
                            </div>
                            <small class="form-text text-muted">Solo se permiten imágenes (jpg, png, webp, avif) o archivos PDF. Tamaño máximo: 5 MB.</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer col-md-6">
                    <button type="submit" class="btn btn-success" name="Registrar">Registrar</button>
                    <button type="reset" class="btn btn-default float-right">Borrar campos</button>
                </div>
            </form>
        </div>
    </div>
</div>