<!-- jQuery -->
<script src="<?= BASE_URL ?>/plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="<?= BASE_URL ?>/plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="<?= BASE_URL ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="<?= BASE_URL ?>/plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline 
<script src="./plugins/sparklines/sparkline.js"></script>-->
<!-- JQVMap -->
<script src="<?= BASE_URL ?>/plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="<?= BASE_URL ?>/plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="<?= BASE_URL ?>/plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="<?= BASE_URL ?>/plugins/moment/moment.min.js"></script>
<script src="<?= BASE_URL ?>/plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="<?= BASE_URL ?>/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="<?= BASE_URL ?>/plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="<?= BASE_URL ?>/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="<?= BASE_URL ?>/js/adminlte.js"></script>

<!-- jQuery -->
<script src="<?= BASE_URL ?>/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="<?= BASE_URL ?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- DataTables  & Plugins -->
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

<!-- Bootstrap4 Duallistbox -->
<script src="<?= BASE_URL ?>/plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.js"></script>
<script src="<?= BASE_URL ?>/plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js"></script>

<!-- Bootstrap Switch -->
<script src="<?= BASE_URL ?>/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script src="<?= BASE_URL ?>/plugins/bootstrap-switch/js/bootstrap-switch.js"></script>

<!-- Sweet Alert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- incluir jQuery, Select2, etc. -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

<!-- incluir JS personalizado -->
<script src="<?= BASE_URL ?>/js/custom.js" defer></script>

<!-- incluir API GPS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- incluir Scaner QR -->
<script src="https://unpkg.com/@zxing/library@latest"></script>

<!-- Modal para mostrar la imagen en grande -->
<div class="modal fade" id="imagenModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Imagen Ampliada</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        <img id="imagenAmpliada" src="" class="img-fluid" alt="Imagen ampliada">
      </div>
    </div>
  </div>
</div>
