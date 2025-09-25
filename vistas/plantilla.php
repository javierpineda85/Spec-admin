<?php include __DIR__ . '/contenido/head.php';

if (!isset($_SESSION)) {
  session_start();
}
?>



<body class="hold-transition sidebar-mini sidebar-collapse">
  <style>
    .parent {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      grid-template-rows: repeat(3, 1fr);
      gap: 8px;
    }

    .parent1 {
      grid-row: span 3 / span 3;
    }


    .parent3 {
      grid-column-start: 2;
    }

    .parent4 {
      grid-column-start: 2;
      grid-row-start: 3;
    }
  </style>
  <!-- Site wrapper -->
  <div class="wrapper">

    <!-- Navbar -->

    <?php include __DIR__ . '/contenido/header.php'; ?>

    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <?php include __DIR__ . '/contenido/aside.php'; ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">


      <!-- Main content -->
      <section class="content mt-2">


        <?php

        RutasController::cargarVista();
        ?>

      </section>
      <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <footer class="main-footer">
      <?php include __DIR__ . '/contenido/footer.php'; ?>
    </footer>



    <!-- Toast reutilizable -->
    <div id="toast-alerta" aria-live="polite" aria-atomic="true" style="position: fixed; top: 1rem; right: 1rem; z-index: 1000;">
      <div class="toast" role="alert" data-delay="7000" style="min-width: 300px;">
        <div class="toast-header bg-info text-white">
          <strong class="mr-auto"><i class="fas fa-info-circle text-dark"></i> Notificación</strong>
          <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="toast-body" id="toast-msg">
          <!-- mensaje dinámico -->
        </div>
      </div>
    </div>



    <!-- Modal Mensajes -->
    <div class="modal fade" id="modalVerMensaje" tabindex="-1" role="dialog" aria-labelledby="modalVerMensajeLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-info">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title" id="modalVerMensajeLabel">Mensaje recibido</h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" id="contenido-mensaje">
            <p class="text-muted">Cargando mensaje...</p>
          </div>
        </div>
      </div>
    </div>
    <?php if (!empty($_SESSION['toast'])): ?>
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          mostrarToast("<?= addslashes($_SESSION['toast']['mensaje']) ?>", "<?= $_SESSION['toast']['tipo'] ?>");
        });
      </script>
      <?php unset($_SESSION['toast']); ?>
    <?php endif; ?>
    <!-- scripts -->
    <script>
      function mostrarToast(mensaje, tipo = 'info') {
        const $contenedor = $('#toast-alerta'); // ahora es el contenedor externo
        const $toast = $contenedor.find('.toast');
        const $header = $toast.find('.toast-header');
        const $icon = $header.find('i');

        // Elevar z-index mientras está visible
        $contenedor.css('z-index', 3000);

        // Limpiar clases anteriores
        $toast.removeClass('bg-success bg-warning bg-danger bg-info');
        $header.removeClass('bg-success bg-warning bg-danger bg-info text-white text-dark');

        // Configurar por tipo
        switch (tipo) {
          case 'success':
            $toast.addClass('bg-success');
            $header.addClass('bg-success text-white');
            $icon.removeClass().addClass('fas fa-check-circle');
            break;
          case 'warning':
            $toast.addClass('bg-warning');
            $header.addClass('bg-warning text-dark');
            $icon.removeClass().addClass('fas fa-exclamation-triangle');
            break;
          case 'danger':
            $toast.addClass('bg-danger');
            $header.addClass('bg-danger text-white');
            $icon.removeClass().addClass('fas fa-times-circle');
            break;
          default:
            $toast.addClass('bg-info');
            $header.addClass('bg-info text-white');
            $icon.removeClass().addClass('fas fa-info-circle');
        }

        $('#toast-msg').text(mensaje);
        $toast.toast({
          delay: 7000
        }).toast('show');

        // Restaurar z-index después del delay
        setTimeout(() => {
          $contenedor.css('z-index', 1000);
        }, 7000);
      }
    </script>
    <?php include __DIR__ . '/contenido/scripts.php'; ?>
</body>

</html>