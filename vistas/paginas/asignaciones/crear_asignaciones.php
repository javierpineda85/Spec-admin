<?php

$db = new Conexion;
$sql = "SELECT * FROM usuarios WHERE rol = 'Vigilador'ORDER BY apellido ASC ";
$usuarios = $db->consultas($sql);

$db = new Conexion;
$sql = "SELECT * FROM objetivos";
$objetivos = $db->consultas($sql);

$db = new Conexion;
$sql = "SELECT u.nombre, o.nombre 
                FROM usuario_objetivo uo
                JOIN usuarios u ON u.idUsuario = uo.usuario_id
                JOIN objetivos o ON o.idObjetivo = uo.objetivo_id";
$asignaciones = $db->consultas($sql);

?>
<!-- Default box -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Asignar objetivos de trabajo</h3>

        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                <i class="fas fa-minus"></i>
            </button>

        </div>
    </div>
    <div class="body">

        <form method="POST" action="#">
            <div class="form-group">
                <label for="usuario">Empleado:</label>
                <select name="usuario_id" id="usuario" class="form-control">
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?= $usuario['idUsuario'] ?>"><?= $usuario['nombre'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="objetivo">Puesto de trabajo:</label>
                <select name="objetivo_id" id="objetivo" class="form-control">
                    <?php foreach ($objetivos as $objetivo): ?>
                        <option value="<?= $objetivo['idObjetivo'] ?>"><?= $objetivo['nombre'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php //$registro = controlladorAsignaciones::agregarAsignacionTemporal();?>
            <button type="submit" class="btn btn-info">Asignar</button>
        </form>

        <?php if (!empty($_SESSION['asignaciones'])): ?>
            <hr>
            <h4>Asignaciones Pendientes</h4>
            <form method="POST" action="#">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Puesto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['asignaciones'] as $a): ?>
                            <tr>
                                <td>
                                    <?php
                                    // Obtener el nombre del usuario
                                    foreach ($usuarios as $u) {
                                        if ($u['idUsuario'] == $a['usuario_id']) echo $u['nombre'];
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    // Obtener el nombre del objetivo
                                    foreach ($objetivos as $o) {
                                        if ($o['idObjetivo'] == $a['objetivo_id']) echo $o['nombre'];
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-success">Guardar Asignaciones</button>
            </form>
        <?php endif; ?>

    </div>

    <!-- /.card-body -->

</div>