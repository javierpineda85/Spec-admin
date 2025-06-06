<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title">Resumen de Horas Por Objetivo</h3>
                    </div>
                    <div class="card-body">
                        <form action="index.php?r=buscar_resumen_horas" method="POST" class="card p-4 shadow-sm mb-4">
                            <div class="form-row">

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
                                    <button type="submit" name="buscar_resumen_horas" class="btn btn-primary mt-2">
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
                        // Recuperamos el resumen que guardó el controlador
                        $res = $_SESSION['resumen_periodo'] ?? [];
                        ?>
                        <table id="example1" class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Objetivo</th>
                                    <th class="text-center">Horas Diurnas</th>
                                    <th class="text-center">Horas Nocturnas</th>
                                    <th class="text-center">Totals Horas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($res): ?>
                                    <?php foreach ($res as $r): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($r['nombre']) ?></td>
                                            <td class="text-center"><?= number_format($r['diurnas'], 2) ?></td>
                                            <td class="text-center"><?= number_format($r['nocturnas'], 2) ?></td>
                                            <td class="text-center"><?= $r['diurnas'] + $r['nocturnas']?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No hay datos para ese periodo</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <?php
                        // limpieza de sesión si no quieres persistir estos datos
                        unset($_SESSION['resumen_periodo'], $_SESSION['filtros_resumen']);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>