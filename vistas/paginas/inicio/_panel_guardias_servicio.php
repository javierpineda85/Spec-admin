<?php
$dbInicio = new Conexion();
$objetivosInicio = $dbInicio->consultas("SELECT idObjetivo, nombre FROM objetivos WHERE activo = 1 ORDER BY nombre") ?: [];
$objetivoFiltroInicio = isset($_GET['home_objetivo_id']) ? (int) $_GET['home_objetivo_id'] : 0;
$guardiasEnServicio = NovedadesController::obtenerGuardiasEnServicioInicio(
    $objetivoFiltroInicio > 0 ? $objetivoFiltroInicio : null,
    5
);
?>

<div class="card dashboard-panel shadow border-0">
    <div class="card-header bg-info d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <h3 class="card-title mb-2 mb-md-0">Guardias en Servicio</h3>
        <form method="get" class="form-inline">
            <div class="form-group mb-0">
                <label for="home_objetivo_id" class="mr-2 mb-0 small text-white">Objetivo:</label>
                <select name="home_objetivo_id" id="home_objetivo_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="0">Todos los objetivos</option>
                    <?php foreach ($objetivosInicio as $objetivo): ?>
                        <option value="<?= (int) $objetivo['idObjetivo'] ?>" <?= $objetivoFiltroInicio === (int) $objetivo['idObjetivo'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($objetivo['nombre'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Vigilador</th>
                    <th>Objetivo</th>
                    <th>Puesto</th>
                    <th>Hora de entrada</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($guardiasEnServicio)): ?>
                    <?php foreach ($guardiasEnServicio as $guardia): ?>
                        <tr>
                            <td><?= htmlspecialchars($guardia['vigilador'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($guardia['objetivo'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($guardia['puesto'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <strong><?= htmlspecialchars($guardia['hora_entrada'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong>
                                <div class="text-success small">
                                    <i class="fas fa-circle mr-1" style="font-size: 0.45rem;"></i>En servicio
                                </div>
                            </td>
                            <td class="text-center">
                                <a href="?r=reporte_entradas_salidas" class="btn btn-sm btn-outline-primary" title="Ver reporte">
                                    <i class="far fa-eye"></i>
                                </a>
                                <a href="?r=historialMarcaciones" class="btn btn-sm btn-outline-secondary" title="Ver historial">
                                    <i class="fas fa-history"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            No hay guardias en servicio para el filtro seleccionado.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white text-center border-top-0">
        <a href="?r=reporte_entradas_salidas" class="text-primary">Ver todos los guardias <i class="fas fa-arrow-right ml-1"></i></a>
    </div>
</div>
