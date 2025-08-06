<?php

// Consultas de datos
$db = new Conexion;
$objetivos = $db->consultas("SELECT * FROM objetivos ORDER BY nombre");

$db = new Conexion;
$usuarios  = $db->consultas("SELECT idUsuario, nombre, apellido FROM usuarios WHERE rol='Vigilador' ORDER BY apellido");

$db = new Conexion;
$puestos = $db->consultas("SELECT idPuesto, puesto, objetivo_id FROM puestos");

// Mapas para resolver nombres
$mapObjetivos = array_column($objetivos, 'nombre', 'idObjetivo');
$mapPuestos   = array_column($puestos,    'puesto', 'idPuesto');

$mapUsuarios  = [];
foreach ($usuarios as $u) {
    $mapUsuarios[$u['idUsuario']] = $u['apellido'] . ', ' . $u['nombre'];
}

// Inicializar la sesión de turnos
if (!isset($_SESSION['turnos'])) {
    $_SESSION['turnos'] = [];
}

// Manejo de POST para agregar/borrar turnos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['borrar_todo'])) {
        $_SESSION['turnos'] = [];
    }
    if (isset($_POST['borrar_uno'])) {
        $idx = $_POST['borrar_uno'];
        if (isset($_SESSION['turnos'][$idx])) {
            unset($_SESSION['turnos'][$idx]);
            $_SESSION['turnos'] = array_values($_SESSION['turnos']);
        }
    }
    if (isset($_POST['agregar_turno'])) {
        $turno = [
            'objetivo'  => $_POST['objetivo'],
            'puesto'     => $_POST['puesto'],
            'fecha'      => $_POST['fecha'],
            'turno'      => $_POST['turno'],
            'vigilador'  => $_POST['vigilador'],
            'tipo_jornada' => $_POST['tipo_jornada'],
            'is_referente' => isset($_POST['is_referente']) ? 1 : 0,
            'entrada'    => $_POST['entrada'],
            'salida'     => $_POST['salida'],
            'color'      => $_POST['color'] ?? '#FFFFFF'
        ];
        $_SESSION['turnos'][] = $turno;
    }
}
?>

<script>
    const rondas = <?= json_encode($puestos, JSON_UNESCAPED_UNICODE) ?>;
</script>

<div class="card">
    <div class="card-header bg-info text-white">
        <h3 class="card-title ">Completa el formulario para crear un nuevo cronograma</h3>
    </div>
    <div class="card-body">
    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible mt-3">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="icon fas fa-check"></i>
            <?= $_SESSION['success_message'];
            unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
        <!-- Formulario de agregado -->
        <form method="POST" class="card p-4 shadow-sm mb-4">

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Objetivo</label>
                    <select id="objetivo" name="objetivo" class="form-control" required>
                        <option value disabled selected>Selecciona un objetivo</option>
                        <?php foreach ($objetivos as $o): ?>
                            <option value="<?= $o['idObjetivo'] ?>"><?= htmlspecialchars($o['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>Puesto</label>
                    <select id="puesto" name="puesto" class="form-control" required>
                        <option value disabled selected>Selecciona un puesto</option>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>Fecha</label>
                    <input type="date" name="fecha" class="form-control" required>
                </div>
                <div class="form-group col-md-2">
                    <label>Turno</label>
                    <select name="turno" class="form-control" required>
                        <option value disabled selected>Seleccionar turno</option>
                        <option value="Diurno">Diurno</option>
                        <option value="Nocturno">Nocturno</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Vigilador</label>
                    <select id="vigilador" name="vigilador" class="form-control select2" required>
                        <option value disabled selected>Seleccione un vigilador</option>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?= $u['idUsuario'] ?>"><?= htmlspecialchars($u['apellido'] . ', ' . $u['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>Tipo de Jornada</label>
                    <select name="tipo_jornada" class="form-control" required>
                        <option value="Normal">Normal</option>
                        <option value="Franco">Franco</option>
                        <option value="Guardia Pasiva">Guardia Pasiva</option>
                        <option value="Licencia">Licencia</option>
                    </select>
                </div>
                <div class="form-group col-md-2 ">
                    <label for="">Marcar si es referente</label>
                    <div class="form-check">
                        <input type="checkbox" name="is_referente" value="1" class="form-check-input" id="is_referente">
                        <label class="form-check-label" for="is_referente">Referente</label>
                    </div>
                </div>
                <div class="form-group col-md-2">
                    <label>Entrada</label>
                    <input type="time" name="entrada" class="form-control" value="06:00" required>
                </div>
                <div class="form-group col-md-2">
                    <label>Salida</label>
                    <input type="time" name="salida" class="form-control" value="18:00" required>
                </div>
                <div class="form-group col-md-1">
                    <label>Color</label>
                    <input type="color" name="color" class="form-control" value="#FFFFFF">
                </div>
                <div class="form-group col-md-2 align-self-end">
                    <button type="submit" name="agregar_turno" class="btn btn-primary">Agregar a Planilla</button>
                </div>
            </div>
        </form>

        <?php if (!empty($_SESSION['turnos'])): ?>
            <div class="card p-3 shadow-sm">
                <h5>Planilla de Turnos a Asignar</h5>
                <!-- Tabla de turnos -->
                <table class="table table-sm table-striped table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Objetivo</th>
                            <th>Puesto</th>
                            <th>Fecha</th>
                            <th>Turno</th>
                            <th>Vigilador</th>
                            <th>Tipo de Jornada</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['turnos'] as $index => $t):
                            $nombreObj    = $mapObjetivos[$t['objetivo']]   ?? '–';
                            $nombrePuesto = $mapPuestos[$t['puesto']]       ?? '–';
                            $nombreUser   = $mapUsuarios[$t['vigilador']]   ?? '–';
                        ?>
                            <tr style="background-color: <?= htmlspecialchars($t['color']) ?>;">
                                <td><?= htmlspecialchars($nombreObj) ?></td>
                                <td><?= htmlspecialchars($nombrePuesto) ?></td>
                                <td><?= htmlspecialchars(date('d-m-Y', strtotime($t['fecha']))) ?></td>
                                <td><?= htmlspecialchars($t['turno']) ?></td>
                                <td><?= htmlspecialchars($nombreUser) ?></td>
                                <td><?= htmlspecialchars($t['tipo_jornada']) ?></td>
                                <td><?= htmlspecialchars($t['entrada']) ?></td>
                                <td><?= htmlspecialchars($t['salida']) ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="borrar_uno" value="<?= $index ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="d-flex justify-content-between mt-3">
                    <form method="POST">
                        <button type="submit" name="guardar_todos" value="guardar_todos" class="btn btn-success">Guardar Planilla</button>
                        <?php $registro = ControladorTurnos::ctrRegistrarPlanilla(); ?>

                    </form>

                    <form method="POST">
                        <button type="submit" name="borrar_todo" class="btn btn-danger">
                            <i class="fas fa-trash-alt"></i> Vaciar Planilla
                        </button>
                    </form>
                </div>

            </div>
        <?php endif; ?>
    </div>
</div>