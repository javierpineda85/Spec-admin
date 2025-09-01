<?php if (
    Auth::hasPermission('cronograma', 'vistaCrearCronograma') ||
    Auth::hasPermission('cronograma', 'vistaListadoCronogramas') ||
    Auth::hasPermission('cronograma', 'vistaListadoCronogramaPorVigilador') ||
    Auth::hasPermission('cronograma', 'vistaJornadasPorObjetivo') ||
    Auth::hasPermission('cronograma', 'vistaReporteHorasPorObjetivo') ||
    Auth::hasPermission('cronograma', 'vistaHorasPorVigilador')
): ?>
    <li class="nav-item has-treeview">
        <a href="#" class="nav-link">
            <i class="nav-icon far fa-calendar-alt text-primary"></i>
            <p>Cronogramas <i class="fas fa-angle-left right"></i></p>
        </a>
        <ul class="nav nav-treeview">
            <?php if (Auth::hasPermission('cronograma', 'vistaCrearCronograma')): ?>
                <li class="nav-item">
                    <a href="?r=crear_cronograma" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Crear</p>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (Auth::hasPermission('cronograma', 'vistaListadoCronogramas')): ?>
                <li class="nav-item">
                    <a href="?r=listado_cronogramas" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Por Objetivo</p>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (Auth::hasPermission('cronograma', 'vistaListadoCronogramaPorVigilador')): ?>
                <li class="nav-item">
                    <a href="?r=listado_porVigilador" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Por Vigilador</p>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (Auth::hasPermission('cronograma', 'vistaJornadasPorObjetivo')): ?>
                <li class="nav-item">
                    <a href="?r=listado_resumen_diario" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Jornadas por Objetivo</p>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (Auth::hasPermission('cronograma', 'vistaReporteHorasPorObjetivo')): ?>
                <li class="nav-item">
                    <a href="?r=reporte_porHoras" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Horas por Objetivo</p>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (Auth::hasPermission('cronograma', 'vistaHorasPorVigilador')): ?>
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
