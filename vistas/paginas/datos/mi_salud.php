<?php
// Auth::check('salud', 'verMiSalud');
$idUsuario = $_SESSION['idUsuario'] ?? 0;
$db = new Conexion;

// Datos existentes
$salud = $db->consultas("SELECT * FROM salud WHERE usuario_id = $idUsuario LIMIT 1")[0] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    SaludController::guardarSalud();
}
?>

<div class="card">
  <div class="card-header bg-info text-white">
    <h3 class="card-title">Mi Información de Salud</h3>
  </div>

  <div class="card-body">
    <form method="POST">
      <input type="hidden" name="usuario_id" value="<?= $idUsuario ?>">

      <!-- Enfermedades y medicación -->
      <h5 class="mt-4">Antecedentes Médicos</h5>
      <hr>
      <div class="row">
        <div class="form-group col-sm-12 col-md-6 col-lg-4">
          <label>¿Padece alguna enfermedad crónica?</label>
          <textarea name="enfermedad_cronica" class="form-control" rows="2" data-optional="true"><?= $salud['enfermedad_cronica'] ?? '' ?></textarea>
        </div>

        <div class="form-group col-sm-12 col-md-6 col-lg-4">
          <label>¿Toma medicación de forma recurrente?</label>
          <textarea name="medicacion" class="form-control" rows="2" data-optional="true"><?= $salud['medicacion'] ?? '' ?></textarea>
        </div>

        <div class="form-group col-sm-6 col-md-3 col-lg-2">
          <label for="grupo_sanguineo">Grupo sanguíneo</label>
          <select name="grupo_sanguineo" id="grupo_sanguineo" class="form-control" data-optional="true">
            <option value="">Seleccione...</option>
            <?php
              foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', '0+', '0-'] as $grupo) {
                $sel = ($salud['grupo_sanguineo'] ?? '') === $grupo ? 'selected' : '';
                echo "<option value=\"$grupo\" $sel>$grupo</option>";
              }
            ?>
          </select>
        </div>

        <div class="form-group col-sm-6 col-md-3 col-lg-2">
          <label>¿Tiene obra social?</label>
          <select id="tiene_obra_social" name="tiene_obra_social" class="form-control" onchange="toggleCarnet(this.value)" data-optional="true">
            <option value="0" <?= ($salud['tiene_obra_social'] ?? 0) == 0 ? 'selected' : '' ?>>No</option>
            <option value="1" <?= ($salud['tiene_obra_social'] ?? 0) == 1 ? 'selected' : '' ?>>Sí</option>
          </select>
        </div>
      </div>

      <!-- Datos de la obra social -->
      <div id="carnet_datos" style="display: <?= ($salud['tiene_obra_social'] ?? 0) == 1 ? 'block' : 'none' ?>;" >
        <h5 class="mt-4">Datos de la Obra Social</h5>
        <hr>
        <div class="row">
          <div class="form-group col-sm-12 col-md-6 col-lg-3">
            <label>Nombre de la obra social</label>
            <input type="text" name="obra_social_nombre" class="form-control" value="<?= $salud['obra_social_nombre'] ?? '' ?>" data-optional="true">
          </div>

          <div class="form-group col-sm-12 col-md-6 col-lg-3">
            <label>Beneficiario</label>
            <input type="text" name="beneficiario" class="form-control" value="<?= $salud['beneficiario'] ?? '' ?>" data-optional="true">
          </div>

          <div class="form-group col-sm-12 col-md-6 col-lg-3">
            <label>N° de afiliado</label>
            <input type="text" name="nro_afiliado" class="form-control" value="<?= $salud['nro_afiliado'] ?? '' ?>" data-optional="true">
          </div>

          <div class="form-group col-sm-12 col-md-6 col-lg-3">
            <label>Vigencia</label>
            <input type="date" name="vigencia_obra_social" class="form-control" value="<?= $salud['vigencia_obra_social'] ?? '' ?>" data-optional="true">
          </div>
        </div>
      </div>

      <!-- Botón -->
      <div class="form-group mt-4">
        <button type="submit" class="btn btn-success">Guardar datos</button>
      </div>
    </form>
  </div>
</div>

<script>
function toggleCarnet(val) {
  document.getElementById('carnet_datos').style.display = val == '1' ? 'block' : 'none';
}
</script>
