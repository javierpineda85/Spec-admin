<?php if (
    Auth::hasPermission('feriados', 'vistaCrearFeriados') ||
    Auth::hasPermission('feriados', 'vistaListadoFeriados') ||
    Auth::hasPermission('art', 'vistaCrearArt') ||
    Auth::hasPermission('art', 'vistaListadoArt')
): ?>
    <li class="nav-item has-treeview">
        <a href="#" class="nav-link">
            <i class="nav-icon fas fa-cogs text-warning"></i>
            <p>Administración <i class="fas fa-angle-left right"></i></p>
        </a>
        <ul class="nav nav-treeview">

            <?php if (Auth::hasPermission('feriados', 'vistaCrearFeriados')): ?>
                <li class="nav-item">
                    <a href="?r=crear_feriados" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Crear Feriados</p>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (Auth::hasPermission('feriados', 'vistaListadoFeriados')): ?>
                <li class="nav-item">
                    <a href="?r=listado_feriados" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Ver Feriados</p>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (Auth::hasPermission('art', 'vistaCrearArt')): ?>
                <li class="nav-item">
                    <a href="?r=crear_art" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Crear A.R.T.</p>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (Auth::hasPermission('art', 'vistaListadoArt')): ?>
                <li class="nav-item">
                    <a href="?r=listado_art" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Listado A.R.T.</p>
                    </a>
                </li>
            <?php endif; ?>

        </ul>
    </li>
<?php endif; ?>
