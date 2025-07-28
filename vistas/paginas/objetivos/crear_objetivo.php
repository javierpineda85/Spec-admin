<?php

$db = new Conexion;
$usuarios = $db->consultas("SELECT idUsuario, nombre, apellido FROM usuarios WHERE rol = 'Vigilador' AND activo = 1 ORDER BY apellido");
$db = new Conexion;
$referentes = $db->consultas("SELECT idUsuario, nombre, apellido FROM usuarios WHERE rol = 'Referente' AND activo = 1 ORDER BY apellido");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  ControladorObjetivos::crtGuardarObjetivo();
}
?>

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
            <div class="form-group col-sm-12 col-md-4">
              <label class="form-label">Nombre</label>
              <input type="text" class="form-control" placeholder="Servicio 1" name="nombreObjetivo" required>
            </div>

            <div class="form-group col-sm-12 col-md-3">
              <label class="form-label">Tipo</label>
              <select id="tipo" name="tipo" class="form-control" required>
                <option value="" disabled selected>Selecciona un tipo</option>
                <option value="fijo">Fijo</option>
                <option value="eventual">Eventual</option>
                <option value="movil">Móvil</option>
              </select>
            </div>
            <div class="form-group col-sm-12 col-md-3">
              <label for="cantidad_vigiladores">Cantidad de Vigiladores</label>
              <input type="number" name="cantidad_vigiladores" id="cantidad_vigiladores" class="form-control" min="1" value="1" required>
            </div>
            <div class="form-group col-sm-12 col-md-3">
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
                <input type="text" id="address" class="form-control" data-optional="true" placeholder="Ingresa una dirección">
                <div class="input-group-append">
                  <button type="button" id="btnSearch" class="btn btn-primary">Buscar</button>
                </div>
              </div>
            </div>
            <div class="form-group col-sm-12 col-md-2">
              <label class="form-label">Radio (m)</label>
              <input type="number" id="radio_m" name="radio_m" class="form-control" placeholder="200" required>
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
              <select name="vigiladores[]" id="vigiladores" class="form-control select2" data-optional="true" multiple >
                <?php foreach ($usuarios as $u): ?>
                  <option value="<?= $u['idUsuario'] ?>"><?= $u['apellido'] ?> <?= $u['nombre'] ?></option>
                <?php endforeach; ?>
              </select>
              <small class="form-text text-muted">Haz click para seleccionar.</small>
            </div>

            <div class="form-group col-sm-12 col-md-5">
              <label for="referentes">Seleccionar Referentes</label>
              <select name="referentes[]" id="referentes" class="form-control select2" data-optional="true" multiple >
                <?php foreach ($referentes as $r): ?>
                  <option value="<?= $r['idUsuario'] ?>"><?= $r['apellido'] . ' ' . $r['nombre'] ?></option>
                <?php endforeach; ?>
              </select>
              <small class="form-text text-muted">Podés seleccionar uno o varios referentes para este objetivo.</small>
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
  var selectProvincia = document.getElementById("localidad");
  var localidadJSON = {
    "departamentos": [{
        "nombre": "Capital"
      }, {
        "nombre": "Godoy Cruz"
      }, {
        "nombre": "Guaymallén"
      },
      {
        "nombre": "Las Heras"
      }, {
        "nombre": "Luján de Cuyo"
      }, {
        "nombre": "Maipú"
      },
      {
        "nombre": "San Martín"
      }, {
        "nombre": "Rivadavia"
      }, {
        "nombre": "Junín"
      },
      {
        "nombre": "Santa Rosa"
      }, {
        "nombre": "La Paz"
      }, {
        "nombre": "Tunuyán"
      },
      {
        "nombre": "Tupungato"
      }, {
        "nombre": "San Carlos"
      }, {
        "nombre": "General Alvear"
      },
      {
        "nombre": "Malargüe"
      }
    ]
  };
  localidadJSON.departamentos.forEach(function(localidad) {
    var option = document.createElement("option");
    option.value = localidad.nombre;
    option.text = localidad.nombre;
    selectProvincia.appendChild(option);
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

      if (seleccionados !== cantidadRequerida) {
        e.preventDefault();
        mostrarToast("Debes seleccionar exactamente " + cantidadRequerida + " vigilador(es). Actualmente seleccionaste " + seleccionados + ".");
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