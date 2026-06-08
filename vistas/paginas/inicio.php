<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['idUsuario'])) { header("Location: ?r=login"); exit(); }
$id = $_SESSION['idUsuario'] ?? 0; // se usa en varios partials
$categoriaSesion = strtolower((string) ($_SESSION['categoria'] ?? ''));
$esOperativoReferente = in_array($categoriaSesion, ['operativo', 'referente'], true);
?>

<style>
  .dashboard-panel {
    border-radius: 16px;
    overflow: hidden;
  }

  .dashboard-panel .card-header {
    border-bottom: 0;
  }

  @media (max-width: 575.98px) {
    .dashboard-shortcuts .col-6 {
      padding-left: 6px;
      padding-right: 6px;
    }
  }
</style>

<div class="card">
  <div class="card-header bg-info">
    <h3 class="card-title">Panel de Control</h3>
  </div>

  <div class="card-body">
    <?php include __DIR__ . '/inicio/_alerts.php'; ?>

    <div class="row dashboard-shortcuts">
      <?php include __DIR__ . '/inicio/_card_hombre_vivo.php'; ?>
      <?php if (!$esOperativoReferente): ?>
        <?php include __DIR__ . '/inicio/_card_directivas.php'; ?>
      <?php endif; ?>
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

    <?php if ($esOperativoReferente): ?>
      <div class="row mt-4">
        <div class="col-12">
          <?php include __DIR__ . '/inicio/_panel_directivas_inicio.php'; ?>
        </div>
      </div>
    <?php else: ?>
      <div class="row mt-4">
        <div class="col-12 col-lg-6 mb-3 mb-lg-0">
          <?php include __DIR__ . '/inicio/_panel_alertas_hvivo.php'; ?>
        </div>
        <div class="col-12 col-lg-6">
          <?php include __DIR__ . '/inicio/_panel_guardias_servicio.php'; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
