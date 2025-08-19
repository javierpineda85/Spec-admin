<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['idUsuario'])) { header("Location: ?r=login"); exit(); }
$id = $_SESSION['idUsuario'] ?? 0; // se usa en varios partials
?>

<div class="card">
  <div class="card-header bg-info">
    <h3 class="card-title">Panel de Control</h3>
  </div>

  <div class="card-body">
    <?php include __DIR__ . '/inicio/_alerts.php'; ?>

    <div class="row">
      <?php include __DIR__ . '/inicio/_card_hombre_vivo.php'; ?>
      <?php include __DIR__ . '/inicio/_card_directivas.php'; ?>
      <?php include __DIR__ . '/inicio/_card_objetivos.php'; ?>
      <?php include __DIR__ . '/inicio/_card_puestos.php'; ?>
      <?php include __DIR__ . '/inicio/_card_rondas.php'; ?>
      <?php include __DIR__ . '/inicio/_card_cronogramas.php'; ?>
      <?php include __DIR__ . '/inicio/_card_novedades.php'; ?>
      <?php include __DIR__ . '/inicio/_card_usuarios.php'; ?>
      <?php include __DIR__ . '/inicio/_card_admin.php'; ?>
      <?php include __DIR__ . '/inicio/_card_noticias.php'; ?>
      <?php include __DIR__ . '/inicio/_card_mensajeria.php'; ?>
      <?php include __DIR__ . '/inicio/_card_mis_datos.php'; ?>
    </div>
  </div>
</div>
