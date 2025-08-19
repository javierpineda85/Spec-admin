<?php if (Auth::hasPermission('mensajes', 'crtMostrarMensajes') || Auth::hasPermission('mensajes', 'crtGuardarMensaje')): ?>
    <li class="nav-item has-treeview">
        <a href="#" class="nav-link">
            <i class="nav-icon far fa-envelope text-info"></i>
            <p>Mensajes <i class="fas fa-angle-left right"></i></p>
        </a>
        <ul class="nav nav-treeview">
            <?php if (Auth::hasPermission('mensajes', 'crtMostrarMensajes')): ?>
                <li class="nav-item">
                    <a href="?r=bandeja-entrada" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Bandeja de entrada</p>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (Auth::hasPermission('mensajes', 'crtGuardarMensaje')): ?>
                <li class="nav-item">
                    <a href="?r=nuevo-mensaje" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Nuevo mensaje</p>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </li>
<?php endif; ?>