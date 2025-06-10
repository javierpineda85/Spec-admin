<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="index.php" class="brand-link">
    <img src="img/logo2024.png" alt="SPEC Logo" class="brand-image img-circle elevation-3">
    <span class="brand-text font-weight-light">SPEC</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar user -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <img src="<?php echo $_SESSION['imgPerfil']; ?>" class="img-circle elevation-2" alt="imagen del usuario">
      </div>
      <div class="info">
        <a href="#" class="d-block"><?php echo $_SESSION['nombre'] . " " . $_SESSION['apellido']; ?></a>
      </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        
        <!-- Panel de Control (acceso genérico) -->
        <li class="nav-item">
          <a href="index.php" class="nav-link">
            <i class="nav-icon fas fa-tachometer-alt text-lime"></i>
            <p>Panel de Control</p>
          </a>
        </li>

        <!-- Hombre vivo -->
        <?php if (Auth::hasPermission('escaneos','registrar')): ?>
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon far fa-life-ring text-danger"></i>
            <p>
              Hombre Vivo
              <i class="fas fa-angle-left right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if (Auth::hasPermission('hvivo','registrar')): ?>
            <li class="nav-item">
              <a href="?r=reporte_hombre_vivo" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Reportar</p>
              </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('hvivo','vistaListadoReportesHombreVivo')): ?>
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

        <!-- Directivas -->
        <?php if (Auth::hasPermission('directivas','crtCrearDirectiva') || Auth::hasPermission('directivas','crtListarDirectivas')||Auth::hasPermission('directivas','vistaCrearDirectiva')) : ?>
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-list-ul text-warning"></i>
            <p>
              Directivas
              <i class="fas fa-angle-left right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if (Auth::hasPermission('directivas','crtCrearDirectiva')||Auth::hasPermission('directivas','vistaCrearDirectiva')): ?>
            <li class="nav-item">
              <a href="?r=vistaCrearDirectiva" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Crear</p>
              </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('directivas','crtListarDirectivas')): ?>
            <li class="nav-item">
              <a href="?r=listado_directivas" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Mostrar Todas</p>
              </a>
            </li>
            <?php endif; ?>
          </ul>
        </li>
        <?php endif; ?>

        <!-- Rondas -->
        <?php if (Auth::hasPermission('rondas','vistaCrearRondas') || Auth::hasPermission('rondas','vistaListadoRondas') || Auth::hasPermission('rondas','vistaEscanearRondas')): ?>
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-sync-alt text-success"></i>
            <p>
              Rondas
              <i class="fas fa-angle-left right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if (Auth::hasPermission('rondas','vistaCrearRondas')): ?>
            <li class="nav-item">
              <a href="?r=crear_rondas" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Crear</p>
              </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('rondas','vistaListadoRondas')): ?>
            <li class="nav-item">
              <a href="?r=listado_rondas" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Mostrar Todas</p>
              </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('rondas','vistaEscanearRondas')): ?>
            <li class="nav-item">
              <a href="?r=escanear" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Escanear QR</p>
              </a>
            </li>
            <?php endif; ?>
          </ul>
        </li>
        <?php endif; ?>

        <!-- Puestos -->
        <?php if (Auth::hasPermission('puestos','vistaCrearPuestos') || Auth::hasPermission('puestos','vistaListadoPuestos') || Auth::hasPermission('puestos','vistaListadoPuestosDesactivados')): ?>
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-eye text-success"></i>
            <p>
              Puestos
              <i class="fas fa-angle-left right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if (Auth::hasPermission('puestos','vistaCrearPuestos')): ?>
            <li class="nav-item">
              <a href="?r=crear_puesto" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Crear</p>
              </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('puestos','vistaListadoPuestos')): ?>
            <li class="nav-item">
              <a href="?r=listado_puestos" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Mostrar Activos</p>
              </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('puestos','vistaListadoPuestosDesactivados')): ?>
            <li class="nav-item">
              <a href="?r=listado_puestos_inactivos" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Mostrar Inactivos</p>
              </a>
            </li>
            <?php endif; ?>
          </ul>
        </li>
        <?php endif; ?>

        <!-- Cronogramas -->
        <?php if (
          Auth::hasPermission('cronogramas','vistaCrearCronograma') ||
          Auth::hasPermission('cronogramas','vistaListadoCronogramas') ||
          Auth::hasPermission('cronogramas','vistaListadoCronogramaPorVigilador') ||
          Auth::hasPermission('cronogramas','vistaJornadasPorObjetivo') ||
          Auth::hasPermission('cronogramas','crtBuscarResumenHoras')
        ): ?>
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon far fa-calendar-alt text-info"></i>
            <p>
              Cronogramas
              <i class="fas fa-angle-left right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if (Auth::hasPermission('cronogramas','vistaCrearCronograma')): ?>
            <li class="nav-item">
              <a href="?r=crear_cronograma" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Crear</p>
              </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('cronogramas','vistaListadoCronogramas')): ?>
            <li class="nav-item">
              <a href="?r=listado_cronogramas" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Por Objetivo</p>
              </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('cronogramas','vistaListadoCronogramaPorVigilador')): ?>
            <li class="nav-item">
              <a href="?r=listado_porVigilador" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Por Vigilador</p>
              </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('cronogramas','vistaJornadasPorObjetivo')): ?>
            <li class="nav-item">
              <a href="?r=listado_resumen_diario" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Jornadas por Objetivo</p>
              </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('cronogramas','crtBuscarResumenHoras')): ?>
            <li class="nav-item">
              <a href="?r=reporte_porHoras" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Horas por Objetivo</p>
              </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('cronogramas','vistaHorasPorVigilador')): ?>
            <li class="nav-item">
              <a href="?r=reporte_porVigilador" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Horas por Vigilador</p>
              </a>
            </li>
            <?php endif; ?>
          </ul>
        </li>
        <?php endif; ?>

        <!-- Novedades -->
        <?php if (
          Auth::hasPermission('novedades','vistaCrearNovedades') ||
          Auth::hasPermission('novedades','vistaListadoNovedades') ||
          Auth::hasPermission('novedades','vistaListadoEntradaSalida')
        ): ?>
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon far fa-newspaper text-info"></i>
            <p>
              Novedades
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if (Auth::hasPermission('novedades','vistaCrearNovedades')): ?>
            <li class="nav-item">
              <a href="?r=crear_novedad" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Crear Novedad</p>
              </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('novedades','vistaListadoNovedades')): ?>
            <li class="nav-item">
              <a href="?r=listado_novedades" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Mostrar Novedades</p>
              </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('novedades','vistaListadoEntradaSalida')): ?>
            <li class="nav-item">
              <a href="?r=reporte_entradas_salidas" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Mostrar Ingresos/Salidas</p>
              </a>
            </li>
            <?php endif; ?>
          </ul>
        </li>
        <?php endif; ?>

        <!-- Objetivos -->
        <?php if (
          Auth::hasPermission('objetivos','vistaCreaObjetivo') ||
          Auth::hasPermission('objetivos','vistaListadoObjetivos') ||
          Auth::hasPermission('objetivos','vistaListadoObjetivosInactivos') ||
          Auth::hasPermission('objetivos','vistaEditarObjetivo')
        ): ?>
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-map-marker-alt text-info"></i>
            <p>
              Objetivos
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if (Auth::hasPermission('objetivos','vistaCreaObjetivo')): ?>
            <li class="nav-item">
              <a href="?r=crear_objetivo" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Crear</p>
              </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('objetivos','vistaListadoObjetivos')): ?>
            <li class="nav-item">
              <a href="?r=listado_objetivos" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Mostrar Activos</p>
              </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('objetivos','vistaListadoObjetivosInactivos')): ?>
            <li class="nav-item">
              <a href="?r=listado_objetivos_inactivos" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Mostrar Inactivos</p>
              </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('objetivos','vistaEditarObjetivo')): ?>
            <li class="nav-item">
              <a href="?r=editar_objetivo" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Editar</p>
              </a>
            </li>
            <?php endif; ?>
          </ul>
        </li>
        <?php endif; ?>

        <!-- Usuarios -->
        <?php if (
          Auth::hasPermission('usuarios','vistaCrearUsuario') ||
          Auth::hasPermission('usuarios','vistaListadoUsuarios') ||
          Auth::hasPermission('usuarios','vistaListadoUsuariosInactivos') ||
          Auth::hasPermission('usuarios','vistaPerfilUsuario')
        ): ?>
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-users text-info"></i>
            <p>
              Usuarios
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if (Auth::hasPermission('usuarios','vistaCrearUsuario')): ?>
            <li class="nav-item">
              <a href="?r=crear-usuario" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Crear</p>
              </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('usuarios','vistaListadoUsuarios')): ?>
            <li class="nav-item">
              <a href="?r=listado-usuarios" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Mostrar Activos</p>
              </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('usuarios','vistaListadoUsuariosInactivos')): ?>
            <li class="nav-item">
              <a href="?r=listado-usuarios-inactivos" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Mostrar Inactivos</p>
              </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('usuarios','vistaPerfilUsuario')): ?>
            <li class="nav-item">
              <a href="?r=perfil-usuario&id=<?= $_SESSION['idUsuario']; ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Mi Perfil</p>
              </a>
            </li>
            <?php endif; ?>
          </ul>
        </li>
        <?php endif; ?>

        <!-- Mensajes -->
        <?php if (
          Auth::hasPermission('mensajes','bandejaEntrada') ||
          Auth::hasPermission('mensajes','crtNuevoMensaje')
        ): ?>
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon far fa-envelope text-primary"></i>
            <p>
              Mensajes
              <i class="fas fa-angle-left right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <?php if (Auth::hasPermission('mensajes','bandejaEntrada')): ?>
            <li class="nav-item">
              <a href="?r=bandeja-entrada" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Bandeja de entrada</p>
              </a>
            </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('mensajes','crtNuevoMensaje')): ?>
            <li class="nav-item">
              <a href="?r=nuevo-mensaje" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Nuevo mensaje</p>
              </a>
            </li>
            <?php endif; ?>
          </ul>
        </li>
        <?php endif; ?>

        <!-- Gestión de Permisos -->
        <?php if (Auth::hasPermission('permisos','index')): ?>
        <li class="nav-item">
          <a href="?r=permisos/index" class="nav-link">
            <i class="fas fa-user-shield text-danger"></i>
            <p>Gestión de Permisos</p>
          </a>
        </li>
        <?php endif; ?>

        <!-- Cerrar Sesión -->
        <li class="nav-item">
          <a href="index.php?r=cerrar_sesion" class="nav-link">
            <i class="fas fa-sign-out-alt nav-icon text-danger"></i>
            <p>Cerrar Sesión</p>
          </a>
        </li>

      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>
