<?php
$nivelSesion = isset($_SESSION['nivel']) ? (float) $_SESSION['nivel'] : 1.0;

// Si viene un ID por GET y el usuario tiene permisos de gestión
if (!empty($_GET['id']) && in_array($nivelSesion, [3.0, 4.0, 5.0, 99.0], true)) {
  $idUsuario = (int) $_GET['id'];
} else {
  // Caso normal: vigilador viendo su propio uniforme
  $idUsuario = $_SESSION['idUsuario'];
}
$db = new Conexion;

// Usuario
$resultUsuario = $db->consultas("SELECT nombre, apellido FROM usuarios WHERE idUsuario = $idUsuario LIMIT 1");
$usuario = $resultUsuario[0] ?? null;
// Talles actuales
$uniforme = $db->consultas("SELECT * FROM uniformes WHERE usuario_id = $idUsuario LIMIT 1")[0] ?? null;

// Categorías
$categorias = $db->consultas("SELECT * FROM uniforme_categorias ORDER BY nombre");

// Items activos
$items = ModeloUniformeItems::listar(true);

// Entregas del usuario
$entregas = ModeloUniformeEntregas::listarPorUsuario($idUsuario);

// Seguridad
$nivelSesion   = isset($_SESSION['nivel']) ? (float) $_SESSION['nivel'] : 1.0;
$puedeGestionar = in_array($nivelSesion, [3.0, 4.0, 5.0, 99.0], true);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_talles'])) {
  UniformesController::guardarUniforme();
}
?>

