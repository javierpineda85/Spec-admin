<?php

$db = new Conexion;
$vigiladores = $db->consultas("SELECT u.idUsuario, u.nombre, u.apellido
                                    FROM usuarios u
                                    INNER JOIN roles r ON u.rol_id = r.id
                                    WHERE r.categoria = 'operativo' AND u.activo = 1
                                    ORDER BY u.apellido ");

$referentes = $db->consultas("SELECT u.idUsuario, u.nombre, u.apellido
                                      FROM usuarios u
                                      INNER JOIN roles r ON u.rol_id = r.id
                                      WHERE r.categoria = 'referente' AND u.activo = 1
                                      ORDER BY u.apellido");
$baseOperativa = $db->consultas("SELECT u.idUsuario, u.nombre, u.apellido
                                FROM usuarios u
                                INNER JOIN roles r ON u.rol_id = r.id
                                WHERE r.categoria = 'baseOperativa' AND u.activo = 1
                                ORDER BY u.apellido");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  ControladorObjetivos::crtGuardarObjetivo();
}

?>
<style>
  #formObjetivo .select2-container--default .select2-selection--multiple {
    min-height: 38px;
    border-color: #6c757d;
  }

  #formObjetivo .select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #1f2937;
    border: 1px solid #111827;
    color: #fff;
    padding: 2px 8px;
  }

  #formObjetivo .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #fff;
    margin-right: 6px;
  }

  #formObjetivo .select2-container--default .select2-results__option--selected {
    background-color: #e2e8f0;
    color: #111827;
  }
</style>

<div class="card">
  <div class="card-header bg-info text-white">
    <h3 class="card-title ">Completa el formulario para crear un nuevo objetivo</h3>
    <div class="card-tools">
      <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
        <i class="fas fa-minus"></i>
      </button>
    </div>
  </div>
  <div class="card-body">

    <div class="card card-info">

      <form class="form-horizontal" id="formObjetivo" action="?r=crear_objetivo" method="POST">
        <div class="card-body">
          <div class="row">
            <div class="form-group col-sm-12 col-md-6">
              <label class="form-label">Nombre</label>
              <input type="text" class="form-control" placeholder="Servicio 1" name="nombreObjetivo" required>
            </div>

            <div class="form-group col-sm-12 col-md-2">
              <label class="form-label">Tipo</label>
              <select id="tipo" name="tipo" class="form-control" required>
                <option value="" disabled selected>Selecciona un tipo</option>
                <option value="fijo">Fijo</option>
                <option value="eventual">Eventual</option>
                <option value="movil">Móvil</option>
              </select>
            </div>
            <div class="form-group col-sm-12 col-md-3" style="display: none;">
              <label for="cantidad_vigiladores">Cantidad de Vigiladores</label>
              <input type="number" name="cantidad_vigiladores" id="cantidad_vigiladores" class="form-control" min="1" value="1" data-optional="true">
            </div>
            <div class="form-group col-sm-12 col-md-4">
              <label class="form-label">Localidad</label>
              <select id="localidad" name="localidad" class="form-control" required>
                <option value="" disabled selected>Selecciona una localidad</option>
              </select>
            </div>

            <div class="form-group col-sm-12 col-md-2" hidden>
              <label class="form-label">Latitud</label>
              <input type="text" id="latitud" name="latitud" class="form-control" placeholder="-32.889458" required>
            </div>
            <div class="form-group col-sm-12 col-md-2" hidden>
              <label class="form-label">Longitud</label>
              <input type="text" id="longitud" name="longitud" class="form-control" placeholder="-68.845839" required>
            </div>

            <div class="form-group col-sm-12 col-md-5">
              <label class="form-label">Buscar dirección</label>
              <div class="input-group">
                <input type="text" id="address" class="form-control" name="domicilio" placeholder="Ingresa una dirección">
                <div class="input-group-append">
                  <button type="button" id="btnSearch" class="btn btn-primary">Buscar</button>
                </div>
              </div>
            </div>
            <div class="form-group col-sm-12 col-md-1">
              <label class="form-label">Radio (m)</label>
              <input type="number" id="radio_m" name="radio_m" class="form-control" placeholder="200" required>
            </div>

            <div class="col-sm-12 col-md-1">
              <div class="form-group">
                <label>Sigla</label>
                <input type="text" name="siglas[0][sigla]" class="form-control" placeholder="6H, MIC" required>
              </div>
            </div>
            <div class="col-sm-12 col-md-1">
              <div class="form-group">
                <label>Horas</label>
                <input type="number" name="siglas[0][horas]" class="form-control" placeholder="Horas" required>
              </div>
            </div>
            <div class="col-sm-12 col-md-4">
              <div class="form-group">
                <label>Descripción</label>
                <input type="text" name="siglas[0][descripcion]" class="form-control" placeholder="Descripción (opcional)" data-optional="true">
              </div>
            </div>

          </div>
          <!-- Contenedor del mapa -->
          <div class="row">
            <div class="col-sm-12 col-md-10">
              <div id="map" style="height: 300px; margin-bottom: 1rem;"></div>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-sm-12 col-md-5">
              <label for="vigiladores">Seleccionar Vigiladores</label>
              <select name="vigiladores[]" id="vigiladores" class="form-control select2"  multiple>
                <?php foreach ($vigiladores as $u): ?>
                  <option value="<?= $u['idUsuario'] ?>"><?= $u['apellido'] ?> <?= $u['nombre'] ?></option>
                <?php endforeach; ?>
              </select>
              <small class="form-text text-muted">Haz click para seleccionar.</small>
            </div>

            <div class="form-group col-sm-12 col-md-5">
              <label for="referentes">Seleccionar Referentes</label>
              <select name="referentes[]" id="referentes" class="form-control select2"  multiple>
                <?php foreach ($referentes as $r): ?>
                  <option value="<?= $r['idUsuario'] ?>"><?= $r['apellido'] . ' ' . $r['nombre'] ?></option>
                <?php endforeach; ?>
              </select>
              <small class="form-text text-muted">Podés seleccionar uno o varios referentes para este objetivo.</small>
            </div>
            <div class="form-group col-sm-12 col-md-5">
              <label for="base_operativa">Seleccionar Base Operativa</label>
              <select name="base_operativa[]" id="base_operativa" class="form-control select2" data-optional="true" multiple>
                <?php foreach ($baseOperativa as $b): ?>
                  <option value="<?= $b['idUsuario'] ?>" <?= in_array($b['idUsuario'], $baseSeleccionados ?? []) ? 'selected' : '' ?>>
                    <?= $b['apellido'] ?> <?= $b['nombre'] ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <small class="form-text text-muted">Podés asignar responsables de base operativa para este objetivo.</small>
            </div>
          </div>


        </div>
        <!-- /.card-body -->

        <div class="card-footer col-sm-12 col-md-12 d-flex justify-content-between">
          <input type="submit" class="btn btn-success" value="Registrar" name="Registrar">
          <button type="reset" class="btn btn-default">Borrar campos</button>
        </div>
        <!-- /.card-footer -->
      </form>
    </div>
    <!-- /.card-info -->

  </div>
  <!-- /.card-body -->
