<?php
$rows = $_SESSION['reporte_vigilador'] ?? [];

?>
<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header bg-info text-white">
        <h3 class="card-title">Resumen de Horas por Vigilador</h3>
      </div>

      <div class="card-body">
        <form action="?r=buscar_resumen_horas_por_vigilador" method="POST" class="card p-4 mb-4 shadow-sm">
          <div class="form-row">
            <div class="form-group col-md-2">
              <label>Desde</label>
              <input type="date" name="desde" class="form-control" required>
            </div>
            <div class="form-group col-md-2">
              <label>Hasta</label>
              <input type="date" name="hasta" class="form-control" required>
            </div>
            <div class="form-group col-md-3 align-self-end">
              <button type="submit" class="btn btn-primary">Generar Reporte</button>
            </div>
          </div>
        </form>
        <?php if (!empty($_SESSION['success_message'])): ?>
          <div class="alert alert-success alert-dismissible mt-3">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="icon fas fa-check"></i>
            <?= $_SESSION['success_message'];
            unset($_SESSION['success_message']); ?>
          </div>
        <?php endif; ?>
        <table id="example1" class="table table-bordered table-striped table-sm">
          <thead>
            <tr>
              <th>Vigilador</th>
              <th class="text-center">Horas Diurnas</th>
              <th class="text-center">Horas Nocturnas</th>
              <th class="text-center">Guardias Diurnas</th>
              <th class="text-center">Guardias Nocturnas</th>
              <th class="text-center">Francos</th>
              <th class="text-center">Total Jornadas</th>
            </tr>
          </thead>

          <tbody>
            <?php if (count($rows)): ?>
              <?php foreach ($rows as $r): ?>
                <tr>
                  <td><?= htmlspecialchars($r['vigilador']) ?></td>
                  <td class="text-center"><?= number_format($r['diurnas'], 2) ?></td>
                  <td class="text-center"><?= number_format($r['nocturnas'], 2) ?></td>
                  <td class="text-center"><?= $r['guardias_diurnas'] ?></td>
                  <td class="text-center"><?= $r['guardias_nocturnas'] ?></td>
                  <td class="text-center"><?= $r['francos'] ?></td>
                  <td class="text-center"><?= $r['jornadas'] ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center text-muted">
                  No hay datos en ese período
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
          <tfoot class="bg-info text-white">
            <?php
            $totD = array_sum(array_column($rows, 'diurnas'));
            $totN = array_sum(array_column($rows, 'nocturnas'));
            $totJ = array_sum(array_column($rows, 'jornadas'));
            $totGPD = array_sum(array_column($rows, 'guardias_diurnas'));
            $totGPN = array_sum(array_column($rows, 'guardias_nocturnas'));
            $totF = array_sum(array_column($rows, 'francos'));
            ?>
            <tr>
              <td><strong>Totales:</strong></td>
              <td class="text-center"><?= number_format($totD, 2) ?></td>
              <td class="text-center"><?= number_format($totN, 2) ?></td>
              <td class="text-center"><?= number_format($totGPD, 2) ?></td>
              <td class="text-center"><?= number_format($totGPN, 2) ?></td>
              <td class="text-center"><?= $totF ?></td>
              <td class="text-center"><?= $totJ ?></td>
            </tr>
          </tfoot>
        </table>

        <?php unset($_SESSION['reporte_vigilador']); ?>
        <!-- Después de la tabla principal -->
        <?php if (!empty($_SESSION['diferencias_horarias'])): ?>
          <div class="card mt-4 border-warning">
            <div class="card-header bg-warning">
              <h4 class="card-title">Discrepancias Horarias</h4>
            </div>
            <div class="card-body">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th>Vigilador</th>
                    <th>Entrada Real</th>
                    <th>Entrada Turno</th>
                    <th>Salida Real</th>
                    <th>Salida Turno</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($_SESSION['diferencias_horarias'] as $d): ?>
                    <tr>
                      <td><?= $d['fecha'] ?></td>
                      <td><?= $d['vigilador'] ?></td>
                      <td><?= $d['entrada_real'] ?></td>
                      <td><?= $d['entrada_turno'] ?></td>
                      <td><?= $d['salida_real'] ?></td>
                      <td><?= $d['salida_turno'] ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php
          unset($_SESSION['diferencias_horarias']);
        endif;
        ?>
      </div>
    </div>
  </div>
</section>