<?php if (
    Auth::hasPermission('novedades', 'vistaCrearNovedades') ||
    Auth::hasPermission('novedades', 'vistaListadoNovedades') ||
    Auth::hasPermission('novedades', 'vistaListadoEntradaSalida') ||
    Auth::hasPermission('novedades', 'vistaEntradaSalida') ||
    Auth::hasPermission('novedades', 'vistaHistorialMarcaciones')
): ?>
  <div class="col-6 col-md-6 col-lg-3">
      <div class="info-box shadow">
          <span class="info-box-icon bg-info"><i class="far fa-newspaper"></i></span>
          <div class="info-box-content">
              <div class="d-flex justify-content-between align-items-center">
                  <span class="info-box-number">Novedades</span>
                  <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseNovedades" aria-expanded="false" aria-controls="collapseNovedades">
                      <i class="fas fa-plus"></i>
                  </button>
              </div>
              <div id="collapseNovedades" class="collapse">
                  <div class="mt-2">
                      <?php if (Auth::hasPermission('novedades', 'vistaEntradaSalida')): ?>
                          <a href="?r=entradas_salidas" class="btn btn-block btn-info btn-sm text-white">Marcar Entrada/Salida</a>
                      <?php endif; ?>
                      <?php if (Auth::hasPermission('novedades', 'vistaCrearNovedades')): ?>
                          <a href="?r=crear_novedad" class="btn btn-block btn-info btn-sm text-white">Crear</a>
                      <?php endif; ?>
                      <?php if (Auth::hasPermission('novedades', 'vistaListadoNovedades')): ?>
                          <a href="?r=listado_novedades" class="btn btn-block btn-info btn-sm text-white">Mostrar Todas</a>
                      <?php endif; ?>
                      <?php if (Auth::hasPermission('novedades', 'vistaListadoEntradaSalida')): ?>
                          <a href="?r=reporte_entradas_salidas" class="btn btn-block btn-primary btn-sm text-white">Reporte Entrada/Salida</a>
                      <?php endif; ?>
                      <?php if (Auth::hasPermission('novedades', 'vistaHistorialMarcaciones')): ?>
                          <a href="?r=historialMarcaciones" class="btn btn-block btn-primary btn-sm text-white">Historial Entrada/Salida</a>
                      <?php endif; ?>
                  </div>
              </div>
          </div>
      </div>
  </div>
<?php endif; ?>
