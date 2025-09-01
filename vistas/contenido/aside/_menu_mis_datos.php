<?php if (
    Auth::hasPermission('usuarios', 'vistaPerfilUsuario') ||
    Auth::hasPermission('art', 'vistaCredencialArt') ||
    Auth::hasPermission('salud', 'vistaMiSalud') ||
    Auth::hasPermission('uniformes', 'vistaMiUniforme')
    // Nota: 'datos_personales' aún no existe en permisos. Agregar cuando lo definas.
): ?>
    <li class="nav-item has-treeview">
        <a href="#" class="nav-link">
            <i class="nav-icon fas fa-user-circle text-info"></i>
            <p>Mis Datos <i class="fas fa-angle-left right"></i></p>
        </a>
        <ul class="nav nav-treeview">
            <?php $id = $_SESSION['idUsuario']; ?>

            <?php if (Auth::hasPermission('usuarios', 'vistaPerfilUsuario')): ?>
                <li class="nav-item">
                    <a href="?r=perfil-usuario&id=<?= $_SESSION['idUsuario']; ?>" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Mi Perfil</p>
                    </a>
                </li>
            <?php endif; ?>

            <?php /* Espera a crear permisos 'datos_personales' en la BD
            <?php if (Auth::hasPermission('datos_personales', 'vistaMisDatosPersonales')): ?>
                <li class="nav-item">
                    <a href="?r=mis_datos_personales&id=<?= $id ?>" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Mis Datos Personales</p>
                    </a>
                </li>
            <?php endif; ?>
            */ ?>

            <?php if (Auth::hasPermission('salud', 'vistaMiSalud')): ?>
                <li class="nav-item">
                    <a href="?r=mi_salud&id=<?= $id ?>" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Mi Salud</p>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (Auth::hasPermission('uniformes', 'vistaMiUniforme')): ?>
                <li class="nav-item">
                    <a href="?r=mi_uniforme&id=<?= $id ?>" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Mi Uniforme</p>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (Auth::hasPermission('art', 'vistaCredencialArt')): ?>
                <li class="nav-item">
                    <a href="?r=credencial_art&id=<?= $id ?>" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Mi A.R.T.</p>
                    </a>
                </li>
            <?php endif; ?>

        </ul>
    </li>
<?php endif; ?>