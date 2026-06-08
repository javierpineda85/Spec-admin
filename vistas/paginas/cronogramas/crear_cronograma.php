<?php
$_SESSION['cronograma_post'] = [];
//Guardar datos en la BD
if (isset($_POST['guardar_cronograma']) && empty($_SESSION['cronograma_post'])) {
  ControladorCronogramas::ctrGuardarCronograma();
}

//Vaciar el cronograma
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

  $info = ControladorCronogramas::precargarCronogramaSiExiste($objetivoReq, $mesReq);

  if (($info['origen'] ?? null) === 'anterior' && !empty($info['mesAnterior'])) {
    ToastifyController::info("Precargando datos del mes anterior ({$info['mesAnterior']}) con continuidad 4×2");
  }

  // ✅ Usar SIEMPRE el postSimulado armado por el controlador
  $datosPrevios = $info['postSimulado'] ?? [];

  // fallback extremo (no debería ocurrir, pero por las dudas)
  if (empty($datosPrevios)) {
    $datosPrevios = ControladorCronogramas::generarSimulacionVacia($objetivoReq, $mesReq);
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
$objetivos  = $db->consultas("SELECT * FROM objetivos WHERE activo = 1 ORDER BY nombre ");
$puestos    = $db->consultas("SELECT idPuesto, puesto, objetivo_id FROM puestos WHERE activo = 1");
$vigiladores = $db->consultas("SELECT DISTINCT u.idUsuario, u.nombre, u.apellido, ov.objetivo_id
                                    FROM usuarios u
                                    JOIN roles r ON u.rol_id = r.id
                                    JOIN objetivo_vigiladores ov ON u.idUsuario = ov.vigilador_id
                                    WHERE r.categoria = 'operativo' AND u.activo = 1
                                    ORDER BY u.apellido, u.nombre
  ");

$referentes = $db->consultas("SELECT DISTINCT u.idUsuario, u.nombre, u.apellido, orf.objetivo_id
                                        FROM usuarios u
                                        JOIN roles r ON u.rol_id = r.id
                                        JOIN objetivo_referentes orf ON u.idUsuario = orf.referente_id
                                        WHERE r.categoria = 'referente' AND u.activo = 1
                                        ORDER BY u.apellido, u.nombre
                                    ");

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
    /*max-width: 100%;
    max-height: 380px;
    overflow: auto;*/
    /* max-height: none;
    overflow: visible;*/
    max-width: 100%;
    max-height: none;
    overflow-x: auto;
    overflow-y: visible;


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

  /* Días */
  .resumen-dias {
    background-color: #d9edf7 !important;
    /* celeste */
  }

  /* Noches */
  .resumen-noches {
    background-color: #e6d9f7 !important;
    /* violeta suave */
  }

  /* Total horas por día */
  .resumen-total-dia {
    background-color: #f2f2f2 !important;
    /* gris claro */
  }

  /* Total general (ya existe pero reforzamos contraste) */
  #total-general {
    font-weight: bold;
    background-color: #1e88e5 !important;
    color: white !important;
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

  .celda-turno:focus {
    border: 1px solid red !important;
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
    const API_SIGLAS_URL = new URL(<?= json_encode(BASE_URL . '/index.php?r=api_siglas', JSON_UNESCAPED_SLASHES) ?>, window.location.origin).toString();
</script>

<script>
  window.CRONOGRAMA_BOOT = {
    puestos: <?= json_encode($puestos, JSON_UNESCAPED_UNICODE) ?>,
    vigiladores: <?= json_encode($vigiladores, JSON_UNESCAPED_UNICODE) ?>,
    referentes: <?= json_encode($referentes, JSON_UNESCAPED_UNICODE) ?>,
    todosFeriados: <?= json_encode($feriados, JSON_UNESCAPED_UNICODE) ?>,
    datosPrevios: <?= json_encode($datosPrevios, JSON_UNESCAPED_UNICODE) ?>,
    horasPorUsuario: <?= json_encode($_SESSION['horas_usuario'] ?? new stdClass(), JSON_UNESCAPED_UNICODE) ?>
  };
</script>
<?php
$cronogramaJsPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'cronograma.js';
$cronogramaJsVersion = file_exists($cronogramaJsPath) ? filemtime($cronogramaJsPath) : time();
?>
<script src="js/cronograma.js?v=<?= $cronogramaJsVersion ?>"></script>
<script>
  // inicializa pasando IDs de elementos vivos en la vista
  Cronograma.init({
    formId: 'frmCronograma',
    contenedorTablaId: 'tablaCronogramaContainer',
    objetivoId: 'objetivo',
    mesId: 'mes',
    feriadosInfoId: 'feriadosMesInfo',
    feriadosListaId: 'feriadosMesLista',
    btnCargarId: 'btnCargarTabla',
    btnVaciarId: 'btnVaciarCronograma'
  }, window.CRONOGRAMA_BOOT);
</script>
