<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idEliminar'])) {
  RondasController::crtDesactivarRonda(intval($_POST['idEliminar']));
}
$db = new Conexion;
$sql = " SELECT r.idRonda, r.puesto, r.objetivo_id, r.tipo, o.nombre AS objetivo
                  FROM rondas r
                  JOIN objetivos o ON r.objetivo_id = o.idObjetivo
                  WHERE r.status = 'active'
                  ORDER BY r.objetivo_id, r.orden_escaneo
";
$rondas = $db->consultas($sql);

?>

<div class="card">
  <div class="card-header bg-info text-white">
    <h3 class="card-title">Listado de Rondas</h3>
  </div>
  <?php if (!empty($_SESSION['success_message'])): ?>
    <div class="alert alert-success mt-3">
      <?= $_SESSION['success_message'];
      unset($_SESSION['success_message']); ?>
    </div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['error_message'])): ?>
    <div class="alert alert-danger mt-3">
      <?= $_SESSION['error_message'];
      unset($_SESSION['error_message']); ?>
    </div>
  <?php endif; ?>

  <div class="card-body">
    <table id="tabla-rondas" class="table table-bordered table-striped table-sm">
      <thead>
        <tr>
          <th>Puesto</th>
          <th>Objetivo</th>
          <th>Tipo</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rondas as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['puesto']) ?></td>
            <td><?= htmlspecialchars($r['objetivo']) ?></td>
            <td><?= htmlspecialchars($r['tipo']) ?></td>
            <td class="text-center">
              <a href="?r=editar_ronda&id=<?= $r['idRonda'] ?>" class="btn btn-success btn-sm" title="Editar ronda"> <i class="fas fa-edit"></i> </a>
              <form method="post" style="display:inline-block;">
                <input type="hidden" name="idEliminar" value="<?= $r['idRonda'] ?>">
                <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('¿Desactivar esta ronda?')" title="Desactivar ronda">
                  <i class="fas fa-ban"></i>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>