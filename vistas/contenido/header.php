<?php
if (!isset($_SESSION)) session_start();

require_once realpath(__DIR__ . '/../../modelos/mensajes.modelo.php');
require_once realpath(__DIR__ . '/../../modelos/conexion.php');

$idUsuario = $_SESSION['idUsuario'] ?? null;
$cantidadNoLeidos = 0;

$mensajes = ModeloMensajes::mdlMostrarMensajes('destinatario_id', $idUsuario);

$mensajesNoLeidos = array_filter($mensajes, fn($m) => $m['leido'] == 0);
$cantidadNoLeidos = count($mensajesNoLeidos);

?>


<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <!-- Left navbar links -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" data-toggle="dropdown" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="index.php" class="nav-link">Inicio</a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="index.php?r=cerrar_sesion" class="nav-link">Cerrar Sesión</a>
    </li>
  </ul>

  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto">

    <!--  Campana de Notificaciones -->
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#">
        <i class="far fa-bell"></i>
        <span id="badge-alertas" class="badge badge-warning navbar-badge" style="display:none;">0</span>
      </a>
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-header">Notificaciones</span>
        <div class="dropdown-divider"></div>


        <!-- Aquí se cargan las alertas -->
        <div id="dropdown-alertas-preview">
          <span class="dropdown-item text-muted">Sin alertas activas</span>
        </div>

        <div class="dropdown-divider"></div>
        <a href="index.php?r=alertas_supervisor" class="dropdown-item dropdown-footer">
          Ver todas las alertas
        </a>
      </div>
    </li>
    <!--Alertas de mensajes-->
    <!-- Icono de mensajes -->
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#" role="button">
        <i class="far fa-envelope"></i>
        <span id="badge-mensajes" class="badge badge-info navbar-badge" style="display:none;">0</span>
      </a>
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-header">Mensajes</span>
        <div class="dropdown-divider"></div>
        <div id="dropdown-mensajes-preview">
          <span class="dropdown-item text-muted">Sin mensajes nuevos</span>
        </div>
        <div class="dropdown-divider"></div>
        <a href="index.php?r=bandeja-entrada" class="dropdown-item dropdown-footer">
          Ver todos los mensajes
        </a>
      </div>
    </li>


    <!-- Botón de pantalla completa -->
    <li class="nav-item">
      <a class="nav-link" data-widget="fullscreen" href="#" role="button">
        <i class="fas fa-expand-arrows-alt"></i>
      </a>
    </li>
  </ul>

  <!-- Audio global para alertas -->
  <audio id="sonido-alerta-global" src="<?= BASE_URL ?>/public/sonidos/spec_notificacion.mp3" preload="auto"></audio>
</nav>

<!-- Script de alertas dinámicas -->
<script>
  let ultimaCantidadAlertas = 0;

  function actualizarContadorAlertas() {
    //console.log("🔄 Verificando alertas en header.php...");

    fetch('ajax/ver_alertas.php')
      .then(res => res.json())
      .then(alertas => {
        //console.log("📬 Alertas recibidas:", alertas);

        const badge = document.getElementById('badge-alertas');
        const contenedor = document.getElementById('dropdown-alertas-preview');

        if (!badge || !contenedor) {
          console.warn('⚠️ No se encontró la campana o el contenedor');
          return;
        }

        if (alertas.length > 0) {
          badge.innerText = alertas.length;
          badge.style.display = 'inline-block';

          // 🔔 Solo suena si la cantidad de alertas aumentó
          const cantidadAnterior = parseInt(localStorage.getItem('alertas_previas')) || 0;
          const pathname = window.location.pathname + window.location.search;
          const estoyEnAlertasSupervisor = pathname.includes('r=alertas_supervisor');

          if (!estoyEnAlertasSupervisor && alertas.length > cantidadAnterior) {
            document.getElementById('sonido-alerta-global').play().catch(err => {
              console.warn('🔇 Sonido bloqueado por navegador:', err);
            });
          }

          localStorage.setItem('alertas_previas', alertas.length);

          contenedor.innerHTML = '';
          alertas.slice(0, 3).forEach(a => {
            const icono = {
              'hombre_vivo': 'fas fa-user-clock',
              'mensaje': 'fas fa-envelope',
              'directiva': 'fas fa-bullhorn'
            } [a.tipo] || 'fas fa-bell';

            const item = document.createElement('a');
            item.href = (a.tipo === 'directiva') ?
              'index.php?r=listado_directivas' :
              'index.php?r=alertas_supervisor';
            item.className = 'dropdown-item';
            item.innerHTML = `
            <i class="${icono} mr-2"></i> ${a.tipo.toUpperCase()}
            <span class="float-right text-muted text-sm">${a.creada_en.slice(11, 16)}</span>
            <div class="text-sm">${a.mensaje}</div>
          `;
            contenedor.appendChild(item);
            contenedor.appendChild(document.createElement('div')).className = 'dropdown-divider';
          });
        } else {
          badge.style.display = 'none';
          contenedor.innerHTML = '<span class="dropdown-item text-muted">Sin alertas activas</span>';
          localStorage.setItem('alertas_previas', 0);
        }
      })
      .catch(e => console.error('❌ Error al obtener alertas:', e));
  }


  actualizarContadorAlertas();
  setInterval(actualizarContadorAlertas, 30000);
</script>