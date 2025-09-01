<?php
//Auth::check('uniformes', 'verMiUniforme');

$idUsuario = $_SESSION['idUsuario'] ?? 0;
$db = new Conexion;
$uniforme = $db->consultas("SELECT * FROM uniformes WHERE usuario_id = $idUsuario LIMIT 1")[0] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  UniformesController::guardarUniforme();
}
?>

<div class="card">
  <div class="card-header bg-info text-dark">
    <h3 class="card-title">Mi Talle de Uniforme</h3>
  </div>

  <div class="card-body">
    <form method="POST">
      <input type="hidden" name="usuario_id" value="<?= $idUsuario ?>">

      <div class="form-row">
        <!-- Pantalón -->
        <div class="form-group col-sm-6 col-md-2">
          <label for="talle_pantalon">Pantalón</label>
          <select name="talle_pantalon" id="talle_pantalon" class="form-control" required>
            <option value="">Seleccionar</option>
            <?php for ($i = 30; $i <= 60; $i++): ?>
              <option value="<?= $i ?>" <?= ($uniforme['talle_pantalon'] ?? '') == $i ? 'selected' : '' ?>><?= $i ?></option>
            <?php endfor; ?>
          </select>
        </div>

        <!-- Calzado -->
        <div class="form-group col-sm-6 col-md-2">
          <label for="talle_calzado">Calzado</label>
          <select name="talle_calzado" id="talle_calzado" class="form-control" required>
            <option value="">Seleccionar</option>
            <?php for ($i = 30; $i <= 50; $i++): ?>
              <option value="<?= $i ?>" <?= ($uniforme['talle_calzado'] ?? '') == $i ? 'selected' : '' ?>><?= $i ?></option>
            <?php endfor; ?>
          </select>
        </div>

        <!-- Remera -->
        <div class="form-group col-sm-6 col-md-2">
          <label for="talle_remera">Remera</label>
          <select name="talle_remera" id="talle_remera" class="form-control" required>
            <option value="">Seleccionar</option>
            <?php
            $talles = ['XS','S','M','L','XL','XXL','3XL','4XL','5XL','6XL'];
            foreach ($talles as $t):
              $selected = ($uniforme['talle_remera'] ?? '') === $t ? 'selected' : '';
              echo "<option value=\"$t\" $selected>$t</option>";
            endforeach;
            ?>
          </select>
        </div>

        <!-- Polar -->
        <div class="form-group col-sm-6 col-md-2">
          <label for="talle_polar">Polar</label>
          <select name="talle_polar" id="talle_polar" class="form-control" required>
            <option value="">Seleccionar</option>
            <?php
            foreach ($talles as $t):
              $selected = ($uniforme['talle_polar'] ?? '') === $t ? 'selected' : '';
              echo "<option value=\"$t\" $selected>$t</option>";
            endforeach;
            ?>
          </select>
        </div>

        <!-- Campera -->
        <div class="form-group col-sm-6 col-md-2">
          <label for="talle_campera">Campera</label>
          <select name="talle_campera" id="talle_campera" class="form-control" required>
            <option value="">Seleccionar</option>
            <?php
            foreach ($talles as $t):
              $selected = ($uniforme['talle_campera'] ?? '') === $t ? 'selected' : '';
              echo "<option value=\"$t\" $selected>$t</option>";
            endforeach;
            ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <button type="submit" class="btn btn-success text-white">Guardar talles</button>
      </div>
    </form>
  </div>
</div>
