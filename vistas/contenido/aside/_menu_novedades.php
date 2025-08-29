<?php if (
    Auth::hasPermission('novedades', 'vistaCrearNovedades') ||
    Auth::hasPermission('novedades', 'vistaListadoNovedades') ||
    Auth::hasPermission('novedades', 'vistaListadoEntradaSalida')
): ?>
    <li class="nav-item has-treeview">
        <a href="#" class="nav-link">
            <i class="nav-icon far fa-newspaper text-info"></i>
            <p>Novedades <i class="right fas fa-angle-left"></i></p>
        </a>
        <ul class="nav nav-treeview">
            <?php if (Auth::hasPermission('novedades', 'vistaEntradaSalida')): ?>
                <li class="nav-item">
                    <a href="?r=entradas_salidas" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Marcar Entrada/Salida</p>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('novedades', 'vistaCrearNovedades')): ?>
                <li class="nav-item">
                    <a href="?r=crear_novedad" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Crear Novedad</p>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('novedades', 'vistaListadoNovedades')): ?>
                <li class="nav-item">
                    <a href="?r=listado_novedades" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Mostrar Novedades</p>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('novedades', 'vistaListadoEntradaSalida')): ?>
                <li class="nav-item">
                    <a href="?r=reporte_entradas_salidas" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Reporte Ingresos/Salidas</p>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('novedades', 'vistaHistorialMarcaciones')): ?>
                <li class="nav-item">
                    <a href="?r=historialMarcaciones" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Historial Ingresos/Salidas</p>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </li>
<?php endif; ?>