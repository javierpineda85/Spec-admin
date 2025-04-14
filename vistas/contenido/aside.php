<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="index.php" class="brand-link">
    <img src="img/logo2024.png" alt="SPEC Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
    <span class="brand-text font-weight-light">SPEC</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar user (optional) -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <img src="<?php echo $_SESSION['imgPerfil']; ?>" class="img-circle elevation-2" alt="imagen del usuario">
      </div>
      <div class="info">
        <a href="#" class="d-block"><?php echo $_SESSION['nombre'] . " " . $_SESSION['apellido'] ; ?></a>
      </div>
    </div>


    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
        <li class="nav-item"> <!-- dashboard -->
          <a href="index.php" class="nav-link">
            <i class="nav-icon fas fa-tachometer-alt text-warning"></i>
            <p>
              Panel de Control
            </p>
          </a>

        </li>
        <li class="nav-item"> <!-- Hombre vivo -->
          <a href="" class="nav-link">

            <i class="nav-icon far fa-life-ring text-danger"></i>
            <p>
              hombre vivo<i class="fas fa-angle-left right"></i>
            </p>

          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="?r=reporte" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Reportar</p>

              </a>
            </li>

          </ul>
        </li>

        <li class="nav-item"><!-- Directivas -->
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-list-ul text-warning"></i>
            <p>
              Directivas
              <i class="fas fa-angle-left right"></i>

            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="?r=listado_directivas" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Ver todos</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="?r=crear_directivas" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Crear</p>
              </a>
            </li>

          </ul>
        </li>

        <li class="nav-item"> <!-- rondas -->
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-sync-alt text-success"></i>
            <p>
              Rondas
              <i class="fas fa-angle-left right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="?r=listado_rondas" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Ver todas</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="?r=crear_rondas" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Crear</p>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item"> <!-- cronograma -->
          <a href="" class="nav-link">
            <i class="nav-icon far fa-calendar-alt text-info"></i>
            <p>
              Cronogramas
              <i class="fas fa-angle-left right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="?r=crear_cronograma" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Subir</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="?r=listado_cronogramas" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Ver todos</p>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item"> <!-- Novedades -->
          <a href="#" class="nav-link">
            <i class="nav-icon far fa-newspaper text-info"></i>
            <p>
              Novedades
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Ver todas</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="?r=entradas_salidas" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Entrada - Salida</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Crear</p>
              </a>

          </ul>
        </li>
        <li class="nav-item"> <!-- Objetivos -->
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-map-marker-alt text-info"></i>
            <p>
              Objetivos
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="?r=crear_objetivo" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Crear</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="?r=listado_objetivos" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Mostrar todos</p>
              </a>

          </ul>
        </li>
        <li class="nav-item"> <!-- usuarios -->
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-users text-info"></i>
            <p>
              Usuarios
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="index.php?r=listado-usuarios" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Ver todos</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="index.php?r=crear-usuario" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Crear</p>
              </a>

          </ul>
        </li>
        <li class="nav-item"> <!-- mensajes -->
          <a href="#" class="nav-link">
            <i class="nav-icon far fa-envelope text-primary"></i>
            <p>
              Mensajes
              <i class="fas fa-angle-left right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="index.php?r=bandeja-entrada" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Bandeja de entrada</p>
                <span class="badge badge-info right">6</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="index.php?r=nuevo-mensaje&t=" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Nuevo mensaje</p>
              </a>
            </li>

          </ul>
        </li>
        <li class="nav-item"> <!-- Salir -->
        <a href="index.php?r=cerrar_sesion" class="nav-link">
          <i class="fas fa-sign-out-alt nav-icon text-danger"></i>
            <p> Cerrar Sesión</p>
          </a>

        </li>
      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>