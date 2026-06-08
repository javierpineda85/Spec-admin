<?php

$idUsuario = isset($_GET['id']) ? intval($_GET['id']) : 0;
$db = new Conexion;

// Cargar datos existentes
$datos = $db->consultas("SELECT * FROM datos_personales WHERE usuario_id = {$idUsuario} LIMIT 1")[0] ?? [];
$usuario = $db->consultas(
  "SELECT nombre, apellido, f_nac, dni 
     FROM usuarios 
     WHERE idUsuario = {$idUsuario} 
     LIMIT 1"
)[0] ?? [];

// Nivel del usuario logueado
$nivelSesion = isset($_SESSION['nivel']) ? floatval($_SESSION['nivel']) : 1.0;

// Niveles 1.0 y 2.0 tienen campos bloqueados si ya hay valor
$isBlocked   = in_array($nivelSesion, [1.0, 2.0], true);

// Helpers para bloquear inputs y selects
function lockIfFilled($blocked, $value)
{
  return $blocked && trim((string)$value) !== '' ? 'readonly' : '';
}
function disableIfFilled($blocked, $value)
{
  return $blocked && trim((string)$value) !== '' ? 'disabled' : '';
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
      <div class="row">

        <!-- Datos básicos -->
        <div class="col-lg-6 col-md-12 mb-4">
          <div class="card card-widget widget-user info-box shadow">
            <div class="card-header bg-info text-white">
              <h6 class="mb-0"><i class="fas fa-user"></i> Datos básicos</h6>
            </div>
            <div class="card-body">

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label>Nombre</label>
                  <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>" readonly>
                </div>
                <div class="form-group col-md-6">
                  <label>Apellido</label>
                  <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['apellido'] ?? '') ?>" readonly>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-4">
                  <label>Fecha de nacimiento</label>
                  <input type="date" class="form-control" value="<?= htmlspecialchars($usuario['f_nac'] ?? '') ?>" readonly>
                </div>
                <div class="form-group col-md-4">
                  <label>DNI</label>
                  <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['dni'] ?? '') ?>" readonly>
                </div>
                <div class="form-group col-md-4">
                  <label>Estado civil</label>
                  <?php $ecValue = $datos['estado_civil'] ?? ''; ?>
                  <select name="estado_civil" class="form-control" required>
                    <?php
                    $estados = ['soltero', 'casado', 'divorciado', 'separado', 'viudo', 'conviviente'];
                    foreach ($estados as $estado):
                      $sel = $ecValue === $estado ? 'selected' : '';
                    ?>
                      <option value="<?= $estado ?>" <?= $sel ?>><?= ucfirst($estado) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="form-row">


                <div class="form-group col-md-8">
                  <label>Correo electrónico</label>
                  <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($datos['email'] ?? '') ?>">
                </div>

                <div class="form-group col-md-4">
                  <label>Nivel de estudio</label>
                  <?php $ne = $datos['nivel_estudio'] ?? ''; ?>
                  <select name="nivel_estudio" class="form-control" required>
                    <?php
                    $niveles = [
                      'primario_incompleto' => 'Primario Incompleto',
                      'primario_completo' => 'Primario Completo',
                      'secundario_incompleto' => 'Secundario Incompleto',
                      'secundario_completo' => 'Secundario Completo',
                      'terciario_incompleto' => 'Terciario Incompleto',
                      'terciario_completo' => 'Terciario Completo',
                      'universitario_incompleto' => 'Universitario Incompleto',
                      'universitario_completo' => 'Universitario Completo'
                    ];
                    foreach ($niveles as $value => $label):
                      $sel = $ne === $value ? 'selected' : '';
                    ?>
                      <option value="<?= $value ?>" <?= $sel ?>><?= $label ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- DATOS DE PAREJA -->
        <div class="col-lg-6 col-md-12 mb-4">
          <div class="card card-widget widget-user info-box shadow">
            <div class="card-header bg-warning text-dark">
              <h6 class="mb-0"><i class="fas fa-heart"></i> Datos de pareja</h6>
            </div>
            <div class="card-body">
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label>Nombre</label>
                  <input type="text" name="pareja_nombre" class="form-control" value="<?= htmlspecialchars($datos['pareja_nombre'] ?? '') ?>">
                </div>
                <div class="form-group col-md-4">
                  <label>Fecha de nacimiento</label>
                  <input type="date" name="pareja_nacimiento" class="form-control" value="<?= htmlspecialchars($datos['pareja_nacimiento'] ?? '') ?>">
                </div>
                <div class="form-group col-md-4">
                  <label>DNI</label>
                  <input type="text" name="pareja_dni" class="form-control" value="<?= htmlspecialchars($datos['pareja_dni'] ?? '') ?>">
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- DATOS FAMILIARES -->
        <div class="col-lg-6 col-md-12 mb-4">
          <div class="card card-widget widget-user info-box shadow">
            <div class="card-header bg-success text-white">
              <h6 class="mb-0"><i class="fas fa-users"></i> Datos familiares</h6>
            </div>
            <div class="card-body">
              <?php
              $bloques = [
                'hijos' => 'Hijos',
                'hijos_adoptivos' => 'Hijos Adoptivos',
                'padres' => 'Padres',
                'hermanos' => 'Hermanos',
                'tutores_discapacidad' => 'Tutores con Discapacidad'
              ];
              foreach ($bloques as $key => $label):
                $items = json_decode($datos[$key] ?? '[]', true) ?? [];
              ?>
                <h6 class="mt-3"><?= $label ?></h6>
                <div id="contenedor_<?= $key ?>">
                  <?php foreach ($items as $i => $item): ?>
                    <div class="form-row mb-2 align-items-end">
                      <div class="col-md-3">
                        <input type="text" name="<?= $key ?>[<?= $i ?>][nombre]" class="form-control" placeholder="Nombre" value="<?= htmlspecialchars($item['nombre'] ?? '') ?>">
                      </div>
                      <div class="col-md-3">
                        <input type="date" name="<?= $key ?>[<?= $i ?>][nacimiento]" class="form-control" value="<?= htmlspecialchars($item['nacimiento'] ?? '') ?>">
                      </div>
                      <div class="col-md-3">
                        <input type="text" name="<?= $key ?>[<?= $i ?>][dni]" class="form-control" placeholder="DNI" value="<?= htmlspecialchars($item['dni'] ?? '') ?>">
                      </div>
                      <?php if ($key === 'padres'): ?>
                        <div class="col-md-2">
                          <div class="form-check">
                            <input type="checkbox" name="<?= $key ?>[<?= $i ?>][fallecido]" class="form-check-input" <?= !empty($item['fallecido']) ? 'checked' : '' ?>>
                            <label class="form-check-label">Fallecido</label>
                          </div>
                        </div>
                      <?php endif; ?>

                      <div class="ml-auto pr-2">
                        <button type="button" class="btn btn-danger btn-sm eliminarFila">&times;</button>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-outline-success btn-sm mt-2" onclick="agregarCampo('contenedor_<?= $key ?>','<?= $key ?>')">
                  Agregar <?= strtolower($label) ?>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

      </div>

      <div class="form-group text-right mt-4">
        <button
          type="submit"
          class="btn btn-success"
          <?= $isBlocked && !empty($datos) ? 'disabled' : '' ?>>
          Guardar datos
        </button>
      </div>

    </form>
  </div>
