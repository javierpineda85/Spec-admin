<?php if (Auth::hasPermission('rondas', 'vistaCrearRondas') || Auth::hasPermission('rondas', 'vistaListadoRondas') || Auth::hasPermission('rondas', 'vistaEscanearRondas')): ?>
    <li class="nav-item has-treeview">
        <a href="#" class="nav-link">
            <i class="nav-icon fas fa-sync-alt text-success"></i>
            <p>Rondas <i class="fas fa-angle-left right"></i></p>
        </a>
        <ul class="nav nav-treeview">
            <?php if (Auth::hasPermission('rondas', 'vistaCrearRondas')): ?>
                <li class="nav-item">
                    <a href="?r=crear_rondas" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Crear</p>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('rondas', 'vistaListadoRondas')): ?>
                <li class="nav-item">
                    <a href="?r=listado_rondas" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Mostrar Todas</p>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('rondas', 'vistaEscanearRondas')): ?>
                <li class="nav-item">
                    <a href="?r=escanear" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Escanear QR</p>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </li>
<?php endif; ?>