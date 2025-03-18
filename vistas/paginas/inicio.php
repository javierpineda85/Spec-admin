<!-- Default box -->
<div class="card">
  <div class="card-header bg-info">
    <h3 class="card-title">Panel de Control</h3>

  </div>
  <div class="card-body">
    <?php
      if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <p><i class="icon fas fa-check"></i>' . $_SESSION['success_message'] . '</p>
              </div>';
        // Elimina el mensaje después de mostrarlo
        unset($_SESSION['success_message']);
      };
    ?>
    <!-- Small boxes (Stat box) -->
    <div class="row">
      <!-- hombre vivo -->
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
                <button class="btn btn-block btn-danger btn-sm"><a href="tel:911" class="text-white">Llamar 911</a></button>
                <button class="btn btn-block btn-info btn-sm"><a href="?r=reporte" class="text-white">Registrar</a> </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- ./hombre vivo-->

      <!-- directivas -->
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
                <button class="btn btn-block btn-warning btn-sm"><a href="?r=crear_directivas" class="text-dark">Crear</a></button>
                <button class="btn btn-block btn-warning btn-sm"><a href="?r=listado_directivas" class="text-dark">Mostrar todas</a></button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- ./directivas -->

      <!-- rondas -->
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
                <button class="btn btn-block btn-success btn-sm"><a href="?r=crear_rondas" class="text-white">Nueva</a></button>
                <button class="btn btn-block btn-success btn-sm"><a href="?r=listado_rondas" class="text-white">Mostrar todas</a></button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- ./rondas -->

      <!-- cronograma -->
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
                <button class="btn btn-block btn-primary btn-sm"><a href="?r=crear_cronograma" class="text-white">Subir</a></button>
                <button class="btn btn-block btn-primary btn-sm"><a href="?r=listado_cronogramas" class="text-white">Mostrar todos</a></button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- ./cronograma -->

      <!-- Novedades -->
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
                <button class="btn btn-block btn-info btn-sm">Nueva</button>
                <button class="btn btn-block btn-info btn-sm">Mostrar todos</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ./novedades -->

      <!-- Objetivos-->
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
                <button class="btn btn-block btn-info btn-sm"><a href="?r=crear_objetivo" class="text-white">Nuevo</a> </button>
                <button class="btn btn-block btn-info btn-sm"><a href="?r=listado_objetivos" class="text-white">Mostrar todos</a></button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- ./objetivos -->

      <!-- Usuarios -->
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
                <button class="btn btn-block btn-info btn-sm"><a href="?r=crear-usuario" class="text-white">Nuevo</a> </button>
                <button class="btn btn-block btn-info btn-sm"><a href="?r=listado-usuarios" class="text-white">Mostrar todos</a></button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ./usuarios -->

      <!-- Mensajeria -->
      <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="info-box shadow">
          <span class="info-box-icon bg-info">
            <i class="far fa-envelope"></i>
          </span>
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
                <button class="btn btn-block btn-info btn-sm">Ver mensajes</button>
                <button class="btn btn-block btn-info btn-sm">Enviar mensaje</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- ./mensajeria -->

      <!-- Main content -->
      <section class="content">
        <div class="container-fluid">


        </div><!-- /.container-fluid -->
      </section>
      <!-- /.content -->
    </div>
  </div>