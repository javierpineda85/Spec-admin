<?php
// Solo rol programador puede acceder
Auth::check('roles', 'vistaConfigSistema');
?>

<div class="card">
    <div class="card-header bg-info text-white">
        <h3 class="card-title">Configuración del Sistema</h3>
    </div>

    <form class="form-horizontal" action="?r=configuracion/ctrGuardarConfig" method="POST" autocomplete="off">
        <div class="card-body">

            <?php if (!empty($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($_SESSION['error_message']); ?>
                    <?php unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success_message']); ?>
                    <?php unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <!-- Nombre del sistema -->
            <div class="form-group row">
                <label for="system_name" class="col-sm-2 col-form-label">Nombre del sistema</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="system_name" id="system_name"
                           value="<?= htmlspecialchars($config['system_name'] ?? '') ?>" required>
                </div>
            </div>

            <!-- Título -->
            <div class="form-group row">
                <label for="system_title" class="col-sm-2 col-form-label">Título</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="system_title" id="system_title"
                           value="<?= htmlspecialchars($config['system_title'] ?? '') ?>" required>
                </div>
            </div>

            <!-- Descripción -->
            <div class="form-group row">
                <label for="system_description" class="col-sm-2 col-form-label">Descripción</label>
                <div class="col-sm-10">
                    <textarea class="form-control" name="system_description" id="system_description" rows="3" required><?= htmlspecialchars($config['system_description'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Autor -->
            <div class="form-group row">
                <label for="system_author" class="col-sm-2 col-form-label">Autor</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="system_author" id="system_author"
                           value="<?= htmlspecialchars($config['system_author'] ?? '') ?>">
                </div>
            </div>

            <!-- Versión -->
            <div class="form-group row">
                <label for="system_version" class="col-sm-2 col-form-label">Versión</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="system_version" id="system_version"
                           value="<?= htmlspecialchars($config['system_version'] ?? '') ?>">
                </div>
            </div>

            <!-- Favicon -->
            <div class="form-group row">
                <label for="system_favicon" class="col-sm-2 col-form-label">Favicon</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="system_favicon" id="system_favicon"
                           value="<?= htmlspecialchars($config['system_favicon'] ?? '') ?>" placeholder="Ruta al favicon">
                </div>
            </div>

            <!-- Propietario -->
            <div class="form-group row">
                <label for="system_owner" class="col-sm-2 col-form-label">Propietario</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="system_owner" id="system_owner"
                           value="<?= htmlspecialchars($config['system_owner'] ?? '') ?>">
                </div>
            </div>

        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="?r=dashboard" class="btn btn-secondary">Volver</a>
            <button type="submit" class="btn btn-info">Guardar</button>
        </div>
    </form>
</div>