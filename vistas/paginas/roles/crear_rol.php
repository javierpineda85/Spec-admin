<?php
//Auth::check('roles', 'vistaCrearRol');
?>

<div class="card">
    <div class="card-header bg-info text-white">
        <h3 class="card-title">Crear Rol</h3>
    </div>

    <form class="form-horizontal" action="?r=roles/ctrGuardarRol" method="POST" autocomplete="off">
        <div class="card-body">

            <?php if (!empty($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error_message']);
                                                unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>

            <div class="form-group row">
                <label for="nombre" class="col-sm-2 col-form-label">Nombre*</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="nombre" id="nombre" required placeholder="Ej: SupervisorIII">
                    <small class="form-text text-muted">Evitar usar <strong>Programador</strong> (reservado).</small>
                </div>
            </div>

            <div class="form-group row">
                <label for="alias" class="col-sm-2 col-form-label">Alias</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="alias" id="alias" placeholder="Nombre legible (opcional)">
                </div>
            </div>

            <div class="form-group row">
                <label for="tipo" class="col-sm-2 col-form-label">Tipo</label>
                <div class="col-sm-10">
                    <select class="form-control" name="tipo" id="tipo">
                        <option value="fijo" selected>Fijo</option>
                        <option value="temporal">Temporal</option>
                    </select>
                </div>
            </div>

        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="?r=roles/listado" class="btn btn-secondary">Volver</a>
            <button type="submit" class="btn btn-info"> Guardar</button>
        </div>
    </form>
</div>