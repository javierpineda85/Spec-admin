<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ControladorObjetivos::crtModificarObjetivo();
}

$db = new Conexion();
$sql = "SELECT * FROM objetivos WHERE idObjetivo = ?";
$objetivo = $db->consultas($sql, [$_GET['id']])[0];

$usuarios = $db->consultas("SELECT idUsuario, nombre, apellido FROM usuarios WHERE rol = 'Vigilador' AND activo = 1 ORDER BY apellido");
$referentes = $db->consultas("SELECT idUsuario, nombre, apellido FROM usuarios WHERE rol = 'Referente' AND activo = 1 ORDER BY apellido");

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
        <form class="form-horizontal" method="POST" action="?r=editar_objetivo&id=<?= $objetivo['idObjetivo'] ?>">
            <div class="card-body">
                <input type="hidden" name="idObjetivo" value="<?= $objetivo['idObjetivo'] ?>">
                <div class="row">
                    <div class="form-group col-md-2">
                        <label>Nombre</label>
                        <input type="text" name="nombreObjetivo" class="form-control" required value="<?= htmlspecialchars($objetivo['nombre']) ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Localidad</label>
                        <select id="localidad" name="localidad" class="form-control" required>
                            <option value="<?= htmlspecialchars($objetivo['localidad']) ?>" selected><?= htmlspecialchars($objetivo['localidad']) ?></option>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Tipo</label>
                        <select id="tipo" name="tipo" class="form-control" required>
                            <option value="<?= htmlspecialchars($objetivo['tipo']) ?>" selected><?= htmlspecialchars($objetivo['tipo']) ?></option>
                            <option value="fijo">Fijo</option>
                            <option value="eventual">Eventual</option>
                            <option value="movil">Móvil</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-2">
                        <label>Latitud</label>
                        <input type="text" id="latitud" name="latitud" class="form-control" required value="<?= htmlspecialchars($objetivo['latitud']) ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Longitud</label>
                        <input type="text" id="longitud" name="longitud" class="form-control" required value="<?= htmlspecialchars($objetivo['longitud']) ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Radio (m)</label>
                        <input type="number" id="radio_m" name="radio_m" class="form-control" required value="<?= htmlspecialchars($objetivo['radio_m']) ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Buscar dirección</label>
                        <div class="input-group">
                            <input type="text" id="address" class="form-control" placeholder="Ingresa una dirección">
                            <div class="input-group-append">
                                <button type="button" id="btnSearch" class="btn btn-primary">Buscar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-md-6">
                        <div id="map" style="height:300px;margin-bottom:1rem;"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-5">
                        <label for="vigiladores">Seleccionar Vigiladores</label>
                        <select name="vigiladores[]" id="vigiladores" class="form-control select2" multiple required>
                            <?php foreach ($usuarios as $u): ?>
                                <option value="<?= $u['idUsuario'] ?>" <?= in_array($u['idUsuario'], $vigiladoresSeleccionados) ? 'selected' : '' ?>>
                                    <?= $u['apellido'] . ' ' . $u['nombre'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-5">
                        <label for="referentes">Seleccionar Referentes</label>
                        <select name="referentes[]" id="referentes" class="form-control select2" multiple required>
                            <?php foreach ($referentes as $r): ?>
                                <option value="<?= $r['idUsuario'] ?>" <?= in_array($r['idUsuario'], $referentesSeleccionados) ? 'selected' : '' ?>>
                                    <?= $r['apellido'] . ' ' . $r['nombre'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-footer col-sm-12 col-md-6">
                <button type="submit" name="Modificar" class="btn btn-success">Modificar</button>
                <button type="reset" class="btn btn-default">Borrar</button>
            </div>
        </form>
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

    $(document).ready(function () {
        $('#vigiladores').select2({ placeholder: "Selecciona los vigiladores asignados" });
        $('#referentes').select2({ placeholder: "Selecciona los referentes asignados" });
    });

    document.addEventListener('DOMContentLoaded', () => {
        const latInput = document.getElementById('latitud');
        const lngInput = document.getElementById('longitud');
        const addressInput = document.getElementById('address');
        const btnSearch = document.getElementById('btnSearch');
        const initLat = parseFloat(latInput.value) || -32.889458;
        const initLng = parseFloat(lngInput.value) || -68.845839;
        const map = L.map('map').setView([initLat, initLng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(map);
        const marker = L.marker([initLat, initLng], { draggable: true }).addTo(map);
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
                        let la = parseFloat(p.lat), ln = parseFloat(p.lon);
                        map.setView([la, ln], 15);
                        marker.setLatLng([la, ln]);
                        latInput.value = la.toFixed(6);
                        lngInput.value = ln.toFixed(6);
                    } else alert('No encontrado');
                });
        });
    });
</script>
