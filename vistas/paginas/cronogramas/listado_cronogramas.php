<?php

$db = new Conexion;
$sql = "SELECT * FROM objetivos ORDER BY nombre";
$objetivos = $db->consultas($sql);


?>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title">Buscar cronogramas por fechas</h3>

                    </div>
                    <div class="card-body">
                        <?php if (!empty($_SESSION['success_message'])): ?>
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <i class="icon fas fa-check"></i>
                                <?= $_SESSION['success_message'];
                                unset($_SESSION['success_message']); ?>
                            </div>
                        <?php endif; ?>
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

                        <table id="example1" class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Puesto</th>
                                    <th>Turno</th>
                                    <th>Vigilador</th>
                                    <th>Actividad</th>
                                    <th>Entrada</th>
                                    <th>Salida</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($_SESSION['turnos'])): ?>
                                    <?php
                                    $currentFecha = null;
                                    foreach ($_SESSION['turnos'] as $t):
                                        // Al cambiar la fecha, pintamos un header de grupo
                                        if ($t['fecha'] !== $currentFecha):
                                            $currentFecha = $t['fecha'];
                                    ?>
                                            <tr>
                                                <td colspan="6"
                                                    class="text-center font-weight-bold bg-secondary text-white">
                                                    <?= date('d/m/Y', strtotime($currentFecha)) ?>
                                                </td>
                                            </tr>
                                        <?php
                                        endif;
                                        ?>
                                        <tr style="background-color: <?= htmlspecialchars($t['color']) ?>;">
                                            <td class="text-center"><?= htmlspecialchars($t['puesto']) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($t['turno']) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($t['vigilador']) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($t['actividad']) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($t['entrada']) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($t['salida']) ?></td>
                                        </tr>
                                    <?php endforeach;
                                    // limpiamos si no queremos persistir al recargar
                                    unset($_SESSION['turnos'], $_SESSION['filtros']);
                                    ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->