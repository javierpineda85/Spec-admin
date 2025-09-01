<?php
if (isset($_POST['Modificar'])) {
    $registro = ControladorDirectivas::crtModificarDirectiva();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$db = new Conexion;
$sql = "SELECT d.idDirectiva, d.id_objetivo, o.nombre, d.detalle, d.adjunto, d.tipo 
        FROM directivas d 
        JOIN objetivos o ON d.id_objetivo = o.idObjetivo 
        WHERE d.idDirectiva = $id";
$directiva = $db->consultas($sql);

$sql = "SELECT idObjetivo, nombre FROM objetivos";
$objetivos = $db->consultas($sql);
?>

<div class="card">
    <div class="card-header bg-info text-white">
        <h3 class="card-title">Modificar directiva</h3>
    </div>
    <div class="card-body">
        <form class="form-horizontal" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="idDirectiva" value="<?= $directiva[0]['idDirectiva']; ?>">

            <div class="row">
                <div class="form-group col-sm-12 col-md-6">
                    <label for="id_objetivo" class="form-label fw-bold">Objetivo</label>
                    <select class="form-control" id="id_objetivo" name="id_objetivo" required>
                        <option value="<?= $directiva[0]['id_objetivo']; ?>" selected><?= htmlspecialchars($directiva[0]['nombre']) ?></option>
                        <?php foreach ($objetivos as $objetivo): ?>
                            <option value="<?= $objetivo['idObjetivo'] ?>"><?= htmlspecialchars($objetivo['nombre']) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="form-group col-sm-12 col-md-6">
                    <label for="tipo" class="form-label fw-bold">Tipo de Directiva</label>
                    <select class="form-control" id="tipo" name="tipo" required>
                        <option value="general" <?= $directiva[0]['tipo'] === 'general' ? 'selected' : '' ?>>General</option>
                        <option value="particular" <?= $directiva[0]['tipo'] === 'particular' ? 'selected' : '' ?>>Particular</option>
                        <option value="eventual" <?= $directiva[0]['tipo'] === 'eventual' ? 'selected' : '' ?>>Eventual</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="form-group col-sm-12 col-md-6">
                    <label for="detalle" class="form-label fw-bold">Detalle</label>
                    <textarea class="form-control" id="detalle" name="detalle" rows="8" required><?= htmlspecialchars($directiva[0]['detalle']) ?></textarea>
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
                                <?= !empty($directiva[0]['adjunto']) ? htmlspecialchars(basename($directiva[0]['adjunto']), ENT_QUOTES, 'UTF-8') : "Selecciona un archivo" ?>
                            </label>
                        </div>
                    </div>
                    <?php if (!empty($directiva[0]['adjunto'])): ?>
                        <div class="mt-2">
                            <a href="<?= htmlspecialchars($directiva[0]['adjunto']) ?>" target="_blank" class="btn btn-sm btn-primary">Ver adjunto actual</a>
                        </div>
                    <?php endif; ?>
                    <small class="form-text text-muted">Solo imágenes (jpg, png, webp, avif) o PDF. Máx: 5 MB.</small>
                </div>
            </div>

            <div class="card-footer col-sm-12 col-md-6">
                <input type="submit" class="btn btn-success" value="Modificar" name="Modificar">
                <button type="reset" class="btn btn-default float-right">Borrar campos</button>
            </div>
        </form>
    </div>
</div>
