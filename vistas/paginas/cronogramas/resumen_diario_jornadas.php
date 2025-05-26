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
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title">Resumen Diario de Jornadas</h3>
                    </div>
                    <div class="card-body">
                        <form action="index.php?r=buscar_resumen_diario" method="POST" class="card p-4 shadow-sm mb-4">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Objetivo</label>
                                    <select id="objetivo" name="objetivo" class="form-control" required>
                                        <option value disabled selected>Selecciona un objetivo</option>
                                        <?php foreach ($objetivos as $o): ?>
                                            <option value="<?= $o['idObjetivo'] ?>"
                                                <?= (isset($_SESSION['filtros_resumen']['objetivo']) && $_SESSION['filtros_resumen']['objetivo'] == $o['idObjetivo']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($o['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Desde</label>
                                    <input type="date" name="desde" class="form-control"
                                        value="<?= $_SESSION['filtros_resumen']['desde'] ?? '' ?>" required>
                                </div>
                                <div class="form-group col-md-2">
                                    <label>Hasta</label>
                                    <input type="date" name="hasta" class="form-control"
                                        value="<?= $_SESSION['filtros_resumen']['hasta'] ?? '' ?>" required>
                                </div>
                                <div class="form-group mt-4 col-md-3">
                                    <button type="submit" name="buscar_resumen_diario" class="btn btn-primary mt-2">
                                       Generar Reporte
                                    </button>

                                </div>
                            </div>
                        </form>
                        <?php if (!empty($_SESSION['success_message'])): ?>
                            <div class="alert alert-success alert-dismissible mt-3">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <i class="icon fas fa-check"></i>
                                <?= $_SESSION['success_message'];
                                unset($_SESSION['success_message']); ?>
                            </div>
                        <?php endif; ?>
                        <?php
                        // 1) Recuperamos el resumen que guardó el controlador
                        $resumen = $_SESSION['resumen_diario'] ?? [];

                        // 2) Extraemos las fechas únicas y las ordenamos
                        if (!empty($resumen)) {
                            // array_column saca todas las fechas, array_unique quita duplicados
                            $fechas = array_unique(array_column($resumen, 'fecha'));
                            sort($fechas);
                        } else {
                            $fechas = [];
                        }
                        ?>
                        <table id="example1" class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="text-center">Puesto</th>
                                    <?php foreach ($fechas as $f): ?>
                                        <th colspan="2" class="text-center"><?= date('d/m/Y', strtotime($f)) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <?php foreach ($fechas as $f): ?>
                                        <th class="text-center">Diurno</th>
                                        <th class="text-center">Nocturno</th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // recogemos el array generado por el controlador
                                $resumen = $_SESSION['resumen_diario'] ?? [];
                                if (!empty($resumen)):
                                ?>
                                    <tr>
                                        <!-- primera celda fija -->
                                        <td class="text-center font-weight-bold">Jornadas</td>

                                        <!-- por cada fecha, mostramos diurnas y nocturnas -->
                                        <?php foreach ($resumen as $row): ?>
                                            <td class="text-center">
                                                <?= htmlspecialchars($row['diurnas']) ?>
                                            </td>
                                            <td class="text-center">
                                                <?= htmlspecialchars($row['nocturnas']) ?>
                                            </td>

                                        <?php endforeach; ?>
                                    </tr>
                                <?php endif; ?>

                            </tbody>
                            <?php
                            // calculamos los totales
                            $totalDiurnas   = array_sum(array_column($resumen, 'diurnas'));
                            $totalNocturnas = array_sum(array_column($resumen, 'nocturnas'));
                            // número total de columnas de datos = 1 (Puesto) + 2 por fecha
                            $colCount = 1 + 2 * count($fechas);
                            ?>
                            <tfoot class="bg-info">
                                <tr>
                                    <!-- una celda que abarca todas las columnas -->
                                    <td colspan="<?= $colCount ?>" class="text-center text-white">
                                        <b>Total periodos:</b> Jornadas Diurnas = <?= $totalDiurnas ?>,
                                        Jornadas Nocturnas = <?= $totalNocturnas ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>

                        <?php
                        // limpieza de sesión si no quieres persistir estos datos
                        unset($_SESSION['resumen_diario'], $_SESSION['filtros_resumen']);
                        ?>



                    </div>
                </div>
            </div>
        </div>
    </div>
</section>