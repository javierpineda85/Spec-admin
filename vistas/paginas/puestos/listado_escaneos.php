<div class="card">
  <div class="card-header bg-info text-white">
    <h4 class="mb-0">Listado de Escaneos</h4>
  </div>
  <div class="card-body table-responsive">
    <table class="table table-striped table-bordered" id="example1">
      <thead class="">
        <tr>          
          <th>Fecha y Hora</th>
          <th>Ronda</th>
          <th>Sector</th>
          <th>Vigilador</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($escaneos as $i => $e): ?>
          <tr>
           
            <td><?= date('d/m/Y H:i', strtotime($e['fecha_hora'])) ?></td>
            <td><?= $e['nombre_ronda'] ?? '-' ?></td>
            <td><?= $e['nombre_sector'] ?? '-' ?></td>
            <td><?= $e['apellido'] . ', ' . $e['nombre'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

