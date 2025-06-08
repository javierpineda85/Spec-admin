<?php

require_once __DIR__ . '/../../../modelos/conexion.php';
require_once __DIR__ . '/../../../controladores/rondas.controller.php';


$idRonda = intval($_GET['id'] ?? 0);
if (!$idRonda) {
    echo "<p>ID de ronda inválido.</p>";
    exit;
}
$db = Conexion::conectar();
$stmt = $db->prepare(" SELECT r.*, o.nombre AS objetivo_nombre FROM rondas r JOIN objetivos o ON r.objetivo_id = o.idObjetivo WHERE r.idRonda = ?");
$stmt->execute([$idRonda]);
$ronda = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$ronda) {
    echo "<p>Ronda no encontrada.</p>";
    exit;
}

$sql = "SELECT * FROM objetivos ORDER BY nombre";
$objetivos = $db->query($sql)->fetchAll();
?>
<div class="card">
    <div class="card-header bg-info text-white">
        <h3 class="card-title">Editar Ronda #<?= $ronda['idRonda'] ?></h3>
    </div>
    <div class="card-body">
        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error_message'];
                unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        <form action="?r=actualizar_ronda" method="POST" class="form-horizontal">

            <input type="hidden" name="idRonda" value="<?= $ronda['idRonda'] ?>">
            <div class="row">
                <div class="form-group col-md-3">
                    <label>Puesto</label>
                    <input name="puesto" class="form-control" value="<?= htmlspecialchars($ronda['puesto'], ENT_QUOTES) ?>">
                </div>
                <div class="form-group col-md-3">
                    <label>Objetivo</label>
                    <select name="objetivo_id" class="form-control">
                        <?php foreach ($objetivos as $o): ?>
                            <option value="<?= $o['idObjetivo'] ?>"
                                <?= $o['idObjetivo'] == $ronda['objetivo_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($o['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>Tipo</label>
                    <select name="tipo" class="form-control">
                        <option value="Fija" <?= $ronda['tipo'] == 'Fija'     ? 'selected' : '' ?>>Fija</option>
                        <option value="Eventual" <?= $ronda['tipo'] == 'Eventual' ? 'selected' : '' ?>>Eventual</option>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>Orden</label>
                    <input type="number" name="orden" class="form-control" value="<?= htmlspecialchars($ronda['orden_escaneo'], ENT_QUOTES) ?>">
                </div>
                <div class="form-group col-md-2 align-self-end">
                    <button class="btn btn-success">Guardar Cambios</button>
                </div>
            </div>
        </form>
    </div>
</div>