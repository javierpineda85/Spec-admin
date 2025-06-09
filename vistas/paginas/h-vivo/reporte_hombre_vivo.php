<?php

$baseScript = basename($_SERVER['SCRIPT_NAME']);

// asume ronda e id_usuario en sesión o GET.

$rondaId   = intval($_GET['ronda_id']   ?? ($_SESSION['ultima_ronda'] ?? 0));
$usuarioId = intval($_SESSION['idUsuario'] ?? 0);
?>
<div class="card">
    <div class="card-header bg-info text-white">
        <h3 class="card-title">Reporte Hombre Vivo</h3>
    </div>
    <div class="card-body text-center">
        <p>
            <span id="status-text">Próximo reporte en</span>:
            <span id="timer">30:00</span>
        </p>
        <button id="btnReportar" class="btn btn-success">Reportar Ahora</button>
    </div>
</div>

<script>
    (function() {
        const rondaId = <?= $rondaId ?>;
        const usuarioId = <?= $usuarioId ?>;
        const keyDeadline = `hVivo_nextDeadline_${usuarioId}_${rondaId}`;

        // 1️⃣ Inicializa o carga el próximo deadline (ms)
        let nextDeadline = parseInt(localStorage.getItem(keyDeadline), 10);
        if (!nextDeadline || isNaN(nextDeadline)) {
            nextDeadline = Date.now() + 30 * 60 * 1000; // +30 min
            localStorage.setItem(keyDeadline, nextDeadline);
        }

        // Elementos del DOM
        const display = document.getElementById('timer');
        const statusText = document.getElementById('status-text');
        const btn = document.getElementById('btnReportar');

        // Formatea milisegundos a MM:SS
        function fmt(ms) {
            const totalSec = Math.floor(ms / 1000);
            const m = String(Math.floor(totalSec / 60)).padStart(2, '0');
            const s = String(totalSec % 60).padStart(2, '0');
            return m + ':' + s;
        }

        // Actualiza contador y color/estado
        function tick() {
            const now = Date.now();
            const diff = nextDeadline - now; // >0 faltan, 0..-180000 tolerancia, <-180000 alerta

            // Mostrar siempre el tiempo absoluto
            display.textContent = fmt(Math.abs(diff));

            if (diff >= 0) {
                statusText.textContent = 'Próximo reporte en';
                statusText.style.color = '';
            } else if (diff >= -3 * 60 * 1000) {
                statusText.textContent = 'Dentro de tolerancia';
                statusText.style.color = 'orange';
            } else {
                statusText.textContent = '¡ALERTA! Excedido >3m';
                statusText.style.color = 'red';
            }
        }

        // Inicia el interval
        tick();
        const interval = setInterval(tick, 1000);

        // 2️⃣ Al hacer clic en “Reportar Ahora”
        btn.addEventListener('click', () => {
            clearInterval(interval);
            const now = Date.now();
            const demoraMs = now - nextDeadline;
            const sign = demoraMs < 0 ? '-' : '';
            const demora = sign + fmt(Math.abs(demoraMs)); // ej. "-08:00" o "04:00"

            // Construye la URL apuntando siempre a index.php
            const base = '<?= basename($_SERVER["SCRIPT_NAME"]) ?>';
            const url = `${base}?r=registrar_reporte` +
                `&ronda_id=${rondaId}` +
                `&id_usuario=${usuarioId}` +
                `&demora=${demora}`;

            console.log('Fetch a:', url);
            fetch(url)
                .then(res => {
                    console.log('Fetch status:', res.status, res.statusText);
                    return res.json();
                })
                .then(json => {
                    console.log('Respuesta JSON:', json);
                    if (json.success) {
                        statusText.textContent = 'Reporte registrado correctamente.';
                        statusText.style.color = '';
                        btn.disabled = true;
                        // Reinicia el siguiente ciclo
                        const newDeadline = Date.now() + 30 * 60 * 1000;
                        localStorage.setItem(keyDeadline, newDeadline);
                    } else {
                        statusText.textContent = 'Error: ' + json.error;
                        statusText.style.color = 'red';
                    }
                })
                .catch(err => {
                    console.error('Error en fetch:', err);
                    statusText.textContent = 'Error en la conexión.';
                    statusText.style.color = 'red';
                });
        });
    })();
</script>