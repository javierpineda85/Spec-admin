<?php if (Auth::hasPermission('noticias', 'vistaCumple')): ?>
    <li class="nav-item has-treeview">
        <a href="#" class="nav-link">
            <i class="nav-icon fas fa-bullhorn text-info"></i>
            <p>Noticias <i class="fas fa-angle-left right"></i></p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="?r=cumpleanos" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Cumpleaños del Mes</p>
                </a>
            </li>
        </ul>
    </li>
<?php endif; ?>
