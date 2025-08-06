<?php
require_once 'modelos/conexion.php';
?>

<section class="content">
  <div class="container-fluid ">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header bg-info text-white">
            <h3 class="card-title">🔔 Alertas Activas</h3>
          </div>
          <div class="card-body">
            <div id="contenedor-alertas" class="row">
              <!-- Aquí se cargan las alertas dinámicamente -->
            </div>
            <!-- Audio -->
            <audio id="sonido-alerta" src="public/sonidos/spec_notificacion.mp3" preload="auto"></audio>

          </div>
        </div>

      </div>
    </div>

  </div>

</section>


<script>
  document.addEventListener('DOMContentLoaded', function() {
    //console.log("✅ Todo el contenido cargado");

    cargarAlertas();
    setInterval(cargarAlertas, 60000); // Cada 60 segundos
  });

  function cargarAlertas() {
    //console.log("🔄 Verificando alertas...");

    fetch('ajax/ver_alertas.php')
      .then(response => response.json())
      .then(alertas => {
        // console.log("📬 Alertas recibidas:", alertas);

        const contenedor = document.getElementById('contenedor-alertas');
        contenedor.innerHTML = '';

        const cantidadPrevias = parseInt(localStorage.getItem('alertas_previas')) || 0;
        const pathname = window.location.pathname + window.location.search;
        const estoyEnAlertasSupervisor = pathname.includes('r=alertas_supervisor');

        // 🔔 Solo sonar si hay más alertas y no estamos en la vista supervisores
        if (alertas.length > cantidadPrevias && !estoyEnAlertasSupervisor) {
          document.getElementById('sonido-alerta-global').play().catch(err => {
            console.warn('🔇 Sonido bloqueado por navegador:', err);
          });
        }

        // Guardamos cantidad actual
        localStorage.setItem('alertas_previas', alertas.length);

        alertas.forEach(a => {
          const fechaOriginal = new Date(a.creada_en);
          const fechaFormateada = fechaOriginal.toLocaleDateString('es-AR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
          });
          
          const tipoClase = a.tipo === 'hombre_vivo' ? 'danger' : 'warning';
          const card = document.createElement('div');
          card.className = 'col-md-3';

          const botonExtra = (a.tipo === 'directiva') ? `
            <a href="index.php?r=listado_directivas" class="btn btn-sm btn-primary ml-2 col-6">Ver Directivas</a>
          ` : '';

          card.innerHTML = `
            <div class="card border-${tipoClase} shadow mb-4">
              <div class="card-header bg-${tipoClase} text-white">
                <strong>${a.tipo.toUpperCase()}</strong> - ${fechaFormateada}
              </div>
              <div class="card-body">
                <p>${a.mensaje}</p>
                <div class="d-flex justify-content-start">
                  <button class="btn btn-sm btn-success col-6" onclick="marcarLeida(${a.idAlerta})">
                    Marcar como leída
                  </button>
                  ${botonExtra}
                </div>
              </div>
            </div>`;
          contenedor.appendChild(card);
        });
      })
      .catch(error => {
        console.error('❌ Error al cargar alertas:', error);
      });
  }

  function marcarLeida(id) {
    fetch('ajax/marcar_alerta_leida.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        id
      })
    }).then(() => cargarAlertas());
  }
</script>
<script src="<?= BASE_URL ?>/js/adminlte.min.js"></script>