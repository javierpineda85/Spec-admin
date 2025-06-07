<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    MarcacionesController::crtRegistrarMarcacion();
}
$fecha = date('Y-m-d');
$hora = date('H:i');
$db           = new Conexion();
$vigilador_id = $_SESSION['idUsuario'];
$rol_usuario  = $_SESSION['rol'] ?? null;

// Cargamos “Base” para roles genéricos
$sqlBase      = "SELECT idObjetivo, nombre
                   FROM objetivos
                  WHERE nombre = 'Base'
                  LIMIT 1";
$resBase      = $db->consultas($sqlBase);
$baseObjetivo = $resBase[0] ?? ['idObjetivo' => null, 'nombre' => 'Sin base definida'];

// Solo para Vigilador/Referente cargamos su turno
$turnoHoy     = null;
$turnoAyer    = null;
if (in_array($rol_usuario, ['Vigilador', 'Referente'])) {
    $hoy        = $fecha;
    $ayer       = date('Y-m-d', strtotime('-1 day'));
    // Turno hoy
    $sqlHoy     = "SELECT o.idObjetivo, o.nombre
                     FROM turnos t
                     JOIN objetivos o ON t.objetivo_id = o.idObjetivo
                    WHERE t.vigilador_id = ? AND t.fecha = ?
                    LIMIT 1";
    $tmpHoy     = $db->consultas($sqlHoy, [$vigilador_id, $hoy]);
    $turnoHoy   = $tmpHoy[0] ?? null;
    // Turno nocturno ayer
    $sqlAyer    = "SELECT o.idObjetivo, o.nombre
                     FROM turnos t
                     JOIN objetivos o ON t.objetivo_id = o.idObjetivo
                    WHERE t.vigilador_id = ? AND t.fecha = ? AND t.turno='Nocturno'
                    LIMIT 1";
    $tmpAyer    = $db->consultas($sqlAyer, [$vigilador_id, $ayer]);
    $turnoAyer  = $tmpAyer[0] ?? null;
}

// Decidimos objetivo inicial
$objetivoAsignado = in_array($rol_usuario, ['Vigilador', 'Referente'])
    ? ($turnoHoy ?? $baseObjetivo)
    : $baseObjetivo;

?>
<!-- Default box -->
<div class="card">
    <div class="card-header bg-info text-white">
        <h3 class="card-title">Completa el formulario para registrar entrada o salida</h3>

        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                <i class="fas fa-minus"></i>
            </button>

        </div>
    </div>
    <div class="card-body">

        <form class="form-horizontal" id="entradaSalidaForm" method="POST">
            <?php if (!empty($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible mt-3">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="icon fas fa-check"></i>
                    <?= $_SESSION['success_message'];
                    unset($_SESSION['success_message']); ?>
                </div>
                <?php if (!empty($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible mt-3">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="icon fas fa-exclamation-circle"></i>
                        <?= $_SESSION['error_message'];
                        unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <div class="card-body">
                <h6>Para registrar la entrada al servicio, solo presiona "Registrar"</h6>
                <div class="row mt-3">

                    <div class="form-group col-sm-12 col-md-3">
                        <label class="form-label fw-bold">Usuario</label>
                        <input type="text" class="form-control" name="idUsuario" value="<?php echo $_SESSION['idUsuario']; ?>" hidden>
                        <input type="text" class="form-control" name="usuario" value="<?php echo $_SESSION['apellido'] . " " . $_SESSION['nombre']; ?>" readonly>
                    </div>
                    <div class="form-group col-sm-12 col-md-2">
                        <label class="form-label fw-bold">Fecha</label>
                        <input type="date" class="form-control mb-2" value="<?= $fecha ?>" readonly>
                    </div>
                    <div class="form-group col-sm-12 col-md-2">
                        <label class="form-label fw-bold">Hora</label>
                        <input type="time" class="form-control" value="<?= $hora ?>" readonly>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-12 col-md-4">
                        <label class="form-label fw-bold">Objetivo:</label>
                        <!-- En todos los casos envío hidden + muestro readonly -->
                        <input type="hidden" name="objetivo_id" value="<?= $objetivoAsignado['idObjetivo'] ?>">
                        <input type="text" class="form-control"
                            value="<?= htmlspecialchars($objetivoAsignado['nombre']) ?>" readonly>
                    </div>
                    <div class="form-group col-sm-12 col-md-3">
                        <label class="form-label fw-bold">Presiona para cambiar</label>
                        <div class="custom-control custom-switch ms-2">
                            <input type="checkbox" name="tipo_registro" class="custom-control-input entrada-salida-switch switch-danger" id="entradaSalidaSwitch">
                            <label class="custom-control-label" for="entradaSalidaSwitch" id="switchLabel">Registrar Entrada</label>
                        </div>
                        <small class="form-text text-muted" id="switchText">Se registrará la entrada</small>
                    </div>

                </div>
                <div class="row">
                    <div class="form-group col-sm-12 col-md-6">
                        <input type="hidden" name="tipo_evento" id="tipo_evento" value="entrada">
                        <input type="hidden" name="latitud" id="latitud">
                        <input type="hidden" name="longitud" id="longitud">
                    </div>
                </div>
            </div>
            <!-- /.card-body -->
            <div class="card-footer col-sm-12 col-md-6">
                <input type="submit" class="btn btn-success" value="Registrar" name="Registrar">

            </div>
            <!-- /.card-footer -->
        </form>
    </div>


</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const userRole = <?= json_encode($rol_usuario) ?>;
        const turnoHoy = <?= json_encode($turnoHoy) ?>;
        const turnoAyer = <?= json_encode($turnoAyer)  ?>;
        const switchInput = document.getElementById('entradaSalidaSwitch');
        const tipoHidden = document.getElementById('tipo_evento');
        const form = document.getElementById('entradaSalidaForm');
        const latInput = document.getElementById('latitud');
        const lngInput = document.getElementById('longitud');

        // Lógica de switch solo para vigilador/referente
        if (switchInput) {
            switchInput.addEventListener('change', () => {
                const esSalida = switchInput.checked;
                tipoHidden.value = esSalida ? 'salida' : 'entrada';
                if (esSalida && turnoAyer) {
                    document.querySelector('input[name="objetivo_id"]').value = turnoAyer.idObjetivo;
                } else if (!esSalida && turnoHoy) {
                    document.querySelector('input[name="objetivo_id"]').value = turnoHoy.idObjetivo;
                }
                document.getElementById('switchLabel').textContent = esSalida ?
                    'Registrar Salida' :
                    'Registrar Entrada';
                document.getElementById('switchText').textContent = esSalida ?
                    'Ahora se registrará la salida' :
                    'Se registrará la entrada';
            });
        }

        // Captura geolocalización antes de enviar
        form.addEventListener('submit', (evt) => {
            evt.preventDefault();
            if (!navigator.geolocation) {
                alert('Tu navegador no soporta geolocalización');
                return form.submit();
            }
            navigator.geolocation.getCurrentPosition(
                pos => {
                    latInput.value = pos.coords.latitude;
                    lngInput.value = pos.coords.longitude;
                    form.submit();
                },
                err => {
                    alert('Error obteniendo ubicación: ' + err.message);
                    form.submit();
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000
                }
            );
        });
    });
</script>