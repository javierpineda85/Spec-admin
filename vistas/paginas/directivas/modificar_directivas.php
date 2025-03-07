<div class="container mt-4">
        <h5 class="mb-3">Selecciona una directiva para modificarla</h5>

        <!-- Encabezado -->
        <div class="bg-success text-white p-3 rounded">
            <h4 class="m-0">Modificar Directiva</h4>
        </div>

        <!-- Formulario -->
        <div class="p-4 border rounded-bottom bg-light">
            <form action="actualizar_directiva.php" method="POST">
                
                <div class="row g-3">
                    <!-- Selección de Directiva -->
                    <div class="col-md-6">
                        <label for="directiva" class="form-label fw-bold">Directiva</label>
                        <select class="form-select" id="directiva" name="directiva" required>
                            <option value="">Selecciona una directiva</option>
                            <option value="Directiva 1">Directiva 1</option>
                            <option value="Directiva 2">Directiva 2</option>
                            <option value="Directiva 3">Directiva 3</option>
                        </select>
                    </div>
                </div>

                <!-- Objetivo -->
                
            
                <!-- Detalle -->
                <div class="row mt-3">
                    <div class="col-12">
                        <label for="detalle" class="form-label fw-bold">Detalle</label>
                        <textarea class="form-control" id="detalle" name="detalle" rows="4" placeholder="Modifica aquí los detalles..." required></textarea>
                    </div>
                </div>

                <!-- Botones -->
                <div class="mt-4 d-flex">
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                    <button type="reset" class="btn btn-light border ms-3">Restablecer</button>
                </div>
            </form>
        </div>
    </div>