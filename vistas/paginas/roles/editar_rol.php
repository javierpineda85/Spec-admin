<?php
//Auth::check('roles', 'vistaEditarRol');
$esReservado = (int)($rol['reservado'] ?? 0) === 1;
$soyProgramador = isset($_SESSION['nivel']) && $_SESSION['nivel'] == 99 && $_SESSION['reservado'] == 1;
?>

<div class="card">
    <div class="card-header bg-info text-white d-flex align-items-center justify-content-between">
        <h3 class="card-title mb-0">Editar Rol</h3>
        <?php if ($esReservado): ?>
            <span class="badge badge-danger">Reservado</span>
        <?php endif; ?>
    </div>

    <form class="form-horizontal" action="?r=roles/ctrActualizarRol" method="POST" autocomplete="off">
        <div class="card-body">

            <?php if (!empty($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($_SESSION['error_message']); ?>
                    <?php unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <input type="hidden" name="id" value="<?= (int)$rol['id'] ?>">

            <!-- Nombre -->
            <div class="form-group row">
                <label for="nombre" class="col-sm-2 col-form-label">Nombre*</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="nombre" id="nombre"
                        value="<?= htmlspecialchars($rol['nombre']) ?>"
                        <?= $esReservado ? 'readonly' : '' ?> required>
                    <small class="form-text text-muted">Evitar usar “Programador” (reservado).</small>
                </div>
            </div>

            <!-- Alias -->
            <div class="form-group row">
                <label for="alias" class="col-sm-2 col-form-label">Alias</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="alias" id="alias"
                        value="<?= htmlspecialchars($rol['alias'] ?? '') ?>"
                        <?= $esReservado ? 'readonly' : '' ?>>
                </div>
            </div>

            <!-- Tipo -->
            <div class="form-group row">
                <label for="tipo" class="col-sm-2 col-form-label">Tipo</label>
                <div class="col-sm-10">
                    <select class="form-control" name="tipo" id="tipo" <?= $esReservado ? 'disabled' : '' ?>>
                        <option value="fijo" <?= ($rol['tipo'] === 'fijo' ? 'selected' : '') ?>>Fijo</option>
                        <option value="temporal" <?= ($rol['tipo'] === 'temporal' ? 'selected' : '') ?>>Temporal</option>
                    </select>
                </div>
            </div>

            <!-- Categoría -->
            <div class="form-group row">
                <label for="categoria" class="col-sm-2 col-form-label">Categoría*</label>
                <div class="col-sm-10">
                    <select class="form-control" name="categoria" id="categoria" <?= $esReservado ? 'disabled' : '' ?> required>
                        <option value="operativo" <?= ($rol['categoria'] === 'operativo' ? 'selected' : '') ?>>Operativo</option>
                        <option value="referente" <?= ($rol['categoria'] === 'referente' ? 'selected' : '') ?>>Referente</option>
                        <option value="baseOperativa" <?= ($rol['categoria'] === 'baseOperativa' ? 'selected' : '') ?>>Base Operativa</option>
                        <option value="supervisor" <?= ($rol['categoria'] === 'supervisor' ? 'selected' : '') ?>>Supervisor</option>
                        <option value="administrativo" <?= ($rol['categoria'] === 'administrativo' ? 'selected' : '') ?>>Administrativo</option>
                        <option value="direccion" <?= ($rol['categoria'] === 'direccion' ? 'selected' : '') ?>>Dirección/Gerencia</option>
                        <?php if ($soyProgramador): ?>
                            <option value="reservado" <?= ($rol['categoria'] === 'reservado' ? 'selected' : '') ?>>Reservado/Sistema</option>
                        <?php endif; ?>
                    </select>
                    <small class="form-text text-muted">
                        El nivel jerárquico se ajustará automáticamente según la categoría.
                    </small>
                </div>
            </div>

            <!-- Estado -->
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">Estado</label>
                <div class="col-sm-10">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="activo" name="activo" value="1"
                            <?= ((int)$rol['activo'] === 1 ? 'checked' : '') ?> <?= $esReservado ? 'disabled' : '' ?>>
                        <label class="form-check-label" for="activo">Activo</label>
                    </div>
                </div>
            </div>

        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="?r=roles/listado" class="btn btn-secondary">Volver</a>

            <div>
                <?php if (!$esReservado): ?>
                    <button type="submit" class="btn btn-info">Guardar</button>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>
