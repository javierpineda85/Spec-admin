<!-- feedback.php -->
<div class="container mt-5">


    <div class="card">
        <div class="card-body text-center">

            <p><strong>Puesto:</strong> <?= htmlspecialchars($nombrePuesto) ?></p>
            <p><strong>Objetivo:</strong> <?= htmlspecialchars($nombreObjetivo) ?></p>
            <a href="?r=escanear" class="btn btn-primary mt-3">Escanear otro QR</a>
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