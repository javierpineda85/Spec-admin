<?php
//Guardar datos en la BD
if (isset($_POST['guardar_cronograma']) && empty($_SESSION['cronograma_post'])) {
  ControladorCronograma::ctrGuardarCronograma();
}

// Primero verificamos si hay datos previos (guardados por el controlador al fallar o precargar)
$datosPrevios = $_SESSION['cronograma_post'] ?? [];
// Siempre limpiamos los datos previos si se está intentando cargar un cronograma nuevo
if (isset($_POST['cargar'])) {
  unset($_SESSION['cronograma_post']);
  $datosPrevios = [];
}

//traemos los datos del mes que hayan cargados
if (isset($_POST['cargar']) && empty($datosPrevios)) {
  $objetivoReq = (int)($_POST['objetivo'] ?? 0);
  $mesReq      = $_POST['mes'] ?? date('Y-m');

  $info = ControladorCronograma::precargarCronogramaSiExiste($objetivoReq, $mesReq);

  if (($info['origen'] ?? null) === 'anterior' && !empty($info['mesAnterior'])) {
    ToastifyController::info("Precargando datos del mes anterior ({$info['mesAnterior']}) con continuidad 4×2");
  }

  // ✅ Usar SIEMPRE el postSimulado armado por el controlador
  $datosPrevios = $info['postSimulado'] ?? [];

  // fallback extremo (no debería ocurrir, pero por las dudas)
  if (empty($datosPrevios)) {
    $datosPrevios = ControladorCronograma::generarSimulacionVacia($objetivoReq, $mesReq);
  }

  $_SESSION['cronograma_post']       = $datosPrevios;
  $_SESSION['cronograma_origen']     = $info['origen'] ?? null;
  $_SESSION['cronograma_mes_anterior'] = $info['mesAnterior'] ?? null;
}


// Al final del archivo PHP (después de usarse en el HTML):
if (empty($datosPrevios)) {
  $datosPrevios = $_SESSION['cronograma_post'] ?? [];
}
//unset($_SESSION['cronograma_post']); // Limpiamos solo después de traer los datos

// ===================== CARGAS INICIALES =====================
$db = new Conexion;
$objetivos  = $db->consultas("SELECT * FROM objetivos ORDER BY nombre");
$puestos    = $db->consultas("SELECT idPuesto, puesto, objetivo_id FROM puestos");
$vigiladores = $db->consultas("SELECT u.idUsuario, u.nombre, u.apellido, ov.objetivo_id 
                               FROM usuarios u 
                               JOIN objetivo_vigiladores ov ON u.idUsuario = ov.vigilador_id 
                               WHERE u.rol = 'Vigilador' AND u.activo = 1");
$referentes = $db->consultas("SELECT u.idUsuario, u.nombre, u.apellido, orf.objetivo_id 
                              FROM usuarios u 
                              JOIN objetivo_referentes orf ON u.idUsuario = orf.referente_id 
                              WHERE u.rol = 'Referente' AND u.activo = 1");

// ===================== FERIADOS DEL MES =====================
//Para la tabla
$anioActual = date('Y');
$feriados = $db->consultas("SELECT fecha, motivo, tipo_feriado 
                            FROM feriados 
                            WHERE YEAR(fecha) = $anioActual");
//Para el div
$mesSeleccionado = $_POST['mes'] ?? date('Y-m');
$feriadosDelMes = array_filter($feriados, function ($f) use ($mesSeleccionado) {
  return strpos($f['fecha'], $mesSeleccionado) === 0;
});
?>
<style>
  /*ESTILOS PARA LA TABLA DE CREAR CRONOGRAMAS*/

  /* === Sticky SOLO para Rol y Usuario === */
  #tablaCronogramaContainer .table-responsive {
    max-width: 100%;
    max-height: 380px;
    overflow: auto;
  }

  #tablaCronogramaContainer table {
    border-collapse: separate;
    /* importante para sticky */
    border-spacing: 0;
    white-space: nowrap;
    table-layout: fixed;
    font-size: .85em;
    background: #fff;
  }

  #tablaCronogramaContainer .sticky-col {
    position: sticky;
    background: #fff;
    z-index: 3;
    box-sizing: border-box;
    border-right: 1px solid #dee2e6;
  }

  #tablaCronogramaContainer thead .sticky-col {
    z-index: 6;
  }

  :root {
    --w-rol: 90px;
    --w-usuario: 240px;
  }

  #tablaCronogramaContainer .rol-sticky {
    left: 0;
    min-width: var(--w-rol);
    width: var(--w-rol);
  }

  #tablaCronogramaContainer .usuario-sticky {
    left: var(--w-rol);
    min-width: var(--w-usuario);
    width: var(--w-usuario);
  }

  /* Para dispositivos móviles, achicamos las columnas fijas */
  @media (max-width: 767px) {
    :root {
      --w-rol: 60px;
      --w-usuario: 200px;
    }
  }

  #tablaCronogramaContainer .day-col {
    min-width: 28px !important;
    max-width: 28px !important;
    width: 28px !important;
    text-align: center;
    padding: 0 2px;
    font-size: .85em;
  }


  .celda-turno {
    font-size: 0.9em !important;
    font-weight: bold !important;
    border: none;
    background: none;
    padding: 0;
    text-align: center;
    /*margin-top: -10px;*/
  }

  .celda-select {
    border: none;
    background: none;
    padding: 0;
    margin-top: 10px !important;
    font-size: 0.9em !important;
  }

  select.no-arrow {
    -moz-appearance: none;
    -webkit-appearance: none;
    appearance: none;
    background-image: none !important;
  }

  .badge-horas {
    margin-left: 6px;
    padding: 3px 6px;
    border-radius: 6px;
    font-size: 0.75rem;
    color: #fff;
    font-weight: bold;
  }

  .horas-bajo {
    background-color: #ffc107;
  }

  .horas-ok {
    background-color: #007bff;
  }

  .horas-alto {
    background-color: #dc3545;
  }
