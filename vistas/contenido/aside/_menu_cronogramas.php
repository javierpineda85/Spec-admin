<?php if (
    Auth::hasPermission('cronogramas', 'vistaCrearCronograma') ||
    Auth::hasPermission('cronogramas', 'vistaListadoCronogramas') ||
    Auth::hasPermission('cronogramas', 'vistaListadoCronogramaPorVigilador') ||
    Auth::hasPermission('cronogramas', 'vistaJornadasPorObjetivo') ||
    Auth::hasPermission('cronogramas', 'crtBuscarResumenHoras')
): ?>
    <li class="nav-item has-treeview">
        <a href="#" class="nav-link">
            <i class="nav-icon far fa-calendar-alt text-primary"></i>
            <p>Cronogramas <i class="fas fa-angle-left right"></i></p>
        </a>
        <ul class="nav nav-treeview">
            <?php if (Auth::hasPermission('cronogramas', 'vistaCrearCronograma')): ?>
                <li class="nav-item">
                    <a href="?r=crear_cronograma" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Crear</p>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('cronogramas', 'vistaListadoCronogramas')): ?>
                <li class="nav-item">
                    <a href="?r=listado_cronogramas" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Por Objetivo</p>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('cronogramas', 'vistaListadoCronogramaPorVigilador')): ?>
                <li class="nav-item">
                    <a href="?r=listado_porVigilador" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Por Vigilador</p>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('cronogramas', 'vistaJornadasPorObjetivo')): ?>
                <li class="nav-item">
                    <a href="?r=listado_resumen_diario" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Jornadas por Objetivo</p>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('cronogramas', 'crtBuscarResumenHoras')): ?>
                <li class="nav-item">
                    <a href="?r=reporte_porHoras" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Horas por Objetivo</p>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('cronogramas', 'vistaHorasPorVigilador')): ?>
                <li class="nav-item">
                    <a href="?r=reporte_porVigilador" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Horas por Vigilador</p>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </li>
<?php endif; ?>