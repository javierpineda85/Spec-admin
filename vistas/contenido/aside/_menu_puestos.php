<?php if (
    Auth::hasPermission('puestos', 'vistaCrearPuestos') ||
    Auth::hasPermission('puestos', 'vistaListadoPuestos') ||
    Auth::hasPermission('puestos', 'vistaListadoPuestosDesactivados') ||
    Auth::hasPermission('puestos', 'vistaRotaciones') /* opcional: para mostrar el menú si solo tienen rotaciones */
): ?>
    <li class="nav-item has-treeview">
        <a href="#" class="nav-link">
            <i class="nav-icon fas fa-eye text-success"></i>
            <p>Puestos <i class="fas fa-angle-left right"></i></p>
        </a>
        <ul class="nav nav-treeview">
            <?php if (Auth::hasPermission('puestos', 'vistaCrearPuestos')): ?>
                <li class="nav-item">
                    <a href="?r=crear_puesto" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Crear</p>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (Auth::hasPermission('puestos', 'vistaListadoPuestos')): ?>
                <li class="nav-item">
                    <a href="?r=listado_puestos" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Mostrar Activos</p>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (Auth::hasPermission('puestos', 'vistaListadoPuestosDesactivados')): ?>
                <li class="nav-item">
                    <a href="?r=listado_puestos_inactivos" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Mostrar Inactivos</p>
                    </a>
                </li>
            <?php endif; ?>

            <?php /* opcional: agregar acceso directo a Rotaciones */
            if (Auth::hasPermission('puestos', 'vistaRotaciones')): ?>
                <li class="nav-item">
                    <a href="?r=rotaciones_puestos" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Asignar Puestos</p>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </li>
<?php endif; ?>