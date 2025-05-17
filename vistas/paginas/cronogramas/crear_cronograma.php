<?php
$db = new Conexion;
$sql = "SELECT * FROM objetivos ORDER BY nombre ";
$objetivos = $db->consultas($sql);

$db = new Conexion;
$sql = "SELECT idUsuario, nombre, apellido FROM usuarios WHERE rol ='Vigilador' ORDER BY apellido ";
$usuarios = $db->consultas($sql);

$db = new Conexion;
$sql = "SELECT idRonda, puesto, objetivo_id FROM rondas";
$rondas = $db->consultas($sql);

if (!isset($_SESSION['turnos'])) {
    $_SESSION['turnos'] = [];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Borrar toda la planilla
    if (isset($_POST['borrar_todo'])) {
        $_SESSION['turnos'] = [];
    }

    // Borrar un solo turno
    if (isset($_POST['borrar_uno'])) {
        $indice = $_POST['borrar_uno'];
        if (isset($_SESSION['turnos'][$indice])) {
            unset($_SESSION['turnos'][$indice]);
            $_SESSION['turnos'] = array_values($_SESSION['turnos']); // reindexar
        }
    }

    // Agregar un nuevo turno
    if (isset($_POST['agregar_turno'])) {
        $turno = [
            'objetivo' => $_POST['objetivo'],
            'puesto' => $_POST['puesto'],
            'fecha' => $_POST['fecha'],
            'turno' => $_POST['turno'],
            'vigilador' => $_POST['vigilador'],
            'actividad' => $_POST['actividad'],
            'entrada' => $_POST['entrada'],
            'salida' => $_POST['salida'],
            'color' => $_POST['color'] ?? '#FFFFFF' // blanco por defecto
        ];

        $_SESSION['turnos'][] = $turno;
    }
}


?>
<script>
  const rondas = <?= json_encode($rondas, JSON_UNESCAPED_UNICODE) ?>;
</script>


<!-- Default box -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Completa el formulario para crear un nuevo cronograma</h3>

        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                <i class="fas fa-minus"></i>
            </button>

        </div>
    </div>
    <div class="card-body">
        <form method="POST" class="card p-4 shadow-sm mb-4">
            <div class="row">
                <div class="mb-3 col-4">
                    <label class="form-label">Objetivo</label>
                    <select id="objetivo" class="form-control" name="objetivo" required>
                        <option value="" disabled selected>Selecciona un objetivo</option>
                        <?php foreach ($objetivos as $o): ?>
                            <option value="<?= $o['idObjetivo'] ?>"><?= htmlspecialchars($o['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3 col-4">
                    <label class="form-label">Puesto</label>
                    <select id="puesto" name="puesto" class="form-control" required>
                        <option value="" disabled selected>Selecciona un puesto</option>
                    </select>
                </div>
                <div class="mb-3 col-2">
                    <label class="form-label">Fecha</label>
                    <input type="date" class="form-control" name="fecha" required>
                </div>
                <div class="mb-3 col-2">
                    <label class="form-label">Turno</label>
                    <select class="form-control" name="turno" required>
                        <option value="" disabled selected>Seleccionar turno</option>
                        <option value="Diurno">Diurno</option>
                        <option value="Nocturno">Nocturno</option>
                        <option value="Guardia">Guardia</option>
                    </select>
                </div>
                <div class="mb-3 col-3">
                    <label class="form-label">Vigilador</label>
                    <select id="vigilador" name="vigilador" class="form-control select2" required>
                        <option value="" disabled selected>Seleccione un vigilador</option>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?= $u['idUsuario'] ?>">
                                <?= htmlspecialchars($u['apellido'] . ', ' . $u['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3 col-2">
                    <label class="form-label">Actividad</label>
                    <select class="form-control" name="actividad" required>
                        <option value="Normal">Normal</option>
                        <option value="Franco">Franco</option>
                        <option value="Licencia">Licencia</option>
                        <option value="Guardia">Guardia Pasiva</option>
                    </select>
                </div>
                <div class="mb-3 col-2">
                    <label class="form-label">Entrada</label>
                    <input type="time" class="form-control" name="entrada" value="08:00" required>
                </div>
                <div class="mb-3 col-2">
                    <label class="form-label">Salida</label>
                    <input type="time" class="form-control" name="salida" value="08:00" required>
                </div>
                <div class="mb-3 col-1">
                    <label class="form-label">Color</label>
                    <input type="color" name="color" value="#FFFFFF">
                </div>
                <div class="mb-3 col-2">
                    <button type="submit" name="agregar_turno" class="btn btn-primary mt-4">Agregar a Planilla</button>
                </div>
            </div>
        </form>

        <!-- Tabla con los datos ingresados -->
        <?php if (!empty($_SESSION['turnos'])): ?>
            <form method="POST">
                <div class="card p-3 shadow-sm">
                    <h5>Planilla de Turnos a Asignar</h5>
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Objetivo</th>
                                <th>Puesto</th>
                                <th>Fecha</th>
                                <th>Turno</th>
                                <th>Vigilador</th>
                                <th>Actividad</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            usort($_SESSION['turnos'], fn($a, $b) => strcmp($a['puesto'], $b['puesto']));
                            foreach ($_SESSION['turnos'] as $index => $t): ?>
                                <tr style="background-color: <?= htmlspecialchars($t['color']) ?>;">
                                    <td><?= $t['objetivo'] ?></td>
                                    <td><?= $t['puesto'] ?></td>
                                    <td><?= $t['fecha'] ?></td>
                                    <td><?= $t['turno'] ?></td>
                                    <td><?= $t['vigilador'] ?></td>
                                    <td><?= $t['actividad'] ?></td>
                                    <td><?= $t['entrada'] ?></td>
                                    <td><?= $t['salida'] ?></td>
                                    <td> <button type="submit" name="borrar_uno" value="<?= $index ?>" class="btn btn-sm btn-outline-danger">Borrar</button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-between mt-3">
                        <button type="submit" name="guardar_todos" class="btn btn-success">Guardar planilla</button>
                        <button type="submit" name="borrar_todo" class="btn btn-danger">Vaciar planilla</button>
                    </div>

                </div>
            </form>
        <?php endif; ?>
    </div>

</div>