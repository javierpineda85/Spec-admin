<div class="card">
  <div class="card-header bg-info">
    <h3 class="card-title">Panel de Control</h3>

  </div>
  <div class="card-body">
    <?php if (!empty($_SESSION['success_message'])): ?>
      <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="icon fas fa-check"></i>
        <?= $_SESSION['success_message'];
        unset($_SESSION['success_message']); ?>
      </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success_error'])): ?>
      <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-exclamation-triangle"></i>
        <?= $_SESSION['success_error'];
        unset($_SESSION['success_error']); ?>
      </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['sinAsignaciones'])): ?>
      <div class="alert alert-success text-center mt-3">
        <strong>Bienvenido.</strong> Aún no tienes objetivos asignados.
      </div>
      <?php unset($_SESSION['sinAsignaciones']); ?>
    <?php endif; ?>
    <!-- Small boxes (Stat box) -->
    <div class="row">
      <!-- hombre vivo -->
      <?php if (
        Auth::hasPermission('hvivo', 'registrar')
        || Auth::hasPermission('hvivo', 'listar')
      ): ?>
        <!-- Hombre Vivo -->
        <div class="col-lg-3 col-md-6 col-sm-12">
          <div class="info-box shadow">
            <span class="info-box-icon bg-danger"><i class="far fa-life-ring"></i></span>
            <div class="info-box-content">
              <!-- Fila para el título y botón de colapsar -->
              <div class="d-flex justify-content-between align-items-center">
                <span class="info-box-number">Hombre Vivo</span>
                <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapsHvivo" aria-expanded="false" aria-controls="collapsHvivo">
                  <i class="fas fa-plus"></i>
                </button>
              </div>
              <!-- Sección colapsable para los botones -->
              <div id="collapsHvivo" class="collapse">
                <div class="mt-2">
                  <?php if (Auth::hasPermission('hvivo', 'registrar')): ?>
                    <a href="tel:911" class="btn btn-block btn-danger btn-sm text-white">Llamar 911</a>
                    <a href="?r=reporte_hombre_vivo" class="btn btn-block btn-info btn-sm text-white">Reportar</a>
                  <?php endif; ?>
                  <?php if (Auth::hasPermission('hvivo','vistaListadoReportesHombreVivo')): ?>
                    <a href="?r=listado_reportes" class="btn btn-block btn-info btn-sm text-white">Ver reportes</a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- ./Hombre Vivo -->
      <?php endif; ?>
      <!-- ./hombre vivo-->

      <!-- Directivas -->
      <?php if (
        Auth::hasPermission('directivas', 'crtCrearDirectiva')
        || Auth::hasPermission('directivas', 'crtListarDirectivas') 
        ||Auth::hasPermission('directivas','vistaCrearDirectivas')
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
                  <?php if (Auth::hasPermission('directivas', 'crtCrearDirectiva')||Auth::hasPermission('directivas','vistaCrearDirectiva')): ?>
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
      <!-- ./directivas -->

      <!-- Rondas -->
      <?php if (
        Auth::hasPermission('rondas', 'vistaCrearRondas')
        || Auth::hasPermission('rondas', 'vistaListadoRondas')
        || Auth::hasPermission('rondas', 'vistaEscanearRondas')
      ): ?>
        <div class="col-lg-3 col-md-6 col-sm-12">
          <div class="info-box shadow">
            <span class="info-box-icon bg-success"><i class="nav-icon fas fa-sync-alt"></i></span>
            <div class="info-box-content">
              <!-- Fila para el título y botón de colapsar -->
              <div class="d-flex justify-content-between align-items-center">
                <span class="info-box-number">Rondas</span>
                <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseRondas" aria-expanded="false" aria-controls="collapseRondas">
                  <i class="fas fa-plus"></i>
                </button>
              </div>
              <!-- Sección colapsable para los botones -->
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
      <!-- ./rondas -->

      <!-- Cronogramas -->
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
      <!-- ./cronograma -->

      <!-- Novedades -->
      <?php if (
        Auth::hasPermission('novedades', 'vistaCrearNovedades')
        || Auth::hasPermission('novedades', 'vistaListadoNovedades')
        || Auth::hasPermission('novedades', 'vistaListadoEntradaSalida')
      ): ?>
        <div class="col-lg-3 col-md-6 col-sm-12">
          <div class="info-box shadow">
            <span class="info-box-icon bg-info"><i class="far fa-newspaper"></i></span>
            <div class="info-box-content">
              <!-- Fila para el título y botón de colapsar -->
              <div class="d-flex justify-content-between align-items-center">
                <span class="info-box-number">Novedades</span>
                <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseNovedades" aria-expanded="false" aria-controls="collapseNovedades">
                  <i class="fas fa-plus"></i>
                </button>
              </div>
              <!-- Sección colapsable para los botones -->
              <div id="collapseNovedades" class="collapse">
                <div class="mt-2">
                  <?php if (Auth::hasPermission('novedades', 'vistaCrearNovedades')): ?>
                    <a href="?r=crear_novedad" class="btn btn-block btn-info btn-sm text-white">Crear Novedad</a>
                  <?php endif; ?>
                  <?php if (Auth::hasPermission('novedades', 'vistaListadoNovedades')): ?>
                    <a href="?r=listado_novedades" class="btn btn-block btn-info btn-sm text-white">Mostrar Todas</a>
                  <?php endif; ?>
                  <?php if (Auth::hasPermission('novedades', 'vistaListadoEntradaSalida')): ?>
                    <a href="?r=reporte_entradas_salidas" class="btn btn-block btn-info btn-sm text-white">Mostrar Entrada/Salida</a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
      <!-- ./novedades -->

      <!-- Objetivos -->
      <?php if (
        Auth::hasPermission('objetivos', 'vistaCreaObjetivo')
        || Auth::hasPermission('objetivos', 'vistaListadoObjetivos')
        || Auth::hasPermission('objetivos', 'vistaListadoObjetivosInactivos')
        || Auth::hasPermission('objetivos', 'vistaEditarObjetivo')
      ): ?>
        <div class="col-lg-3 col-md-6 col-sm-12">
          <div class="info-box shadow">
            <span class="info-box-icon bg-info"><i class="fas fa-map-marker-alt"></i></span>
            <div class="info-box-content">
              <!-- Fila para el título y botón de colapsar -->
              <div class="d-flex justify-content-between align-items-center">
                <span class="info-box-number">Objetivos</span>
                <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseObjetivos" aria-expanded="false" aria-controls="collapseObjetivos">
                  <i class="fas fa-plus"></i>
                </button>
              </div>
              <!-- Sección colapsable para los botones -->
              <div id="collapseObjetivos" class="collapse">
                <div class="mt-2">
                  <?php if (Auth::hasPermission('objetivos', 'vistaCreaObjetivo')): ?>
                    <a href="?r=crear_objetivo" class="btn btn-block btn-info btn-sm text-white">Crear Objetivo</a>
                  <?php endif; ?>
                  <?php if (Auth::hasPermission('objetivos', 'vistaListadoObjetivos')): ?>
                    <a href="?r=listado_objetivos" class="btn btn-block btn-info btn-sm text-white">Mostrar Activos</a>
                  <?php endif; ?>
                  <?php if (Auth::hasPermission('objetivos', 'vistaListadoObjetivosInactivos')): ?>
                    <a href="?r=listado_objetivos_inactivos" class="btn btn-block btn-info btn-sm text-white">Mostrar Inactivos</a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
      <!-- ./objetivos -->

      <!-- Usuarios -->
      <?php if (
        Auth::hasPermission('usuarios', 'vistaCrearUsuario')
        || Auth::hasPermission('usuarios', 'vistaListadoUsuarios')
        || Auth::hasPermission('usuarios', 'vistaListadoUsuariosInactivos')
      ): ?>
        <div class="col-lg-3 col-md-6 col-sm-12">
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
      <!-- ./usuarios -->

      <!-- Mensajería -->
      <?php if (
        Auth::hasPermission('mensajes', 'bandejaEntrada')
        || Auth::hasPermission('mensajes', 'crtNuevoMensaje')
      ): ?>
        <div class="col-lg-3 col-md-6 col-sm-12">
          <div class="info-box shadow">
            <span class="info-box-icon bg-info"><i class="far fa-envelope"></i></span>
            <div class="info-box-content">
              <!-- Fila para el título y botón de colapsar -->
              <div class="d-flex justify-content-between align-items-center">
                <span class="info-box-number">Mensajería</span>
                <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseMensajeria" aria-expanded="false" aria-controls="collapseMensajeria">
                  <i class="fas fa-plus"></i>
                </button>
              </div>
              <!-- Sección colapsable para los botones -->
              <div id="collapseMensajeria" class="collapse">
                <div class="mt-2">
                  <?php if (Auth::hasPermission('mensajes', 'bandejaEntrada')): ?>
                    <a href="?r=bandeja-entrada" class="btn btn-block btn-info btn-sm text-white">Ver mensajes</a>
                  <?php endif; ?>
                  <?php if (Auth::hasPermission('mensajes', 'crtNuevoMensaje')): ?>
                    <a href="?r=nuevo-mensaje" class="btn btn-block btn-info btn-sm text-white">Enviar mensaje</a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
      <!-- ./mensajeria -->



    </div>
  </div>
</div>