<?php
Auth::check('datos_personales', 'verMisDatos');

$idUsuario   = $_SESSION['idUsuario'] ?? 0;
$db           = new Conexion;

// Cargar datos existentes
$datos       = $db->consultas(
    "SELECT * FROM datos_personales WHERE usuario_id = {$idUsuario} LIMIT 1"
)[0] ?? [];
$usuario     = $db->consultas(
    "SELECT nombre, apellido, f_nac, dni 
     FROM usuarios 
     WHERE idUsuario = {$idUsuario} 
     LIMIT 1"
)[0] ?? [];

// Nivel del usuario logueado
$nivelSesion = isset($_SESSION['nivel']) 
    ? floatval($_SESSION['nivel']) 
    : 1.0;

// Niveles 1.0 y 2.0 tienen campos bloqueados si ya hay valor
$isBlocked   = in_array($nivelSesion, [1.0, 2.0], true);

// Helpers para bloquear inputs y selects
function lockIfFilled($blocked, $value) {
    return $blocked && trim((string)$value) !== '' 
        ? 'readonly' 
        : '';
}
function disableIfFilled($blocked, $value) {
    return $blocked && trim((string)$value) !== '' 
        ? 'disabled' 
        : '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    DatosPersonalesController::guardarDatos();
}
?>