</div>
<!-- /.card -->


<script>

  // Carga los departamentos en el select
  const deps = [
    "Capital", "Godoy Cruz", "Guaymallén", "Las Heras", "Luján de Cuyo", "Lavalle", "Maipú",
    "San Martín", "Rivadavia", "Junín", "Santa Rosa", "La Paz", "Tunuyán",
    "Tupungato", "San Carlos", "San Rafael", "General Alvear", "Malargüe"
  ];

  const sel = document.getElementById("localidad");
  deps.forEach(d => {
    let o = document.createElement("option");
    o.value = d;
    o.text = d;
    sel.append(o);
  });

  document.addEventListener('DOMContentLoaded', () => {
    const latInput = document.getElementById('latitud');
    const lngInput = document.getElementById('longitud');
    const radioInput = document.getElementById('radio_m');
    const addressInput = document.getElementById('address');
    const btnSearch = document.getElementById('btnSearch');

    // Valores iniciales
    const initialLat = parseFloat(latInput.value) || -32.889458;
    const initialLng = parseFloat(lngInput.value) || -68.845839;
    const map = L.map('map').setView([initialLat, initialLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    const marker = L.marker([initialLat, initialLng], {
      draggable: true
    }).addTo(map);

    // Actualiza inputs al mover marcador
    marker.on('dragend', () => {
      const pos = marker.getLatLng();
      latInput.value = pos.lat.toFixed(6);
      lngInput.value = pos.lng.toFixed(6);
    });

    // Al hacer clic en el mapa reposiciona
    map.on('click', (e) => {
      marker.setLatLng(e.latlng);
      latInput.value = e.latlng.lat.toFixed(6);
      lngInput.value = e.latlng.lng.toFixed(6);
    });

    // Búsqueda de dirección con Nominatim
    btnSearch.addEventListener('click', () => {
      const query = addressInput.value.trim();
      if (!query) return;
      fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(results => {
          if (results && results.length) {
            const place = results[0];
            const lat = parseFloat(place.lat);
            const lon = parseFloat(place.lon);
            map.setView([lat, lon], 15);
            marker.setLatLng([lat, lon]);
            latInput.value = lat.toFixed(6);
            lngInput.value = lon.toFixed(6);
          } else {
            alert('Dirección no encontrada.');
          }
        })
        .catch(() => alert('Error al buscar la dirección.'));
    });
  });

  // Validación de cantidad de vigiladores
  /*
  $(document).ready(function() {
    $('#vigiladores').select2({
      placeholder: "Selecciona los vigiladores asignados"
    });
    $('#referentes').select2({
      placeholder: "Selecciona los referentes asignados"
    });
    // ✅ Nuevo campo Base Operativa
    $('#base_operativa').select2({
      placeholder: "Selecciona los responsables de base operativa"
    });
  });
*/
  document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("formObjetivo");
    //const inputCantidad = document.getElementById("cantidad_vigiladores");
    const $selectVigiladores = $('#vigiladores');
    const $selectReferentes = $('#referentes');
    const $selectBase = $('#base_operativa'); // ✅ Nuevo campo

    // Inicializar Select2
    $selectVigiladores.select2({
      placeholder: "Selecciona los vigiladores asignados"
    });
    $selectReferentes.select2({
      placeholder: "Selecciona los referentes asignados"
    });
    $selectBase.select2({
      placeholder: "Selecciona los responsables de base operativa"
    });

    // Mostrar toast
    function mostrarToast(mensaje) {
      $('#toast-msg').text(mensaje);
      $('#toast-alerta').toast('show');
    }


    // Validación de respaldo al enviar
    form.addEventListener("submit", function(e) {
      const seleccionadosVigiladores = $selectVigiladores.select2('data').length;
      const seleccionadosBase = $selectBase.select2('data').length;

      // Si no hay vigiladores O no hay base operativa → bloquear envío
      if (seleccionadosVigiladores === 0 && seleccionadosBase === 0) {
        e.preventDefault();
        mostrarToast("Debes seleccionar al menos un vigilador y un responsable de base operativa.");
      }
    });


  });
</script>
