<?php
$reportesHvivo = HombreVivoController::obtenerReportesRecientesInicio(5);

$clasificarDemora = static function (?string $demora): array {
    $demora = trim((string) $demora);
    if ($demora === '') {
        return ['badge-secondary', '-'];
    }

    $signo = '';
    if ($demora[0] === '-') {
        $signo = '-';
        $demora = substr($demora, 1);
    } elseif ($demora[0] === '+') {
        $signo = '+';
        $demora = substr($demora, 1);
    }

    $partes = explode(':', $demora);
    while (count($partes) < 3) {
        array_unshift($partes, '00');
    }

    [$h, $m, $s] = array_map('intval', $partes);
    $totalSeg = ($h * 3600) + ($m * 60) + $s;
    if ($signo === '-') {
        $totalSeg *= -1;
    }

    if ($totalSeg <= 0) {
        return ['badge-primary', 'En tiempo'];
    }

    if ($totalSeg <= 180) {
        return ['badge-success', '≤ 3 min'];
    }

    return ['badge-danger', '> 3 min'];
};
?>

<div class="card dashboard-panel shadow border-0">
    <div class="card-header bg-info d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Alertas de Hombre Vivo</h3>
        <a href="index.php?r=listado_reportes" class="btn btn-sm btn-link text-primary p-0">Ver todas</a>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($reportesHvivo)): ?>
            <div class="px-3 pt-3 pb-2">
                <?php
                $conteo = ['En tiempo' => 0, '≤ 3 min' => 0, '> 3 min' => 0];
                foreach ($reportesHvivo as $r) {
                    [, $estado] = $clasificarDemora($r['demora'] ?? '');
                    if (isset($conteo[$estado])) {
                        $conteo[$estado]++;
                    }
                }
                ?>
                <span class="badge badge-primary mr-1">En tiempo: <?= $conteo['En tiempo'] ?></span>
                <span class="badge badge-success mr-1">≤ 3 min: <?= $conteo['≤ 3 min'] ?></span>
                <span class="badge badge-danger mr-1">> 3 min: <?= $conteo['> 3 min'] ?></span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Vigilador</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Demora</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($reportesHvivo, 0, 5) as $reporte): ?>
                            <?php
                            [$badgeClass, $label] = $clasificarDemora($reporte['demora'] ?? '');
                            $demoraFmt = trim((string) ($reporte['demora'] ?? ''));
                            if ($demoraFmt !== '') {
                                $demoraFmt = ltrim($demoraFmt, '+');
                                $partes = explode(':', $demoraFmt);
                                while (count($partes) < 3) {
                                    array_unshift($partes, '00');
                                }
                                [, $m, $s] = array_map('intval', $partes);
                                $demoraFmt = sprintf('%02d:%02d', $m, $s);
                            }
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($reporte['vigilador'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= !empty($reporte['fecha']) ? date('d/m/Y', strtotime($reporte['fecha'])) : '-' ?></td>
                                <td><?= htmlspecialchars($reporte['hora'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($demoraFmt ?: '-', ENT_QUOTES, 'UTF-8') ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-4 text-center text-muted">
                No hay reportes recientes de Hombre Vivo.
            </div>
        <?php endif; ?>
    </div>
    <div class="card-footer bg-white text-center border-top-0">
        <a href="index.php?r=listado_reportes" class="text-primary">Ver todos los reportes <i class="fas fa-arrow-right ml-1"></i></a>
    </div>
</div>
