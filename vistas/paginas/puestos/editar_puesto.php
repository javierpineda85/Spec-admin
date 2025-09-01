<?php
$db = new Conexion;
$sql = "SELECT * FROM objetivos ORDER BY nombre ";
$objetivos = $db->consultas($sql);
$db = new Conexion;

$sql = "SELECT p.idPuesto, p.puesto, p.objetivo_id, p.tipo, o.nombre as nombreObjetivo FROM puestos p JOIN objetivos o ON p.objetivo_id = o.idObjetivo WHERE p.idPuesto = " . $_GET['id'];
$puesto = $db->consultas($sql);
// Traemos los turnos del puesto actual
$sqlTurnos = "SELECT * FROM puestos_turnos WHERE puesto_id = ?";
$turnos = $db->consultas($sqlTurnos, [$puesto[0]['idPuesto']]);

// Reorganizamos por numero_turno para acceso directo
$turnosPorNumero = [];
foreach ($turnos as $t) {
    $turnosPorNumero[$t['numero_turno']] = $t;
}

?>
<div class="card">
    <div class="card-header bg-info text-white">
        <h3 class="card-title">Completa los datos para generar un nuevo puesto</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Aquí definimos el action para invocar el método del controlador Qr -->
        <form action="" method="POST" class="form-horizontal">
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-sm-12 col-md-3">
                        <input type="text" name="idPuesto" value="<?php echo $puesto[0]['idPuesto'] ?>" hidden>
                        <label class="form-label">Sector / Puesto</label>
                        <input type="text" class="form-control" placeholder="Puesto 1" name="puesto" value="<?php echo $puesto[0]['puesto']; ?>">
                    </div>
                    <div class="form-group col-sm-12 col-md-3">
                        <label class="form-label">Objetivo</label>
                        <select id="objetivo" name="objetivo_id" class="form-control">
                            <option value="<?php echo $puesto[0]['objetivo_id']; ?>" selected><?php echo $puesto[0]['nombreObjetivo']; ?></option>

                            <?php foreach ($objetivos as $objetivo): ?>
                                <option value="<?php echo $objetivo['idObjetivo'] ?>"><?php echo $objetivo['nombre'] ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-12 col-md-2">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" id="" class="form-control">
                            <option value="<?php echo $puesto[0]['tipo']; ?>" selected><?php echo $puesto[0]['tipo']; ?></option>
                            <option value="Fijo">Fijo</option>
                            <option value="Rotativo">Rotativo</option>
                            <option value="Eventual">Eventual</option>
                        </select>
                    </div>
                    <div class="form-group col-12">
                        <label><strong>Turnos del puesto</strong></label>
                        <p>Podés configurar hasta 3 turnos con hora de entrada y salida.</p>
                    </div>

                    <?php for ($i = 1; $i <= 3; $i++):
                        $horaEntrada = $turnosPorNumero[$i]['hora_entrada'] ?? '';
                        $horaSalida  = $turnosPorNumero[$i]['hora_salida'] ?? '';
                    ?>
                        <div class="form-row mb-2 align-items-center border p-2 rounded">
                            <div class="form-group col-md-2">
                                <label>Turno <?= $i ?></label>
                                <input type="hidden" name="turnos[<?= $i ?>][numero_turno]" value="<?= $i ?>">
                            </div>
                            <div class="form-group col-md-5">
                                <label>Hora de entrada</label>
                                <input type="time" class="form-control" name="turnos[<?= $i ?>][hora_entrada]" value="<?= $horaEntrada ?>" data-optional="true">
                                <small>Ejemplo: 06:00</small>
                            </div>
                            <div class="form-group col-md-5">
                                <label>Hora de salida</label>
                                <input type="time" class="form-control" name="turnos[<?= $i ?>][hora_salida]" value="<?= $horaSalida ?>" data-optional="true">
                                <small>Ejemplo: 17:59</small>
                            </div>
                        </div>
                    <?php endfor; ?>


                </div>
                <div class="card-footer">

                    <?php
                    $registro =  ControladorPuestos::crtModificarPuesto();
                    ?>

                    <input type="submit" class="btn btn-success" value="Modificar Puesto">
                    <button type="reset" class="btn btn-default float-right">Borrar campos</button>

                    <?php if (!empty($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible mt-3">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="icon fas fa-check"></i>
                            <?= $_SESSION['success_message'];
                            unset($_SESSION['success_message']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </form>
    </div>
</div>