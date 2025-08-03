<?php
//Auth::check('art', 'vistaListadoArt');
$db = new Conexion;
$arts = $db->consultas("SELECT * FROM art ORDER BY fecha_alta DESC");

// Eliminar si se recibió el POST
if (isset($_POST['idEliminar'])) {
  $idEliminar = $_POST['idEliminar'];
  $db->consultas("DELETE FROM art WHERE idArt = ?", [$idEliminar]);
  ToastifyController::success("ART eliminada correctamente.");
  echo "<script>window.location.href = '?r=listado_art';</script>";
  exit;
}
?>

<div class="card">
  <div class="card-header bg-success text-white">
    <h3 class="card-title">Listado de ART registradas</h3>
  </div>

  <div class="card-body table-responsive">
    <?php if (!empty($arts)): ?>
      <table class="table table-bordered table-hover table-sm">
        <thead class="thead-light">
          <tr>
            <th>Razón Social</th>
            <th>CUIT Empresa</th>
            <th>Tel. Empresa</th>
            <th>Aseguradora</th>
            <th>CUIT Aseguradora</th>
            <th>Póliza</th>
            <th>Tel. Aseguradora</th>
            <th>Fecha Alta</th>
            <th class="text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($arts as $art): ?>
            <tr>
              <td><?= htmlspecialchars($art['razon_social']) ?></td>
              <td><?= $art['cuit_empresa'] ?></td>
              <td><?= $art['telefono_empresa'] ?></td>
              <td><?= htmlspecialchars($art['empresa_aseguradora']) ?></td>
              <td><?= $art['cuit_aseguradora'] ?></td>
              <td><?= $art['nro_poliza'] ?></td>
              <td><?= $art['telefono_aseguradora'] ?></td>
              <td><?= date('d/m/Y', strtotime($art['fecha_alta'])) ?></td>
              <td style="vertical-align: middle; text-align: center;">
                <div class="d-flex justify-content-center">
                  <!-- Botón Editar -->
                  <a href="?r=editar_art&id=<?= $art['idArt'] ?>" class="btn btn-success btn-sm mr-1" title="Editar ART">
                    <i class="fas fa-edit"></i>
                  </a>

                  <!-- Botón Eliminar -->
                  <form method="post" style="display:inline-block;">
                    <input type="hidden" name="idEliminar" value="<?= $art['idArt'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm" title="Eliminar ART"
                      onclick="return confirm('¿Seguro que deseas eliminar PERMANENTEMENTE esta ART?');">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="alert alert-info">No hay ART registradas.</div>
    <?php endif; ?>
  </div>
</div>
