/* Editor mensual: preservar historial, validar al editar y volver a validar al guardar. */
const Cronograma = (() => {
    const normalizar = code => String(code || '').trim().toUpperCase();
    const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const numeros = Array.from({length:24}, (_, i) => `${i + 1}H`);
    const defaults = {
        ausencias: ['F','E','P','L','S','S.','VAC','P/EN','F/INJ','F/JUS'],
        pasivas: ['GP/D','GP/N'],
        referencias: ['SALA','MIC','NOTT','GUE','PER','PERR','PAL','BOS','OFI','BERM','METR','TUP','H/8','C5,6','C/5,6','ETI'],
        especiales: ['9RF','9HEX','N15','BE','D/LEM','D/GU','D/AR','D/LUJ','D/LH','D/GC','D/MA','N/AR']
    };
    let cfg = {}, estado = {}, catalogo = defaults, siglas = [], objetivoActual = '', mesActual = '';
    let validaciones = 0;
    const porId = id => document.getElementById(id);
    const all = (selector, root = document) => Array.from(root.querySelectorAll(selector));
    const miembros = (rol, objetivo) => {
        const seen = new Set();
        return (estado[rol === 'vigilador' ? 'vigiladores' : 'referentes'] || []).filter(u => {
            if (String(u.objetivo_id) !== String(objetivo) || seen.has(String(u.idUsuario))) return false;
            seen.add(String(u.idUsuario));
            return true;
        });
    };
    function avisar(texto) {
        const box = porId('cronogramaEstado');
        if (box) { box.textContent = texto; box.classList.toggle('d-none', !texto); }
    }
    function actualizarGuardar() {
        const button = porId('btnGuardarCronograma');
        if (!button) return;
        button.disabled = !objetivoActual || !mesActual || validaciones > 0
            || all(`#${cfg.contenedorTablaId} select[data-validacion="conflicto"], #${cfg.contenedorTablaId} select[data-validacion="pendiente"], #${cfg.contenedorTablaId} select[data-validacion="error"]`).length > 0;
    }
    function horasCodigo(code) {
        const c = normalizar(code);
        if (!c || catalogo.ausencias.includes(c)) return 0;
        if (['D','N','GP/D','GP/N'].includes(c)) return 12;
        const match = c.match(/^(\d+(?:[.,]\d+)?)H$/);
        if (match) return Number(match[1].replace(',', '.'));
        if (['9RF','9HEX'].includes(c)) return 9;
        if (c === 'N15') return 15;
        const s = siglas.find(s => normalizar(s.sigla) === c && String(s.objetivo_id) === objetivoActual);
        return s ? Number(s.horas) || 0 : 0;
    }
    function esCodigoValido(code) {
        const c = normalizar(code);
        return !c || ['D','N',...numeros,...catalogo.ausencias,...catalogo.pasivas,...catalogo.referencias,...catalogo.especiales].includes(c)
            || /^(\d+(?:[.,]\d+)?)H$/.test(c) || siglas.some(s => normalizar(s.sigla) === c);
    }
    function opcionesTurnoHTML(seleccion) {
        seleccion = normalizar(seleccion);
        const seen = new Set(['']);
        let html = '<option value=""></option>';
        for (const [nombre, codes] of [
            ['Jornada', ['D','N',...numeros,...catalogo.especiales]],
            ['Servicios y referencias', [...siglas.map(s => normalizar(s.sigla)), ...catalogo.referencias]],
            ['Francos, guardias y licencias', [...catalogo.pasivas,...catalogo.ausencias]]
        ]) {
            html += `<optgroup label="${nombre}">`;
            for (const c of codes) {
                if (!c || seen.has(c)) continue;
                seen.add(c);
                html += `<option value="${esc(c)}" ${seleccion === c ? 'selected' : ''}>${esc(c)}</option>`;
            }
            html += '</optgroup>';
        }
        // Nunca convertir silenciosamente una sigla guardada en un vacío.
        if (seleccion && !seen.has(seleccion)) html += `<option value="${esc(seleccion)}" selected>${esc(seleccion)} (histórico)</option>`;
        return html;
    }
    function pintarFeriados(mes) {
        const fechas = (estado.todosFeriados || []).filter(f => f.fecha.startsWith(mes));
        const ul = porId(cfg.feriadosListaId), box = porId(cfg.feriadosInfoId);
        if (ul) ul.innerHTML = fechas.map(f => `<li>${esc(f.fecha)}: ${esc(f.motivo)}</li>`).join('');
        if (box) box.classList.toggle('d-none', fechas.length === 0);
        return new Set(fechas.map(f => f.fecha));
    }
    const renderTabla = async (objetivo, mes) => {
        if (!/^\d{4}-(0[1-9]|1[0-2])$/.test(mes) || !Number(objetivo)) return;
        const response = await fetch(API_SIGLAS_URL, {credentials:'same-origin'});
        if (response.ok === false) throw new Error('No se pudieron consultar las siglas.');
        const data = JSON.parse(await response.text());
        if (!Array.isArray(data)) throw new Error('La respuesta de siglas no es válida.');
        siglas = data;
        objetivoActual = String(objetivo); mesActual = mes;
        const [year, month] = mes.split('-').map(Number);
        const days = new Date(year, month, 0).getDate();
        const feriados = pintarFeriados(mes);
        let html = '<div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr><th class="rol-sticky sticky-col">Rol</th><th class="usuario-sticky sticky-col">Usuario</th>';
        for (let d = 1; d <= days; d++) {
            const date = `${mes}-${String(d).padStart(2,'0')}`;
            const weekday = new Date(year,month - 1,d).getDay();
            html += `<th class="day-col ${feriados.has(date) || [0,6].includes(weekday) ? 'bg-olive text-white' : ''}">${d}</th>`;
        }
        html += '<th class="day-col">F</th><th class="day-col">GP</th></tr></thead><tbody>';
        let rows = 0;
        for (const rol of ['vigilador','referente']) {
            for (const u of miembros(rol,objetivo)) {
                rows++;
                const id = Number(u.idUsuario);
                html += `<tr data-rol="${rol === 'vigilador' ? 'Vigilador' : 'Referente'}" data-usuario="${id}" data-objetivo="${Number(objetivo)}"><td class="rol-sticky sticky-col">${rol === 'vigilador' ? 'Vigilador' : 'Referente'}</td><td class="usuario-sticky sticky-col"><select class="form-control select-usuario" data-anterior="${id}">`;
                for (const option of miembros(rol,objetivo)) html += `<option value="${Number(option.idUsuario)}" ${Number(option.idUsuario) === id ? 'selected' : ''}>${esc(option.apellido)}, ${esc(option.nombre)}</option>`;
                html += `</select><span class="badge badge-horas"></span><input type="hidden" name="${rol}[${id}][usuario]" value="${id}"></td>`;
                for (let d = 1; d <= days; d++) {
                    const code = estado.datosPrevios?.[rol]?.[id]?.[d] || '';
                    html += `<td class="day-col"><select name="${rol}[${id}][${d}]" class="form-control no-arrow celda-turno" data-optional="true">${opcionesTurnoHTML(code)}</select></td>`;
                }
                html += '<td class="franco-count">0</td><td class="guardia-count">0</td></tr>';
            }
        }
        html += '</tbody><tfoot>';
        const resumenCodes = [...new Set(['D','N',...numeros,...catalogo.especiales,...catalogo.ausencias,...catalogo.pasivas,...catalogo.referencias,...siglas.map(s => normalizar(s.sigla))])];
        for (const code of resumenCodes) {
            html += `<tr data-tipo-resumen="${esc(code)}"><td colspan="2">${esc(code)}</td>`;
            for (let d = 1; d <= days; d++) html += `<td data-dia="${d}" data-resumen="${esc(code)}">0</td>`;
            html += '<td colspan="2"></td></tr>';
        }
        html += '<tr><td colspan="2">Total horas</td>';
        for (let d = 1; d <= days; d++) html += `<td data-dia="${d}" data-resumen="total_horas">0</td>`;
        html += `<td colspan="2"></td></tr><tr><td colspan="${days + 4}" id="total-general"></td></tr></tfoot></table></div>`;
        porId(cfg.contenedorTablaId).innerHTML = html;
        for (const [id,value] of [['cronogramaObjetivo',objetivoActual],['cronogramaMes',mesActual],['cronogramaFilas',rows]]) if (porId(id)) porId(id).value = value;
        calcularHoras(); actualizarGuardar();
    };
    function calcularHoras() {
        const byDay = {}, counts = {};
        let total = 0;
        all(`#${cfg.contenedorTablaId} tbody tr[data-usuario]`).forEach(row => {
            let hours = 0, f = 0, gp = 0;
            all('select.celda-turno',row).forEach(select => {
                const day = Number(select.name.match(/\[(\d+)\]$/)?.[1]);
                const code = normalizar(select.value);
                const h = horasCodigo(code);
                hours += h; total += h;
                byDay[day] = (byDay[day] || 0) + h;
                counts[`${day}:${code}`] = (counts[`${day}:${code}`] || 0) + 1;
                f += code === 'F' ? 1 : 0; gp += catalogo.pasivas.includes(code) ? 1 : 0;
            });
            for (const [selector,value] of [['.franco-count',f],['.guardia-count',gp],['.badge-horas',`${hours} hs`]]) {
                const el = row.querySelector(selector); if (el) el.textContent = value;
            }
            const badge = row.querySelector('.badge-horas');
            if (badge) badge.className = `badge badge-horas ${hours < 200 ? 'horas-bajo' : hours > 240 ? 'horas-alto' : 'horas-ok'}`;
        });
        all(`#${cfg.contenedorTablaId} tr[data-tipo-resumen]`).forEach(row => {
            const code = row.dataset.tipoResumen;
            let units = 0;
            all('td[data-dia]',row).forEach(td => { const n = counts[`${td.dataset.dia}:${code}`] || 0; td.textContent = n; units += n; });
            row.hidden = units === 0;
        });
        all(`#${cfg.contenedorTablaId} td[data-resumen="total_horas"]`).forEach(td => { td.textContent = byDay[td.dataset.dia] || 0; });
        const general = porId('total-general'); if (general) general.textContent = `${total} hs`;
    }
    async function validarCelda(select) {
        const token = (Number(select.dataset.peticion) || 0) + 1;
        select.dataset.peticion = token;
        const code = normalizar(select.value);
        select.dataset.validacion = 'libre';
        select.classList.remove('is-invalid');
        if (!code) { actualizarGuardar(); return; }
        const row = select.closest('tr');
        const day = select.name.match(/\[(\d+)\]$/)?.[1];
        const base = typeof API_VALIDAR_URL !== 'undefined' ? API_VALIDAR_URL : 'index.php?r=validar_turno_global';
        const url = new URL(base, window.location.href);
        for (const [k,v] of Object.entries({usuario:row.dataset.usuario,dia:day,mes:mesActual.split('-')[1],anio:mesActual.split('-')[0],codigo:code,objetivo_actual:objetivoActual})) url.searchParams.set(k,v);
        validaciones++; actualizarGuardar();
        try {
            const response = await fetch(url, {credentials:'same-origin'});
            const result = await response.json();
            if (Number(select.dataset.peticion) !== token) return;
            if (!response.ok || !['libre','pendiente','conflicto'].includes(result.estado)) throw new Error(result.mensaje || 'No se pudo verificar el turno.');
            select.dataset.validacion = result.estado;
            select.classList.toggle('is-invalid',result.estado !== 'libre');
            avisar(result.estado === 'libre' ? '' : result.mensaje);
        } catch (error) {
            if (Number(select.dataset.peticion) === token) { select.dataset.validacion = 'error'; select.classList.add('is-invalid'); avisar(error.message); }
        } finally { validaciones--; actualizarGuardar(); }
    }
    function bindEventos() {
        const form = porId(cfg.formId);
        porId(cfg.contenedorTablaId).addEventListener('change',event => {
            const select = event.target;
            if (select.classList.contains('celda-turno')) { calcularHoras(); validarCelda(select); }
            if (select.classList.contains('select-usuario')) {
                const row = select.closest('tr'), oldId = row.dataset.usuario, newId = select.value;
                if (all(`#${cfg.contenedorTablaId} tr[data-usuario]`).some(other => other !== row && other.dataset.usuario === newId)) {
                    select.value = oldId; avisar('Ese usuario ya tiene una fila en este objetivo.'); return;
                }
                all('[name]',row).forEach(el => { el.name = el.name.replace(`[${oldId}]`,`[${newId}]`); if (el.type === 'hidden') el.value = newId; });
                row.dataset.usuario = newId;
                all('select.celda-turno',row).forEach(el => { el.dataset.peticion = Number(el.dataset.peticion || 0) + 1; el.dataset.validacion = 'libre'; el.classList.remove('is-invalid'); });
                calcularHoras(); actualizarGuardar();
            }
        });
        for (const id of [cfg.objetivoId,cfg.mesId]) porId(id).addEventListener('change',() => {
            objetivoActual = ''; mesActual = ''; actualizarGuardar(); avisar('Pulsa Cargar Cronograma para mostrar el objetivo y mes seleccionados.');
        });
        porId(cfg.btnVaciarId)?.addEventListener('click',() => {
            porId(cfg.contenedorTablaId).innerHTML = ''; objetivoActual = ''; mesActual = '';
            actualizarGuardar(); avisar('Tabla vaciada. Los turnos guardados se conservan.');
        });
        form.addEventListener('submit',event => {
            if (event.submitter?.name === 'cargar') return;
            if (!objetivoActual || !mesActual || validaciones > 0 || porId('btnGuardarCronograma')?.disabled) {
                event.preventDefault(); avisar('Carga una tabla y resuelve sus validaciones antes de guardar.');
                return;
            }
            // Una tabla grande supera max_input_vars. Enviar las filas como un único valor.
            const data = {vigilador:{},referente:{}};
            const fields = all('[name]',porId(cfg.contenedorTablaId));
            fields.forEach(el => {
                const m = el.name.match(/^(vigilador|referente)\[(\d+)\]\[(usuario|\d+)\]$/);
                if (m) { data[m[1]][m[2]] ||= {}; data[m[1]][m[2]][m[3]] = el.value; }
            });
            let payload = form.querySelector('input[name="cronograma_json"]');
            if (!payload) { payload = document.createElement('input'); payload.type = 'hidden'; payload.name = 'cronograma_json'; form.appendChild(payload); }
            payload.value = JSON.stringify(data);
            fields.forEach(el => { el.disabled = true; });
        });
    }
    const init = async (config, bootData) => {
        cfg = config; estado = bootData || {}; catalogo = estado.catalogo || defaults;
        try {
            if (estado.datosPrevios?.objetivo && estado.datosPrevios?.mes) await renderTabla(estado.datosPrevios.objetivo, estado.datosPrevios.mes);
        } catch (error) { objetivoActual = ''; mesActual = ''; avisar(error.message); actualizarGuardar(); }
        bindEventos();
    };
    return { init, renderTabla };
})();
window.Cronograma = Cronograma;
