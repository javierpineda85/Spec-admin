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
                <a href="tel:911" class="btn btn-block btn-danger btn-sm text-white">Llamar 911</a>
                <a href="?r=reporte" class=" btn btn-block btn-info btn-smtext-white">Registrar</a>
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
                <a href="?r=crear_directivas" class="btn btn-block btn-warning btn-sm text-dark">Crear</a>
                <a href="?r=listado_directivas" class="btn btn-block btn-warning btn-sm text-dark">Mostrar todas</a>
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
                <a href="?r=crear_rondas" class="btn btn-block btn-success btn-sm text-white">Crear</a>
                <a href="?r=listado_rondas" class="btn btn-block btn-success btn-sm text-white">Mostrar Todas</a>
                <a href="?r=escanear" class="btn btn-block btn-success btn-sm text-white">Escanear QR</a>
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
                <a href="?r=crear_cronograma" class="btn btn-block btn-primary btn-sm text-white">Crear</a>
                <a href="?r=listado_cronogramas" class="btn btn-block btn-primary btn-sm text-white">Por Objetivo</a>
                <a href="?r=listado_porVigilador" class="btn btn-block btn-primary btn-sm text-white">Por Vigilador</a></button>
                <a href="?r=listado_resumen_diario" class="btn btn-block btn-primary btn-sm text-white">Jornadas por Objetivo</a>
                <a href="?r=reporte_porHoras" class="btn btn-block btn-primary btn-sm text-white">Horas por Objetivo</a>
                <a href="?r=reporte_porVigilador" class="btn btn-block btn-primary btn-sm text-white">Horas por Vigilador</a>
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
                <a href="?r=entradas_salidas" class="btn btn-block btn-info btn-sm text-white">Crear Novedad</a>
                <a href="?r=listado_novedades" class="btn btn-block btn-info btn-sm text-white">Mostrar Todas</a>
                <a href="?r=entradas_salidas" class="btn btn-block btn-info btn-sm text-white">Marcar Entrada/Salida</a>
                <a href="?r=reporte_entradas_salidas" class="btn btn-block btn-info btn-sm text-white">Mostrar Entrada/Salida</a>
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
                <a href="?r=crear_objetivo" class="btn btn-block btn-info btn-sm text-white">Crear Objetivo</a> 
                <a href="?r=listado_objetivos" class="btn btn-block btn-info btn-sm text-white">Mostrar Activos</a>
                <a href="?r=listado_objetivos_inactivos" class="btn btn-block btn-info btn-sm text-white">Mostrar Inactivos</a>
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
                <a href="?r=crear-usuario" class="btn btn-block btn-info btn-sm text-white">Crear</a> <
                <a href="?r=listado-usuarios" class="btn btn-block btn-info btn-sm text-white">Mostrar Activos</a>
                <a href="?r=listado-usuarios-inactivos" class="btn btn-block btn-info btn-sm text-white">Mostrar Inactivos</a>
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
                <a href="?r=bandeja-entrada" class="btn btn-block btn-info btn-sm text-white">Ver mensajes</a>
                <a href="?r=nuevo-mensaje" class="btn btn-block btn-info btn-sm text-white">Enviar mensaje</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- ./mensajeria -->


    </div>
  </div>
</div>