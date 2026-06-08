<?php
$db = new Conexion;

$mes = $_GET['mes'] ?? '';
$tipo = $_GET['tipo'] ?? '';

$condiciones = [];
$params = [];

if (!empty($mes)) {
  $condiciones[] = "MONTH(fecha) = :mes";
  $params[':mes'] = intval($mes);
}

if (!empty($tipo)) {
  $condiciones[] = "tipo_feriado = :tipo";
  $params[':tipo'] = $tipo;
}

$sql = "SELECT * FROM feriados";

// Validación explícita para saber si hay alguna condición
if (!empty($condiciones)) {
  $sql .= " WHERE " . implode(" AND ", $condiciones);
}

$sql .= " ORDER BY fecha ASC";

// Ejecutamos consulta
$feriados = $db->consultas($sql, $params);

$meses = [
  1 => 'Enero',
  2 => 'Febrero',
  3 => 'Marzo',
  4 => 'Abril',
  5 => 'Mayo',
  6 => 'Junio',
  7 => 'Julio',
  8 => 'Agosto',
  9 => 'Septiembre',
  10 => 'Octubre',
  11 => 'Noviembre',
  12 => 'Diciembre'
];
?>

<div class="card">
  <div class="card-header bg-secondary text-white">
    <h3 class="card-title">Listado de feriados</h3>
  </div>
  <div class="card-body">
    <form method="GET" action="index.php" class="form-inline mb-3">
      <input type="hidden" name="r" value="listado_feriados">
      <label class="mr-2">Mes:</label>
      <select name="mes" class="form-control mr-3" data-optional="true">
        <option value="">Todos</option>
        <?php for ($i = 1; $i <= 12; $i++): ?>
          <option value="<?= $i ?>" <?= (isset($_GET['mes']) && $_GET['mes'] == $i) ? 'selected' : '' ?>>
            <?= $meses[$i] ?>
          </option>
        <?php endfor; ?>
      </select>


      <label class="mr-2">Tipo:</label>
      <select name="tipo" class="form-control mr-3" data-optional="true">
        <option value="">Todos</option>
        <?php
        $tipos = ['nacional', 'provincial', 'departamental', 'asueto', 'día no laborable'];
        foreach ($tipos as $t):
        ?>
          <option value="<?= $t ?>" <?= ($tipo === $t) ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
        <?php endforeach; ?>
      </select>

      <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>


    <?php if (!empty($feriados)): ?>
      <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm" id="example1">
          <thead class="thead-light">
            <tr>
              <th>Fecha</th>
              <th>Motivo</th>
              <th>Tipo</th>
              <?php if ($_SESSION['nivel'] > 2): ?>
                <th>Acciones</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($feriados as $f): ?>
              <tr>
                <td><?= date('d/m/Y', strtotime($f['fecha'])) ?></td>
                <td><?= htmlspecialchars($f['motivo']) ?></td>
                <td><?= ucfirst($f['tipo_feriado']) ?></td>
                <?php if ($_SESSION['nivel'] > 2): ?>
                  <td>
                    <a href="?r=editar_feriado&id=<?= $f['idFeriado'] ?>" class="btn btn-sm btn-info">Editar</a>
                    <a href="?r=eliminar_feriado&id=<?= $f['idFeriado'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar feriado?')">Eliminar</a>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>

      <div class="alert alert-info">No se encontraron feriados con los filtros aplicados.</div>
    <?php endif; ?>
  </div>
</div>