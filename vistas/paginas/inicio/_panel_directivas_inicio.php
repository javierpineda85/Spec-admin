<?php
$categoriaInicio = strtolower((string) ($_SESSION['categoria'] ?? ''));
$rolInicio = (string) ($_SESSION['rol'] ?? '');
$esRestringidoInicio = in_array($categoriaInicio, ['operativo', 'referente'], true) || in_array($rolInicio, ['Vigilador', 'Referente'], true);
$objetivoInicio = $esRestringidoInicio ? (int) ($_SESSION['objetivo_id'] ?? 0) : 0;
$directivasInicio = $objetivoInicio > 0
    ? ControladorDirectivas::obtenerDirectivasInicio($objetivoInicio, 5)
    : [];

$formatearTipoDirectiva = static function (string $tipo): array {
    return match ($tipo) {
        'general' => ['badge-primary', 'General'],
        'particular' => ['badge-success', 'Particular'],
        'eventual' => ['badge-warning', 'Eventual'],
        default => ['badge-secondary', ucfirst($tipo)],
    };
};
?>

<div class="card dashboard-panel shadow border-0">
    <div class="card-header bg-info d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Directivas</h3>
        <a href="?r=listado_directivas" class="btn btn-sm btn-link text-primary p-0">Mostrar todas</a>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($directivasInicio)): ?>
            <div class="list-group list-group-flush">
                <?php foreach ($directivasInicio as $directiva): ?>
                    <?php [$badgeClass, $badgeLabel] = $formatearTipoDirectiva((string) ($directiva['tipo'] ?? '')); ?>
                    <div class="list-group-item border-0 py-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="pr-3">
                                <div class="font-weight-bold text-dark">
                                    <?= htmlspecialchars($directiva['objetivo'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                    <span class="badge <?= $badgeClass ?> ml-2"><?= htmlspecialchars($badgeLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <div class="text-muted small mt-1">
                                    <?= nl2br(htmlspecialchars(mb_strimwidth((string) ($directiva['detalle'] ?? ''), 0, 180, '...'), ENT_QUOTES, 'UTF-8')) ?>
                                </div>
                                <?php if (!empty($directiva['adjunto'])): ?>
                                    <div class="mt-2">
                                        <a href="<?= htmlspecialchars($directiva['adjunto'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-paperclip mr-1"></i>Ver adjunto
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="p-4 text-center text-muted">
                No hay directivas para mostrar.
            </div>
        <?php endif; ?>
    </div>
    <div class="card-footer bg-white text-center border-top-0">
        <a href="?r=listado_directivas" class="text-primary">Ver todas las directivas <i class="fas fa-arrow-right ml-1"></i></a>
    </div>
</div>
