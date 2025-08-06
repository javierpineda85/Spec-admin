<!-- feedback.php -->
<div class="container mt-5">


    <div class="card">
        <div class="card-body text-center">
            <?php if (!empty($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="icon fas fa-check"></i>
                    <?= $_SESSION['success_message'];
                    unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="icon fas fa-exclamation-circle"></i>
                    <?= $_SESSION['error_message'];
                    unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>
            <p><strong>Puesto:</strong> <?= htmlspecialchars($nombrePuesto) ?></p>
            <p><strong>Objetivo:</strong> <?= htmlspecialchars($nombreObjetivo) ?></p>
            <a href="?r=escanear" class="btn btn-primary mt-3">Volver a escanear</a>
        </div>
    </div>
</div>

<?php
// Limpieza de sesión para la próxima vez
unset(
    $_SESSION['ultima_ronda_id'],
    $_SESSION['ultimo_sector_id']
);
?>