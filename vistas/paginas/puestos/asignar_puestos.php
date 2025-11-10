<?php
//Auth::check('puestos', 'gestionarRotaciones');

$objetivo_id = (int)($_GET['objetivo_id'] ?? 0);
$mes = $_GET['mes'] ?? date('Y-m');

$desde = $mes . "-01";
$hasta = date("Y-m-t", strtotime($desde));

// Normalizo arrays para JS
$puestos = $puestos ?? [];
$vigiladores = $vigiladores ?? [];
$turnos = $turnos ?? [];
$rotaciones = $rotaciones ?? [];
?>
<style>
    /* Mantener encabezado visible al hacer scroll vertical */
    #tabla-rotaciones thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f8f9fa;
        /* coincide con .thead-light */
    }

    /* Columna "Puesto" fija (encabezado y cuerpo) */
    #tabla-rotaciones th:first-child,
    #tabla-rotaciones td:first-child {
        position: sticky;
        left: 0;
        z-index: 3;
        /* por encima del resto de celdas */
        background: #fff;
        /* evitar traslucir al scrollear */
        box-shadow: inset -1px 0 0 rgba(0, 0, 0, .08);
        /* línea divisoria */
        white-space: nowrap;
        /* no cortar nombre de puesto */
    }

    /* La celda cabecera top-left necesita mayor z-index */
    #tabla-rotaciones thead th:first-child {
        z-index: 4;
        background: #f8f9fa;
    }

    /* Ancho mínimo para las columnas de días */
    #tabla-rotaciones th:not(:first-child),
    #tabla-rotaciones td:not(:first-child) {
        min-width: 120px;
        /* ajustable a gusto */
        white-space: nowrap;
    }

    /* Asegurar que el select no se achique demasiado */
    #tabla-rotaciones td:not(:first-child) .select-rotacion {
        min-width: 100%;
    }

    /* Ayuda para sticky dentro del contenedor con overflow */
    #tabla-rotaciones {
        border-collapse: separate;
        /* mejora borde con sticky */
        border-spacing: 0;
        /* alineado prolijo */
    }

    /* garantizamos scroll horizontal */
    .table-responsive {
        overflow-x: auto;
    }
</style>

