<?php
// Opcional: rol actual, para abrir "roles/permisos" con un rol por defecto
$rolActual    = $_SESSION['rol'] ?? '';
$tieneNuevo   = Auth::hasPermission('roles', 'vistaPermisosRol');   // nueva pantalla
$tieneLegacy  = Auth::hasPermission('permisos', 'index');           // compat vieja
$hrefPermisos = $tieneNuevo
    ? ('?r=roles/permisos' . ($rolActual ? '&rol=' . urlencode($rolActual) : ''))
    : ($tieneLegacy ? '?r=permisos/index' : '#');
?>

<?php if (
    $tieneNuevo || $tieneLegacy ||
    Auth::hasPermission('roles', 'vistaListadoRoles') ||
    Auth::hasPermission('roles', 'vistaCrearRol')
): ?>
    <li class="nav-item has-treeview">
        <a href="#" class="nav-link">
            <i class="nav-icon fas fa-user-shield text-danger"></i>
            <p>Roles y Permisos <i class="right fas fa-angle-left"></i></p>
        </a>

        <ul class="nav nav-treeview">
            <?php if ($tieneNuevo || $tieneLegacy): ?>
                <li class="nav-item">
                    <a href="<?= $hrefPermisos ?>" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Gestión de Permisos</p>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (Auth::hasPermission('roles', 'vistaListadoRoles')): ?>
                <li class="nav-item">
                    <a href="?r=roles/listado" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Listado de Roles</p>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (Auth::hasPermission('roles', 'vistaCrearRol')): ?>
                <li class="nav-item">
                    <a href="?r=roles/crear" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Crear Rol</p>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </li>
<?php endif; ?>