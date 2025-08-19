      <?php if (
            Auth::hasPermission('rondas', 'vistaCrearRondas')
            || Auth::hasPermission('rondas', 'vistaListadoRondas')
            || Auth::hasPermission('rondas', 'vistaEscanearRondas')
        ): ?>
          <div class="col-lg-3 col-md-6 col-sm-12">
              <div class="info-box shadow">
                  <span class="info-box-icon bg-success"><i class="nav-icon fas fa-sync-alt"></i></span>
                  <div class="info-box-content">
                      <div class="d-flex justify-content-between align-items-center">
                          <span class="info-box-number">Rondas</span>
                          <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseRondas" aria-expanded="false" aria-controls="collapseRondas">
                              <i class="fas fa-plus"></i>
                          </button>
                      </div>
                      <div id="collapseRondas" class="collapse">
                          <div class="mt-2">
                              <?php if (Auth::hasPermission('rondas', 'vistaCrearRondas')): ?>
                                  <a href="?r=crear_rondas" class="btn btn-block btn-success btn-sm text-white">Crear</a>
                              <?php endif; ?>
                              <?php if (Auth::hasPermission('rondas', 'vistaListadoRondas')): ?>
                                  <a href="?r=listado_rondas" class="btn btn-block btn-success btn-sm text-white">Mostrar Todas</a>
                              <?php endif; ?>
                              <?php if (Auth::hasPermission('rondas', 'vistaEscanearRondas')): ?>
                                  <a href="?r=escanear" class="btn btn-block btn-success btn-sm text-white">Escanear QR</a>
                              <?php endif; ?>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      <?php endif; ?>