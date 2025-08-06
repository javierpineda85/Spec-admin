<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  FeriadosController::ctrGuardarFeriados();
}
?>

<div class="card">
  <div class="card-header bg-primary text-white">
    <h3 class="card-title">Carga masiva de feriados</h3>
  </div>
  <div class="card-body">
    <form method="POST" id="formFeriados">
      <div id="contenedorFeriados">
        <div class="form-row feriado-row mb-2">
          <div class="col-md-2">
            <input type="date" name="feriados[0][fecha]" class="form-control" required>
          </div>
          <div class="col-md-5">
            <input type="text" name="feriados[0][motivo]" class="form-control" placeholder="Motivo" required>
          </div>
          <div class="col-md-2">
            <select name="feriados[0][tipo_feriado]" class="form-control" required>
              <option value="nacional">Nacional</option>
              <option value="provincial">Provincial</option>
              <option value="departamental">Departamental</option>
              <option value="asueto">Asueto</option>
              <option value="día no laborable">Día no laborable</option>
            </select>
          </div>
          <div class="col-md-1">
            <button type="button" class="btn btn-danger btn-sm eliminarFila">&times;</button>
          </div>
        </div>
      </div>
      <div class="row mt-5">
        <button type="button" class="btn btn-secondary mb-3" id="agregarFila">Agregar feriado</button>
        <button type="submit" class="btn btn-success mb-3 mx-5">Guardar feriados</button>
      </div>

    </form>
  </div>
</div>

<script>
  let indice = 1;
  document.getElementById('agregarFila').addEventListener('click', function() {
    const contenedor = document.getElementById('contenedorFeriados');
    const nuevaFila = contenedor.firstElementChild.cloneNode(true);
    nuevaFila.querySelectorAll('input, select').forEach(input => {
      const nombre = input.name.replace(/\[\d+\]/, `[${indice}]`);
      input.name = nombre;
      input.value = '';
    });
    contenedor.appendChild(nuevaFila);
    indice++;
  });

  document.getElementById('contenedorFeriados').addEventListener('click', function(e) {
    if (e.target.classList.contains('eliminarFila')) {
      const filas = document.querySelectorAll('.feriado-row');
      if (filas.length > 1) {
        e.target.closest('.feriado-row').remove();
      }
    }
  });
</script>