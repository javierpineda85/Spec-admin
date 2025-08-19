      <?php if (
            Auth::hasPermission('feriados', 'vistaCrearFeriado') ||
            Auth::hasPermission('feriados', 'vistaListadoFeriado') ||
            Auth::hasPermission('art', 'crtGuardarArt') ||
            Auth::hasPermission('art', 'vistaListadoArt') ||
            Auth::hasPermission('uniformes', 'verListadoUniformes')
        ): ?>
          <div class="col-lg-3 col-md-6 col-sm-12">
              <div class="info-box shadow">
                  <span class="info-box-icon bg-warning"><i class="fas fa-cogs"></i></span>
                  <div class="info-box-content">

                      <!-- Fila para el título y botón de colapsar -->
                      <div class="d-flex justify-content-between align-items-center">
                          <span class="info-box-number">Administración</span>
                          <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseAdministracion" aria-expanded="false" aria-controls="collapseAdministracion">
                              <i class="fas fa-plus"></i>
                          </button>
                      </div>

                      <!-- Sección colapsable para los botones -->
                      <div id="collapseAdministracion" class="collapse">
                          <div class="mt-2">

                              <?php if (Auth::hasPermission('feriados', 'vistaCrearFeriado')): ?>
                                  <a href="?r=crear_feriados" class="btn btn-block btn-warning btn-sm text-white">Crear Feriados</a>
                              <?php endif; ?>

                              <?php if (Auth::hasPermission('feriados', 'vistaListadoFeriado')): ?>
                                  <a href="?r=listado_feriados" class="btn btn-block btn-warning btn-sm text-white">Ver Feriados</a>
                              <?php endif; ?>

                              <?php if (Auth::hasPermission('art', 'crtGuardarArt')): ?>
                                  <a href="?r=crear_art" class="btn btn-block btn-warning btn-sm text-white">Crear ART</a>
                              <?php endif; ?>

                              <?php if (Auth::hasPermission('art', 'vistaListadoArt')): ?>
                                  <a href="?r=listado_art" class="btn btn-block btn-warning btn-sm text-white">Listado ART</a>
                              <?php endif; ?>

                              <?php if (Auth::hasPermission('uniformes', 'verListadoUniformes')): ?>
                                  <a href="?r=listado_uniformes" class="btn btn-block btn-warning btn-sm text-white">Listado Uniformes</a>
                              <?php endif; ?>
                          </div>
                      </div>

                  </div>
              </div>
          </div>
      <?php endif; ?>