<?php if (Auth::hasPermission('escaneos', 'registrar')): ?>
  <li class="nav-item has-treeview">
    <a href="#" class="nav-link">
      <i class="nav-icon far fa-life-ring text-danger"></i>
      <p>Hombre Vivo <i class="fas fa-angle-left right"></i></p>
    </a>
    <ul class="nav nav-treeview">
      <?php if (Auth::hasPermission('hvivo', 'registrar')): ?>
        <li class="nav-item">
          <a href="?r=reporte_hombre_vivo" class="nav-link">
            <i class="far fa-circle nav-icon"></i>
            <p>Reportar</p>
          </a>
        </li>
      <?php endif; ?>
      <?php if (Auth::hasPermission('hvivo', 'vistaListadoReportesHombreVivo')): ?>
        <li class="nav-item">
          <a href="?r=listado_reportes" class="nav-link">
            <i class="far fa-circle nav-icon"></i>
            <p>Ver reportes</p>
          </a>
        </li>
      <?php endif; ?>
    </ul>
  </li>
<?php endif; ?>