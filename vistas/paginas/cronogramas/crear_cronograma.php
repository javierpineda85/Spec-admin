<?php
$datosPrevios = $_SESSION['cronograma_error'] ?? $_SESSION['cronograma_post'] ?? [];
unset($_SESSION['cronograma_error']);
$avisos = $_SESSION['cronograma_avisos'] ?? [];
$objetivoReq = (int)($_POST['objetivo'] ?? $_GET['objetivo'] ?? $datosPrevios['objetivo'] ?? 0);
$mesSeleccionado = (string)($_POST['mes'] ?? $_GET['mes'] ?? $datosPrevios['mes'] ?? date('Y-m'));
try {
  CronogramaReglas::mes($mesSeleccionado);
  if (isset($_POST['cargar']) || (isset($_GET['objetivo'], $_GET['mes']) && !isset($_SESSION['cronograma_error']))) {
    $info = ControladorCronogramas::precargarCronogramaSiExiste($objetivoReq, $mesSeleccionado);
    $datosPrevios = $info['postSimulado'];
    $avisos = array_merge($avisos, $_SESSION['cronograma_avisos'] ?? []);
    if ($info['origen'] === 'anterior') $avisos[] = 'Continuidad desde ' . $info['mesAnterior'] . '. Revisa las filas que requieren completar el ciclo.';
  }
} catch (Throwable $e) {
  $avisos[] = $e->getMessage();
  $datosPrevios = [];
}
unset($_SESSION['cronograma_avisos']);
$db = new Conexion;
$objetivos = $db->consultas('SELECT idObjetivo, nombre FROM objetivos WHERE activo = 1 ORDER BY nombre');
$puestos = $db->consultas('SELECT idPuesto, puesto, objetivo_id FROM puestos WHERE activo = 1');
$vigiladores = $db->consultas("SELECT DISTINCT u.idUsuario, u.nombre, u.apellido, ov.objetivo_id FROM usuarios u JOIN roles r ON r.id = u.rol_id JOIN objetivo_vigiladores ov ON ov.vigilador_id = u.idUsuario WHERE r.categoria = 'operativo' AND u.activo = 1 ORDER BY u.apellido, u.nombre");
$referentes = $db->consultas("SELECT DISTINCT u.idUsuario, u.nombre, u.apellido, orf.objetivo_id FROM usuarios u JOIN roles r ON r.id = u.rol_id JOIN objetivo_referentes orf ON orf.referente_id = u.idUsuario WHERE r.categoria = 'referente' AND u.activo = 1 ORDER BY u.apellido, u.nombre");
// Conservar personas históricas aunque ahora no estén activas o vinculadas.
foreach (['vigilador' => &$vigiladores, 'referente' => &$referentes] as $rol => &$lista) {
  $ids = array_map('intval', array_keys($datosPrevios[$rol] ?? []));
  foreach ($ids as $id) {
    $presente = false;
    foreach ($lista as $u) if ((int)$u['idUsuario'] === $id && (int)$u['objetivo_id'] === $objetivoReq) $presente = true;
    if (!$presente) {
      $usuarios = $db->consultas('SELECT idUsuario, nombre, apellido FROM usuarios WHERE idUsuario = ?', [$id]);
      if ($usuarios) $lista[] = $usuarios[0] + ['objetivo_id' => $objetivoReq];
    }
  }
}
unset($lista);
$anioSeleccionado = (int)substr($mesSeleccionado, 0, 4);
$feriados = $db->consultas('SELECT fecha, motivo, tipo_feriado FROM feriados WHERE YEAR(fecha) = ?', [$anioSeleccionado]);
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
    <?php if ($avisos): ?>
      <div class="alert alert-warning"><ul class="mb-0"><?php foreach (array_unique($avisos) as $aviso): ?><li><?= htmlspecialchars($aviso, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form id="frmCronograma" method="POST" class="mb-4">
      <div id="cronogramaEstado" class="alert alert-warning d-none" role="status"></div>
      <div id="feriadosMesInfo" class="alert alert-secondary small d-none">
        <strong>Feriados del mes:</strong>
        <ul id="feriadosMesLista" class="mb-0"></ul>
      </div>

      <div class="form-row">
        <div class="form-group col-md-4">
          <label>Objetivo</label>
          <?php $valueSelected = $objetivoReq; ?>
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
          <?php $mes = $mesSeleccionado; ?>
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
        <button type="submit" name="guardar_cronograma" id="btnGuardarCronograma" class="btn btn-success" disabled>Guardar Cronograma</button>
        <?php if (!empty($datosPrevios)): ?>
          <button type="button" id="btnVaciarCronograma" class="btn btn-outline-danger">Vaciar Cronograma</button>

        <?php endif; ?>

      </div>
      <input type="hidden" id="cronogramaObjetivo" name="cronograma_objetivo" value="">
      <input type="hidden" id="cronogramaMes" name="cronograma_mes" value="">
      <input type="hidden" id="cronogramaFilas" name="cronograma_filas" value="0">
      <input type="hidden" name="cronograma_completo" value="1">
    </form>
  </div>
</div>
<script>
    const API_VALIDAR_URL = <?= json_encode(BASE_URL . '/index.php?r=validar_turno_global', JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const API_SIGLAS_URL = new URL(<?= json_encode(BASE_URL . '/index.php?r=api_siglas', JSON_UNESCAPED_SLASHES) ?>, window.location.origin).toString();
</script>

<script>
  window.CRONOGRAMA_BOOT = {
    catalogo: <?= json_encode(['ausencias' => CronogramaReglas::AUSENCIAS, 'pasivas' => CronogramaReglas::PASIVAS, 'referencias' => CronogramaReglas::REFERENCIAS, 'especiales' => CronogramaReglas::ESPECIALES], JSON_HEX_TAG | JSON_HEX_AMP) ?>,
    puestos: <?= json_encode($puestos, JSON_UNESCAPED_UNICODE) ?>,
    vigiladores: <?= json_encode($vigiladores, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    referentes: <?= json_encode($referentes, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    todosFeriados: <?= json_encode($feriados, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    datosPrevios: <?= json_encode($datosPrevios, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    horasPorUsuario: <?= json_encode($_SESSION['horas_usuario'] ?? new stdClass(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
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
