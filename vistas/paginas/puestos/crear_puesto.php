<?php

$db = new Conexion;
$sql = "SELECT * FROM objetivos WHERE activo = 1 ORDER BY nombre ";
$objetivos = $db->consultas($sql);
?>
<div class="card">
    <div class="card-header bg-info text-white">
        <h3 class="card-title">Completa los datos para generar un nuevo puesto</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Aquí definimos el action para invocar el método del controlador Qr -->
        <form action="" method="POST" class="form-horizontal">
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-sm-12 col-md-3">
                        <label class="form-label">Sector / Puesto</label>
                        <input type="text" class="form-control" placeholder="Puesto 1" name="puesto">
                    </div>
                    <div class="form-group col-sm-12 col-md-3">
                        <label class="form-label">Objetivo</label>
                        <select id="objetivo" name="objetivo_id" class="form-control">
                            <option value="" disabled selected>Selecciona un objetivo</option>
                            <?php foreach ($objetivos as $objetivo): ?>
                                <option value="<?php echo $objetivo['idObjetivo'] ?>"><?php echo $objetivo['nombre'] ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-12 col-md-2">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" id="" class="form-control">
                            <option value="" selected disabled>Elige una opción</option>
                            <option value="Fijo">Fijo</option>
                            <option value="Eventual">Eventual</option>
                        </select>
                    </div>
                </div>
                <div class="card-footer">

                    <?php
                    $registro =  ControladorPuestos::ctrGuardarPuesto();
                    ?>

                    <input type="submit" class="btn btn-success" value="Crear Puesto">
                    <button type="reset" class="btn btn-default float-right">Borrar campos</button>

                    <?php if (!empty($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible mt-3">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="icon fas fa-check"></i>
                            <?= $_SESSION['success_message'];
                            unset($_SESSION['success_message']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </form>
    </div>
</div>