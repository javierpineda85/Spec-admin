<div class="container mt-4">
        <h5 class="mb-3">Completa el formulario para crear una nueva directiva</h5>

        <!-- Encabezado -->
        <div class="bg-info text-white p-3 rounded">
            <h4 class="m-0">Crear Directiva</h4>
        </div>

        <!-- Formulario -->
        <div class="p-4 border rounded-bottom bg-light">
            <form action="guardar_objetivo.php" method="POST">
                
                <div class="row g-3">
                    <!-- Objetivo -->
                    <div class="col-md-6">
                        <label for="objetivo" class="form-label fw-bold">Objetivo</label>
                        <select class="form-select" id="objetivo" name="objetivo" required>
                            <option value="">Selecciona un objetivo</option>
                            <option value="Objetivo 1">Objetivo 1</option>
                            <option value="Objetivo 2">Objetivo 2</option>
                            <option value="Objetivo 3">Objetivo 3</option>
                        </select>
                    </div>
                </div>

                <!-- Detalle -->
                <div class="row mt-3">
                    <div class="col-12">
                        <label for="detalle" class="form-label fw-bold">Detalle</label>
                        <textarea class="form-control" id="detalle" name="detalle" rows="4" placeholder="Escribe aquí los detalles..." required></textarea>
                    </div>
                </div>

                <!-- Botones -->
                <div class="mt-4 d-flex">
                  <button type="submit" class="btn btn-success">Crear</button>
                  <button type="reset" class="btn btn-light border ms-3">Borrar campos</button>
                </div>
            </form>
        </div>
    </div>