</div>

<script>
  function agregarCampo(containerId, tipo) {
    const contenedor = document.getElementById(containerId);
    const index = contenedor.querySelectorAll('.form-row').length;
    const div = document.createElement('div');
    div.className = 'form-row mb-2 align-items-end';

    let html = `
    <div class="col-md-3">
      <input type="text" name="${tipo}[${index}][nombre]" class="form-control" placeholder="Nombre">
    </div>
    <div class="col-md-3">
      <input type="date" name="${tipo}[${index}][nacimiento]" class="form-control">
    </div>
    <div class="col-md-3">
      <input type="text" name="${tipo}[${index}][dni]" class="form-control" placeholder="DNI">
    </div>
  `;

    if (tipo === 'padres') {
      html += `
      <div class="col-md-2">
        <div class="form-check">
          <input type="checkbox" name="${tipo}[${index}][fallecido]" class="form-check-input">
          <label class="form-check-label">Fallecido</label>
        </div>
      </div>
    `;
    }

    html += `
    <div class="ml-auto pr-2">
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
          fb.className = 'invalid-feedback';
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
  document.querySelector('form').addEventListener('submit', function() {
    const grupos = ['hijos', 'hijos_adoptivos', 'padres', 'hermanos', 'tutores_discapacidad'];

    grupos.forEach(grupo => {
      const cont = document.getElementById('contenedor_' + grupo);
      const filas = cont.querySelectorAll('.form-row');

      if (filas.length === 0) {
        // Enviar array vacío
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = grupo;
        input.value = '[]';
        this.appendChild(input);
      }
    });
  });
</script>