<?php if (
    Auth::hasPermission('objetivos', 'vistaCreaObjetivo') ||
    Auth::hasPermission('objetivos', 'vistaListadoObjetivos') ||
    Auth::hasPermission('objetivos', 'vistaListadoObjetivosInactivos') ||
    Auth::hasPermission('objetivos', 'vistaEditarObjetivo')
): ?>
    <li class="nav-item has-treeview">
        <a href="#" class="nav-link">
            <i class="nav-icon fas fa-map-marker-alt text-success"></i>
            <p>Objetivos <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
            <?php if (Auth::hasPermission('objetivos', 'vistaCrearObjetivo')): ?>
                <li class="nav-item">
                    <a href="?r=crear_objetivo" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Crear</p>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('objetivos', 'vistaListadoObjetivos')): ?>
                <li class="nav-item">
                    <a href="?r=listado_objetivos" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Mostrar Activos</p>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('objetivos', 'vistaListadoObjetivosInactivos')): ?>
                <li class="nav-item">
                    <a href="?r=listado_objetivos_inactivos" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Mostrar Inactivos</p>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </li>
<?php endif; ?>