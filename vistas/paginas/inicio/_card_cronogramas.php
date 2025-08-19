      <?php if (
            Auth::hasPermission('cronogramas', 'vistaCrearCronograma')
            || Auth::hasPermission('cronogramas', 'vistaListadoCronogramas')
            || Auth::hasPermission('cronogramas', 'vistaListadoCronogramaPorVigilador')
            || Auth::hasPermission('cronogramas', 'vistaJornadasPorObjetivo')
            || Auth::hasPermission('cronogramas', 'crtBuscarResumenHoras')
            || Auth::hasPermission('cronogramas', 'vistaHorasPorVigilador')
        ): ?>
          <div class="col-lg-3 col-md-6 col-sm-12">
              <div class="info-box shadow">
                  <span class="info-box-icon bg-primary"><i class="fas fa-calendar-alt"></i></span>
                  <div class="info-box-content">
                      <!-- Fila para el título y botón de colapsar -->
                      <div class="d-flex justify-content-between align-items-center">
                          <span class="info-box-number">Cronogramas</span>
                          <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseCronogramas" aria-expanded="false" aria-controls="collapseCronogramas">
                              <i class="fas fa-plus"></i>
                          </button>
                      </div>
                      <!-- Sección colapsable para los botones -->
                      <div id="collapseCronogramas" class="collapse">
                          <div class="mt-2">
                              <?php if (Auth::hasPermission('cronogramas', 'vistaCrearCronograma')): ?>
                                  <a href="?r=crear_cronograma" class="btn btn-block btn-primary btn-sm text-white">Crear</a>
                              <?php endif; ?>
                              <?php if (Auth::hasPermission('cronogramas', 'vistaListadoCronogramas')): ?>
                                  <a href="?r=listado_cronogramas" class="btn btn-block btn-primary btn-sm text-white">Por Objetivo</a>
                              <?php endif; ?>
                              <?php if (Auth::hasPermission('cronogramas', 'vistaListadoCronogramaPorVigilador')): ?>
                                  <a href="?r=listado_porVigilador" class="btn btn-block btn-primary btn-sm text-white">Por Vigilador</a>
                              <?php endif; ?>
                              <?php if (Auth::hasPermission('cronogramas', 'vistaJornadasPorObjetivo')): ?>
                                  <a href="?r=listado_resumen_diario" class="btn btn-block btn-primary btn-sm text-white">Jornadas por Objetivo</a>
                              <?php endif; ?>
                              <?php if (Auth::hasPermission('cronogramas', 'crtBuscarResumenHoras')): ?>
                                  <a href="?r=reporte_porHoras" class="btn btn-block btn-primary btn-sm text-white">Horas por Objetivo</a>
                              <?php endif; ?>
                              <?php if (Auth::hasPermission('cronogramas', 'vistaHorasPorVigilador')): ?>
                                  <a href="?r=reporte_porVigilador" class="btn btn-block btn-primary btn-sm text-white">Horas por Vigilador</a>
                              <?php endif; ?>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      <?php endif; ?>