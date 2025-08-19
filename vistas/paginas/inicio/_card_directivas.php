      <?php if (
            Auth::hasPermission('directivas', 'crtCrearDirectiva')
            || Auth::hasPermission('directivas', 'crtListarDirectivas')
            || Auth::hasPermission('directivas', 'vistaCrearDirectivas')
        ): ?>
          <div class="col-lg-3 col-md-6 col-sm-12">
              <div class="info-box shadow">
                  <span class="info-box-icon bg-warning"><i class="fas fa-list-ul"></i></span>
                  <div class="info-box-content">
                      <!-- Fila para el título y botón de colapsar -->
                      <div class="d-flex justify-content-between align-items-center">
                          <span class="info-box-number">Directivas</span>
                          <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapsDirectivas" aria-expanded="false" aria-controls="collapsDirectivas">
                              <i class="fas fa-plus"></i>
                          </button>
                      </div>
                      <!-- Sección colapsable para los botones -->
                      <div id="collapsDirectivas" class="collapse">
                          <div class="mt-2">
                              <?php if (Auth::hasPermission('directivas', 'crtCrearDirectiva') || Auth::hasPermission('directivas', 'vistaCrearDirectiva')): ?>
                                  <a href="?r=vistaCrearDirectiva" class="btn btn-block btn-warning btn-sm text-dark">Crear</a>
                              <?php endif; ?>
                              <?php if (Auth::hasPermission('directivas', 'crtListarDirectivas')): ?>
                                  <a href="?r=listado_directivas" class="btn btn-block btn-warning btn-sm text-dark">Mostrar todas</a>
                              <?php endif; ?>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      <?php endif; ?>