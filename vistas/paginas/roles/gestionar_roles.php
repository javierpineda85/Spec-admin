<?php
// vistas/paginas/roles/permisos_rol.php
//Auth::check('roles', 'vistaPermisosRol');

/**
 * Variables esperadas desde RolesController::vistaPermisosRol:
 * - $rol            (array del rol actual, con claves: nombre, reservado, etc.)
 * - $roles          (array de roles para el <select>)
 * - $permisos       (array de permisos: id, controlador, accion, alias?, descripcion?)
 * - $idsAsignados   (array de ints con los permission_id ya asignados al rol)
 *
 * Si tu backend te pasa $permissions/$assignedIds/$r['rol'], también funciona con los fallback de abajo.
 */

// Fallbacks para compatibilidad con tu snippet original
$permissions  = $permissions  ?? $permisos   ?? [];
$assignedIds  = $assignedIds  ?? $idsAsignados ?? [];
$selectedRole = $selectedRole ?? ($rol['nombre'] ?? ($rol['rol'] ?? ''));

// El rol Programador queda bloqueado desde UI
$esReservado  = ((int)($rol['reservado'] ?? 0) === 1) || (mb_strtolower($selectedRole) === 'programador');
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">

            <div class="card">
                <div class="card-header bg-danger">
                    <h3 class="card-title text-white">Gestión de Permisos</h3>
                </div>

                <div class="card-body">

                    <div class="card bg-warning">
                        <p class="p-3 text center">
                            <i class="fas fa-exclamation-triangle"></i>
                            No realizar ningún cambio si no está seguro de cómo funciona el permiso. Esto puede afectar gravemente al sistema.
                            <i class="fas fa-exclamation-triangle"></i>
                        </p>
                    </div>

                    <?php if (!empty($_SESSION['success_message'])): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($_SESSION['success_message']);
                            unset($_SESSION['success_message']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($_SESSION['error_message']);
                            unset($_SESSION['error_message']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="?r=roles/ctrGuardarPermisosRol" method="post">
                        <?php if (!empty($roles)): ?>
                            <div class="form-group">
                                <label for="roleSelect">Selecciona un rol:</label>
                                <select
                                    name="role_dummy"
                                    id="roleSelect"
                                    class="form-control"
                                    onchange="location = '?r=roles/permisos&rol='+encodeURIComponent(this.value)">
                                    <?php foreach ($roles as $r): ?>
                                        <?php $nombreRol = $r['nombre'] ?? $r['rol'] ?? ''; ?>
                                        <option value="<?= htmlspecialchars($nombreRol); ?>"
                                            <?= $nombreRol === $selectedRole ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($nombreRol); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php else: ?>
                            <!-- Fallback: sin select, solo mostramos el rol actual -->
                            <div class="form-group">
                                <label>Rol:</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($selectedRole); ?>" readonly>
                            </div>
                        <?php endif; ?>


                        <!-- El rol efectivo viaja en hidden -->
                        <input type="hidden" name="rol" value="<?= htmlspecialchars($selectedRole); ?>">

                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Controlador</th>
                                    <th>Acción</th>
                                    <th>Descripción</th>
                                    <th style="text-align:center;">Asignar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($permissions as $p): ?>
                                    <?php
                                    // ID, controlador y acción
                                    $id   = (int) ($p['id'] ?? 0);
                                    $ctrl = htmlspecialchars($p['controlador']  ?? '', ENT_QUOTES, 'UTF-8');
                                    $acc  = htmlspecialchars($p['accion']        ?? '', ENT_QUOTES, 'UTF-8');

                                    // Alias amigable: usamos alias si existe, si no descripción, si no cadena vacía
                                    $rawAlias   = trim((string) ($p['alias'] ?? ''));
                                    $fallback   = $p['descripcion'] ?? '';
                                    $textoDesc  = $rawAlias !== '' ? $rawAlias : $fallback;
                                    $desc       = htmlspecialchars($textoDesc, ENT_QUOTES, 'UTF-8');

                                    // Estado del checkbox
                                    $checked  = in_array($id, $assignedIds, true) ? 'checked'  : '';
                                    $disabled = $esReservado                     ? 'disabled' : '';
                                    ?>
                                    <tr>
                                        <td><?= $ctrl; ?></td>
                                        <td><?= $acc;  ?></td>
                                        <td><?= $desc; ?></td>
                                        <td class="text-center">
                                            <input
                                                type="checkbox"
                                                name="permission_ids[]"
                                                value="<?= $id; ?>"
                                                <?= $checked; ?>
                                                <?= $disabled; ?>>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>


                        <button type="submit" class="btn btn-primary" <?= $esReservado ? 'disabled' : ''; ?>>
                            Guardar cambios
                        </button>

                        <?php if ($esReservado): ?>
                            <small class="text-muted d-block mt-2">
                                El rol <strong>Programador</strong> es reservado. No se permiten cambios desde la UI.
                            </small>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>