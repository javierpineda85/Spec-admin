<?php
//Auth::check('roles', 'vistaListadoRoles');
?>
<style>
    .badge{
        padding: 8px 16px !important;
    }
</style>
<div class="card">
    <div class="card-header bg-info text-white d-flex align-items-center justify-content-between">
        <h3 class="card-title mb-0">Roles</h3>
        <div>
            <a href="?r=roles/crear" class="btn btn-sm btn-light text-info font-weight-bold">
                <i class="fas fa-plus"></i> Nuevo Rol
            </a>
        </div>
    </div>

    <div class="card-body">
        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']);
                                                unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error_message']);
                                            unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover" id="tablaRoles">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 24%">Rol</th>
                        <th style="width: 30%">Alias</th>
                        <th style="width: 12%">Tipo</th>
                        <th style="width: 12%">Estado</th>
                        <th style="width: 10%">Reservado</th>
                        <th style="width: 12%">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['nombre']) ?></td>
                            <td><?= htmlspecialchars($r['alias'] ?? '') ?></td>
                            <td><span class="badge badge-<?= ($r['tipo'] === 'temporal' ? 'warning' : 'info') ?>"><?= htmlspecialchars($r['tipo']) ?></span></td>
                            <td>
                                <?php if ((int)$r['activo'] === 1): ?>
                                    <span class="badge badge-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ((int)$r['reservado'] === 1): ?>
                                    <span class="badge badge-danger">Sí</span>
                                <?php else: ?>
                                    <span class="badge badge-light">No</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group" aria-label="Acciones">
                                    <a href="?r=roles/permisos&rol=<?= urlencode($r['nombre']) ?>" class="btn btn-outline-info" title="Permisos">
                                        <i class="fas fa-key"></i>
                                    </a>
                                    <a href="?r=roles/editar&id=<?= (int)$r['id'] ?>" class="btn btn-outline-primary" title="Editar"
                                        <?= ((int)$r['reservado'] === 1) ? 'disabled tabindex="-1" aria-disabled="true"' : '' ?>>
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="?r=roles/ctrDesactivarRol" method="POST" class="d-inline"
                                        onsubmit="return confirm('¿Desactivar este rol?');">
                                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger"
                                            <?= ((int)$r['reservado'] === 1) ? 'disabled tabindex="-1" aria-disabled="true"' : '' ?>>
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <small class="text-muted d-block mt-2">
            <i class="fas fa-info-circle"></i> El rol <strong>Programador</strong> es reservado: no puede editarse ni desactivarse.
        </small>
    </div>
</div>
