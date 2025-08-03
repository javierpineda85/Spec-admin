<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  ArtController::ctrGuardarArt();
}
?>

<div class="card">
  <div class="card-header bg-success text-white">
    <h3 class="card-title">Cargar A.R.T.</h3>
  </div>

  <div class="card-body">
    <form method="POST">
      <div class="row">

        <div class="form-group col-md-4">
          <label for="razon_social">Razón Social</label>
          <input type="text" name="razon_social" id="razon_social" class="form-control" required >
        </div>

        <div class="form-group col-md-4">
          <label for="cuit_empresa">CUIT de la Empresa</label>
          <input type="text" name="cuit_empresa" id="cuit_empresa" class="form-control" required pattern="\d{11}" title="Formato: 11 dígitos sin guiones">
          <small>Sólo números, sin espacios ni guiones</small>
        </div>

        <div class="form-group col-md-4">
          <label for="telefono_empresa">Teléfono de la Empresa</label>
          <input type="tel" name="telefono_empresa" id="telefono_empresa" class="form-control" required placeholder="Ej: 02611234567" pattern="\d{10,11}" title="Ingrese un número de 10 u 11 dígitos">
        </div>

        <div class="form-group col-md-3">
          <label for="empresa_aseguradora">Empresa Aseguradora</label>
          <input type="text" name="empresa_aseguradora" id="empresa_aseguradora" required class="form-control"  >
        </div>

        <div class="form-group col-md-3">
          <label for="cuit_aseguradora">CUIT de la Aseguradora</label>
          <input type="text" name="cuit_aseguradora" id="cuit_aseguradora" class="form-control" required  pattern="\d{11}" title="Formato: 11 dígitos sin guiones">
          <small>Sólo números, sin espacios ni guiones</small>
        </div>

        <div class="form-group col-md-3">
          <label for="nro_poliza">Número de Póliza</label>
          <input type="text" name="nro_poliza" id="nro_poliza" class="form-control" required  >
        </div>

        <div class="form-group col-md-3">
          <label for="telefono_aseguradora">Teléfono de la Aseguradora</label>
          <input type="tel" name="telefono_aseguradora" id="telefono_aseguradora" class="form-control" required placeholder="Ej: 08001234567" pattern="\d{10,11}" title="Ingrese un número de 10 u 11 dígitos">
        </div>

      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-success">Guardar A.R.T.</button>
      </div>
    </form>
  </div>
</div>
