<?php


$db = new Conexion();
$sql = "SELECT idUsuario, apellido, nombre FROM usuarios ORDER BY apellido, nombre";
$vigiladores = $db->consultas($sql);

$filtros = $_SESSION['filtros_vigilador'] ?? [];
$turnos  = $_SESSION['turnos_porVigilador'] ?? [];
?>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Buscar cronogramas por vigilador</h3>
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
                        <form action="index.php?r=buscar_porVigilador" method="POST" class="form-inline">
                            <label class="mr-2">Vigilador</label>
                            <select name="vigilador" class="form-control mr-4" required>
                                <option value disabled selected>Selecciona un vigilador</option>
                                <?php foreach ($vigiladores as $u): ?>
                                    <?php $sel = ($filtros['vigilador'] ?? '') == $u['idUsuario']; ?>
                                    <option value="<?= $u['idUsuario'] ?>" <?= $sel ? 'selected' : '' ?>>
                                        <?= htmlspecialchars("{$u['apellido']} {$u['nombre']}") ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <label class="mr-2">Desde</label>
                            <input type="date" name="desde" class="form-control mr-4"
                                value="<?= htmlspecialchars($filtros['desde'] ?? '') ?>" required>

                            <label class="mr-2">Hasta</label>
                            <input type="date" name="hasta" class="form-control mr-4"
                                value="<?= htmlspecialchars($filtros['hasta'] ?? '') ?>" required>

                            <button type="submit" name="buscar_por_vigilador"
                                class="btn btn-primary">Buscar</button>
                        </form>

                        <hr>

                        <?php if (!empty($_SESSION['success_message'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
                            <?php unset($_SESSION['success_message']); ?>
                        <?php endif; ?>

                        <?php if (empty($turnos)): ?>
                            <div class="alert alert-info">
                                No hay turnos para el vigilador en ese rango.
                            </div>
                            <?php return; ?>
                        <?php endif; ?>

                        <?php
                        // Preparamos el pivot horizontal
                        $fechas       = [];
                        $datos        = []; // datos por fecha: Objetivo, Puesto, Turno, Actividad
                        $colorPorFecha = [];

                        foreach ($turnos as $t) {
                            $f = $t['fecha'];
                            $fechas[$f] = $f;

                            // bajo cada fecha guardamos la tupla de 4 valores
                            $datos[$f] = [
                                'Objetivo'  => $t['objetivo'],
                                'Puesto'    => $t['puesto'],
                                'Turno'     => $t['turno'],
                                'Actividad' => $t['actividad']
                            ];
                            // guardamos el color para ese día
                            $colorPorFecha[$f] = $t['color'];
                        }

                        // Ordenamos las fechas cronológicamente
                        $fechas = array_keys($fechas);
                        sort($fechas);
                        ?>

                        <table id="example1" class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th rowspan="2">Vigilador</th>
                                    <?php foreach ($fechas as $f): ?>
                                        <th colspan="4" class="text-center">
                                            <?= date('d/m/Y', strtotime($f)) ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <?php foreach ($fechas as $f): ?>
                                        <th>Objetivo</th>
                                        <th>Puesto</th>
                                        <th>Turno</th>
                                        <th>Actividad</th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <!-- Nombre del vigilador -->
                                    <?php
                                    // buscamos el nombre completo
                                    $vis = array_filter($vigiladores, fn($u) => $u['idUsuario'] == ($filtros['vigilador'] ?? 0));
                                    $nombreV = $vis ? reset($vis)['apellido'] . ' ' . reset($vis)['nombre'] : '';
                                    ?>
                                    <td class="text-center font-weight-bold">
                                        <?= htmlspecialchars($nombreV) ?>
                                    </td>

                                    <?php foreach ($fechas as $f): ?>
                                        <?php $bg = htmlspecialchars($colorPorFecha[$f] ?? '#fff'); ?>
                                        <td class="text-center" style="background-color: <?= $bg ?>">
                                            <?= htmlspecialchars($datos[$f]['Objetivo']  ?? '') ?>
                                        </td>
                                        <td class="text-center" style="background-color: <?= $bg ?>">
                                            <?= htmlspecialchars($datos[$f]['Puesto']    ?? '') ?>
                                        </td>
                                        <td class="text-center" style="background-color: <?= $bg ?>">
                                            <?= htmlspecialchars($datos[$f]['Turno']     ?? '') ?>
                                        </td>
                                        <td class="text-center" style="background-color: <?= $bg ?>">
                                            <?= htmlspecialchars($datos[$f]['Actividad'] ?? '') ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            </tbody>
                        </table>

                        <?php
                        unset($_SESSION['turnos_porVigilador'], $_SESSION['filtros_vigilador']);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</section>
<!-- /.content -->