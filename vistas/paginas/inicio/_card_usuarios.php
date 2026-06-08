      <?php if (
            Auth::hasPermission('usuarios', 'vistaCrearUsuario')
            || Auth::hasPermission('usuarios', 'vistaListadoUsuarios')
            || Auth::hasPermission('usuarios', 'vistaListadoUsuariosInactivos')
        ): ?>
  <div class="col-6 col-md-6 col-lg-3">
      <div class="info-box shadow">
                  <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                  <div class="info-box-content">
                      <!-- Fila para el título y botón de colapsar -->
                      <div class="d-flex justify-content-between align-items-center">
                          <span class="info-box-number">Usuarios</span>
                          <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseUsuarios" aria-expanded="false" aria-controls="collapseUsuarios">
                              <i class="fas fa-plus"></i>
                          </button>
                      </div>
                      <!-- Sección colapsable para los botones -->
                      <div id="collapseUsuarios" class="collapse">
                          <div class="mt-2">
                              <?php if (Auth::hasPermission('usuarios', 'vistaCrearUsuario')): ?>
                                  <a href="?r=crear-usuario" class="btn btn-block btn-info btn-sm text-white">Crear</a>
                              <?php endif; ?>
                              <?php if (Auth::hasPermission('usuarios', 'vistaListadoUsuarios')): ?>
                                  <a href="?r=listado-usuarios" class="btn btn-block btn-info btn-sm text-white">Mostrar Activos</a>
                              <?php endif; ?>
                              <?php if (Auth::hasPermission('usuarios', 'vistaListadoUsuariosInactivos')): ?>
                                  <a href="?r=listado-usuarios-inactivos" class="btn btn-block btn-info btn-sm text-white">Mostrar Inactivos</a>
                              <?php endif; ?>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      <?php endif; ?>
