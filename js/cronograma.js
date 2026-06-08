// /js/cronograma.js

const Cronograma = (() => {
    // ---------- Config y estado ----------
    // Estas constantes siguen siendo válidas
    const OFF_CODES = new Set(['F', 'E', 'P', 'L', 'S']);
    const LICENCIAS = new Set(['F', 'GP/D', 'GP/N', 'E', 'P', 'L', 'S']);
    //const JORNADA_NORMAL = new Set(['D', 'N', '6H', '7H', '8H', '9H', '9RF', '9HEX', '13H', '14H', 'N15', 'D/LEM', 'D/GU', 'D/AR', 'D/LUJ', 'D/LH', 'D/GC', 'D/MA', 'BE']);
    const TURNOS_JORNADA = ['D', 'N', ...Array.from({ length: 24 }, (_, i) => `${i + 1}H`)];
    const JORNADA_NORMAL = new Set(TURNOS_JORNADA);
    const TURNOS_12_HORAS = new Set(['D', 'N', 'GP/D', 'GP/N']);
    const HOUR_CODE_RE = /^(\d+(?:[.,]\d+)?)H$/;
    const RESUMEN_BASE_CODES = [
        'D', 'N', 'GP/D', 'GP/N',
        ...Array.from({ length: 24 }, (_, i) => `${i + 1}H`)
    ];

    // Estas dos se reemplazan por datos dinámicos
    let SIGLAS = {};       // { 'MIC': 'Microhospital', ... }
    let HORAS_SIGLA = {};  // { 'MIC': 12, 'PAL': 8, ... }
    let OBJETIVO_SIGLA = {};
    let objetivoActual = null;
    let cfg = {};
    let boot = {};
    let estado = {
        puestos: [],
        vigiladores: [],
        referentes: [],
        todosFeriados: [],
        datosPrevios: {},
        horasPorUsuario: {}
    };
    const normalizarCodigo = code => (code || '').toString().toUpperCase().trim();

    function procesarSiglas(data, objetivoVal) {
        objetivoActual = String(objetivoVal || '');
        SIGLAS = {};
        HORAS_SIGLA = {};
        OBJETIVO_SIGLA = {};

        const ordenadas = [...(data || [])].sort((a, b) => {
            const aActual = String(a.objetivo_id ?? '') === objetivoActual ? 0 : 1;
            const bActual = String(b.objetivo_id ?? '') === objetivoActual ? 0 : 1;
            return aActual - bActual;
        });

        ordenadas.forEach(s => {
            const sigla = normalizarCodigo(s.sigla);
            if (!sigla || SIGLAS[sigla] !== undefined) return;
            SIGLAS[sigla] = s.descripcion || sigla;
            HORAS_SIGLA[sigla] = parseFloat(s.horas);
            OBJETIVO_SIGLA[sigla] = s.objetivo_id ?? null;
        });
    }

    function horasCodigo(code) {
        const c = normalizarCodigo(code);
        if (!c || OFF_CODES.has(c)) return 0;
        if (TURNOS_12_HORAS.has(c)) return 12;

        const hMatch = c.match(HOUR_CODE_RE);
        if (hMatch) {
            const hs = parseFloat(hMatch[1].replace(',', '.'));
            return Number.isFinite(hs) ? hs : 0;
        }

        if (
            OBJETIVO_SIGLA[c] !== undefined &&
            OBJETIVO_SIGLA[c] !== null &&
            objetivoActual &&
            String(OBJETIVO_SIGLA[c]) !== objetivoActual
        ) {
            return 0;
        }

        const hs = parseFloat(HORAS_SIGLA[c]);
        return Number.isFinite(hs) ? hs : 0;
    }

    function esCodigoValido(code) {
        const c = normalizarCodigo(code);
        return !c || JORNADA_NORMAL.has(c) || LICENCIAS.has(c) || HOUR_CODE_RE.test(c) || SIGLAS[c] !== undefined;
    }

    async function cargarSiglas(objetivoVal) {
        const url = API_SIGLAS_URL;
        const res = await fetch(url, {
            credentials: 'include'
        });

        const text = await res.text();

        let data = [];
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error("No se pudo parsear JSON en cargarSiglas:", e);
            data = [];
        }

        procesarSiglas(data, objetivoVal);
    }


    // --- Función utilitaria ---
    function uniqueById(arr) {
        const seen = new Set();
        return arr.filter(item => {
            if (seen.has(item.idUsuario)) return false;
            seen.add(item.idUsuario);
            return true;
        });
    }

    // --- Carga inicial de datos ---
    estado = window.CRONOGRAMA_BOOT;

    // Limpiar duplicados apenas cargan
    estado.vigiladores = uniqueById(estado.vigiladores);
    estado.referentes = uniqueById(estado.referentes);
    let feriadosMes = [];
    let rendered = false; // evita doble render

    // ---------- Utilidades ----------
    const qs = (sel, ctx = document) => ctx.querySelector(sel);
    const $all = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

    const toYmd = (y, m, d) => `${y}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    const daysInMonth = (y, m) => new Date(y, m, 0).getDate();

    const flattenData = (prefix, obj) => {
        const out = {};
        Object.keys(obj || {}).forEach(k => {
            const path = `${prefix}[${k}]`;
            if (obj[k] && typeof obj[k] === 'object' && !Array.isArray(obj[k])) {
                Object.assign(out, flattenData(path, obj[k]));
            } else {
                out[path] = obj[k];
            }
        });
        return out;
    };

    const getYearMonth = (mesVal) => {
        if (typeof mesVal !== 'string' || !mesVal.includes('-')) return null;
        const [y, m] = mesVal.split('-').map(Number);
        if (!y || !m) return null;
        return { y, m };
    };

    // ---------- Feriados ----------
    const cargarFeriadosDeMes = (mesVal) => {
        const lista = (estado.todosFeriados || []).filter(f => (f.fecha || '').startsWith(mesVal));
        feriadosMes = lista.map(f => f.fecha);
        return lista; // para UI
    };

    function pintarFeriadosUI(lista) {
        const ul = $('#miLista'); // Objeto jQuery
        const info = $('#infoFeriados'); // También en jQuery

        ul.empty(); // Limpia la lista antes de volver a pintar

        if (lista.length) {
            lista.forEach(f => {
                const fechaFormat = (f.fecha || '').includes('-')
                    ? f.fecha.split('-').reverse().join('/')
                    : f.fecha;

                // Creamos el <li> como objeto jQuery
                const li = $('<li>').text(`${fechaFormat} - ${f.motivo} (${f.tipo_feriado})`);

                // Lo agregamos con jQuery
                ul.append(li);
            });

            info.removeClass('d-none');
        } else {
            info.addClass('d-none');
        }
    }


    // ---------- Render tabla ----------
    const renderCabecera = (y, m) => {
        const dim = daysInMonth(y, m);
        const th = [];
        th.push('<thead><tr>');
        th.push('<th class="rol-sticky sticky-col">Rol</th>');
        th.push('<th class="usuario-sticky sticky-col">Usuario</th>');
        for (let d = 1; d <= dim; d++) {
            const fecha = toYmd(y, m, d);
            const dia = new Date(`${fecha}T00:00:00`).getDay();
            const esFeriado = feriadosMes.includes(fecha);
            const clase = (dia === 0 || dia === 6 || esFeriado) ? 'bg-olive color-palette text-white' : '';
            th.push(`<th class="day-col ${clase}">${d}</th>`);
        }
        // columnas de resumen por empleado
        th.push('<th class="day-col">F</th>');
        th.push('<th class="day-col">GP</th>');
        th.push('</tr></thead>');
        return th.join('');
    };
    const opcionesTurnoHTML = (seleccion) => {
        const jornada = TURNOS_JORNADA;
        const licencias = Array.from(LICENCIAS);      // F, E, P, L, S, GP/D, GP/N

        let html = `<option value=""></option>`;

        // --- Jornada normal ---
        html += `<optgroup label="Jornada normal">`;
        jornada.forEach(c => {
            html += `<option value="${c}" ${seleccion === c ? 'selected' : ''}>${c}</option>`;
        });
        html += `</optgroup>`;

        // --- Siglas dinámicas ---
        html += `<optgroup label="Servicios">`;
        for (const sigla in SIGLAS) {
            if (JORNADA_NORMAL.has(sigla) || LICENCIAS.has(sigla) || HOUR_CODE_RE.test(sigla)) continue;
            html += `<option value="${sigla}" ${seleccion === sigla ? 'selected' : ''}>${sigla}</option>`;
        }
        html += `</optgroup>`;

        // --- Licencias ---
        html += `<optgroup label="Licencias">`;
        licencias.forEach(c => {
            html += `<option value="${c}" ${seleccion === c ? 'selected' : ''}>${c}</option>`;
        });
        html += `</optgroup>`;

        return html;
    };
    const renderFila = (rol, u, dim, y, m, objetivoVal) => {
        const usuarioId = parseInt(u.idUsuario);
        const horasIni = (estado.horasPorUsuario && estado.horasPorUsuario[usuarioId]) ? estado.horasPorUsuario[usuarioId] : 0;
        const claseHoras = (horasIni < 200) ? 'horas-bajo' : (horasIni > 240 ? 'horas-alto' : 'horas-ok');

        let selectUsr = `<select class="form-control select2 select-usuario" data-rol="${rol.toLowerCase()}">`;
        selectUsr += `<option value="">Selecciona</option>`;
        const col = rol.toLowerCase() === 'vigilador' ? estado.vigiladores.filter(v => v.objetivo_id == objetivoVal)
            : estado.referentes.filter(v => v.objetivo_id == objetivoVal);
        col.forEach(v => {
            const sel = (parseInt(v.idUsuario) === usuarioId) ? 'selected' : '';
            selectUsr += `<option value="${v.idUsuario}" ${sel}>${v.apellido}, ${v.nombre}</option>`;
        });
        selectUsr += `</select>`;

        let html = '';
        html += `<tr data-rol="${rol}" data-usuario="${usuarioId}" data-objetivo="${objetivoVal}">`;
        html += `<td class="sticky-col rol-sticky">${rol}</td>`;
        html += `<td class="sticky-col usuario-sticky">
            <div class="d-flex align-items-center">
                ${selectUsr}
                <span class="badge badge-horas ${claseHoras} ml-2" title="${horasIni} hs">${horasIni} hs</span>
            </div>
            <input type="hidden" name="${rol.toLowerCase()}[${usuarioId}][usuario]" value="${usuarioId}">
            </td>`;

        for (let d = 1; d <= dim; d++) {
            const fecha = new Date(`${toYmd(y, m, d)}T00:00:00`).toISOString().slice(0, 10);
            const dia = new Date(`${fecha}T00:00:00`).getDay();
            const esFeriado = feriadosMes.includes(fecha);
            const clase = (dia === 0 || dia === 6 || esFeriado) ? 'bg-olive color-palette text-white' : '';

            const dp = estado.datosPrevios?.[rol.toLowerCase()]?.[usuarioId]?.[d] || '';
            html += `<td class="day-col ${clase}">
                <select name="${rol.toLowerCase()}[${usuarioId}][${d}]" class="form-control no-arrow celda-turno" data-optional="true" data-rol="${rol.toLowerCase()}">
                    ${opcionesTurnoHTML(dp)}
                </select>
            </td>`;
        }
        // columnas resumen por empleado
        html += `
            <td class="day-col resumen-usuario franco-count" data-usuario="${usuarioId}">0</td>
            <td class="day-col resumen-usuario guardia-count" data-usuario="${usuarioId}">0</td>
        </tr>`;
        return html;
    };
    // ===============================
    // FILAS DE TOTALES
    // ===============================
    function renderFilaResumen(label, tipo, dim, extraClass = '') {
        let html = `<tr class="fila-resumen ${extraClass}" data-tipo-resumen="${tipo}" style="display:none">`;
        html += `<td class="sticky-col rol-sticky">${label}</td>`;
        html += `<td class="sticky-col usuario-sticky"></td>`;
        for (let d = 1; d <= dim; d++) {
            html += `<td class="day-col" data-dia="${d}" data-resumen="${tipo}">0</td>`;
        }
        html += `<td class="day-col"></td><td class="day-col"></td>`;
        html += `</tr>`;
        return html;
    }

    function renderFilaTotalHorasDia(dim, extraClass = '') {
        let html = `<tr class="fila-resumen bg-light font-weight-bold ${extraClass}" data-tipo-resumen="total_horas">`;
        html += `<td class="sticky-col rol-sticky">Total horas</td>`;
        html += `<td class="sticky-col usuario-sticky"></td>`;
        for (let d = 1; d <= dim; d++) {
            html += `<td class="day-col" data-dia="${d}" data-resumen="total_horas">0</td>`;
        }
        html += `<td class="day-col"></td><td class="day-col"></td>`;
        html += `</tr>`;
        return html;
    }

    function renderFilaTotalGeneral(dim) {
        let html = '<tr class="bg-info text-white font-weight-bold">';
        html += '<td class="sticky-col rol-sticky">Total</td>';
        html += '<td class="sticky-col usuario-sticky">Horas totales</td>';
        html += `<td colspan="${dim}" class="text-center" id="total-general">0 hs</td>`;
        html += `<td class="day-col"></td><td class="day-col"></td>`;
        html += '</tr>';
        return html;
    }


    const renderTablaOld = async (objetivoVal, mesVal) => {
        const ym = getYearMonth(mesVal);
        if (!ym) return;

        // 1) Cargar siglas dinámicas del objetivo
        const url = API_SIGLAS_URL;

        let siglas = [];
        try {
            const res = await fetch(url, {
                credentials: 'include'
            });

            const text = await res.text();
            siglas = JSON.parse(text);
        } catch (e) {
            console.error('No se pudo parsear JSON en renderTabla:', e);
            siglas = [];
        }


        // Copiar siglas dinámicas al sistema global
        SIGLAS = {};
        HORAS_SIGLA = {};
        siglas.forEach(s => {
            SIGLAS[s.sigla] = s.descripcion;
            HORAS_SIGLA[s.sigla] = parseFloat(s.horas);
        });

        // LIMPIAR códigos viejos de datosPrevios
        if (estado.datosPrevios) {
            for (const rol in estado.datosPrevios) {
                for (const uid in estado.datosPrevios[rol]) {
                    for (const d in estado.datosPrevios[rol][uid]) {
                        const code = estado.datosPrevios[rol][uid][d];
                        // Si NO es jornada normal, NO es licencia y NO está en SIGLAS → borrar
                        if (code && !JORNADA_NORMAL.has(code) && !LICENCIAS.has(code) && !SIGLAS[code]) {
                            estado.datosPrevios[rol][uid][d] = '';
                        }
                    }
                }
            }
        }


        // 2) Si no hay siglas, no rompemos nada
        const siglasMap = {};
        siglas.forEach(s => siglasMap[s.sigla] = s.horas);

        // 3) El resto de tu renderTabla queda igual
        const listaF = cargarFeriadosDeMes(mesVal);
        pintarFeriadosUI(listaF);

        const dim = daysInMonth(ym.y, ym.m);
        const vigObj = estado.vigiladores.filter(u => u.objetivo_id == objetivoVal);
        const refObj = estado.referentes.filter(u => u.objetivo_id == objetivoVal);

        const partes = [];
        partes.push('<div class="table-responsive"><table class="table table-sm table-bordered">');
        partes.push(renderCabecera(ym.y, ym.m));

        partes.push('<tbody>');
        vigObj.forEach(u => partes.push(renderFila('Vigilador', u, dim, ym.y, ym.m, objetivoVal, siglas)));
        refObj.forEach(u => partes.push(renderFila('Referente', u, dim, ym.y, ym.m, objetivoVal, siglas)));
        partes.push('</tbody>');

        // PIE dinámico basado en siglas
        partes.push('<tfoot>');
        RESUMEN_BASE_CODES.forEach(code => partes.push(renderFilaResumen(code, code, dim)));
        siglas.forEach(s => partes.push(renderFilaResumen(s.sigla, s.sigla, dim)));
        partes.push(renderFilaTotalHorasDia(dim));
        partes.push(renderFilaTotalGeneral(dim));
        partes.push('</tfoot>');

        const cont = document.getElementById(cfg.contenedorTablaId);
        cont.innerHTML = partes.join('');

        if (window.jQuery && jQuery().select2) {
            jQuery('.select2').select2({ width: 'resolve' });
        }

        calcularHoras(siglasMap);
        rendered = true;
    };


    // --- Clasificación de códigos según constantes existentes ---
    const renderTabla = async (objetivoVal, mesVal) => {
        const ym = getYearMonth(mesVal);
        if (!ym) return;

        await cargarSiglas(objetivoVal);

        if (estado.datosPrevios) {
            for (const rol in estado.datosPrevios) {
                if (!['vigilador', 'referente'].includes(rol)) continue;
                for (const uid in estado.datosPrevios[rol]) {
                    for (const d in estado.datosPrevios[rol][uid]) {
                        const code = normalizarCodigo(estado.datosPrevios[rol][uid][d]);
                        if (!esCodigoValido(code)) {
                            estado.datosPrevios[rol][uid][d] = '';
                        }
                    }
                }
            }
        }

        const listaF = cargarFeriadosDeMes(mesVal);
        pintarFeriadosUI(listaF);

        const dim = daysInMonth(ym.y, ym.m);
        const vigObj = estado.vigiladores.filter(u => u.objetivo_id == objetivoVal);
        const refObj = estado.referentes.filter(u => u.objetivo_id == objetivoVal);
        const resumenCodes = [
            ...RESUMEN_BASE_CODES,
            ...Object.keys(SIGLAS).filter(code => !RESUMEN_BASE_CODES.includes(code))
        ];

        const partes = [];
        partes.push('<div class="table-responsive"><table class="table table-sm table-bordered">');
        partes.push(renderCabecera(ym.y, ym.m));
        partes.push('<tbody>');
        vigObj.forEach(u => partes.push(renderFila('Vigilador', u, dim, ym.y, ym.m, objetivoVal)));
        refObj.forEach(u => partes.push(renderFila('Referente', u, dim, ym.y, ym.m, objetivoVal)));
        partes.push('</tbody>');
        partes.push('<tfoot>');
        resumenCodes.forEach(code => partes.push(renderFilaResumen(code, code, dim)));
        partes.push(renderFilaTotalHorasDia(dim));
        partes.push(renderFilaTotalGeneral(dim));
        partes.push('</tfoot>');

        const cont = document.getElementById(cfg.contenedorTablaId);
        cont.innerHTML = partes.join('');

        if (window.jQuery && jQuery().select2) {
            jQuery('.select2').select2({ width: 'resolve' });
        }

        calcularHoras();
        rendered = true;
    };

    function tipoDeCodigo(code) {
        code = (code || '').toUpperCase().trim();
        if (jornadaNormalHoras.hasOwnProperty(code)) return 'jornada';
        if (referencias.includes(code)) return 'referencia';
        if (licencias.includes(code)) return 'licencia';
        return 'otro';
    }

    // --- Evento de cambio de turno con validación integrada ---
    $('#tablaCronogramaContainer').on('change', 'select.celda-turno', function () {
        const select = $(this);
        const fila = select.closest('tr');
        const uid = parseInt(fila.attr('data-usuario'));
        const name = select.attr('name');
        const diaMatch = name.match(/\[(\d+)\]$/);
        if (!diaMatch) return;
        const dia = parseInt(diaMatch[1]);
        const nuevoCodigo = select.val();

        if (!nuevoCodigo) {
            calcularHoras();
            return;
        }

        $.getJSON('vistas/paginas/cronogramas/validar-turno-global.php', {
            usuario: uid,
            dia: dia,
            mes: $('#mes').val().split('-')[1],
            anio: $('#mes').val().split('-')[0],
            codigo: nuevoCodigo,
            objetivo_actual: $('#objetivo').val() // 👈 nuevo parámetro
        }, function (resp) {
            if (resp.conflicto) {
                select.val('');
                if (window.Toastify) {
                    Toastify({
                        text: `⚠️ Este vigilador ya tiene asignado "${resp.codigo_existente}" en "${resp.objetivo}" ese día.`,
                        backgroundColor: "#dc3545",
                        duration: 5000
                    }).showToast();
                } else {
                    alert(`Este vigilador ya tiene asignado "${resp.codigo_existente}" en "${resp.objetivo}" ese día.`);
                }
            } else {
                calcularHoras();
            }
        });
    });


    // ---------- Cálculo de horas ----------
    const calcularHoras = () => {
        const totalsUsuario = {};          // uid => horas totales
        const francosUsuario = {};         // uid => cantidad de F
        const guardiasUsuario = {};        // uid => cantidad de GP/D + GP/N

        const contadoresPorDiaPorCodigo = {}; // dia => { code => count }
        const totalsDiaHoras = {};             // dia => horas totales
        let totalGeneral = 0;

        // inicializar estructuras por día
        const dim = $all(`#${cfg.contenedorTablaId} thead th.day-col`).length - 2; // restamos F/GP
        for (let d = 1; d <= dim; d++) {
            contadoresPorDiaPorCodigo[d] = {};
            totalsDiaHoras[d] = 0;
        }

        // recorrer filas de usuarios
        $all(`#${cfg.contenedorTablaId} tbody tr[data-usuario]`).forEach(fila => {
            const uid = parseInt(fila.getAttribute('data-usuario'));
            if (!uid) return;

            let horasFila = 0;
            let francos = 0;
            let guardias = 0;

            $all('select.celda-turno', fila).forEach(sel => {
                const code = normalizarCodigo(sel.value);
                const name = sel.getAttribute('name') || '';
                const diaMatch = name.match(/\[(\d+)\]$/);
                if (!diaMatch) return;
                const dia = parseInt(diaMatch[1]);

                if (!contadoresPorDiaPorCodigo[dia]) {
                    contadoresPorDiaPorCodigo[dia] = {};
                }

                // francos
                if (code === 'F') {
                    francos++;
                    return;
                }

                // guardias
                if (code === 'GP/D' || code === 'GP/N') {
                    guardias++;
                }

                // unidades por tipo (D, N, XH)
                if (code) {
                    if (!contadoresPorDiaPorCodigo[dia][code]) {
                        contadoresPorDiaPorCodigo[dia][code] = 0;
                    }
                    contadoresPorDiaPorCodigo[dia][code]++;
                }

                // horas
                const hs = horasCodigo(code);
                horasFila += hs;
                totalsDiaHoras[dia] += hs;
                totalGeneral += hs;
            });

            totalsUsuario[uid] = horasFila;
            francosUsuario[uid] = francos;
            guardiasUsuario[uid] = guardias;
            estado.horasPorUsuario[uid] = horasFila;
        });

        // actualizar resumen por empleado (F / GP)
        $all(`#${cfg.contenedorTablaId} tbody tr[data-usuario]`).forEach(fila => {
            const uid = parseInt(fila.getAttribute('data-usuario'));
            const horas = totalsUsuario[uid] || 0;
            const francos = francosUsuario[uid] || 0;
            const guardias = guardiasUsuario[uid] || 0;

            const tdF = fila.querySelector('.franco-count');
            const tdGP = fila.querySelector('.guardia-count');
            const badge = fila.querySelector('.badge-horas');

            if (tdF) tdF.textContent = francos;
            if (tdGP) tdGP.textContent = guardias;

            if (badge) {
                badge.classList.remove('horas-bajo', 'horas-ok', 'horas-alto');
                badge.textContent = `${horas} hs`;
                if (horas < 200) badge.classList.add('horas-bajo');
                else if (horas > 240) badge.classList.add('horas-alto');
                else badge.classList.add('horas-ok');
                badge.title = `${horas} hs`;
            }
        });

        // actualizar pie
        const tfoot = document.querySelector(`#${cfg.contenedorTablaId} tfoot`);
        if (!tfoot) return;

        $all('tr[data-tipo-resumen]', tfoot).forEach(fila => {
            const tipo = fila.getAttribute('data-tipo-resumen');
            if (!tipo || tipo === 'total_horas') return;
            let total = 0;

            for (let d = 1; d <= dim; d++) {
                const count = contadoresPorDiaPorCodigo[d][tipo] || 0;
                total += count;

                const celda = fila.querySelector(`td[data-dia="${d}"][data-resumen="${tipo}"]`);
                if (celda) celda.textContent = count;
            }

            fila.style.display = total > 0 ? '' : 'none';
        });

        // total horas por día
        for (let d = 1; d <= dim; d++) {
            const celda = tfoot.querySelector(`td[data-dia="${d}"][data-resumen="total_horas"]`);
            if (celda) celda.textContent = totalsDiaHoras[d] || 0;
        }

        // total general
        const totalGeneralCell = tfoot.querySelector('#total-general');
        if (totalGeneralCell) {
            totalGeneralCell.textContent = `${totalGeneral} hs`;
        }
    };

    // ---------- Validaciones ----------
    // Regla: un vigilador no puede tener en el mismo día más de 1 código de Jornada Normal o Licencia en filas distintas.
    // Sí puede coexistir cualquier cantidad de códigos de Referencias.
    const validarAsignacion = (uid, diaIndex, nuevoCodigo, filaActual) => {
        const code = (nuevoCodigo || '').toUpperCase().trim();
        const esRefer = (code === '') || (SIGLAS[code] !== undefined);
        if (esRefer) return { ok: true };

        // buscar otros selects del mismo usuario/día
        let conflicto = null;
        $all(`#${cfg.contenedorTablaId} tbody tr[data-usuario="${uid}"]`).forEach(tr => {
            if (tr === filaActual) return;
            const sel = tr.querySelector(`select[name^="vigilador[${uid}][${diaIndex}]"], select[name^="referente[${uid}][${diaIndex}]"]`);
            if (!sel) return;
            const val = (sel.value || '').toUpperCase().trim();
            if (JORNADA_NORMAL.has(val) || LICENCIAS.has(val)) {
                conflicto = val;
            }
        });

        if (conflicto) {
            return { ok: false, reason: `Ya tiene asignado ${conflicto} ese día. Solo se permiten "Referencias" en paralelo.` };
        }
        return { ok: true };
    };

    // ---------- Eventos ----------
    const bindEventos = () => {
        // Cargar tabla
        const btnCargar = document.getElementById(cfg.btnCargarId);
        if (btnCargar) {
            btnCargar.addEventListener('click', (e) => {
                e.preventDefault();
                // limpia datosPrevios para evitar arrastre
                estado.datosPrevios = {};
                const objetivo = document.getElementById(cfg.objetivoId).value;
                const mesVal = document.getElementById(cfg.mesId).value;
                if (!objetivo || !mesVal) return alert('Selecciona objetivo y mes.');

                // Señal explícita al backend para precargar o no
                // Sugerencia: controla en el backend con un checkbox "Precargar del mes anterior"
                const form = document.getElementById(cfg.formId);
                if (!form.querySelector('input[name="cargar"]')) {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'cargar';
                    hidden.value = '1';
                    form.appendChild(hidden);
                }
                form.submit();
            });
        }

        // Cambio de objetivo: recargar con cargar=1, y NO precargar por defecto
        const selObj = document.getElementById(cfg.objetivoId);
        if (selObj) {
            selObj.addEventListener('change', () => {
                const form = document.getElementById(cfg.formId);
                const h = document.createElement('input');
                h.type = 'hidden';
                h.name = 'cargar';
                h.value = '1';
                form.appendChild(h);
                form.submit();
            });
        }

        // Vaciar
        const btnVaciar = document.getElementById(cfg.btnVaciarId);
        if (btnVaciar) {
            btnVaciar.addEventListener('click', () => {
                document.getElementById(cfg.contenedorTablaId).innerHTML = '';
                const selObj = document.getElementById(cfg.objetivoId);
                if (selObj) selObj.selectedIndex = 0;
                const mes = document.getElementById(cfg.mesId);
                if (mes) {
                    const now = new Date();
                    mes.value = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
                }
                const info = document.getElementById(cfg.feriadosInfoId);
                const ul = document.getElementById(cfg.feriadosListaId);
                if (info) info.classList.add('d-none');
                if (ul) ul.innerHTML = '';
            });
        }

        // Cambio en celdas: validar y recalcular
        document.getElementById(cfg.contenedorTablaId).addEventListener('change', (ev) => {
            const sel = ev.target;
            if (!sel.classList.contains('celda-turno')) return;
            // detectar uid y día
            const fila = sel.closest('tr[data-usuario]');
            const uid = parseInt(fila?.getAttribute('data-usuario') || 0);
            if (!uid) return;

            // día se obtiene del name="vigilador[uid][d]" o "referente[uid][d]"
            const name = sel.getAttribute('name') || '';
            const match = name.match(/\[\d +\]\[(\d +) \]$ /);
            const diaIndex = match ? parseInt(match[1]) : null;
            if (!diaIndex) return;

            // Validación de coexistencia
            const v = validarAsignacion(uid, diaIndex, sel.value, fila);
            if (!v.ok) {
                // revertir selección
                sel.value = '';
                // feedback UX
                if (window.Toastify) {
                    Toastify({ text: v.reason, backgroundColor: "#dc3545" }).showToast();
                } else {
                    alert(v.reason);
                }
            }
            calcularHoras();
        });

        // Cambio de usuario en fila: renombrar names y recalcular (limpia duplicados de hidden)
        document.getElementById(cfg.contenedorTablaId).addEventListener('change', (ev) => {
            const sel = ev.target;
            if (!sel.classList.contains('select-usuario')) return;
            const fila = sel.closest('tr');
            const rol = (fila.getAttribute('data-rol') || '').toLowerCase();
            const oldId = parseInt(fila.getAttribute('data-usuario'));
            const newId = parseInt(sel.value || 0);
            if (!newId || newId === oldId) return;

            // renombrar selects de días
            $all(`select[name^="${rol}[${oldId}]"]`, fila).forEach(s => {
                s.name = s.name.replace(`${rol}[${oldId}]`, `${rol}[${newId}]`);
            });

            // limpiar hiddens previos de ese rol-oldId en la fila
            $all(`input[type="hidden"][name="${rol}[${oldId}][usuario]"]`, fila).forEach(h => h.remove());

            // crear/actualizar hidden para newId
            let hidden = fila.querySelector(`input[type="hidden"][name="${rol}[${newId}][usuario]"]`);
            if (!hidden) {
                hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = `${rol}[${newId}][usuario]`;
                hidden.value = newId;
                fila.appendChild(hidden);
            } else {
                hidden.value = newId;
            }

            fila.setAttribute('data-usuario', newId);
            calcularHoras();
        });
    };

    // ---------- Carga inicial ----------
    const init = async (config, bootData) => {
        cfg = config || {};
        boot = bootData || {};
        estado = {
            puestos: boot.puestos || [],
            vigiladores: boot.vigiladores || [],
            referentes: boot.referentes || [],
            todosFeriados: boot.todosFeriados || [],
            datosPrevios: boot.datosPrevios || {},
            horasPorUsuario: boot.horasPorUsuario || {}
        };

        // Evitar doble render: solo render si hay datosPrevios completos (objetivo + mes)
        const objetivo = estado.datosPrevios?.objetivo || document.getElementById(cfg.objetivoId)?.value || '';
        const mesVal = estado.datosPrevios?.mes || document.getElementById(cfg.mesId)?.value || '';

        if (objetivo && mesVal) {
            await renderTabla(objetivo, mesVal);
        }

        bindEventos();
    };
    // API pública
    return { init, renderTabla };
})();

window.Cronograma = Cronograma;
