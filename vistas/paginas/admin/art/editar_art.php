<?php
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "<div class='alert alert-danger'>ID inválido</div>";
  exit;
}

$idArt = intval($_GET['id']);
$art = ModeloArt::mdlObtenerArtPorId('art', $idArt);

if (!$art) {
  echo "<div class='alert alert-warning'>A.R.T. no encontrada</div>";
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  ArtController::ctrEditarArt();
}
?>

<div class="card">
  <div class="card-header bg-info text-white">
    <h3 class="card-title">Editar A.R.T.</h3>
  </div>

  <div class="card-body">
    <form method="POST">
      <input type="hidden" name="idArt" value="<?= $art['idArt'] ?>">

      <div class="row">

        <div class="form-group col-md-4">
          <label for="razon_social">Razón Social</label>
          <input type="text" name="razon_social" id="razon_social" class="form-control" required value="<?= htmlspecialchars($art['razon_social']) ?>">
        </div>

        <div class="form-group col-md-4">
          <label for="cuit_empresa">CUIT de la Empresa</label>
          <input type="text" name="cuit_empresa" id="cuit_empresa" class="form-control" required pattern="\d{11}" title="Formato: 11 dígitos sin guiones" value="<?= $art['cuit_empresa'] ?>">
          <small>Sólo números, sin espacios ni guiones</small>
        </div>

        <div class="form-group col-md-4">
          <label for="telefono_empresa">Teléfono de la Empresa</label>
          <input type="tel" name="telefono_empresa" id="telefono_empresa" class="form-control" required placeholder="Ej: 02611234567" pattern="\d{10,11}" title="Ingrese un número de 10 u 11 dígitos" value="<?= $art['telefono_empresa'] ?>">
        </div>

        <div class="form-group col-md-3">
          <label for="empresa_aseguradora">Empresa Aseguradora</label>
          <input type="text" name="empresa_aseguradora" id="empresa_aseguradora" required class="form-control" value="<?= htmlspecialchars($art['empresa_aseguradora']) ?>">
        </div>

        <div class="form-group col-md-3">
          <label for="cuit_aseguradora">CUIT de la Aseguradora</label>
          <input type="text" name="cuit_aseguradora" id="cuit_aseguradora" class="form-control" required pattern="\d{11}" title="Formato: 11 dígitos sin guiones" value="<?= $art['cuit_aseguradora'] ?>">
          <small>Sólo números, sin espacios ni guiones</small>
        </div>

        <div class="form-group col-md-3">
          <label for="nro_poliza">Número de Póliza</label>
          <input type="text" name="nro_poliza" id="nro_poliza" class="form-control" required value="<?= $art['nro_poliza'] ?>">
        </div>

        <div class="form-group col-md-3">
          <label for="telefono_aseguradora">Teléfono de la Aseguradora</label>
          <input type="tel" name="telefono_aseguradora" id="telefono_aseguradora" class="form-control" required placeholder="Ej: 08001234567" pattern="\d{10,11}" title="Ingrese un número de 10 u 11 dígitos" value="<?= $art['telefono_aseguradora'] ?>">
        </div>

      </div>

      <div class="form-group text-right">
        <a href="?r=listado_art" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-info">Actualizar A.R.T.</button>
      </div>
    </form>
  </div>
</div>
