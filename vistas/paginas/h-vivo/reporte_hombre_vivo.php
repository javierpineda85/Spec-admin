<?php

$baseScript = basename($_SERVER['SCRIPT_NAME']);

// asume ronda e id_usuario en sesión o GET.
$tieneEntrada = !empty($_SESSION['hVivo_tiene_entrada']);
$yaSalida     = !empty($_SESSION['hVivo_ya_salida']);

$objetivoId  = intval($_GET['objetivo_id'] ?? ($_SESSION['ultimo_objetivo'] ?? 0));
$usuarioId = intval($_SESSION['idUsuario'] ?? 0);
?>
<div class="card">
  <div class="card-header bg-info text-white">
    <h3 class="card-title">Reporte Hombre Vivo</h3>
  </div>
  <div class="card-body text-center">
    <?php if (! $tieneEntrada): ?>
      <p class="text-warning">
        Debes <strong>registrar tu entrada</strong> primero para activar el reporte.
      </p>
      <button class="btn btn-primary" disabled>Esperando Entrada</button>

    <?php elseif ($yaSalida): ?>
      <p class="text-success">Has marcado la salida. El reporte finalizó.</p>

    <?php else: ?>
      <!-- Aquí tu contador y botón reales -->
      <p>
        <span id="status-text">Próximo reporte en</span>:
        <span id="timer">30:00</span>
      </p>
      <button id="btnReportar" class="btn btn-success">Reportar Ahora</button>
    <?php endif; ?>
  </div>
</div>
<?php if ($tieneEntrada && ! $yaSalida): ?>

  <script>
    (function() {
      //const rondaId = <?= json_encode($rondaId) ?>;
      const objetivoId = <?= json_encode($_SESSION['ultimo_objetivo'] ?? 0) ?>;
      const usuarioId = <?= json_encode($_SESSION['idUsuario']) ?>;

      const key = `hVivo_nextDeadline_${usuarioId}_${objetivoId}`;
      const btn = document.getElementById('btnReportar');
      const timer = document.getElementById('timer');
      const status = document.getElementById('status-text');

      // Si no hay deadline guardado, lo inicializamos
      let next = parseInt(localStorage.getItem(key), 10);
      if (!next || isNaN(next)) {
        next = Date.now() + 30 * 60 * 1000;
        localStorage.setItem(key, next);
      }

      // Deshabilitar al inicio
      btn.disabled = true;

      // Formatea mm:ss
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
        } else if (diff >= -3 * 60 * 1000) {
          status.textContent = 'Dentro de tolerancia';
          status.style.color = 'orange';
        } else {
          status.textContent = '¡ALERTA! Excedido >3m';
          status.style.color = 'red';
        }

        // Habilita solo en últimos 3 minutos
        btn.disabled = !(diff <= 3 * 60 * 1000 && diff >= -3 * 60 * 1000);
        if (diff > 3 * 60 * 1000) {
          btn.innerText = `Disponible en ${Math.ceil(diff / 60000)} min`;
        } else {
          btn.innerText = "Reportar Ahora";
        }
      }

      tick();
      //window.intervaloTick = setInterval(tick, 1000);
      const iv = setInterval(tick, 1000);

      btn.addEventListener('click', () => {
        //clearInterval(window.intervaloTick);
        clearInterval(iv);
        // clearInterval(window.intervaloAlerta);

        btn.disabled = true;
        const demoraMs = Date.now() - next;
        const sign = demoraMs < 0 ? '-' : '';
        const demora = sign + fmt(Math.abs(demoraMs));
        //const base = '<?= basename($_SERVER["SCRIPT_NAME"]) ?>';
        const base = 'index.php';
        const url = `${base}?r=registrar_reporte&objetivo_id=${objetivoId}&id_usuario=${usuarioId}&demora=${encodeURIComponent(demora)}`;

        fetch(url)
          .then(res => res.text())
          /*.then(text => {
            try {
              const json = JSON.parse(text);
              if (json.success) {
                status.textContent = 'Reporte registrado correctamente.';
                status.style.color = '';
                next = Date.now() + 30 * 60 * 1000;
                localStorage.setItem(key, next);
                tick();
              } else {
                status.textContent = 'Error: ' + (json.error || 'Respuesta inválida');
                status.style.color = 'red';
              }
            } catch (e) {
              console.error("❌ Respuesta no JSON:", text);
              status.textContent = '⚠ Error inesperado del servidor.';
              status.style.color = 'red';
            }
          })*/
          .then(json => {
            if (json.success) {
              status.textContent = 'Reporte registrado correctamente.';
              status.style.color = '';
              // Nuevo ciclo
              next = Date.now() + 30 * 60 * 1000;
              localStorage.setItem(key, next);
              tick();
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


<script>
  //Script para emitir alertas
  let segundosTranscurridos = 0;
  const limiteAlerta = 180; // 3 minutos
  const limiteSupervisor = 300; // 5 minutos
  let alertaVigiladorMostrada = false;
  let alertaSupervisorEnviada = false;

  const intervaloAlerta = setInterval(() => {
    // window.intervaloAlerta = setInterval(() => {
    segundosTranscurridos++;

    // Alerta sonora
    if (segundosTranscurridos >= limiteAlerta && !alertaVigiladorMostrada) {
      alertaVigiladorMostrada = true;

      const audio = new Audio('public/sonidos/spec_notificacion.mp3');
      audio.play();

      const alerta = document.createElement('div');
      alerta.className = 'alert alert-warning fixed-top text-center';
      alerta.innerHTML = `
            <strong>⚠ Atención:</strong> Han pasado más de 3 minutos sin registrar el reporte de hombre vivo.
        `;
      document.body.appendChild(alerta);
      setTimeout(() => alerta.remove(), 20000);
    }

    // Alerta para supervisor
    if (segundosTranscurridos >= limiteSupervisor && !alertaSupervisorEnviada) {
      alertaSupervisorEnviada = true;

      fetch('ajax/registrar_alerta_hombrevivo.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          usuario_id: <?= intval($_SESSION['idUsuario']) ?>,
          objetivo_id: <?= intval($objetivoId) ?>,
          tiempo: segundosTranscurridos
        })
      });
    }
  }, 1000);
</script>