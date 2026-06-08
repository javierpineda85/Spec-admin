<?php if (
    Auth::hasPermission('usuarios', 'vistaPerfilUsuario') ||
    Auth::hasPermission('art', 'vistaCredencialArt') ||
    Auth::hasPermission('salud', 'vistaMiSalud') ||
    Auth::hasPermission('uniformes', 'vistaMiUniforme') ||
    Auth::hasPermission('datos_personales', 'vistaMisDatosPersonales')
): ?>
  <div class="col-6 col-md-6 col-lg-3">
      <div class="info-box shadow">
            <span class="info-box-icon bg-info"><i class="fas fa-user-circle"></i></span>
            <div class="info-box-content">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="info-box-number">Mis Datos</span>
                    <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseMisDatos" aria-expanded="false" aria-controls="collapseMisDatos">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>

                <div id="collapseMisDatos" class="collapse">
                    <div class="mt-2">
                        <?php $id = $_SESSION['idUsuario']; ?>

                        <?php if (Auth::hasPermission('usuarios', 'vistaPerfilUsuario')): ?>
                            <a href="?r=perfil-usuario&id=<?= $id ?>" class="btn btn-block btn-info btn-sm text-white">Mi Perfil</a>
                        <?php endif; ?>

                        <?php if (Auth::hasPermission('datos_personales', 'vistaMisDatosPersonales')): ?>
                            <a href="?r=mis_datos_personales&id=<?= $id ?>" class="btn btn-block btn-info btn-sm text-white">Mis Datos Personales</a>
                        <?php endif; ?>

                        <?php if (Auth::hasPermission('salud', 'vistaMiSalud')): ?>
                            <a href="?r=mi_salud&id=<?= $id ?>" class="btn btn-block btn-info btn-sm text-white">Mi Salud</a>
                        <?php endif; ?>

                        <?php if (Auth::hasPermission('uniformes', 'vistaMiUniforme')): ?>
                            <a href="?r=mi_uniforme&id=<?= $id ?>" class="btn btn-block btn-info btn-sm text-white">Mi Uniforme</a>
                        <?php endif; ?>

                        <?php if (Auth::hasPermission('art', 'vistaCredencialArt')): ?>
                            <a href="?r=credencial_art&id=<?= $id ?>" class="btn btn-block btn-info btn-sm text-white">Mi A.R.T.</a>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
