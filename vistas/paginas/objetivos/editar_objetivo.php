<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ControladorObjetivos::crtModificarObjetivo();
}

$db = new Conexion();
$sql = "SELECT * FROM objetivos WHERE idObjetivo = ?";
$objetivo = $db->consultas($sql, [$_GET['id']])[0];

$usuarios = $db->consultas("SELECT u.idUsuario, u.nombre, u.apellido
                            FROM usuarios u
                            INNER JOIN roles r ON u.rol_id = r.id
                            WHERE r.categoria = 'operativo' AND u.activo = 1
                            ORDER BY u.apellido");

$referentes = $db->consultas("SELECT u.idUsuario, u.nombre, u.apellido
                              FROM usuarios u
                              INNER JOIN roles r ON u.rol_id = r.id
                              WHERE r.categoria = 'referente' AND u.activo = 1
                              ORDER BY u.apellido");

$asignadosVigiladores = $db->consultas("SELECT vigilador_id FROM objetivo_vigiladores WHERE objetivo_id = ?", [$objetivo['idObjetivo']]);
$asignadosReferentes = $db->consultas("SELECT referente_id FROM objetivo_referentes WHERE objetivo_id = ?", [$objetivo['idObjetivo']]);

$vigiladoresSeleccionados = array_column($asignadosVigiladores, 'vigilador_id');
$referentesSeleccionados = array_column($asignadosReferentes, 'referente_id');
?>

<div class="card">
    <div class="card-header bg-info text-white">
        <h3 class="card-title">Completa el formulario para modificar el objetivo</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">

        <div class="card card-info">
            <form class="form-horizontal" id="formObjetivo" method="POST" action="?r=editar_objetivo&id=<?= $objetivo['idObjetivo'] ?>">
                <div class="card-body">
                    <input type="hidden" name="idObjetivo" value="<?= $objetivo['idObjetivo'] ?>">

                    <div class="row">
                        <div class="form-group col-sm-12 col-md-4">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombreObjetivo" required value="<?= htmlspecialchars($objetivo['nombre']) ?>">
                        </div>

                        <div class="form-group col-sm-12 col-md-3">
                            <label class="form-label">Tipo</label>
                            <select id="tipo" name="tipo" class="form-control" required>
                                <option value="" disabled>Selecciona un tipo</option>
                                <option value="fijo" <?= $objetivo['tipo'] == 'fijo' ? 'selected' : '' ?>>Fijo</option>
                                <option value="eventual" <?= $objetivo['tipo'] == 'eventual' ? 'selected' : '' ?>>Eventual</option>
                                <option value="movil" <?= $objetivo['tipo'] == 'movil' ? 'selected' : '' ?>>Móvil</option>
                            </select>
                        </div>

                        <div class="form-group col-sm-12 col-md-3"  style="display: none;">
                            <label for="cantidad_vigiladores">Cantidad de Vigiladores</label>
                            <input type="number" name="cantidad_vigiladores" id="cantidad_vigiladores" class="form-control" min="1" data-optional="true">
                        </div>

                        <div class="form-group col-sm-12 col-md-3">
                            <label class="form-label">Localidad</label>
                            <select id="localidad" name="localidad" class="form-control" required>
                                <option value="<?= htmlspecialchars($objetivo['localidad']) ?>" selected><?= htmlspecialchars($objetivo['localidad']) ?></option>
                            </select>
                        </div>

                        <div class="form-group col-sm-12 col-md-2" hidden>
                            <label class="form-label">Latitud</label>
                            <input type="text" id="latitud" name="latitud" class="form-control" required value="<?= htmlspecialchars($objetivo['latitud']) ?>">
                        </div>
                        <div class="form-group col-sm-12 col-md-2" hidden>
                            <label class="form-label">Longitud</label>
                            <input type="text" id="longitud" name="longitud" class="form-control" required value="<?= htmlspecialchars($objetivo['longitud']) ?>">
                        </div>

                        <div class="form-group col-sm-12 col-md-5">
                            <label class="form-label">Buscar dirección</label>
                            <div class="input-group">
                                <input type="text" id="address" class="form-control" data-optional="true" placeholder="Ingresa una dirección">
                                <div class="input-group-append">
                                    <button type="button" id="btnSearch" class="btn btn-primary">Buscar</button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-sm-12 col-md-2">
                            <label class="form-label">Radio (m)</label>
                            <input type="number" id="radio_m" name="radio_m" class="form-control" placeholder="200" required value="<?= htmlspecialchars($objetivo['radio_m']) ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12 col-md-10">
                            <div id="map" style="height: 300px; margin-bottom: 1rem;"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-sm-12 col-md-5">
                            <label for="vigiladores">Seleccionar Vigiladores</label>
                            <select name="vigiladores[]" id="vigiladores" class="form-control select2" data-optional="true" multiple>
                                <?php foreach ($usuarios as $u): ?>
                                    <option value="<?= $u['idUsuario'] ?>" <?= in_array($u['idUsuario'], $vigiladoresSeleccionados) ? 'selected' : '' ?>>
                                        <?= $u['apellido'] ?> <?= $u['nombre'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Haz click para seleccionar.</small>
                        </div>

                        <div class="form-group col-sm-12 col-md-5">
                            <label for="referentes">Seleccionar Referentes</label>
                            <select name="referentes[]" id="referentes" class="form-control select2" data-optional="true" multiple>
                                <?php foreach ($referentes as $r): ?>
                                    <option value="<?= $r['idUsuario'] ?>" <?= in_array($r['idUsuario'], $referentesSeleccionados) ? 'selected' : '' ?>>
                                        <?= $r['apellido'] ?> <?= $r['nombre'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Podés seleccionar uno o varios referentes para este objetivo.</small>
                        </div>
                    </div>
                </div>

                <div class="card-footer col-sm-12 col-md-12 d-flex justify-content-between">
                    <button type="submit" name="Modificar" class="btn btn-success">Modificar</button>
                    <button type="reset" class="btn btn-default">Borrar</button>
                </div>
            </form>
        </div>

    </div>
</div>


<script>
    const deps = ["Capital", "Godoy Cruz", "Guaymallén", "Las Heras", "Luján de Cuyo", "Maipú", "San Martín", "Rivadavia", "Junín", "Santa Rosa", "La Paz", "Tunuyán", "Tupungato", "San Carlos", "General Alvear", "Malargüe"];
    const sel = document.getElementById('localidad');
    deps.forEach(d => {
        if (d !== '<?= $objetivo['localidad'] ?>') {
            let o = document.createElement('option');
            o.value = d;
            o.text = d;
            sel.append(o);
        }
    });

    $(document).ready(function() {
        $('#vigiladores').select2({
            placeholder: "Selecciona los vigiladores asignados"
        });
        $('#referentes').select2({
            placeholder: "Selecciona los referentes asignados"
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        const latInput = document.getElementById('latitud');
        const lngInput = document.getElementById('longitud');
        const addressInput = document.getElementById('address');
        const btnSearch = document.getElementById('btnSearch');
        const initLat = parseFloat(latInput.value) || -32.889458;
        const initLng = parseFloat(lngInput.value) || -68.845839;
        const map = L.map('map').setView([initLat, initLng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OSM'
        }).addTo(map);
        const marker = L.marker([initLat, initLng], {
            draggable: true
        }).addTo(map);
        marker.on('dragend', () => {
            const p = marker.getLatLng();
            latInput.value = p.lat.toFixed(6);
            lngInput.value = p.lng.toFixed(6);
        });
        map.on('click', e => {
            marker.setLatLng(e.latlng);
            latInput.value = e.latlng.lat.toFixed(6);
            lngInput.value = e.latlng.lng.toFixed(6);
        });
        btnSearch.addEventListener('click', () => {
            const q = addressInput.value.trim();
            if (!q) return;
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}`)
                .then(r => r.json()).then(rs => {
                    if (rs.length) {
                        let p = rs[0];
                        let la = parseFloat(p.lat),
                            ln = parseFloat(p.lon);
                        map.setView([la, ln], 15);
                        marker.setLatLng([la, ln]);
                        latInput.value = la.toFixed(6);
                        lngInput.value = ln.toFixed(6);
                    } else alert('No encontrado');
                });
        });
    });

    //Validacion de cantidad de vigiladores
    $(document).ready(function() {
        $('#vigiladores').select2({
            placeholder: "Selecciona los vigiladores asignados"
        });
    });
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById("formObjetivo");
        const inputCantidad = document.getElementById("cantidad_vigiladores");
        const $selectVigiladores = $('#vigiladores');

        // Inicializar Select2
        $selectVigiladores.select2({
            placeholder: "Selecciona los vigiladores asignados"
        });
        // Mostrar toast
        function mostrarToast(mensaje) {
            $('#toast-msg').text(mensaje);
            $('#toast-alerta').toast('show');
        }
        // Validación dinámica al seleccionar
        $selectVigiladores.on('select2:select', function(e) {
            const max = parseInt(inputCantidad.value) || 0;
            const seleccionados = $selectVigiladores.select2('data');

            if (seleccionados.length > max) {
                // Elimina el último seleccionado
                const idEliminar = e.params.data.id;
                const opciones = $selectVigiladores.val().filter(val => val !== idEliminar);
                $selectVigiladores.val(opciones).trigger('change');

                mostrarToast('Solo puedes seleccionar hasta ' + max + ' vigilador(es).');
            }
        });

        // Validación de respaldo al enviar
        form.addEventListener("submit", function(e) {
            const cantidadRequerida = parseInt(inputCantidad.value);
            const seleccionados = $selectVigiladores.select2('data').length;

            // Solo validar si el campo tiene un valor numérico válido
            if (!isNaN(cantidadRequerida) && cantidadRequerida > 0) {
                if (seleccionados !== cantidadRequerida) {
                    e.preventDefault();
                    mostrarToast("Debes seleccionar exactamente " + cantidadRequerida + " vigilador(es). Actualmente seleccionaste " + seleccionados + ".");
                }
            }
        });
    });

    //Carga de referentes
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById("formObjetivo");
        const $selectReferentes = $('#referentes');

        // Inicializar Select2
        $selectReferentes.select2({
            placeholder: "Selecciona los referentes asignados"
        });


    });
</script>