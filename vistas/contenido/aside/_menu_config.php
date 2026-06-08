<?php if (Auth::hasPermission('configuracion', 'vistaPanel')): ?>
    <li class="nav-item has-treeview">
        <a href="#" class="nav-link">
            <i class="nav-icon fas fa-cogs text-danger"></i>
            <p>Configuración <i class="fas fa-angle-left right"></i></p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="?r=configuracion/panel" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Configurar Sistema</p>
                </a>
            </li>
        </ul>
    </li>
<?php endif; ?>