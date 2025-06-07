<?php
if (isset($_POST['Modificar'])) {
    $registro = ControladorDirectivas::crtModificarDirectiva();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;;
$db = new Conexion;
$sql = "SELECT d.idDirectiva, d.id_objetivo, o.nombre, d.detalle, d.adjunto FROM directivas d JOIN objetivos o ON d.id_objetivo = o.idObjetivo WHERE d.idDirectiva = $id";
$directiva = $db->consultas($sql);

$db = new Conexion;
$sql = "SELECT idObjetivo, nombre FROM objetivos";
$objetivos = $db->consultas($sql);

?>

<!-- Default box -->
<div class="card">
    <div class="card-header bg-info text-white">
        <h3 class="card-title">Completa el formulario para crear modificar la directiva</h3>

    </div>
    <div class="card-body">
        <div class="card card-info">

            <form class="form-horizontal" method="POST" enctype="multipart/form-data">

                <div class="card-body">
                    <?php if (!empty($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible mt-3">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="icon fas fa-check"></i>
                            <?= $_SESSION['success_message'];
                            unset($_SESSION['success_message']); ?>
                        </div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="form-group col-sm-12 col-md-6">

                            <input type="number" name="idDirectiva" value="<?php echo $directiva[0]['idDirectiva']; ?>" hidden>

                            <label for="objetivo" class="form-label fw-bold">Objetivo</label>
                            <select class="form-control" id="id_objetivo" name="id_objetivo" required>
                                <option value="<?php echo $directiva[0]['id_objetivo']; ?>" selected><?php echo $directiva[0]['nombre']; ?></option>
                                <?php foreach ($objetivos as $objetivo): ?>
                                    <option value="<?php echo $objetivo['idObjetivo'] ?>"><?php echo $objetivo['nombre'] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-12 col-md-6">
                            <label for="detalle" class="form-label fw-bold">Detalle</label>
                            <textarea class="form-control" id="detalle" name="detalle" rows="8" placeholder="Escribe aquí los detalles..." required><?php echo $directiva[0]['detalle']; ?></textarea>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-sm-12 col-md-6">
                            <label for="inputGroupFile01" class="form-label">Adjuntar imagen o PDF</label>
                            <div class="input-group">
                                <div class="custom-file">

                                    <input type="hidden" name="adjuntoActual" value="<?= htmlspecialchars($directiva[0]['adjunto'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="file" class="custom-file-input" id="inputGroupFile01" name="adjunto" accept=".jpg,.jpeg,.png,.webp,.avif,.pdf">
                                    <label class="custom-file-label" for="inputGroupFile01">
                                        <?php
                                        if (!empty($directiva[0]['adjunto'])) {
                                            // basename() nunca recibirá null porque estamos comprobando con !empty()
                                            echo htmlspecialchars(basename($directiva[0]['adjunto']), ENT_QUOTES, 'UTF-8');
                                        } else {
                                            echo "Selecciona un archivo";
                                        }
                                        ?>
                                    </label>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Solo se permiten imágenes (jpg, png, webp, avif) o archivos PDF. Tamaño máximo: 5 MB.
                            </small>

                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer col-sm-12 col-md-6">
                        <input type="submit" class="btn btn-success" value="Modificar" name="Modificar">
                        <button type="reset" class="btn btn-default float-right">Borrar campos</button>
                    </div>
                    <!-- /.card-footer -->
            </form>
        </div>

    </div>
    <!-- /.card-body -->

</div>
<!-- /.card -->