<?php $esRestringido = in_array(($_SESSION['rol'] ?? ''), ['Vigilador', 'Referente'], true)
    || in_array(($_SESSION['categoria'] ?? ''), ['operativo', 'referente'], true); ?>
<?php if (
    !$esRestringido && (
        Auth::hasPermission('usuarios', 'vistaCrearUsuario') ||
        Auth::hasPermission('usuarios', 'vistaListadoUsuarios') ||
        Auth::hasPermission('usuarios', 'vistaListadoUsuariosInactivos') ||
        Auth::hasPermission('usuarios', 'vistaPerfilUsuario') ||
        Auth::hasPermission('legajos', 'vistaLegajos')
    )
): ?>
    <li class="nav-item has-treeview">
        <a href="#" class="nav-link">
            <i class="nav-icon fas fa-users text-info"></i>
            <p>Usuarios <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
            <?php if (!$esRestringido && Auth::hasPermission('usuarios', 'vistaCrearUsuario')): ?>
                <li class="nav-item">
                    <a href="?r=crear-usuario" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Crear</p>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (!$esRestringido && Auth::hasPermission('usuarios', 'vistaListadoUsuarios')): ?>
                <li class="nav-item">
                    <a href="?r=listado-usuarios" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Mostrar Activos</p>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (!$esRestringido && Auth::hasPermission('usuarios', 'vistaListadoUsuariosInactivos')): ?>
                <li class="nav-item">
                    <a href="?r=listado-usuarios-inactivos" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Mostrar Inactivos</p>
                    </a>
                </li>
            <?php endif; ?>

        </ul>
    </li>
<?php endif; ?>