<div class="card shadow">
  <div class="card-header bg-info text-white">
    <h3 class="card-title">
      Mi Uniforme — <b><?= $usuario['apellido'] . ', ' . $usuario['nombre'] ?> </b>
    </h3>

  </div>

  <div class="card-body">
    <div class="row">
      <!-- SECCIÓN 1 — TALLES ACTUALES -->
      <div class="card mb-4 shadow-sm col-12 shadow">
        <div class="card-header bg-primary text-white">
          <h6 class="mb-0">Mis talles</h6>
        </div>

        <div class="card-body">
          <form method="POST">
            <input type="hidden" name="usuario_id" value="<?= $idUsuario ?>">
            <input type="hidden" name="guardar_talles" value="1">

            <div class="form-row">

              <!-- Pantalón -->
              <div class="form-group col-sm-6 col-md-2">
                <label>Pantalón</label>
                <select name="talle_pantalon" class="form-control" required>
                  <option value="" disabled selected>Seleccionar</option>
                  <?php for ($i = 30; $i <= 60; $i++): ?>
                    <option value="<?= $i ?>" <?= ($uniforme['talle_pantalon'] ?? '') == $i ? 'selected' : '' ?>><?= $i ?></option>
                  <?php endfor; ?>
                </select>
              </div>

              <!-- Calzado -->
              <div class="form-group col-sm-6 col-md-2">
                <label>Calzado</label>
                <select name="talle_calzado" class="form-control" required>
                  <option value="" disabled selected>Seleccionar</option>
                  <?php for ($i = 30; $i <= 50; $i++): ?>
                    <option value="<?= $i ?>" <?= ($uniforme['talle_calzado'] ?? '') == $i ? 'selected' : '' ?>><?= $i ?></option>
                  <?php endfor; ?>
                </select>
              </div>

              <!-- Remera -->
              <div class="form-group col-sm-6 col-md-2">
                <label>Remera</label>
                <select name="talle_remera" class="form-control" required>
                  <option value="" disabled selected>Seleccionar</option>
                  <?php
                  $talles = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '4XL', '5XL', '6XL'];
                  foreach ($talles as $t):
                    $sel = ($uniforme['talle_remera'] ?? '') === $t ? 'selected' : '';
                    echo "<option value='$t' $sel>$t</option>";
                  endforeach;
                  ?>
                </select>
              </div>

              <!-- Polar -->
              <div class="form-group col-sm-6 col-md-2">
                <label>Polar</label>
                <select name="talle_polar" class="form-control" required>
                  <option value="" disabled selected>Seleccionar</option>
                  <?php foreach ($talles as $t): ?>
                    <option value="<?= $t ?>" <?= ($uniforme['talle_polar'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Campera -->
              <div class="form-group col-sm-6 col-md-2">
                <label>Campera</label>
                <select name="talle_campera" class="form-control" required>
                  <option value="" disabled selected>Seleccionar</option>
                  <?php foreach ($talles as $t): ?>
                    <option value="<?= $t ?>" <?= ($uniforme['talle_campera'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

            </div>

            <button type="submit" class="btn btn-success mt-2"> Guardar talles</button>
          </form>
        </div>
      </div>
      <?php if ($puedeGestionar): ?>

        <!-- SECCIÓN 2 — REGISTRAR ENTREGA -->
        <div class="card mb-4 shadow-sm col-12 shadow">
          <div class="card-header bg-success text-white">
            <h6 class="mb-0">Registrar entrega</h6>
          </div>

          <div class="card-body">

            <form method="POST" action="?r=registrar_entrega_uniforme_multiple">

              <input type="hidden" name="usuario_id" value="<?= $idUsuario ?>">

              <!-- CONTENEDOR DE FILAS DINÁMICAS -->
              <div id="contenedor_entregas"></div>

              <!-- BOTÓN AGREGAR FILA -->
              <button type="button" class="btn btn-primary mt-3" onclick="agregarFilaEntrega()">
                <i class="fas fa-plus-circle"></i> Agregar ítem
              </button>

              <!-- BOTÓN GUARDAR TODO -->
              <button type="submit" class="btn btn-success mt-3 ml-2">
                Guardar entregas
              </button>

            </form>

          </div>
        </div>

        <!-- SECCIÓN 3 — HISTORIAL DE ENTREGAS -->
        <div class="card shadow-sm col-12 shadow">
          <div class="card-header bg-secondary text-white">
            <h6 class="mb-0">Historial de entregas</h6>
          </div>

          <div class="card-body">

            <?php if (empty($entregas)): ?>
              <p class="text-muted">No hay entregas registradas.</p>
            <?php else: ?>

              <table class="table table-bordered table-striped">
                <thead class="bg-secondary text-white">
                  <tr>
                    <th>Fecha</th>
                    <th>Categoría</th>
                    <th>Ítem</th>
                    <th>Talle</th>
                    <th>Cant.</th>
                    <th>Observaciones</th>
                    <th>Devolución</th>
                    <th>Acción</th>
                  </tr>
                </thead>

                <tbody>
                  <?php foreach ($entregas as $e): ?>
                    <?php $dev = ModeloUniformeDevoluciones::buscarPorEntrega($e['id']); ?>
                    <tr>
                      <td><?= $e['fecha_entrega'] ?></td>
                      <td><?= $e['categoria_nombre'] ?></td>
                      <td><?= $e['item_nombre'] ?: $e['item_libre'] ?></td>
                      <td><?= $e['talle'] ?></td>
                      <td><?= $e['cantidad'] ?></td>
                      <td><?= $e['observaciones'] ?></td>

                      <td>
                        <?php if ($dev): ?>
                          <span class="badge badge-success">Devuelto</span><br>
                          <small><?= $dev['fecha_devolucion'] ?></small>
                        <?php else: ?>
                          <span class="badge badge-warning">Pendiente</span>
                        <?php endif; ?>
                      </td>

                      <td>
                        <?php if (!$dev): ?>
                          <button class="btn btn-sm btn-primary" data-toggle="modal"
                            data-target="#modalDevolucion<?= $e['id'] ?>">
                            Registrar devolución
                          </button>
                        <?php endif; ?>
                      </td>
                    </tr>

                    <!-- Modal de devolución -->
                    <div class="modal fade" id="modalDevolucion<?= $e['id'] ?>">
                      <div class="modal-dialog">
                        <div class="modal-content">

                          <form method="POST" action="?r=registrar_devolucion_uniforme">
                            <input type="hidden" name="usuario_id" value="<?= $idUsuario ?>">
                            <div class="modal-header bg-primary text-white">
                              <h5 class="modal-title">Registrar devolución</h5>
                              <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>

                            <div class="modal-body">

                              <input type="hidden" name="entrega_id" value="<?= $e['id'] ?>">

                              <div class="form-group">
                                <label>Fecha de devolución</label>
                                <input type="date" name="fecha_devolucion" class="form-control" required>
                              </div>

                              <div class="form-group">
                                <label>Estado</label>
                                <select name="estado_devolucion" class="form-control" required>
                                  <option value="bueno">Bueno</option>
                                  <option value="regular">Regular</option>
                                  <option value="malo">Malo</option>
                                  <option value="inservible">Inservible</option>
                                </select>
                              </div>

                              <div class="form-group">
                                <label>Recibido por</label>
                                <input type="text" name="recibido_por" class="form-control" required>
                              </div>

                              <div class="form-group">
                                <label>Observaciones</label>
                                <textarea name="observaciones" class="form-control"></textarea>
                              </div>

                            </div>

                            <div class="modal-footer">
                              <button class="btn btn-success">Guardar</button>
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            </div>

                          </form>

                        </div>
                      </div>
                    </div>

                  <?php endforeach; ?>
                </tbody>
              </table>

            <?php endif; ?>

          </div>
        </div>
      <?php endif; ?>

    </div>


  </div>
</div>

<script>
  let contador = 0;

  // ============================================================
  // AGREGAR FILA
  // ============================================================
  function agregarFilaEntrega() {

    const contenedor = document.getElementById('contenedor_entregas');

    const fila = document.createElement('div');
    fila.className = "card p-3 mb-3 border";
    fila.id = "fila_" + contador;

    fila.innerHTML = `
    <div class="form-row">

      <!-- Categoría -->
      <div class="form-group col-md-2">
        <label>Categoría</label>
        <select name="entregas[${contador}][categoria_id]" 
                class="form-control categoria_select" required>
          <option value="" disabled selected>Seleccionar</option>
          <?php foreach ($categorias as $c): ?>
            <option value="<?= $c['id'] ?>"><?= $c['nombre'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Ítem -->
      <div class="form-group col-md-2">
        <label>Ítem</label>
        <select name="entregas[${contador}][item_id]" 
                class="form-control item_select" required>
          <option value="" disabled selected>Seleccionar</option>
          <?php foreach ($items as $i): ?>
            <option value="<?= $i['id'] ?>" data-cat="<?= $i['categoria_id'] ?>">
              <?= $i['nombre'] ?>
            </option>
          <?php endforeach; ?>
          <option value="otro">Otro (especificar)</option>
        </select>
      </div>

      <!-- Ítem libre -->
      <div class="form-group col-md-2 item_libre_box" style="display:none;">
        <label>Especificar ítem</label>
        <input type="text" name="entregas[${contador}][item_libre]" class="form-control" data-optional="true">
      </div>

      <!-- Talle -->
      <div class="form-group col-md-1">
        <label>Talle</label>
        <input type="text" name="entregas[${contador}][talle]" class="form-control" data-optional="true">
      </div>

      <!-- Fecha -->
      <div class="form-group col-md-2">
        <label>Fecha de entrega</label>
        <input type="date" name="entregas[${contador}][fecha_entrega]" 
               class="form-control fecha_entrega" required>
      </div>

      <!-- Cantidad -->
      <div class="form-group col-md-2">
        <label>Cantidad</label>
        <input type="number" name="entregas[${contador}][cantidad]" 
               class="form-control" min="1" value="1">
      </div>

      <!-- Entregado por -->
      <div class="form-group col-md-3">
        <label>Entregado por</label>
        <input type="text" name="entregas[${contador}][entregado_por]" 
               class="form-control entregado_por">
      </div>

      <div class="form-group col-md-11">
        <label>Observaciones</label>
          <textarea name="entregas[${contador}][observaciones]" 
                  class="form-control" rows="2" data-optional="true"></textarea>
      </div>

      <!-- ELIMINAR -->
        <div class="form-group col-md-1 d-flex align-items-end">
          <button type="button" class="btn btn-danger btn-sm" onclick="eliminarFila(${contador})">
            <i class="fas fa-trash"></i>
          </button>
        </div>
    </div>
  `;

    contenedor.appendChild(fila);

    // AUTOCOMPLETAR FECHA Y ENTREGADO_POR
    autocompletarCampos(contador);

    // EVENTOS
    activarEventosFila(fila);

    contador++;
  }

  // ============================================================
  // ELIMINAR FILA
  // ============================================================
  function eliminarFila(id) {
    document.getElementById("fila_" + id).remove();
  }

  // ============================================================
  // AUTOCOMPLETAR FECHA Y ENTREGADO_POR
  // ============================================================
  function autocompletarCampos(index) {

    const primeraFecha = document.querySelector(".fecha_entrega");
    const primerEntregado = document.querySelector(".entregado_por");

    const nuevaFecha = document.querySelector(`input[name="entregas[${index}][fecha_entrega]"]`);
    const nuevoEntregado = document.querySelector(`input[name="entregas[${index}][entregado_por]"]`);

    if (primeraFecha && primeraFecha.value) {
      nuevaFecha.value = primeraFecha.value;
    }

    if (primerEntregado && primerEntregado.value) {
      nuevoEntregado.value = primerEntregado.value;
    }
  }

  // ============================================================
  // ACTIVAR EVENTOS DE FILA
  // ============================================================
  function activarEventosFila(fila) {

    // Mostrar item libre
    fila.querySelector(".item_select").addEventListener("change", function() {
      const libre = fila.querySelector(".item_libre_box");
      libre.style.display = this.value === "otro" ? "block" : "none";
    });

    // Filtrar ítems por categoría
    fila.querySelector(".categoria_select").addEventListener("change", function() {
      const cat = this.value;
      const itemSelect = fila.querySelector(".item_select");

      [...itemSelect.options].forEach(opt => {
        if (!opt.dataset.cat) return;
        opt.style.display = (opt.dataset.cat === cat) ? "block" : "none";
      });

      itemSelect.value = "";
      fila.querySelector(".item_libre_box").style.display = "none";
    });
  }
</script>