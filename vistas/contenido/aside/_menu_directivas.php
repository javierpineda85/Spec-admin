<?php if (
    Auth::hasPermission('directivas', 'crtGuardarDirectiva') || 
    Auth::hasPermission('directivas', 'vistaListadoDirectivas') || 
    Auth::hasPermission('directivas', 'vistaCrearDirectiva')
): ?>
    <li class="nav-item has-treeview">
        <a href="#" class="nav-link">
            <i class="nav-icon fas fa-list-ul text-warning"></i>
            <p>Directivas <i class="fas fa-angle-left right"></i></p>
        </a>
        <ul class="nav nav-treeview">
            <?php if (Auth::hasPermission('directivas', 'crtGuardarDirectiva') || Auth::hasPermission('directivas', 'vistaCrearDirectiva')): ?>
                <li class="nav-item">
                    <a href="?r=vistaCrearDirectiva" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Crear</p>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('directivas', 'vistaListadoDirectivas')): ?>
                <li class="nav-item">
                    <a href="?r=listado_directivas" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Mostrar Todas</p>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </li>
<?php endif; ?>
