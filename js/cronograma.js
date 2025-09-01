// /js/cronograma.js
const Cronograma = (() => {
    // ---------- Config y estado ----------
    const HOURS_BY_CODE = {
        D: 12, N: 12, N15: 15, '6H': 6, '7H': 7, '8H': 8, '9H': 9, '9RF': 9, '9HEX': 9, '13H': 13, '14H': 14,
        'D/LEM': 12, 'D/GU': 12, 'D/AR': 12, 'D/LUJ': 12, 'D/LH': 12, 'D/GC': 12, 'D/MA': 12, BE: 12,
        'GP/D': 0, 'GP/N': 0
    };
    const OFF_CODES = new Set(['F', 'E', 'P', 'L', 'S']);
    const JORNADA_NORMAL = new Set(['D', 'N', '6H', '7H', '8H', '9H', '9RF', '9HEX', '13H', '14H', 'N15', 'D/LEM', 'D/GU', 'D/AR', 'D/LUJ', 'D/LH', 'D/GC', 'D/MA', 'BE']);
    const REFERENCIAS = new Set(['SALA', 'MIC', 'F/JUS', 'NOTT', 'GUE', 'PER', 'PAL', 'BOS', 'OFI']);
    const LICENCIAS = new Set(['F', 'GP/D', 'GP/N', 'E', 'P', 'L', 'S']); // ojo: GP/D, GP/N se tratan como licencia para coexistencia

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
        th.push('</tr></thead>');
        return th.join('');
    };

    const opcionesTurnoHTML = (seleccion) => {
        const jornada = Array.from(JORNADA_NORMAL);
        const referencias = Array.from(REFERENCIAS);
        const licencias = Array.from(LICENCIAS);

        let html = `<option value=""></option>`;
        html += `<optgroup label="Jornada normal">`;
        jornada.forEach(c => html += `<option value="${c}" ${seleccion === c ? 'selected' : ''}>${c}</option>`);
        html += `</optgroup>`;
        html += `<optgroup label="Referencias">`;
        referencias.forEach(c => html += `<option value="${c}" ${seleccion === c ? 'selected' : ''}>${c}</option>`);
        html += `</optgroup>`;
        html += `<optgroup label="Licencias">`;
        licencias.forEach(c => html += `<option value="${c}" ${seleccion === c ? 'selected' : ''}>${c}</option>`);
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
        html += `</tr>`;
        return html;
    };

    const renderTabla = (objetivoVal, mesVal) => {
        const ym = getYearMonth(mesVal);
        if (!ym) return;

        // feriados UI
        const listaF = cargarFeriadosDeMes(mesVal);
        pintarFeriadosUI(listaF);

        const dim = daysInMonth(ym.y, ym.m);
        const vigObj = estado.vigiladores.filter(u => u.objetivo_id == objetivoVal);
        const refObj = estado.referentes.filter(u => u.objetivo_id == objetivoVal);

        const partes = [];
        partes.push('<div class="table-responsive"><table class="table table-sm table-bordered">');
        partes.push(renderCabecera(ym.y, ym.m));
        partes.push('<tbody>');
        vigObj.forEach(u => partes.push(renderFila('Vigilador', u, dim, ym.y, ym.m, objetivoVal)));
        refObj.forEach(u => partes.push(renderFila('Referente', u, dim, ym.y, ym.m, objetivoVal)));
        partes.push('</tbody></table></div>');

        const cont = document.getElementById(cfg.contenedorTablaId);
        cont.innerHTML = partes.join('');

        // select2 opcional
        if (window.jQuery && jQuery().select2) {
            jQuery('.select2').select2({ width: 'resolve' });
        }


        // cálculo inicial
        calcularHoras();
        rendered = true;
    };
    // --- Clasificación de códigos según constantes existentes ---
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
        const totals = {}; // uid => horas
        $all(`#${cfg.contenedorTablaId} tbody tr[data-usuario]`).forEach(fila => {
            const uid = parseInt(fila.getAttribute('data-usuario'));
            if (!uid) return;
            let horasFila = 0;
            $all('select.celda-turno', fila).forEach(sel => {
                const code = (sel.value || '').toUpperCase().trim();
                if (OFF_CODES.has(code)) return;
                horasFila += (HOURS_BY_CODE[code] || 0);
            });
            totals[uid] = (totals[uid] || 0) + horasFila;
        });

        // actualizar badges y estado
        $all(`#${cfg.contenedorTablaId} tbody tr[data-usuario]`).forEach(fila => {
            const uid = parseInt(fila.getAttribute('data-usuario'));
            const horas = totals[uid] || 0;
            const badge = fila.querySelector('.badge-horas');
            badge.classList.remove('horas-bajo', 'horas-ok', 'horas-alto');
            badge.textContent = `${horas} hs`;
            if (horas < 200) badge.classList.add('horas-bajo');
            else if (horas > 240) badge.classList.add('horas-alto');
            else badge.classList.add('horas-ok');
            badge.title = `${horas} hs`;
            estado.horasPorUsuario[uid] = horas;
        });
    };

    // ---------- Validaciones ----------
    // Regla: un vigilador no puede tener en el mismo día más de 1 código de Jornada Normal o Licencia en filas distintas.
    // Sí puede coexistir cualquier cantidad de códigos de Referencias.
    const validarAsignacion = (uid, diaIndex, nuevoCodigo, filaActual) => {
        const code = (nuevoCodigo || '').toUpperCase().trim();
        const esRefer = REFERENCIAS.has(code) || code === '';
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
    const init = (config, bootData) => {
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
            renderTabla(objetivo, mesVal);
        }

        bindEventos();
    };

    // API pública
    return { init, renderTabla };
})();

window.Cronograma = Cronograma;
