<?php
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "<div class='alert alert-danger'>ID de feriado inválido</div>";
  exit;
}

$idFeriado = intval($_GET['id']);
$feriado = ModeloFeriados::mdlObtenerFeriado('feriados', $idFeriado);

if (!$feriado) {
  echo "<div class='alert alert-warning'>Feriado no encontrado</div>";
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  FeriadosController::ctrEditarFeriado();
}
?>

<div class="card">
  <div class="card-header bg-warning text-white">
    <h3 class="card-title">Editar feriado</h3>
  </div>

  <div class="card-body">
    <form method="POST">
      <input type="hidden" name="idFeriado" value="<?= $feriado['idFeriado'] ?>">

      <div class="form-row feriado-row mb-3">
        <div class="col-md-2">
          <input type="date" name="fecha" class="form-control" value="<?= $feriado['fecha'] ?>" required>
        </div>
        <div class="col-md-5">
          <input type="text" name="motivo" class="form-control" value="<?= htmlspecialchars($feriado['motivo'])?? '' ?>" required>
        </div>
        <div class="col-md-3">
          <select name="tipo_feriado" class="form-control" required>
            <?php
            $tipos = ['nacional', 'provincial', 'departamental', 'asueto', 'día no laborable'];
            foreach ($tipos as $tipo) {
              $selected = ($feriado['tipo_feriado'] === $tipo) ? 'selected' : '';
              echo "<option value='$tipo' $selected>" . ucfirst($tipo) . "</option>";
            }
            ?>
          </select>
        </div>
      </div>

      <div class="form-group text-right mt-4">
        <a href="?r=listado_feriados" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-success mx-3">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>
