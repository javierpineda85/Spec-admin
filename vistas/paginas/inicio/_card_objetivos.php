      <?php if (
            Auth::hasPermission('objetivos', 'vistaCreaObjetivo')
            || Auth::hasPermission('objetivos', 'vistaListadoObjetivos')
            || Auth::hasPermission('objetivos', 'vistaListadoObjetivosInactivos')
            || Auth::hasPermission('objetivos', 'vistaEditarObjetivo')
        ): ?>
          <div class="col-lg-3 col-md-6 col-sm-12">
              <div class="info-box shadow">
                  <span class="info-box-icon bg-success"><i class="fas fa-map-marker-alt"></i></span>
                  <div class="info-box-content">
                      <div class="d-flex justify-content-between align-items-center">
                          <span class="info-box-number">Objetivos</span>
                          <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseObjetivos" aria-expanded="false" aria-controls="collapseObjetivos">
                              <i class="fas fa-plus"></i>
                          </button>
                      </div>
                      <div id="collapseObjetivos" class="collapse">
                          <div class="mt-2">
                              <?php if (Auth::hasPermission('objetivos', 'vistaCreaObjetivo')): ?>
                                  <a href="?r=crear_objetivo" class="btn btn-block btn-success btn-sm text-white">Crear</a>
                              <?php endif; ?>
                              <?php if (Auth::hasPermission('objetivos', 'vistaListadoObjetivos')): ?>
                                  <a href="?r=listado_objetivos" class="btn btn-block btn-success btn-sm text-white">Mostrar Activos</a>
                              <?php endif; ?>
                              <?php if (Auth::hasPermission('objetivos', 'vistaListadoObjetivosInactivos')): ?>
                                  <a href="?r=listado_objetivos_inactivos" class="btn btn-block btn-success btn-sm text-white">Mostrar Inactivos</a>
                              <?php endif; ?>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      <?php endif; ?>