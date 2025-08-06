<?php

$db = new Conexion();

// Vigiladores disponibles (autolimitado si es vigilador)
if (($_SESSION['rol'] ?? '') === 'Vigilador') {
    $sql = "SELECT idUsuario, apellido, nombre FROM usuarios WHERE idUsuario = ?";
    $vigiladores = $db->consultas($sql, [$_SESSION['idUsuario']]);
} else {
    $sql = "SELECT idUsuario, apellido, nombre FROM usuarios ORDER BY apellido, nombre";
    $vigiladores = $db->consultas($sql);
}

// Datos de búsqueda
$filtros    = $_SESSION['filtros_vigilador'] ?? [];
$turnos     = $_SESSION['turnos_porVigilador'] ?? [];
$diasRango  = $_SESSION['dias_rango'] ?? [];
$feriados   = $_SESSION['feriados_rango'] ?? [];

?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header bg-info text-white">
        <h3 class="card-title">urnos asignados</h3>
        
      </div>

      <div class="card-body">

        <!-- Filtros -->
        <form action="index.php?r=buscar_porVigilador" method="POST" class="form-inline mb-3">
          <label class="mr-2">Vigilador</label>
          <select name="vigilador" class="form-control mr-3" required>
            <option value="" disabled selected>Selecciona un vigilador</option>
            <?php foreach ($vigiladores as $v): ?>
              <?php $sel = ($filtros['vigilador'] ?? '') == $v['idUsuario']; ?>
              <option value="<?= $v['idUsuario'] ?>" <?= $sel ? 'selected' : '' ?>>
                <?= htmlspecialchars("{$v['apellido']} {$v['nombre']}") ?>
              </option>
            <?php endforeach; ?>
          </select>

          <label class="mr-2">Desde</label>
          <input type="date" name="desde" class="form-control mr-3"
                 value="<?= htmlspecialchars($filtros['desde'] ?? '') ?>" required>

          <label class="mr-2">Hasta</label>
          <input type="date" name="hasta" class="form-control mr-3"
                 value="<?= htmlspecialchars($filtros['hasta'] ?? '') ?>" required>

          <button type="submit" class="btn btn-primary">Buscar</button>
        </form>

        <?php if (empty($diasRango)): ?>
          <div class="alert alert-info">No hay fechas para mostrar.</div>
          <?php return; ?>
        <?php endif; ?>

        <!-- Tabla compacta para móviles -->
        <div class="table-responsive">
          <table class="table table-sm table-bordered text-center">
            <thead class="thead-light">
              <tr>
                <th>Fecha</th>
                <th>Turno</th>
                <th>Puesto</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($diasRango as $fecha): ?>
                <?php
                  $diaSemana = date('N', strtotime($fecha)); // 6=sábado, 7=domingo
                  $esFeriado = in_array($fecha, $feriados);
                  $verde = ($esFeriado || $diaSemana >= 6) ? 'bg-olive text-white' : '';
                  $info = $turnos[$fecha] ?? null;
                ?>
                <tr class="<?= $verde ?>">
                  <td><?= date('d/m/Y', strtotime($fecha)) ?></td>
                  <td><?= htmlspecialchars($info['turno'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($info['puesto'] ?? '-') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <?php
        unset($_SESSION['filtros_vigilador'], $_SESSION['turnos_porVigilador'], $_SESSION['dias_rango'], $_SESSION['feriados_rango']);
        ?>
      </div>
    </div>
  </div>
</section>
