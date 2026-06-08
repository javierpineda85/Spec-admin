<?php

$baseScript = basename($_SERVER['SCRIPT_NAME']);

$tieneEntrada = !empty($_SESSION['hVivo_tiene_entrada']);
$yaSalida = !empty($_SESSION['hVivo_ya_salida']);

$objetivoId = intval($_GET['objetivo_id'] ?? ($_SESSION['ultimo_objetivo'] ?? 0));
$usuarioId = intval($_SESSION['idUsuario'] ?? 0);
$configHV = $_SESSION['hVivo_config'] ?? ModeloReporteHombreVivo::mdlObtenerConfiguracion();
$turnoHV = $_SESSION['hVivo_turno'] ?? 'diurno';
?>
<div class="card">
  <div class="card-header bg-info text-white">
    <h3 class="card-title">Reporte Hombre Vivo</h3>
  </div>
  <div class="card-body text-center">
    <?php if (!$tieneEntrada): ?>
      <p class="text-warning">
        Debes <strong>registrar tu entrada</strong> primero para activar el reporte.
      </p>
      <button class="btn btn-primary" disabled>Esperando Entrada</button>

    <?php elseif ($yaSalida): ?>
      <p class="text-success">Has marcado la salida. El reporte finalizo.</p>

    <?php else: ?>
      <p>
        <span id="status-text">Próximo reporte en</span>:
        <span id="timer">30:00</span>
      </p>
      <button id="btnReportar" class="btn btn-success">Reportar Ahora</button>
    <?php endif; ?>
  </div>
</div>

<?php if ($tieneEntrada && !$yaSalida): ?>
  <script>
    (function() {
      const objetivoId = <?= json_encode($_SESSION['ultimo_objetivo'] ?? 0) ?>;
      const usuarioId = <?= json_encode($_SESSION['idUsuario']) ?>;
      const turno = <?= json_encode($turnoHV) ?>;
      const config = <?= json_encode($configHV, JSON_UNESCAPED_UNICODE) ?>;
      const intervaloMinutos = turno === 'nocturno'
        ? Number(config.nocturno || 30)
        : Number(config.diurno || 30);
      const toleranciaMs = 3 * 60 * 1000;

      const key = `hVivo_nextDeadline_${usuarioId}_${objetivoId}_${turno}_${intervaloMinutos}`;
      const btn = document.getElementById('btnReportar');
      const timer = document.getElementById('timer');
      const status = document.getElementById('status-text');

      let next = parseInt(localStorage.getItem(key), 10);
      if (!next || isNaN(next)) {
        next = Date.now() + intervaloMinutos * 60 * 1000;
        localStorage.setItem(key, next);
      }

      let alertaVencimientoEnviada = false;
      let alertaExcesoEnviada = false;
      let alertaIntervalo = null;

      function dispararAlertaSonoraHibrida() {
        let repeticiones = 0;
        const audio = new Audio('public/sonidos/spec_notificacion.mp3');

        const pitidosRapidos = setInterval(() => {
          audio.currentTime = 0;
          audio.play();
          repeticiones++;
          if (repeticiones >= 3) {
            clearInterval(pitidosRapidos);

            if (!alertaIntervalo) {
              alertaIntervalo = setInterval(() => {
                const audioPersistente = new Audio('public/sonidos/spec_notificacion.mp3');
                audioPersistente.play();
              }, 10000);
            }
          }
        }, 1000);
      }

      function detenerAlertaSonora() {
        if (alertaIntervalo) {
          clearInterval(alertaIntervalo);
          alertaIntervalo = null;
        }
      }

      function registrarAlertaHV(fase, tiempoSegundos) {
        return fetch('ajax/registrar_alerta_hombrevivo.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            usuario_id: usuarioId,
            objetivo_id: objetivoId,
            fase,
            tiempo: tiempoSegundos
          })
        });
      }

      function fmt(ms) {
        const s = Math.floor(ms / 1000);
        return String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');
      }

      function tick() {
        const diff = next - Date.now();
        timer.textContent = fmt(Math.abs(diff));

        if (diff >= 0) {
          status.textContent = 'Próximo reporte en';
          status.style.color = '';
          detenerAlertaSonora();
          alertaVencimientoEnviada = false;
          alertaExcesoEnviada = false;
        } else if (diff >= -toleranciaMs) {
          status.textContent = 'Tiempo vencido, dentro de tolerancia';
          status.style.color = 'orange';
          detenerAlertaSonora();
          alertaExcesoEnviada = false;

          if (!alertaVencimientoEnviada) {
            alertaVencimientoEnviada = true;
            const audio = new Audio('public/sonidos/spec_notificacion.mp3');
            audio.play();
            registrarAlertaHV('vencido', Math.max(0, Math.floor(Math.abs(diff) / 1000)));
          }
        } else {
          status.textContent = '¡ALERTA! Tiempo excedido superior a 3 minutos';
          status.style.color = 'red';

          if (!alertaVencimientoEnviada) {
            alertaVencimientoEnviada = true;
            const audio = new Audio('public/sonidos/spec_notificacion.mp3');
            audio.play();
            registrarAlertaHV('vencido', Math.max(0, Math.floor(Math.abs(diff) / 1000)));
          }

          if (!alertaExcesoEnviada) {
            alertaExcesoEnviada = true;
            dispararAlertaSonoraHibrida();
            registrarAlertaHV('excedido', Math.max(0, Math.floor(Math.abs(diff) / 1000)));
          }
        }

        btn.disabled = !(diff <= toleranciaMs);
        if (diff > toleranciaMs) {
          btn.innerText = `Disponible en ${Math.ceil(diff / 60000)} min`;
        } else {
          btn.innerText = 'Reportar Ahora';
        }
      }

      tick();
      let iv = setInterval(tick, 1000);

      btn.addEventListener('click', () => {
        clearInterval(iv);
        btn.disabled = true;

        const demoraMs = Date.now() - next;
        const sign = demoraMs < 0 ? '-' : '';
        const demora = sign + fmt(Math.abs(demoraMs));

        const base = 'index.php';
        const url = `${base}?r=registrar_reporte&objetivo_id=${objetivoId}&id_usuario=${usuarioId}&demora=${encodeURIComponent(demora)}`;

        fetch(url)
          .then(res => res.json())
          .then(json => {
            if (json.success) {
              status.textContent = 'Reporte registrado correctamente.';
              status.style.color = '';
              next = Date.now() + intervaloMinutos * 60 * 1000;
              localStorage.setItem(key, next);
              alertaVencimientoEnviada = false;
              alertaExcesoEnviada = false;
              detenerAlertaSonora();
              tick();
              iv = setInterval(tick, 1000);
            } else {
              status.textContent = 'Error: ' + json.error;
              status.style.color = 'red';
            }
          })
          .catch(() => {
            status.textContent = 'Error en la conexión.';
            status.style.color = 'red';
          });
      });
    })();
  </script>
<?php endif; ?>
