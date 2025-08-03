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
  $info = ControladorCronograma::precargarCronogramaSiExiste($_POST['objetivo'], $_POST['mes']);

  if ($info['origen'] === 'anterior') {
    ToastifyController::info("Precargando datos del mes anterior ({$info['mesAnterior']})");
}

  $turnos = $info['turnos'] ?? [];
  $postSimulado = [];

  if (!empty($turnos)) {
    foreach ($turnos as $t) {
      $dia = intval(substr($t['fecha'], 8, 2));
      $usuarioId = $t['usuario_id'];
      $puestoId = $t['puesto_id'] ?? '-';
      $rol = strtolower($t['rol']);
      $tipoTurno = ($t['tipo_turno'] === 'Licencia') ? 'Licencias' : ($t['codigo_turno'] === 'D' ? 'Diurno' : 'Nocturno');

      if ($rol === 'vigilador') {
        $postSimulado[$rol][$puestoId][$tipoTurno]['usuario'] = $usuarioId;
        $postSimulado[$rol][$puestoId][$tipoTurno][$dia] = $t['codigo_turno'];
      } elseif ($rol === 'referente') {
        $postSimulado[$rol][$tipoTurno]['usuario'] = $usuarioId;
        $postSimulado[$rol][$tipoTurno][$dia] = $t['codigo_turno'];
      }
    }

    $_SESSION['cronograma_post'] = $postSimulado;
    $datosPrevios = $postSimulado;
  } else {
    // Si no hay turnos en BD, generar simulación vacía
    $postSimulado = ControladorCronograma::generarSimulacionVacia($_POST['objetivo'], $_POST['mes']);
    $_SESSION['cronograma_post'] = $postSimulado;
    $datosPrevios = $postSimulado;
  }

  // Guardamos info de origen para mostrar Toastify
  $_SESSION['cronograma_origen'] = $info['origen'] ?? null;
  $_SESSION['cronograma_mes_anterior'] = $info['mesAnterior'] ?? null;
}