<div class="card">
  <div class="card-header bg-info text-white">
    <h3 class="card-title">Mis Datos Personales adicionales</h3>
  </div>
  <div class="card-body">
    <form method="POST">
      <input type="hidden" name="usuario_id" value="<?= (int)$idUsuario ?>">

      <!-- Datos básicos (lectura) -->
      <h5>Datos básicos</h5>
      <div class="form-row">
        <div class="form-group col-md-3">
          <label>Nombre</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>" readonly>
        </div>
        <div class="form-group col-md-3">
          <label>Apellido</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['apellido'] ?? '') ?>" readonly>
        </div>
        <div class="form-group col-md-2">
          <label>Fecha de nacimiento</label>
          <input type="date" class="form-control" value="<?= htmlspecialchars($usuario['f_nac'] ?? '') ?>" readonly>
        </div>
        <div class="form-group col-md-2">
          <label>DNI</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['dni'] ?? '') ?>" readonly>
        </div>
        <div class="form-group col-md-2">
          <label for="estado_civil">Estado Civil</label>
          <?php $ecValue = $datos['estado_civil'] ?? ''; ?>
          <select
            name="estado_civil"
            id="estado_civil"
            class="form-control"
            required
            <?= disableIfFilled($isBlocked, $ecValue) ?>
          >
            <?php
            $estados = [
              'soltero','casado','divorciado',
              'separado','viudo','conviviente'
            ];
            foreach ($estados as $estado):
              $sel = $ecValue === $estado ? 'selected' : '';
            ?>
              <option value="<?= $estado ?>" <?= $sel ?>>
                <?= ucfirst($estado) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <hr>

      <!-- Información de contacto -->
      <h5>Información de contacto</h5>
      <?php $email = $datos['email'] ?? ''; ?>
      <div class="form-group">
        <label for="email">Correo electrónico</label>
        <input
          type="email"
          class="form-control"
          name="email"
          value="<?= htmlspecialchars($email) ?>"
          data-optional="true"
          <?= lockIfFilled($isBlocked, $email) ?>
        >
      </div>

      <hr>

      <!-- Pareja -->
      <h5>Pareja</h5>
      <div class="form-row">
        <?php
          $pn  = $datos['pareja_nombre']      ?? '';
          $pfn = $datos['pareja_nacimiento'] ?? '';
          $pdi = $datos['pareja_dni']        ?? '';
        ?>
        <div class="form-group col-md-6">
          <label>Nombre de la pareja</label>
          <input
            type="text"
            name="pareja_nombre"
            class="form-control"
            value="<?= htmlspecialchars($pn) ?>"
            data-optional="true"
            <?= lockIfFilled($isBlocked, $pn) ?>
          >
        </div>
        <div class="form-group col-md-2">
          <label>Fecha de nacimiento</label>
          <input
            type="date"
            name="pareja_nacimiento"
            class="form-control"
            value="<?= htmlspecialchars($pfn) ?>"
            data-optional="true"
            <?= lockIfFilled($isBlocked, $pfn) ?>
          >
        </div>
        <div class="form-group col-md-1">
          <label>DNI</label>
          <input
            type="text"
            name="pareja_dni"
            class="form-control"
            value="<?= htmlspecialchars($pdi) ?>"
            pattern="^\d{1,8}$"
            title="Debe contener hasta 8 dígitos numéricos"
            data-optional="true"
            <?= lockIfFilled($isBlocked, $pdi) ?>
          >
        </div>
      </div>

      <hr>

      <!-- Nivel de estudio -->
      <h5>Nivel de Estudio</h5>
      <?php $ne = $datos['nivel_estudio'] ?? ''; ?>
      <div class="form-group">
        <label for="nivel_estudio">Seleccione su nivel de estudio</label>
        <select
          name="nivel_estudio"
          id="nivel_estudio"
          class="form-control"
          required
          <?= disableIfFilled($isBlocked, $ne) ?>
        >
          <?php
          $nivelesEstudio = [
            'primario_incompleto'       => 'Primario Incompleto',
            'primario_completo'         => 'Primario Completo',
            'secundario_incompleto'     => 'Secundario Incompleto',
            'secundario_completo'       => 'Secundario Completo',
            'terciario_incompleto'      => 'Terciario Incompleto',
            'terciario_completo'        => 'Terciario Completo',
            'universitario_incompleto'  => 'Universitario Incompleto',
            'universitario_completo'    => 'Universitario Completo'
          ];
          foreach ($nivelesEstudio as $value => $label):
            $sel = $ne === $value ? 'selected' : '';
          ?>
            <option value="<?= $value ?>" <?= $sel ?>>
              <?= $label ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (disableIfFilled($isBlocked, $ne)): ?>
          <input type="hidden" name="nivel_estudio" value="<?= htmlspecialchars($ne) ?>">
        <?php endif; ?>
      </div>

      <?php
      // Bloques JSON: hijos, hijos_adoptivos, tutores, hermanos...
      $bloques = [
        'hijos'               => 'Hijos',
        'hijos_adoptivos'     => 'Hijos Adoptivos',
        'padres'              => 'Padres',
        'hermanos'            => 'Hermanos',
        'tutores_discapacidad'=> 'Tutores con Discapacidad'
      ];
      foreach ($bloques as $key => $label):
        $datosJSON = $datos[$key] ?? '[]';
        $items     = json_decode($datosJSON, true) ?? [];
        $hasItems  = count($items) > 0;
        $canModify = !($isBlocked && $hasItems);
      ?>
        <hr>
        <h5><?= $label ?></h5>
        <div class="form-group">
          <div id="contenedor_<?= $key ?>">
            <?php foreach ($items as $i => $item):
              $nombre    = $item['nombre']    ?? '';
              $nacimiento= $item['nacimiento']?? '';
              $dniItem   = $item['dni']       ?? '';
              $fallecido = !empty($item['fallecido']);
            ?>
              <div class="form-row mb-2 align-items-end">
                <div class="col-md-3">
                  <input
                    type="text"
                    name="<?= $key ?>[<?= $i ?>][nombre]"
                    class="form-control"
                    placeholder="Nombre"
                    value="<?= htmlspecialchars($nombre) ?>"
                    data-optional="true"
                    <?= $isBlocked && trim($nombre) !== '' ? 'readonly' : '' ?>
                  >
                </div>
                <div class="col-md-2">
                  <input
                    type="date"
                    name="<?= $key ?>[<?= $i ?>][nacimiento]"
                    class="form-control"
                    value="<?= htmlspecialchars($nacimiento) ?>"
                    data-optional="true"
                    <?= $isBlocked && trim($nacimiento) !== '' ? 'readonly' : '' ?>
                  >
                </div>
                <div class="col-md-2">
                  <input
                    type="text"
                    name="<?= $key ?>[<?= $i ?>][dni]"
                    class="form-control"
                    placeholder="DNI"
                    value="<?= htmlspecialchars($dniItem) ?>"
                    pattern="^\d{1,8}$"
                    title="Hasta 8 dígitos numéricos"
                    data-optional="true"
                    <?= $isBlocked && trim($dniItem) !== '' ? 'readonly' : '' ?>
                  >
                </div>
                <?php if ($key === 'padres'): ?>
                  <div class="col-md-2">
                    <div class="form-check">
                      <input
                        type="checkbox"
                        name="<?= $key ?>[<?= $i ?>][fallecido]"
                        id="<?= $key ?>_fallecido_<?= $i ?>"
                        class="form-check-input"
                        <?= $fallecido ? 'checked' : '' ?>
                        <?= disableIfFilled($isBlocked, $item['fallecido'] ?? '') ?>
                      >
                      <label class="form-check-label" for="<?= $key ?>_fallecido_<?= $i ?>">
                        Fallecido
                      </label>
                    </div>
                  </div>
                <?php endif; ?>

                <?php if ($canModify): ?>
                  <div class="col-md-2 text-right">
                    <button type="button" class="btn btn-danger btn-sm eliminarFila">&times;</button>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>

          <?php if ($canModify): ?>
            <button
              type="button"
              class="btn btn-sm btn-outline-primary"
              onclick="agregarCampo('contenedor_<?= $key ?>','<?= $key ?>')"
            >
              Agregar <?= strtolower($label) ?>
            </button>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>

      <div class="form-group text-right mt-4">
        <button
          type="submit"
          class="btn btn-success"
          <?= $isBlocked && !empty($datos) ? 'disabled' : '' ?>
        >
          Guardar datos
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function agregarCampo(containerId, tipo) {
  const contenedor = document.getElementById(containerId);
  const index      = contenedor.querySelectorAll('.form-row').length;
  const div        = document.createElement('div');
  div.className    = 'form-row mb-2 align-items-end';

  let html = `
    <div class="col-md-3">
      <input type="text" name="\${tipo}[\${index}][nombre]" class="form-control" placeholder="Nombre" data-optional="true">
    </div>
    <div class="col-md-2">
      <input type="date" name="\${tipo}[\${index}][nacimiento]" class="form-control" data-optional="true">
    </div>
    <div class="col-md-2">
      <input type="text" name="\${tipo}[\${index}][dni]" class="form-control" placeholder="DNI" pattern="^\\d{1,8}$" title="Hasta 8 dígitos numéricos" data-optional="true">
    </div>
  `;

  // Si es bloque 'padres', agregamos checkbox de fallecido
  if (tipo === 'padres') {
    html += `
      <div class="col-md-2">
        <div class="form-check">
          <input type="checkbox" name="\${tipo}[\${index}][fallecido]" id="\${tipo}_fallecido_\${index}" class="form-check-input">
          <label class="form-check-label" for="\${tipo}_fallecido_\${index}">Fallecido</label>
        </div>
      </div>
    `;
  }

  // Botón de eliminar solo si permitimos modificar
  html += `
    <div class="col-md-2 text-right">
      <button type="button" class="btn btn-danger btn-sm eliminarFila">&times;</button>
    </div>
  `;

  div.innerHTML = html;
  contenedor.appendChild(div);
}

document.addEventListener('click', function(e) {
  if (e.target.classList.contains('eliminarFila')) {
    const fila = e.target.closest('.form-row');
    if (fila) fila.remove();
  }
});

// Validación de DNI
document.querySelector('form').addEventListener('submit', function(e) {
  const dniInputs = this.querySelectorAll('input[pattern]');
  let valid = true;

  dniInputs.forEach(input => {
    const val = input.value.trim();
    if (val && !/^\d{1,8}$/.test(val)) {
      valid = false;
      input.classList.add('is-invalid');
      if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('invalid-feedback')) {
        const fb = document.createElement('div');
        fb.className    = 'invalid-feedback';
        fb.textContent = 'El DNI debe tener solo números y hasta 8 dígitos sin puntos.';
        input.after(fb);
      }
    }
  });

  if (!valid) {
    e.preventDefault();
    alert('Corregí los campos de DNI antes de guardar.');
  }
});
</script>
