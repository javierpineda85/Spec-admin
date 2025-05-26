<?php

$db = new Conexion;
$sql = "SELECT * FROM objetivos ORDER BY nombre";
$objetivos = $db->consultas($sql);


?>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Buscar cronogramas por fechas</h3>
                        <?php if (!empty($_SESSION['success_message'])): ?>
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <i class="icon fas fa-check"></i>
                                <?= $_SESSION['success_message'];
                                unset($_SESSION['success_message']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <form action="index.php?r=buscar_cronogramas" method="POST" class="card p-4 shadow-sm mb-4">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Objetivo</label>
                                    <select id="objetivo" name="objetivo" class="form-control" required>
                                        <option value disabled selected>Selecciona un objetivo</option>
                                        <?php foreach ($objetivos as $o): ?>
                                            <option value="<?= $o['idObjetivo'] ?>"
                                                <?= (isset($_SESSION['filtros']['objetivo']) && $_SESSION['filtros']['objetivo'] == $o['idObjetivo']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($o['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Desde</label>
                                    <input type="date" name="desde" class="form-control"
                                        value="<?= $_SESSION['filtros']['desde'] ?? '' ?>" required>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Hasta</label>
                                    <input type="date" name="hasta" class="form-control"
                                        value="<?= $_SESSION['filtros']['hasta'] ?? '' ?>" required>
                                </div>
                                <div class="form-group mt-4 col-md-2">
                                    <button type="submit" name="buscar_turnos" class="btn btn-primary mt-2">
                                        Buscar Turnos
                                    </button>

                                </div>
                            </div>
                        </form>

                        <?php
                        $turnos = $_SESSION['turnos'] ?? [];
                        if (empty($turnos)) {
                            echo '<div class="alert alert-info">No hay turnos para mostrar. Primero haz la búsqueda.</div>';
                            return;
                        }

                        // 1) Estructuras vacías
                        $datosTurno     = [];
                        $datosActividad = [];
                        $fechas         = [];
                        $puestos        = [];

                        // 2) Rellenamos pivote
                        foreach ($turnos as $t) {
                            $fecha  = $t['fecha'];
                            $puesto = $t['puesto'];

                            // fechas y puestos únicos
                            $fechas[$fecha] = $fecha;
                            if (!isset($puestos[$puesto])) {
                                $puestos[$puesto] = [
                                    'denom' => $puesto,
                                    'color' => $t['color']
                                ];
                            }

                            // normalizamos turno
                            $raw   = mb_strtolower(trim($t['turno']), 'UTF-8');
                            if (strpos($raw, 'diurn') !== false) {
                                $col = 'Diurno';
                            } elseif (strpos($raw, 'nocturn') !== false) {
                                $col = 'Nocturno';
                            } else {
                                continue;
                            }

                            // asignamos vigilador al turno
                            $datosTurno[$puesto][$fecha][$col] = $t['vigilador'];

                            // asignamos actividad (única por puesto/fecha)
                            $datosActividad[$puesto][$fecha] = $t['actividad'];
                        }

                        // 3) Ordenamos fechas
                        $fechas = array_keys($fechas);
                        sort($fechas);
                        ?>

                        <table id="example1" class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th rowspan="2">Puesto</th>
                                    <?php foreach ($fechas as $f): ?>
                                        <th colspan="3" class="text-center"><?= date('d/m/Y', strtotime($f)) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <?php foreach ($fechas as $f): ?>
                                        <th>Diurno</th>
                                        <th>Nocturno</th>
                                        <th>Actividad</th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($puestos as $puesto => $info): ?>
                                    <tr>
                                        <td class="text-center"><?= htmlspecialchars($info['denom']) ?></td>
                                        <?php foreach ($fechas as $f): ?>
                                            <td class="text-center" style="background-color: <?= htmlspecialchars($info['color']) ?>;">
                                                <?= htmlspecialchars($datosTurno[$puesto][$f]['Diurno']   ?? '') ?>
                                            </td>
                                            <td class="text-center">
                                                <?= htmlspecialchars($datosTurno[$puesto][$f]['Nocturno'] ?? '') ?>
                                            </td>
                                            <td class="text-center">
                                                <?= htmlspecialchars($datosActividad[$puesto][$f] ?? '') ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <?php
                        // limpieza de sesión si no quieres persistir estos datos
                        unset($_SESSION['turnos'], $_SESSION['filtros']);
                        ?>



                    </div>
                </div>
            </div>
        </div>
    </div>
</section>