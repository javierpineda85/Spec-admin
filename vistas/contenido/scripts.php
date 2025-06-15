<!-- AdminLTE (asegurate de que esté luego de jQuery y Bootstrap) -->
<script src="<?= BASE_URL ?>/js/adminlte.min.js"></script>

<!-- Service Worker -->
<script src="<?= BASE_URL ?>/sw-register.js"></script>

<!-- jQuery (debe ir primero) -->
<script src="<?= BASE_URL ?>/plugins/jquery/jquery.min.js"></script>

<!-- jQuery UI -->
<script src="<?= BASE_URL ?>/plugins/jquery-ui/jquery-ui.min.js"></script>

<!-- Resolver conflicto con tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>

<!-- Bootstrap 4 bundle (incluye Popper) -->
<script src="<?= BASE_URL ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- ChartJS -->
<script src="<?= BASE_URL ?>/plugins/chart.js/Chart.min.js"></script>

<!-- JQVMap -->
<script src="<?= BASE_URL ?>/plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="<?= BASE_URL ?>/plugins/jqvmap/maps/jquery.vmap.usa.js"></script>

<!-- Knob -->
<script src="<?= BASE_URL ?>/plugins/jquery-knob/jquery.knob.min.js"></script>

<!-- Daterangepicker -->
<script src="<?= BASE_URL ?>/plugins/moment/moment.min.js"></script>
<script src="<?= BASE_URL ?>/plugins/daterangepicker/daterangepicker.js"></script>

<!-- Tempusdominus -->
<script src="<?= BASE_URL ?>/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>

<!-- Summernote -->
<script src="<?= BASE_URL ?>/plugins/summernote/summernote-bs4.min.js"></script>

<!-- OverlayScrollbars -->
<script src="<?= BASE_URL ?>/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>


<!-- DataTables -->
<script src="<?= BASE_URL ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= BASE_URL ?>/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= BASE_URL ?>/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= BASE_URL ?>/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="<?= BASE_URL ?>/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="<?= BASE_URL ?>/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="<?= BASE_URL ?>/plugins/jszip/jszip.min.js"></script>
<script src="<?= BASE_URL ?>/plugins/pdfmake/pdfmake.min.js"></script>
<script src="<?= BASE_URL ?>/plugins/pdfmake/vfs_fonts.js"></script>
<script src="<?= BASE_URL ?>/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="<?= BASE_URL ?>/plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="<?= BASE_URL ?>/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>

<!-- Duallistbox -->
<script src="<?= BASE_URL ?>/plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js"></script>

<!-- Bootstrap Switch -->
<script src="<?= BASE_URL ?>/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

<!-- Leaflet (geoposicionamiento) -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- QR Scanner -->
<script src="https://unpkg.com/@zxing/library@latest"></script>

<!-- JS personalizado -->
<script src="<?= BASE_URL ?>/js/main.js"></script>
<script src="<?= BASE_URL ?>/js/custom.js" defer></script>

<!-- Modal para imagen ampliada -->
<div class="modal fade" id="imagenModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Imagen Ampliada</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        <img id="imagenAmpliada" src="" class="img-fluid" alt="Imagen ampliada">
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    if ('Notification' in window && navigator.serviceWorker) {
      const yaPidioPermiso = localStorage.getItem('notificaciones_permiso_pedido');

      if (!yaPidioPermiso) {
        Notification.requestPermission().then(permission => {
          if (permission === 'granted') {
            console.log('✅ Permiso de notificación concedido');
          } else if (permission === 'denied') {
            console.warn('❌ El usuario rechazó las notificaciones');
          } else {
            console.log('ℹ️ Permiso de notificación no definido');
          }

          localStorage.setItem('notificaciones_permiso_pedido', '1');
        });
      }
    }
  });
</script>
<script>
  document.addEventListener('click', () => {
    const sonido = document.getElementById('sonido-alerta-global');
    if (sonido) {
      sonido.volume = 0;
      sonido.play().then(() => {
        sonido.pause();
        sonido.currentTime = 0;
        sonido.volume = 1;
      }).catch(err => {
        console.warn('🔇 No se pudo desbloquear audio aún:', err);
      });
    }
  }, {
    once: true
  }); // Solo una vez
  
</script>