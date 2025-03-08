<!-- Default box -->
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Completa el formulario para crear un nuevo objetivo</h3>

    <div class="card-tools">
      <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
        <i class="fas fa-minus"></i>
      </button>

    </div>
  </div>
  <div class="card-body">
    <form action="" method="POST">
      <div class="card card-info">
        <div class="card-header">
          <h3 class="card-title">Crear Objetivo</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form class="form-horizontal" method="POST">
          <div class="card-body">
            <?php
            if (isset($_SESSION['success_message'])) {
              echo '<div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <p><i class="icon fas fa-check"></i>' . $_SESSION['success_message'] .
                '</p></div>';
              // Elimina el mensaje después de mostrarlo
              unset($_SESSION['success_message']);
            };
            ?>
            <div class="row">
              <div class="form-group col-sm-12 col-md-3">
                <label class="form-label">Nombre</label>
                <input type="text" class="form-control" placeholder="Servicio 1" name="nombreObjetivo">
              </div>
              <div class="form-group col-sm-12 col-md-3">
                <label class="form-label">Localidad</label>
                <select id="localidad" name="localidad" class="form-control">
                  <option value="" disabled selected>Selecciona una localidad</option>
                </select>
              </div>
              <div class="form-group col-sm-12 col-md-3">
                <label class="form-label">Referente</label>
                <input type="text" class="form-control" placeholder="Juan Perez" name="referente">
              </div>
              <div class="form-group col-sm-12 col-md-3">
                <label class="form-label">Tipo</label>
                <select id="tipo" name="tipo" class="form-control">
                  <option value="" disabled selected>Selecciona un tipo</option>
                  <option value="fijo">Fijo</option>
                  <option value="eventual">Eventual</option>
                  <option value="movil">Móvil</option>
                </select>
              </div>



              <!-- /.card-body -->
              <div class="card-footer">
                <button type="reset" class="btn btn-default float-right">Borrar campos</button>
                <?php

                $registro =  ControladorObjetivos::crtGuardarObjetivo();
                ?>

                <input type="submit" class="btn btn-success" value="Registrar">

              </div>
              <!-- /.card-footer -->
        </form>
      </div>
      <!-- /.card-info -->
    </form>
  </div>
  <!-- /.card-body -->

</div>
<!-- /.card -->

</section>
<!-- /.content -->

<script>
  // Obtener el elemento select
  var selectProvincia = document.getElementById("localidad");

  // JSON con los departamentos de Mendoza
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

  // Agregar opciones al select correctamente
  localidadJSON.departamentos.forEach(function(localidad) { // Aquí está la corrección
    var option = document.createElement("option");
    option.value = localidad.nombre;
    option.text = localidad.nombre;
    selectProvincia.appendChild(option);
  });
</script>