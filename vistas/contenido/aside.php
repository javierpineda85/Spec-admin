<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$id = $_SESSION['idUsuario'] ?? 0; // disponible para partials si lo necesitan
?>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <?php include __DIR__ . '/aside/_brand.php'; ?>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar user -->
    <?php include __DIR__ . '/aside/_user_panel.php'; ?>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

        <!-- Panel de Control -->
        <li class="nav-item">
          <a href="index.php" class="nav-link">
            <i class="nav-icon fas fa-tachometer-alt text-lime"></i>
            <p>Panel de Control</p>
          </a>
        </li>

        <?php include __DIR__ . '/aside/_menu_hvivo.php'; ?>
        <?php include __DIR__ . '/aside/_menu_directivas.php'; ?>
        <?php include __DIR__ . '/aside/_menu_objetivos.php'; ?>
        <?php include __DIR__ . '/aside/_menu_puestos.php'; ?>
        <?php include __DIR__ . '/aside/_menu_rondas.php'; ?>
        <?php include __DIR__ . '/aside/_menu_cronogramas.php'; ?>
        <?php include __DIR__ . '/aside/_menu_novedades.php'; ?>
        <?php include __DIR__ . '/aside/_menu_usuarios.php'; ?>
        <?php include __DIR__ . '/aside/_menu_admin.php'; ?>
        <?php include __DIR__ . '/aside/_menu_mensajes.php'; ?>
        <?php include __DIR__ . '/aside/_menu_noticias.php'; ?>
        <?php include __DIR__ . '/aside/_menu_mis_datos.php'; ?>
        <?php include __DIR__ . '/aside/_menu_permisos.php'; ?>
        <?php include __DIR__ . '/aside/_menu_config.php'; ?>
        <?php include __DIR__ . '/aside/_menu_logout.php'; ?>

      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>
