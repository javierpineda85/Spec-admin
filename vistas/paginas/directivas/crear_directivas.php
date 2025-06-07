<?php

if (isset($_POST['Crear'])) {
    $registro = ControladorDirectivas::crtGuardarDirectiva();
}
$db = new Conexion;
$sql = "SELECT * FROM objetivos ORDER BY nombre ";
$objetivos = $db->consultas($sql);

?>

<!-- Default box -->
<div class="card">
    <div class="card-header bg-info text-white">
        <h3 class="card-title">Completa el formulario para crear una nueva directiva</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible mt-3">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="icon fas fa-check"></i>
                <?= $_SESSION['success_message'];
                unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <!-- form start -->
        <form class="form-horizontal" method="POST">

            <div class="row">
                <div class="form-group col-sm-12 col-md-6">
                    <label for="objetivo" class="form-label fw-bold">Objetivo</label>
                    <select class="form-control" id="id_objetivo" name="id_objetivo" required>
                        <option value="" disabled selected>Selecciona un objetivo</option>
                        <?php foreach ($objetivos as $objetivo): ?>
                            <option value="<?php echo $objetivo['idObjetivo'] ?>"><?php echo $objetivo['nombre'] ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-12 col-md-6">
                    <label for="detalle" class="form-label fw-bold">Detalle</label>
                    <textarea class="form-control" id="detalle" name="detalle" rows="8" placeholder="Escribe aquí los detalles..." required></textarea>

                </div>
            </div>
            <div class="row">

                <div class="form-group col-sm-12 col-md-6">
                    <label for="inputGroupFile01" class="form-label">Adjuntar imagen o PDF</label>
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="inputGroupFile01" name="adjunto" accept=".jpg,.jpeg,.png,.webp,.avif,.pdf">
                            <label class="custom-file-label" for="inputGroupFile01">Selecciona un archivo</label>
                        </div>
                    </div>
                    <small class="form-text text-muted">Solo se permiten imágenes (jpg, png, webp, avif) o archivos PDF. Tamaño máximo: 5 MB.</small>
                </div>

            </div>

            <!-- /.card-body -->
            <div class="card-footer col-sm-12 col-md-6">
                <input type="submit" class="btn btn-success" name="Crear" value="Crear">
                <button type="reset" class="btn btn-default float-right">Borrar campos</button>
            </div>
            <!-- /.card-footer -->
        </form>
    </div>
    <!-- /.card-info -->

</div>
<!-- /.card-body -->
</div>