</style>


<div class="card">
  <div class="card-header bg-info text-white">
    <h3 class="card-title">Crear Cronograma Mensual</h3>
  </div>
  <div class="card-body">
    <form id="frmCronograma" method="POST" class="mb-4">
      <div id="feriadosMesInfo" class="alert alert-secondary small d-none">
        <strong>Feriados del mes:</strong>
        <ul id="feriadosMesLista" class="mb-0"></ul>
      </div>

      <div class="form-row">
        <div class="form-group col-md-4">
          <label>Objetivo</label>
          <?php $valueSelected = $_POST['objetivo'] ?? ''; ?>
          <select name="objetivo" id="objetivo" class="form-control select2">
            <?php foreach ($objetivos as $o): ?>
              <option value="<?= $o['idObjetivo'] ?>" <?= ($o['idObjetivo'] == $valueSelected ? 'selected' : '') ?>>
                <?= htmlspecialchars($o['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group col-md-2">
          <label>Mes</label>
          <?php $mes = $_POST['mes'] ?? ($datosPrevios['mes'] ?? date('Y-m')); ?>
          <input type="month" class="form-control" name="mes" id="mes" value="<?= htmlspecialchars($mes) ?>">
        </div>
        <div class="form-group col-md-2 align-self-end">
          <button type="submit" name="cargar" id="btnCargarTabla" class="btn btn-primary">Cargar Cronograma</button>
        </div>
      </div>
      <div class="row">
        <div class="form-group">
          <label>Licencias:</label>
          <label class="btn bg-dark btn-sm disabled"><b>F:</b> Franco</label>
          <label class="btn bg-dark btn-sm disabled"><b>G:</b> Guardia Pasiva</label>
          <label class="btn bg-dark btn-sm disabled"><b>E:</b> Parte de Enfermo</label>
          <label class="btn bg-dark btn-sm disabled"><b>P:</b> Permiso especial</label>
          <label class="btn bg-dark btn-sm disabled"><b>L:</b> Licencia</label>
          <label class="btn bg-dark btn-sm disabled"><b>S:</b> Suspensión</label>
        </div>

      </div>

      <div id="tablaCronogramaContainer" class="mt-4"></div>

      <div class="form-group mt-3">
        <button type="submit" name="guardar_cronograma" class="btn btn-success">Guardar Cronograma</button>
        <?php if (!empty($datosPrevios)): ?>
          <button type="button" id="btnVaciarCronograma" class="btn btn-outline-danger">Vaciar Cronograma</button>

        <?php endif; ?>

      </div>
    </form>
  </div>
</div>

<script>
  // Variables globales que vienen desde PHP y se transforman en JS

  let puestos = <?= json_encode($puestos, JSON_UNESCAPED_UNICODE) ?>;
  let feriados = []; // Este array se completa dinámicamente según el mes elegido
  let datosPrevios = <?= json_encode($datosPrevios, JSON_UNESCAPED_UNICODE) ?>;
  const vigiladores = <?= json_encode($vigiladores, JSON_UNESCAPED_UNICODE) ?>;
  const referentes = <?= json_encode($referentes, JSON_UNESCAPED_UNICODE) ?>;
  const todosFeriados = <?= json_encode($feriados, JSON_UNESCAPED_UNICODE) ?>;
  const horasPorUsuario = <?= json_encode($_SESSION['horas_usuario'] ?? new stdClass(), JSON_UNESCAPED_UNICODE) ?>;


  const HOURS_BY_CODE = {
    'D': 12,
    'N': 12,
    'N15': 15,
    '6H': 6,
    '7H': 7,
    '8H': 8,
    '9H': 9,
    '9RF': 9,
    '9HEX': 9,
    '13H': 13,
    '14H': 14,
    'D/LEM': 12,
    'D/GU': 12,
    'D/AR': 12,
    'D/LUJ': 12,
    'D/LH': 12,
    'D/GC': 12,
    'D/MA': 12,
    'BE': 12,
    'GP/D': 0,
    'GP/N': 0
  };
  const OFF_CODES = new Set(['F', 'E', 'P', 'L', 'S']);


  // Si no hay datos previos pero sí hay valores en los inputs, generamos la tabla igualmente
  // Intentamos extraer mes y objetivo desde datosPrevios si existen
  let objetivoVal = '';
  let mesVal = '';

  if (datosPrevios && typeof datosPrevios === 'object') {
    if (datosPrevios.objetivo) {
      objetivoVal = datosPrevios.objetivo;
    } else {
      objetivoVal = $('#objetivo').val();
    }

    if (datosPrevios.mes) {
      mesVal = datosPrevios.mes;
    } else {
      mesVal = $('#mes').val();
    }
  }

  // Si tenemos ambos valores válidos, generamos la tabla
  if (objetivoVal && mesVal) {
    generarTablaCronograma(objetivoVal, mesVal);
  } else {
    console.warn("❌ No se puede generar la tabla. Objetivo o mes no válidos:", objetivoVal, mesVal);
  }

  // Esta función genera toda la tabla del cronograma (cabecera y cuerpo)
  function generarTablaCronograma(objetivo, mesVal) {
    // Paso 0: Validamos mesVal
    let year = 0,
      month = 0;
    if (typeof mesVal === 'string' && mesVal.includes('-')) {
      [year, month] = mesVal.split('-').map(Number);
    } else {
      console.warn('⚠️ mesVal no tiene formato esperado:', mesVal);
      return;
    }

    const fechaReferencia = new Date(year, month - 1);
    if (isNaN(fechaReferencia)) {
      console.error('❌ Fecha inválida:', year, month);
      return;
    }

    const daysInMonth = new Date(year, month, 0).getDate();

    // Paso 1: Filtrar feriados del mes
    feriados = todosFeriados
      .filter(f => f.fecha.startsWith(mesVal))
      .map(f => f.fecha);

    const feriadosLista = todosFeriados.filter(f => f.fecha.startsWith(mesVal));
    const ul = document.getElementById('feriadosMesLista');
    ul.innerHTML = '';
    if (feriadosLista.length > 0) {
      feriadosLista.forEach(f => {
        const li = document.createElement('li');
        let fechaFormat = '';
        if (f && typeof f.fecha === 'string' && f.fecha.includes('-')) {
          fechaFormat = f.fecha.split('-').reverse().join('/');
        } else {
          console.warn('⚠️ f.fecha inválido:', f?.fecha);
        }
        li.textContent = `${fechaFormat} - ${f.motivo} (${f.tipo_feriado})`;
        ul.appendChild(li);
      });
      document.getElementById('feriadosMesInfo').classList.remove('d-none');
    } else {
      document.getElementById('feriadosMesInfo').classList.add('d-none');
    }

    // Paso 2: Preparar datos
    const puestosFiltrados = puestos.filter(p => p.objetivo_id == objetivo);
    const vigiladoresObjetivo = vigiladores.filter(u => u.objetivo_id == objetivo);
    const referentesObjetivo = referentes.filter(u => u.objetivo_id == objetivo);

    // Paso 3: Comenzar a construir la tabla HTML
    let html = [];
    html.push('<div class="table-responsive"><table class="table table-sm table-bordered">');
    html.push('<thead><tr>' +
      '<th class="rol-sticky sticky-col">Rol</th>' +
      '<th class="usuario-sticky sticky-col">Usuario</th>');
    for (let d = 1; d <= daysInMonth; d++) {
      const fecha = `${year}-${String(month).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
      const esFeriado = feriados.includes(fecha);
      const diaSemana = new Date(`${fecha}T00:00:00`).getDay();
      const claseExtra = (diaSemana === 0 || diaSemana === 6 || esFeriado) ? 'bg-olive color-palette text-white' : '';
      html.push(`<th class="day-col ${claseExtra}">${d}</th>`);
    }
    html.push('</tr></thead><tbody>');


    /*
        for (let d = 1; d <= daysInMonth; d++) {
          const fechaJS = new Date(`${year}-${String(month).padStart(2, '0')}-${String(d).padStart(2, '0')}T00:00:00`);
          const fecha = fechaJS.toISOString().slice(0, 10);
          const diaSemana = fechaJS.getDay();
          const esFeriado = feriados.includes(fecha);
          const claseExtra = (diaSemana === 0 || diaSemana === 6 || esFeriado) ? 'bg-olive color-palette text-white' : '';
          html.push(`<th class="day-col ${claseExtra}">${d}</th>`);
        }

        html.push('</tr></thead><tbody>');
    */
    // Paso 4: Render filas
    renderVigiladores(puestosFiltrados, vigiladoresObjetivo, daysInMonth, year, month, html);
    renderReferentes(referentesObjetivo, daysInMonth, year, month, html);

    html.push('</tbody></table></div>');

    // Paso 5: Mostrar tabla
    $('#tablaCronogramaContainer').empty().html(html.join(''));

    // Restaurar nombres a selects
    document.querySelectorAll('select[data-name]').forEach(select => {
      select.setAttribute('name', select.getAttribute('data-name'));
    });

    // Paso 6: Cargar datos previos si existen
    for (const name in datosPrevios) {
      if (!datosPrevios.hasOwnProperty(name)) continue;
      const value = datosPrevios[name];
      if (typeof value === 'object') {
        const flat = flattenData(name, value);
        for (const inputName in flat) {
          const val = flat[inputName];
          const input = document.querySelector(`[name="${inputName}"]`);
          if (input) {
            input.value = val;
          }
        }
      } else {
        const input = document.querySelector(`[name="${name}"]`);
        if (input) {
          input.value = value;
        }
      }
    }
    if ($('.select2').length) {
      try {
        $('.select2').select2({
          width: 'resolve'
        });
      } catch (e) {}
    }

    // Paso 7: Cálculo en tiempo real
    calcularHorasEnTiempoReal();
  }

  // Convierte un objeto multidimensional en claves planas del tipo a[b][c]
  function flattenData(prefix, obj) {
    const out = {};
    for (const key in obj) {
      if (!obj.hasOwnProperty(key)) continue;
      const path = `${prefix}[${key}]`;
      if (typeof obj[key] === 'object') {
        Object.assign(out, flattenData(path, obj[key]));
      } else {
        out[path] = obj[key];
      }
    }
    return out;
  }

  // Esta función se invoca automáticamente si hay datos previos en la sesión
  if (datosPrevios && Object.keys(datosPrevios).length > 0) {
    const objetivo = datosPrevios.objetivo;
    const mesVal = datosPrevios.mes;
    generarTablaCronograma(objetivo, mesVal);
  }

  $('#btnCargarTabla').on('click', function(e) {
    e.preventDefault();
    const objetivo = $('#objetivo').val();
    const mesVal = $('#mes').val();
    datosPrevios = {};
    if (!objetivo || !mesVal) return alert('Selecciona objetivo y mes.');

    if (!$('input[name="cargar"]').length) {
      $('<input>').attr({
        type: 'hidden',
        name: 'cargar',
        value: '1'
      }).appendTo('#frmCronograma');
    }

    feriados = todosFeriados.filter(f => f.fecha.startsWith(mesVal)).map(f => f.fecha);

    const feriadosLista = todosFeriados.filter(f => f.fecha.startsWith(mesVal));
    const ul = document.getElementById('feriadosMesLista');
    ul.innerHTML = '';
    if (feriadosLista.length > 0) {
      feriadosLista.forEach(f => {
        const li = document.createElement('li');
        //const fechaFormat = f.fecha.split('-').reverse().join('/');
        let fechaFormat = '';
        if (f && typeof f.fecha === 'string' && f.fecha.includes('-')) {
          fechaFormat = f.fecha.split('-').reverse().join('/');
        } else {
          console.warn('⚠️ f.fecha inválido:', f?.fecha);
        }

        li.textContent = `${fechaFormat} - ${f.motivo} (${f.tipo_feriado})`;
        ul.appendChild(li);
      });
      document.getElementById('feriadosMesInfo').classList.remove('d-none');
    } else {
      document.getElementById('feriadosMesInfo').classList.add('d-none');
    }

    const puestosFiltrados = puestos.filter(p => p.objetivo_id == objetivo);
    const vigiladoresObjetivo = vigiladores.filter(u => u.objetivo_id == objetivo);
    const referentesObjetivo = referentes.filter(u => u.objetivo_id == objetivo);
    //const [year, month] = mesVal.split('-').map(Number);
    let year = 0,
      month = 0;
    if (typeof mesVal === 'string' && mesVal.includes('-')) {
      [year, month] = mesVal.split('-').map(Number);
    } else {
      console.warn('⚠️ mesVal no tiene formato esperado:', mesVal);
    }

    const daysInMonth = new Date(year, month, 0).getDate();

    let html = [];
    html.push('<div class="table-responsive"><table class="table table-sm table-bordered">');

    html.push('<thead><tr>' +
      '<th class="rol-sticky sticky-col">Rol</th>' +
      '<th class="usuario-sticky sticky-col">Usuario</th>');
    for (let d = 1; d <= daysInMonth; d++) {
      const fecha = `${year}-${String(month).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
      const esFeriado = feriados.includes(fecha);
      const diaSemana = new Date(`${fecha}T00:00:00`).getDay();
      const claseExtra = (diaSemana === 0 || diaSemana === 6 || esFeriado) ? 'bg-olive color-palette text-white' : '';
      html.push(`<th class="day-col ${claseExtra}">${d}</th>`);
    }
    html.push('</tr></thead><tbody>');
    /*
        for (let d = 1; d <= daysInMonth; d++) {
          const fecha = `${year}-${String(month).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
          const esFeriado = feriados.includes(fecha);
          const diaSemana = new Date(`${fecha}T00:00:00`).getDay();
          const claseExtra = (diaSemana === 0 || diaSemana === 6 || esFeriado) ? 'bg-olive color-palette text-white' : '';
          html.push(`<th class="day-col ${claseExtra}">${d}</th>`);
        }
        html.push('</tr></thead><tbody>');
    */
    renderVigiladores(puestosFiltrados, vigiladoresObjetivo, daysInMonth, year, month, html);
    renderReferentes(referentesObjetivo, daysInMonth, year, month, html);

    html.push('</tbody></table></div>');
    $('#tablaCronogramaContainer').empty().html(html.join(''));

    for (const name in datosPrevios) {
      if (!datosPrevios.hasOwnProperty(name)) continue;
      const value = datosPrevios[name];

      if (typeof value === 'object') {
        const flat = flattenData(name, value);
        for (const inputName in flat) {
          const val = flat[inputName];
          const input = document.querySelector(`[name="${inputName}"]`);
          if (input) input.value = val;
        }
        /*for (const inputName in flat) {
          const val = flat[inputName];
          const input = document.querySelector(`[name="${inputName}"]`);
          if (input) {
            input.value = val;
          } else {
            console.warn("No se encontró el input con name:", inputName);
          }
        }*/

      } else {
        const input = document.querySelector(`[name="${name}"]`);
        if (input) input.value = value;
      }
    }

    $('#frmCronograma').submit(); // Esto solo si estás recargando la página
  });

  // Activar etiquetas de color dinámicas
  $('#tablaCronogramaContainer').on('change', 'select[name^="vigilador"], select[name^="referente"]', function() {
    calcularHorasEnTiempoReal();
    const select = $(this);
    const span = select.closest('td').find('.badge-horas');

    const selectedId = parseInt(select.val());
    const horas = horasPorUsuario[selectedId] || 0;

    // Determinar clase
    let nuevaClase = '';
    if (horas < 200) nuevaClase = 'horas-bajo';
    else if (horas > 240) nuevaClase = 'horas-alto';
    else if (horas >= 200) nuevaClase = 'horas-ok';

    // Limpiar clases previas y asignar nueva
    span.removeClass('horas-bajo horas-ok horas-alto').addClass(nuevaClase);
    span.attr('title', `${horas} hs`);
    span.text(`${horas} hs`);

  });

  $('#btnVaciarCronograma').on('click', function() {
    // Limpiar tabla
    $('#tablaCronogramaContainer').html('');

    // Reiniciar select objetivo
    $('#objetivo').val('').prop('selectedIndex', 0);

    // Reiniciar campo mes al mes actual
    const now = new Date();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const year = now.getFullYear();
    $('#mes').val(`${year}-${month}`);

    // Ocultar feriados si están visibles
    $('#feriadosMesInfo').addClass('d-none');
    $('#feriadosMesLista').empty();
  });

  // Al cambiar el select de usuario, renombramos todos los name="rol[oldId][d]" a "rol[newId][d]"
  $('#tablaCronogramaContainer').on('change', '.select-usuario', function() {
    const select = $(this);
    const fila = select.closest('tr');
    const rol = (fila.attr('data-rol') || '').toLowerCase(); // 'vigilador' | 'referente'
    const oldId = parseInt(fila.attr('data-usuario'));
    const newId = parseInt(select.val() || 0);
    if (!newId || newId === oldId) return;

    // Renombrar selects de días
    fila.find(`select[name^="${rol}[${oldId}]"]`).each(function() {
      const name = $(this).attr('name'); // ej: vigilador[18][5]
      $(this).attr('name', name.replace(`${rol}[${oldId}]`, `${rol}[${newId}]`));
    });

    // Renombrar/actualizar hidden usuario
    const hidden = fila.find(`input[name="${rol}[${oldId}][usuario]"]`);
    if (hidden.length) {
      hidden.attr('name', `${rol}[${newId}][usuario]`).val(newId);
    } else {
      $('<input type="hidden">')
        .attr('name', `${rol}[${newId}][usuario]`)
        .val(newId)
        .appendTo(fila);
    }

    // Actualizar data-usuario y recalcular horas (badge cambia para el nuevo usuario)
    fila.attr('data-usuario', newId);
    calcularHorasEnTiempoReal();
  });

  function calcularHorasEnTiempoReal() {
    const totals = {}; // usuarioId => horas

    // 1) Sumar por usuario (toda la tabla, por si el mismo usuario aparece más de una vez)
    $('#tablaCronogramaContainer tbody tr[data-usuario]').each(function() {
      const fila = $(this);
      const uid = parseInt(fila.attr('data-usuario'));
      if (!uid) return;

      let horasFila = 0;
      fila.find('select.celda-turno').each(function() {
        const code = (($(this).val() || '') + '').toUpperCase().trim();
        if (OFF_CODES.has(code)) return; // 0 hs
        horasFila += (HOURS_BY_CODE[code] || 0);
      });
      totals[uid] = (totals[uid] || 0) + horasFila;
    });

    // 2) Actualizar todas las badges según el total por usuario
    $('#tablaCronogramaContainer tbody tr[data-usuario]').each(function() {
      const fila = $(this);
      const uid = parseInt(fila.attr('data-usuario'));
      const horas = totals[uid] || 0;
      const span = fila.find('.badge-horas');

      span.removeClass('horas-bajo horas-ok horas-alto').text(`${horas} hs`);
      if (horas < 200) span.addClass('horas-bajo').attr('title', `${horas} hs`);
      else if (horas > 240) span.addClass('horas-alto').attr('title', `${horas} hs`);
      else span.addClass('horas-ok').attr('title', `${horas} hs`);
    });

    // 3) Persistir en horasPorUsuario si existe
    if (typeof horasPorUsuario === 'object') {
      Object.keys(totals).forEach(uid => horasPorUsuario[uid] = totals[uid]);
    }
  }


  function renderVigiladores(_puestosFiltrados, vigiladoresObjetivo, daysInMonth, year, month, html) {
    vigiladoresObjetivo.forEach(u => {
      const usuarioId = parseInt(u.idUsuario);

      html.push(`<tr data-rol="Vigilador" data-usuario="${usuarioId}" data-objetivo="${$('#objetivo').val()}">`);

      // Columna 1: Rol
      html.push(`<td class="sticky-col rol-sticky">Vigilador</td>`);

      // Columna 2: Usuario (select + badge + hidden)
      const horasIni = (horasPorUsuario && horasPorUsuario[usuarioId]) ? horasPorUsuario[usuarioId] : 0;
      let claseHoras = (horasIni < 200) ? 'horas-bajo' : (horasIni > 240 ? 'horas-alto' : 'horas-ok');

      let selectUsr = `<select class="form-control select2 select-usuario" data-rol="vigilador">`;
      selectUsr += `<option value="">Selecciona</option>`;
      vigiladoresObjetivo.forEach(v => {
        const sel = (parseInt(v.idUsuario) === usuarioId) ? 'selected' : '';
        selectUsr += `<option value="${v.idUsuario}" ${sel}>${v.apellido}, ${v.nombre}</option>`;
      });
      selectUsr += `</select>`;

      html.push(`<td class="sticky-col usuario-sticky">
      <div class="d-flex align-items-center">
        ${selectUsr}
        <span class="badge badge-horas ${claseHoras} ml-2" title="${horasIni} hs">${horasIni} hs</span>
      </div>
      <input type="hidden" name="vigilador[${usuarioId}][usuario]" value="${usuarioId}">
    </td>`);

      // Columnas de días
      for (let d = 1; d <= daysInMonth; d++) {
        const fechaJS = new Date(`${year}-${String(month).padStart(2, '0')}-${String(d).padStart(2, '0')}T00:00:00`);
        const fecha = fechaJS.toISOString().slice(0, 10);
        const diaSemana = fechaJS.getDay();
        const esFeriado = feriados.includes(fecha);
        const claseExtra = (diaSemana === 0 || diaSemana === 6 || esFeriado) ? 'bg-olive color-palette text-white' : '';

        const valPrevio = (datosPrevios && datosPrevios.vigilador && datosPrevios.vigilador[usuarioId] && datosPrevios.vigilador[usuarioId][d]) ? datosPrevios.vigilador[usuarioId][d] : '';

        html.push(`<td class="day-col ${claseExtra}">
        <select name="vigilador[${usuarioId}][${d}]" class="form-control no-arrow celda-turno" data-optional="true" data-rol="vigilador">
          ${opcionesTurnoHTML(valPrevio)}
        </select>
      </td>`);
      }

      html.push(`</tr>`);
    });
  }

  function renderReferentes(referentesObjetivo, daysInMonth, year, month, html) {
    referentesObjetivo.forEach(u => {
      const usuarioId = parseInt(u.idUsuario);

      html.push(`<tr data-rol="Referente" data-usuario="${usuarioId}" data-objetivo="${$('#objetivo').val()}">`);

      // Columna 1: Rol
      html.push(`<td class="sticky-col rol-sticky">Referente</td>`);

      // Columna 2: Usuario (select + badge + hidden)
      const horasIni = (horasPorUsuario && horasPorUsuario[usuarioId]) ? horasPorUsuario[usuarioId] : 0;
      let claseHoras = (horasIni < 200) ? 'horas-bajo' : (horasIni > 240 ? 'horas-alto' : 'horas-ok');

      let selectUsr = `<select class="form-control select2 select-usuario" data-rol="referente">`;
      selectUsr += `<option value="">Selecciona</option>`;
      referentesObjetivo.forEach(v => {
        const sel = (parseInt(v.idUsuario) === usuarioId) ? 'selected' : '';
        selectUsr += `<option value="${v.idUsuario}" ${sel}>${v.apellido}, ${v.nombre}</option>`;
      });
      selectUsr += `</select>`;

      html.push(`<td class="sticky-col usuario-sticky">
      <div class="d-flex align-items-center">
        ${selectUsr}
        <span class="badge badge-horas ${claseHoras} ml-2" title="${horasIni} hs">${horasIni} hs</span>
      </div>
      <input type="hidden" name="referente[${usuarioId}][usuario]" value="${usuarioId}">
    </td>`);

      // Columnas de días
      for (let d = 1; d <= daysInMonth; d++) {
        const fechaJS = new Date(`${year}-${String(month).padStart(2, '0')}-${String(d).padStart(2, '0')}T00:00:00`);
        const fecha = fechaJS.toISOString().slice(0, 10);
        const diaSemana = fechaJS.getDay();
        const esFeriado = feriados.includes(fecha);
        const claseExtra = (diaSemana === 0 || diaSemana === 6 || esFeriado) ? 'bg-olive color-palette text-white' : '';

        const valPrevio = (datosPrevios && datosPrevios.referente && datosPrevios.referente[usuarioId] && datosPrevios.referente[usuarioId][d]) ? datosPrevios.referente[usuarioId][d] : '';

        html.push(`<td class="day-col ${claseExtra}">
        <select name="referente[${usuarioId}][${d}]" class="form-control no-arrow celda-turno" data-optional="true">
          ${opcionesTurnoHTML(valPrevio)}
        </select>
      </td>`);
      }

      html.push(`</tr>`);
    });
  }


  $('#objetivo').on('change', function() {
    // Agregamos un input hidden 'cargar' antes de enviar
    if ($('#frmCronograma').length) {
      $('<input>').attr({
        type: 'hidden',
        name: 'cargar',
        value: '1'
      }).appendTo('#frmCronograma');

      $('#frmCronograma').submit();
    }
  });

  document.addEventListener('DOMContentLoaded', function() {
    if (datosPrevios && Object.keys(datosPrevios).length > 0) {
      const objetivo = datosPrevios.objetivo;
      const mesVal = datosPrevios.mes;
      generarTablaCronograma(objetivo, mesVal);
    }
  });

  function opcionesTurnoHTML(seleccion) {
    // Ajustá si querés más códigos en “Jornada normal”
    const jornadaNormal = ['D', 'N', '6H', '7H', '8H', '9H', '9RF', '9HEX', '13H', '14H', 'N15', 'D/LEM', 'D/GU', 'D/AR', 'D/LUJ', 'D/LH', 'D/GC', 'D/MA', 'BE'];
    const referencias = ['SALA', 'MIC', 'F/JUS', 'NOTT', 'GUE', 'PER', 'PAL', 'BOS', 'OFI'];
    const licencias = ['F', 'GP/D', 'GP/N', 'E', 'P', 'L', 'S'];

    let html = `<option value=""></option>`;
    html += `<optgroup label="Jornada normal">`;
    jornadaNormal.forEach(c => html += `<option value="${c}" ${seleccion===c?'selected':''}>${c}</option>`);
    html += `</optgroup>`;

    html += `<optgroup label="Referencias">`;
    referencias.forEach(c => html += `<option value="${c}" ${seleccion===c?'selected':''}>${c}</option>`);
    html += `</optgroup>`;

    html += `<optgroup label="Licencias">`;
    licencias.forEach(c => html += `<option value="${c}" ${seleccion===c?'selected':''}>${c}</option>`);
    html += `</optgroup>`;

    return html;
  }
</script>
<?php unset($_SESSION['cronograma_post']); ?>