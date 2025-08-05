<?php
Auth::check('datos_personales', 'verMisDatos');

$idUsuario = $_SESSION['idUsuario'] ?? 0;
$db = new Conexion;

$datos = $db->consultas("SELECT * FROM datos_personales WHERE usuario_id = $idUsuario LIMIT 1")[0] ?? null;
$usuario = $db->consultas("SELECT nombre, apellido, f_nac, dni FROM usuarios WHERE idUsuario = $idUsuario LIMIT 1")[0] ?? [];

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

            <input type="hidden" name="usuario_id" value="<?= $idUsuario ?>">

            <h5>Datos básicos</h5>
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Nombre</label>
                    <input type="text" class="form-control" value="<?= $usuario['nombre'] ?? '' ?>" readonly>
                </div>
                <div class="form-group col-md-3">
                    <label>Apellido</label>
                    <input type="text" class="form-control" value="<?= $usuario['apellido'] ?? '' ?>" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label>Fecha de nacimiento</label>
                    <input type="date" class="form-control" value="<?= $usuario['f_nac'] ?? '' ?>" readonly data-optional="true">
                </div>
                <div class="form-group col-md-1">
                    <label>DNI</label>
                    <input type="text" class="form-control" value="<?= $usuario['dni'] ?? '' ?>" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label for="estado_civil">Estado Civil</label>
                    <select name="estado_civil" id="estado_civil" class="form-control" required>
                        <?php
                        $estados = ['soltero', 'casado', 'divorciado', 'separado', 'viudo', 'conviviente'];
                        foreach ($estados as $estado) {
                            $sel = ($datos['estado_civil'] ?? '') === $estado ? 'selected' : '';
                            echo "<option value=\"$estado\" $sel>" . ucfirst($estado) . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <hr>
            <h5>Información de contacto</h5>
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" class="form-control" name="email" value="<?= $datos['email'] ?? '' ?>" data-optional="true">
            </div>
            <hr>
            <h5>Pareja</h5>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Nombre de la pareja</label>
                    <input type="text" name="pareja_nombre" class="form-control" value="<?= $datos['pareja_nombre'] ?? '' ?>" data-optional="true">
                </div>
                <div class="form-group col-md-2">
                    <label>Fecha de nacimiento</label>
                    <input type="date" name="pareja_nacimiento" class="form-control" value="<?= $datos['pareja_nacimiento'] ?? '' ?>" data-optional="true">
                </div>
                <div class="form-group col-md-1">
                    <label>DNI</label>
                    <input type="text" name="pareja_dni" class="form-control" value="<?= $datos['pareja_dni'] ?? '' ?>" pattern="^\d{1,8}$" title="Debe contener hasta 8 dígitos numéricos" data-optional="true">
                </div>
            </div>

            <?php
            $bloques = [
                'hijos' => 'Hijos',
                'hijos_adoptivos' => 'Hijos Adoptivos',
                'padres' => 'Padres',
                'hermanos' => 'Hermanos',
                'tutores_discapacidad' => 'Tutores con Discapacidad'
            ];

            foreach ($bloques as $key => $label):
                $datosJSON = $datos[$key] ?? '[]';
                $items = json_decode($datosJSON, true) ?? [];
            ?>
                <hr>
                <h5><?= $label ?></h5>
                <div class="form-group">
                    <div id="contenedor_<?= $key ?>">
                        <?php foreach ($items as $i => $item): ?>
                            <div class="form-row mb-2 align-items-end">
                                <div class="col-md-4">
                                    <input type="text" name="<?= $key ?>[<?= $i ?>][nombre]" class="form-control" value="<?= $item['nombre'] ?? '' ?>" placeholder="Nombre">
                                </div>
                                <div class="col-md-3">
                                    <input type="date" name="<?= $key ?>[<?= $i ?>][nacimiento]" class="form-control" value="<?= $item['nacimiento'] ?? '' ?>">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="<?= $key ?>[<?= $i ?>][dni]" class="form-control" value="<?= $item['dni'] ?? '' ?>" placeholder="DNI">
                                </div>
                                <div class="col-md-2 text-right">
                                    <button type="button" class="btn btn-danger btn-sm eliminarFila">&times;</button>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="agregarCampo('contenedor_<?= $key ?>', '<?= $key ?>')">Agregar <?= strtolower($label) ?></button>
                </div>
            <?php endforeach; ?>

            <div class="form-group text-right mt-4">
                <button type="submit" class="btn btn-success">Guardar datos</button>
            </div>
        </form>
    </div>
</div>

<script>
    function agregarCampo(containerId, tipo) {
        const contenedor = document.getElementById(containerId);
        const index = contenedor.querySelectorAll('.form-row').length;
        const div = document.createElement('div');
        div.classList.add('form-row', 'mb-2', 'align-items-end');
        div.innerHTML = `
      <div class="col-md-4">
        <input type="text" name="${tipo}[${index}][nombre]" class="form-control" placeholder="Nombre" data-optional="true">
      </div>
      <div class="col-md-2">
        <input type="date" name="${tipo}[${index}][nacimiento]" class="form-control" data-optional="true">
      </div>
      <div class="col-md-1">
        <input type="text" name="${tipo}[${index}][dni]" class="form-control" pattern="^\d{1,8}$" title="Debe contener hasta 8 dígitos numéricos" placeholder="DNI" data-optional="true">
      </div>
      <div class="col-md-2 text-right">
        <div class="col-md-2 text-right">
            <button type="button" class="btn btn-danger btn-sm eliminarFila">&times;</button>
        </div>

      </div>`;
        contenedor.appendChild(div);
    }

    // Eliminar fila
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('eliminarFila')) {
            const fila = e.target.closest('.form-row');
            if (fila) fila.remove();
        }
    });
    //Validar cantidad de digitos en DNI
    document.querySelector('form').addEventListener('submit', function(e) {
        const dniInputs = this.querySelectorAll('input[name*="[dni]"], input[name="pareja_dni"]');
        let valid = true;

        dniInputs.forEach(input => {
            const valor = input.value.trim();
            if (valor !== '') {
                const soloNumeros = /^\d{1,8}$/.test(valor);
                if (!soloNumeros) {
                    valid = false;
                    input.classList.add('is-invalid');
                    if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('invalid-feedback')) {
                        const feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        feedback.textContent = 'El DNI debe tener solo números y hasta 8 dígitos sin puntos.';
                        input.after(feedback);
                    }
                } else {
                    input.classList.remove('is-invalid');
                    const siguiente = input.nextElementSibling;
                    if (siguiente && siguiente.classList.contains('invalid-feedback')) {
                        siguiente.remove();
                    }
                }
            }
        });

        if (!valid) {
            e.preventDefault();
            alert('Corregí los campos de DNI antes de guardar.');
        }
    });
</script>