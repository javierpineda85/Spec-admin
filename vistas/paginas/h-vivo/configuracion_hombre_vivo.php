<?php
$diurno = intval($config['diurno'] ?? 30);
$nocturno = intval($config['nocturno'] ?? 30);
$tolerancia = intval($config['tolerancia'] ?? 3);
?>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12 col-lg-8">
        <div class="card">
          <div class="card-header bg-info text-white">
            <h3 class="card-title">Configuración de Hombre Vivo</h3>
          </div>
          <form action="?r=guardar_configuracion_hvivo" method="post">
            <div class="card-body">
              <div class="form-group">
                <label for="minutos_diurno">Minutos por reporte en turno diurno</label>
                <input
                  type="number"
                  min="1"
                  step="1"
                  class="form-control"
                  id="minutos_diurno"
                  name="minutos_diurno"
                  value="<?= $diurno ?>"
                  required>
              </div>
              <div class="form-group">
                <label for="minutos_nocturno">Minutos por reporte en turno nocturno</label>
                <input
                  type="number"
                  min="1"
                  step="1"
                  class="form-control"
                  id="minutos_nocturno"
                  name="minutos_nocturno"
                  value="<?= $nocturno ?>"
                  required>
              </div>
              <div class="alert alert-secondary mb-0">
                La tolerancia de alerta se mantiene fija en <?= $tolerancia ?> minutos.
              </div>
            </div>
            <div class="card-footer d-flex justify-content-end">
              <button type="submit" class="btn btn-primary">Guardar configuración</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