// Al final del archivo PHP (después de usarse en el HTML):
$datosPrevios = $_SESSION['cronograma_post'] ?? [];
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

  /* Estilo tabla cronograma */
  #tablaCronogramaContainer .table-responsive {
    max-width: 100%;
    max-height: 380px;
    /*overflow: auto;*/
  }

  /* Fijar las primeras 4 columnas: Rol, Puesto, Turno, Usuario */
  #tablaCronogramaContainer td:nth-child(1),
  #tablaCronogramaContainer th:nth-child(1) {

    background: #fff;
    z-index: 5;
    min-width: 80px;
    width: 80px;
  }

  #tablaCronogramaContainer td:nth-child(2),
  #tablaCronogramaContainer th:nth-child(2) {
    background: #fff;
    z-index: 5;
    min-width: 100px;
    width: 100px;
  }

  #tablaCronogramaContainer td:nth-child(3),
  #tablaCronogramaContainer th:nth-child(3) {

    background: #fff;
    z-index: 5;
    min-width: 150px;
    width: 150px;
  }

  #tablaCronogramaContainer td:nth-child(4),
  #tablaCronogramaContainer th:nth-child(4) {

    background: #fff;
    z-index: 5;
    min-width: 90px;
    width: 90px;
  }


  /* Estética y scroll horizontal limpio */
  #tablaCronogramaContainer table {
    white-space: nowrap;
    border-collapse: collapse;
    font-size: 0.85em;
    background-color: white;
    table-layout: fixed;
  }


  #tablaCronogramaContainer select {
    border: none;
    padding: 0;
    background: none;
    appearance: none;
    /* lo más importante */
    -webkit-appearance: none;
    -moz-appearance: none;
  }

  #tablaCronogramaContainer .day-col {
    min-width: 25px !important;
    max-width: 25px !important;
    width: 25px !important;
    text-align: center;
    padding: 0 2px;
    font-size: 0.85em;

  }

  .sticky-col {
    position: sticky;
    background-color: #fff;
    z-index: 5;
  }

  .usuario-sticky {
    left: 180px;
    /* Ajustalo según tu layout real */
    min-width: 150px;
    width: 150px;
  }

  .usuario-placeholder {
    visibility: hidden;
    border-left: none;
    padding: 0 !important;
    margin: 0 !important;
    width: 0;
    height: 0;
    display: none !important;
  }

  .celda-turno {
    font-size: 0.8em !important;
    font-weight: bold !important;
    text-align: center;
    margin-top: -10px;
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

  /* amarillo */
  .horas-ok {
    background-color: #007bff;
  }

  /* azul */
  .horas-alto {
    background-color: #dc3545;
  }

  /* rojo */
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
          <?php $mes = $datosPrevios['mes'] ?? date('Y-m'); ?>
          <input type="month" class="form-control" name="mes" id="mes" value="<?= $_POST['mes'] ?? '' ?>">
        </div>
        <div class="form-group col-md-2 align-self-end">
          <button type="submit" name="cargar" id="btnCargarTabla" class="btn btn-primary">Cargar Cronograma</button>
        </div>
      </div>
      <div class="row">
        <div class="form-group">
          <label for="">Referencias:</label>
          <button class="btn bg-dark btn-sm disabled"><b>D:</b> Diurno</button>
          <button class="btn bg-dark btn-sm disabled"><b>N:</b> Nocturno</button>
          <button class="btn bg-dark btn-sm disabled"><b>F:</b> Franco</button>
          <button class="btn bg-dark btn-sm disabled"><b>G:</b> Guardia Pasiva</button>
          <button class="btn bg-dark btn-sm disabled"><b>E:</b> Parte de Enfermo</button>
          <button class="btn bg-dark btn-sm disabled"><b>P:</b> Permiso especial</button>
          <button class="btn bg-dark btn-sm disabled"><b>L:</b> Licencia</button>
          <button class="btn bg-dark btn-sm disabled"><b>S:</b> Suspensión</button>
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
    html.push('<thead><tr><th>Rol</th><th>Puesto</th><th>Usuario</th><th>Turno</th>');

    for (let d = 1; d <= daysInMonth; d++) {
      const fechaJS = new Date(`${year}-${String(month).padStart(2, '0')}-${String(d).padStart(2, '0')}T00:00:00`);
      const fecha = fechaJS.toISOString().slice(0, 10);
      const diaSemana = fechaJS.getDay();
      const esFeriado = feriados.includes(fecha);
      const claseExtra = (diaSemana === 0 || diaSemana === 6 || esFeriado) ? 'bg-olive color-palette text-white' : '';
      html.push(`<th class="day-col ${claseExtra}">${d}</th>`);
    }

    html.push('</tr></thead><tbody>');

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

    html.push('<thead><tr><th>Rol</th><th>Puesto</th><th>Usuario</th><th>Turno</th>');
    for (let d = 1; d <= daysInMonth; d++) {
      const fecha = `${year}-${String(month).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
      const esFeriado = feriados.includes(fecha);
      const diaSemana = new Date(`${fecha}T00:00:00`).getDay();
      const claseExtra = (diaSemana === 0 || diaSemana === 6 || esFeriado) ? 'bg-olive color-palette text-white' : '';
      html.push(`<th class="day-col ${claseExtra}">${d}</th>`);
    }
    html.push('</tr></thead><tbody>');

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

  function calcularHorasEnTiempoReal() {
    const horasTotales = {};

    // Recorremos todos los selects de usuario
    $('select[name*="[usuario]"]').each(function() {
      const usuarioId = parseInt($(this).val());
      if (!usuarioId) return;

      const celdaUsuario = $(this).closest('td');
      const filaBase = celdaUsuario.closest('tr');
      const puestoId = filaBase.data('puesto');
      const rol = filaBase.data('rol');
      const turno = filaBase.data('turno');

      let horas = 0;

      // Buscar todas las filas relacionadas con este usuario
      const filasRelacionadas = $(`tr[data-puesto="${puestoId}"][data-rol="${rol}"]`);

      filasRelacionadas.each(function() {
        const fila = $(this);
        const turno = fila.data('turno');

        for (let d = 1; d <= 31; d++) {
          const input = fila.find(`[name*="[${d}]"]`);
          if (!input.length) continue;
          const valor = input.val();
          if (['D', 'N'].includes(valor)) {
            horas += 12;
          }
        }
      });

      horasTotales[usuarioId] = horas;

      // actualizar visualmente la etiqueta
      const span = celdaUsuario.find('.badge-horas');

      span.removeClass('horas-bajo horas-ok horas-alto').text(`${horas} hs`);

      if (horas < 200) {
        span.addClass('horas-bajo').attr('title', `${horas} hs`);
      } else if (horas > 240) {
        span.addClass('horas-alto').attr('title', `${horas} hs`);
      } else {
        span.addClass('horas-ok').attr('title', `${horas} hs`);
      }
    });
  }

  function renderVigiladores(puestosFiltrados, vigiladoresObjetivo, daysInMonth, year, month, html) {

    puestosFiltrados.forEach(p => {
      const tiposTurno = ['Diurno', 'Nocturno', 'Licencias'];
      tiposTurno.forEach((tipoTurno, idx) => {
        html.push(`<tr data-puesto="${p.idPuesto}" data-turno="${tipoTurno}" data-rol="Vigilador">`);

        if (idx === 0) {
          html.push(`<td rowspan="3" class="sticky-col">Vigilador</td>`);
          html.push(`<td rowspan="3" class="sticky-col">${p.puesto}</td>`);

          let selectV = `<select name="vigilador[${p.idPuesto}][${tipoTurno}][usuario]" class="form-control celda-turno no-arrow" data-rol="vigilador">
          <option selected>Selecciona</option>`;
          vigiladoresObjetivo.forEach(u => {
            selectV += `<option value="${u.idUsuario}">${u.apellido}, ${u.nombre}</option>`;
          });
          selectV += `</select>`;

          let usuarioId = datosPrevios?.vigilador?.[p.idPuesto]?.[tipoTurno]?.usuario ?? null;
          let horas = horasPorUsuario[usuarioId] || 0;
          let claseHoras = '';
          if (horas < 200) claseHoras = 'horas-bajo';
          else if (horas > 240) claseHoras = 'horas-alto';
          else claseHoras = 'horas-ok';

          html.push(`<td rowspan="3">
          <div class="d-flex align-items-center">
            ${selectV}
            <span class="badge badge-horas ${claseHoras}" title="${horas} hs">${horas} hs</span>
          </div>
        </td>`);
        }

        html.push(`<td class="sticky-col">${tipoTurno}</td>`);

        for (let d = 1; d <= daysInMonth; d++) {
          const fechaJS = new Date(`${year}-${String(month).padStart(2, '0')}-${String(d).padStart(2, '0')}T00:00:00`);
          const fecha = fechaJS.toISOString().slice(0, 10);
          const diaSemana = fechaJS.getDay();
          const esFeriado = feriados.includes(fecha);
          const claseExtra = (diaSemana === 0 || diaSemana === 6 || esFeriado) ? 'bg-olive color-palette text-white' : '';

          html.push(`<td class="day-col ${claseExtra}">
          <select name="vigilador[${p.idPuesto}][${tipoTurno}][${d}]" class="form-control no-arrow celda-turno" data-optional="true" data-rol="vigilador">
            <option selected value=""> </option>`);

          if (tipoTurno === 'Diurno') {
            html.push(`<option value="D">D</option>`);
          } else if (tipoTurno === 'Nocturno') {
            html.push(`<option value="N">N</option>`);
          } else if (tipoTurno === 'Licencias') {
            ['F', 'G', 'E', 'P', 'L', 'S'].forEach(c => {
              html.push(`<option value="${c}">${c}</option>`);
            });
          }

          html.push(`</select></td>`);
        }

        html.push(`</tr>`);
      });
    });

  }


  function renderReferentes(referentesObjetivo, daysInMonth, year, month, html) {
    const tiposTurno = ['Diurno', 'Nocturno', 'Licencias'];
    tiposTurno.forEach((tipoTurno, idx) => {
      html.push(`<tr data-puesto="-" data-turno="${tipoTurno}" data-rol="Referente">`);

      if (idx === 0) {
        html.push(`<td rowspan="3" class="sticky-col">Referente</td>`);
        html.push(`<td rowspan="3" class="sticky-col">-</td>`);

        let selectR = `<select name="referente[${tipoTurno}][usuario]" class="form-control celda-turno" data-name="referente[${tipoTurno}][usuario]">

        <option selected>Selecciona</option>`;
        referentesObjetivo.forEach(u => {
          selectR += `<option value="${u.idUsuario}">${u.apellido}, ${u.nombre}</option>`;
        });
        selectR += `</select>`;

        let usuarioId = datosPrevios?.referente?.[tipoTurno]?.usuario ?? null;
        let horas = horasPorUsuario[usuarioId] || 0;
        let claseHoras = '';
        if (horas < 200) claseHoras = 'horas-bajo';
        else if (horas > 240) claseHoras = 'horas-alto';
        else claseHoras = 'horas-ok';

        html.push(`<td rowspan="3">
        <div class="d-flex align-items-center">
          ${selectR}
          <span class="badge badge-horas ${claseHoras}" title="${horas} hs">${horas} hs</span>
          </div>
        </td>`);
      }

      html.push(`<td class="sticky-col">${tipoTurno}</td>`);

      for (let d = 1; d <= daysInMonth; d++) {
        const fechaJS = new Date(`${year}-${String(month).padStart(2, '0')}-${String(d).padStart(2, '0')}T00:00:00`);
        const fecha = fechaJS.toISOString().slice(0, 10);
        const diaSemana = fechaJS.getDay();
        const esFeriado = feriados.includes(fecha);
        const claseExtra = (diaSemana === 0 || diaSemana === 6 || esFeriado) ? 'bg-olive color-palette text-white' : '';

        html.push(`<td class="day-col ${claseExtra}">
        <select name="referente[${tipoTurno}][${d}]" class="form-control no-arrow celda-turno" data-optional="true">
          <option selected value=""> </option>`);

        if (tipoTurno === 'Diurno') {
          html.push(`<option value="D">D</option>`);
        } else if (tipoTurno === 'Nocturno') {
          html.push(`<option value="N">N</option>`);
        } else if (tipoTurno === 'Licencias') {
          ['F', 'G', 'E', 'P', 'L', 'S'].forEach(c => {
            html.push(`<option value="${c}">${c}</option>`);
          });
        }

        html.push(`</select></td>`);
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
</script>
<?php unset($_SESSION['cronograma_post']); ?>