<div class="card">
    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <h3 class="card-title">Rotaciones por Puesto (<?= htmlspecialchars($mes) ?>)</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row ml-1">

            <form method="GET" class="form-inline">
                <input type="hidden" name="r" value="rotaciones_puestos">
                <label class="form-label">Objetivo: </label>
                <select name="objetivo_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                    <option value="0" <?= $objetivo_id === 0 ? 'selected' : '' ?>>Seleccione…</option>
                    <?php foreach ($objetivos as $o): ?>
                        <option value="<?= (int)$o['idObjetivo'] ?>" <?= $objetivo_id === (int)$o['idObjetivo'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($o['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <!-- si ya hay objetivo, preservamos -->
                <?php if ($mes): ?>
                    <input type="hidden" name="mes" value="<?= htmlspecialchars($mes) ?>">
                <?php endif; ?>
            </form>

            <form method="GET" class="form-inline">
                <input type="hidden" name="r" value="rotaciones_puestos">
                <input type="hidden" name="objetivo_id" value="<?= $objetivo_id ?>">
                <input type="month" name="mes" class="form-control form-control-sm mr-2" value="<?= htmlspecialchars($mes) ?>">
                <button class="btn btn-sm btn-info">Cambiar mes</button>
            </form>
        </div>
        <div class="row mt-3">

            <div class="mb-3 col-md-2">
                <label class="form-label">Código turno</label>
                <select id="rr-codigo-turno" class="form-control form-control-sm">
                    <option value="D">Diurno (D)</option>
                    <option value="N">Nocturno (N)</option>
                </select>
            </div>
            <div class="mr-2 col-md-3">
                <label class="form-label">Auto-rotar</label><br>
                <button id="btn-auto-rr" class="btn btn-sm btn-primary">Equitativo</button>
            </div>
            <div class="mr-2 col-md-3">
                <label class="mb-0 form-label">Swap</label><br>
                <button id="btn-swap" class="btn btn-sm btn-warning">Intercambiar</button>
            </div>

        </div>

        <div class="table-responsive">
            <table id="tabla-rotaciones" class="table table-bordered table-sm">
                <thead class="thead-light">
                    <tr>
                        <th style="min-width:180px">Puesto</th>
                        <?php
                        $d1 = new DateTime($desde);
                        $d2 = new DateTime($hasta);
                        for ($d = $d1; $d <= $d2; $d->modify('+1 day')) {
                            echo '<th class="text-center">' . $d->format('d') . '</th>';
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($puestos as $p): ?>
                        <tr data-puesto-id="<?= (int)$p['idPuesto'] ?>" data-puesto-tipo="<?= htmlspecialchars($p['tipo']) ?>">
                            <td>
                                <?= htmlspecialchars($p['puesto']) ?>
                                <?php if ($p['tipo'] === 'Fijo'): ?>
                                    <span class="badge badge-secondary ml-1">Fijo</span>
                                <?php elseif ($p['tipo'] === 'Rotativo'): ?>
                                    <span class="badge badge-info ml-1">Rotativo</span>
                                <?php else: ?>
                                    <span class="badge badge-light ml-1"><?= htmlspecialchars($p['tipo']) ?></span>
                                <?php endif; ?>
                            </td>
                            <?php
                            for ($d = $d1 = new DateTime($desde); $d1 <= $d2; $d1->modify('+1 day')):
                                $fecha = $d1->format('Y-m-d');
                            ?>
                                <td class="p-1 text-center align-middle"
                                    data-fecha="<?= $fecha ?>">
                                    <!-- select de vigiladores habilitados para esa fecha según turno, se llena por JS -->
                                    <select class="form-control form-control-sm select-rotacion"
                                        data-fecha="<?= $fecha ?>"
                                        data-puesto-id="<?= (int)$p['idPuesto'] ?>">
                                        <option value="">—</option>
                                    </select>
                                    <small class="text-muted d-block mt-1 turnos-hint" style="font-size:11px"></small>
                                </td>
                            <?php endfor; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <small class="text-muted">
            Regla: un vigilador no puede estar en dos puestos el mismo día y turno. Solo se listan vigiladores con turno asignado ese día/turno.
        </small>

    </div>
</div>

<script>
    // ===== Datos del backend → JS =====
    const OBJETIVO_ID = <?= (int)$objetivo_id ?>;
    const MES = "<?= htmlspecialchars($mes) ?>";
    const VIGILADORES = <?= json_encode($vigiladores, JSON_UNESCAPED_UNICODE) ?>;
    const TURNOS = <?= json_encode($turnos, JSON_UNESCAPED_UNICODE) ?>;
    const ROTACIONES = <?= json_encode($rotaciones, JSON_UNESCAPED_UNICODE) ?>;

    // ===== Indexaciones =====
    // a) Código real por fecha y usuario (D/N/F/…)
    //    Si tu BD marca Franco con tipo_turno='Licencia', lo tratamos como 'F'
    const codByFechaUsuario = {}; // fecha -> { usuario_id: 'D'|'N'|'F'|'' }
    for (const t of TURNOS) {
        const fecha = t.fecha;
        const uid = parseInt(t.usuario_id);
        const cod = (t.codigo_turno || '').toUpperCase();
        const tipo = (t.tipo_turno || '').toLowerCase();
        const codFinal = (cod === 'F' || tipo === 'licencia') ? 'F' : (cod === 'D' || cod === 'N' ? cod : '');
        if (!codByFechaUsuario[fecha]) codByFechaUsuario[fecha] = {};
        codByFechaUsuario[fecha][uid] = codFinal; // '', 'D', 'N', 'F'
    }

    // b) Ocupados por fecha y código de turno (según rotaciones existentes)
    const ocupados = {}; // key: fecha|codigo_turno => Set(usuario_id)
    for (const r of ROTACIONES) {
        const k = `${r.fecha}|${r.codigo_turno}`;
        if (!ocupados[k]) ocupados[k] = new Set();
        ocupados[k].add(parseInt(r.usuario_id));
    }

    // c) Rotaciones actuales para precarga en cada celda
    const rotByKey = {}; // key: fecha|puesto_id|codigo_turno => usuario_id
    for (const r of ROTACIONES) {
        const k = `${r.fecha}|${r.puesto_id}|${r.codigo_turno}`;
        rotByKey[k] = parseInt(r.usuario_id);
    }

    const selCodigoTurno = document.getElementById('rr-codigo-turno');
    const tabla = document.getElementById('tabla-rotaciones');

    // Helper: etiqueta con el código real del día para ese vigilador
    function etiquetaNombre(v, fecha) {
        const uid = parseInt(v.idUsuario);
        const cod = (codByFechaUsuario[fecha]?.[uid]) || '';
        const suf = cod ? ` [${cod}]` : ' [—]';
        return `${v.nombre}${suf}`;
    }

    // Build de opciones por celda:
    // - Incluye a todos los vigiladores del objetivo que NO estén en F ese día
    // - Deshabilita a los que no coinciden con el turno seleccionado (D/N)
    // - Deshabilita a los que ya están ocupados en ese día/turno (otra celda/puesto)
    function buildOptions(fecha, turnoSel) {
        const lista = [{
            id: '',
            txt: '—',
            disabled: false
        }];
        const dayMap = codByFechaUsuario[fecha] || {};
        const keyOcup = `${fecha}|${turnoSel}`;
        const usados = ocupados[keyOcup] || new Set();

        for (const v of VIGILADORES) {
            const uid = parseInt(v.idUsuario);
            const cod = (dayMap[uid] || ''); // '', 'D', 'N', 'F'
            if (cod === 'F') continue; // excluir Franco del listado

            const noCoincideTurno = (cod && (cod !== turnoSel)); // tiene D/N pero distinta al selector
            const yaUsado = usados.has(uid);

            lista.push({
                id: v.idUsuario,
                txt: etiquetaNombre(v, fecha),
                disabled: noCoincideTurno || yaUsado
            });
        }
        return lista;
    }

    function renderTabla() {
        const turno = selCodigoTurno.value;
        const rows = tabla.querySelectorAll('tbody tr');
        rows.forEach(tr => {
            tr.querySelectorAll('td[data-fecha]').forEach(td => {
                const fecha = td.getAttribute('data-fecha');
                const puestoId = td.getAttribute('data-puesto-id');
                const sel = td.querySelector('select.select-rotacion');
                const hint = td.querySelector('.turnos-hint');

                // options
                sel.innerHTML = '';
                const opts = buildOptions(fecha, turno);
                for (const o of opts) {
                    const op = document.createElement('option');
                    op.value = o.id;
                    op.textContent = o.txt;
                    if (o.disabled) op.disabled = true;
                    sel.appendChild(op);
                }

                // valor actual (si había)
                const k = `${fecha}|${puestoId}|${turno}`;
                const val = rotByKey[k] || '';
                sel.value = val ? String(val) : '';

                // hint: conteo rápido
                const habilCount = opts.filter(x => x.id && !x.disabled).length;
                hint.textContent = habilCount ? `${habilCount} habilitado(s)` : 'sin habilitados';
                sel.disabled = (habilCount === 0);
            });
        });
    }

    // Guardar una celda con validaciones extra de F y coincidir turno
    async function guardarRotacion({
        fecha,
        puesto_id,
        usuario_id,
        codigo_turno
    }) {
        const uid = parseInt(usuario_id);
        const codReal = (codByFechaUsuario[fecha]?.[uid]) || '';

        // Bloquear Franco
        if (codReal === 'F') {
            alert('No se puede asignar: el vigilador está en Franco (F) ese día.');
            return false;
        }
        // Bloquear si no coincide el turno (ej.: tiene N y estás en D)
        if (codReal && codReal !== codigo_turno) {
            alert(`No se puede asignar: el vigilador tiene turno ${codReal} ese día.`);
            return false;
        }

        const fd = new FormData();
        fd.append('objetivo_id', OBJETIVO_ID);
        fd.append('fecha', fecha);
        fd.append('puesto_id', puesto_id);
        fd.append('usuario_id', usuario_id);
        fd.append('codigo_turno', codigo_turno);

        const res = await fetch('?r=guardar_rotacion', {
            method: 'POST',
            body: fd
        });
        const text = await res.text();
        try {
            const json = JSON.parse(text);
            // usar json normalmente
        } catch (e) {
            console.error('Respuesta no válida:', text);
        }

        // Sincronizar estado local
        const k = `${fecha}|${puesto_id}|${codigo_turno}`;
        const prev = rotByKey[k] || null; // usuario previo en esa celda

        // actualizar rotByKey
        if (usuario_id) rotByKey[k] = uid;
        else delete rotByKey[k];

        // actualizar ocupados para evitar duplicados en otras celdas del mismo día/turno
        const keyOcup = `${fecha}|${codigo_turno}`;
        if (!ocupados[keyOcup]) ocupados[keyOcup] = new Set();
        if (prev) ocupados[keyOcup].delete(parseInt(prev));
        if (usuario_id) ocupados[keyOcup].add(uid);

        return true;
    }

    // Eventos
    selCodigoTurno.addEventListener('change', renderTabla);

    tabla.addEventListener('change', async (e) => {
        if (!e.target.classList.contains('select-rotacion')) return;
        const sel = e.target;
        const td = sel.closest('td');
        const fecha = td.getAttribute('data-fecha');
        const puestoId = td.getAttribute('data-puesto-id');
        const usuarioId = sel.value || '';
        const turno = selCodigoTurno.value;

        // Si eligió vacío, interpretamos como “quitar asignación”
        if (!usuarioId) {
            const ok = await guardarRotacion({
                fecha,
                puesto_id: puestoId,
                usuario_id: '',
                codigo_turno: turno
            });
            if (!ok) {
                const k = `${fecha}|${puestoId}|${turno}`;
                sel.value = rotByKey[k] ? String(rotByKey[k]) : '';
            } else {
                renderTabla(); // refresca deshabilitados por ocupados
            }
            return;
        }

        const ok = await guardarRotacion({
            fecha,
            puesto_id: puestoId,
            usuario_id: usuarioId,
            codigo_turno: turno
        });
        if (!ok) {
            // revertir UI
            const k = `${fecha}|${puestoId}|${turno}`;
            sel.value = rotByKey[k] ? String(rotByKey[k]) : '';
        } else {
            renderTabla(); // refresca deshabilitados por ocupados
        }
    });

    // Auto-rotar y Swap (tus handlers, sin cambios)
    document.getElementById('btn-auto-rr').addEventListener('click', async () => {
        const turno = selCodigoTurno.value;
        const fd = new FormData();
        fd.append('objetivo_id', OBJETIVO_ID);
        fd.append('mes', MES);
        fd.append('codigo_turno', turno);
        const res = await fetch('?r=auto_rotar', {
            method: 'POST',
            body: fd
        });
        const json = await res.json();
        if (!json.ok) {
            alert(json.msg || 'No se pudo completar.');
            return;
        }
        location.reload();
    });

    document.getElementById('btn-swap').addEventListener('click', async () => {
        const turno = selCodigoTurno.value;
        const uA = prompt('ID vigilador A:');
        const uB = prompt('ID vigilador B:');
        const desde = prompt('Desde (YYYY-MM-DD):', '<?= $desde ?>');
        const hasta = prompt('Hasta (YYYY-MM-DD):', '<?= $hasta ?>');
        if (!uA || !uB || !desde || !hasta) return;

        const fd = new FormData();
        fd.append('objetivo_id', OBJETIVO_ID);
        fd.append('desde', desde);
        fd.append('hasta', hasta);
        fd.append('usuario_a', uA);
        fd.append('usuario_b', uB);
        fd.append('codigo_turno', turno);

        const res = await fetch('?r=swap_rotacion', {
            method: 'POST',
            body: fd
        });
        const json = await res.json();
        if (!json.ok) {
            alert(json.msg || 'No se pudo completar el swap.');
            return;
        }
        location.reload();
    });

    // Inicial
    renderTabla();
</script>