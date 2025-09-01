<?php if (
    Auth::hasPermission('puestos', 'vistaCrearPuestos') ||
    Auth::hasPermission('puestos', 'vistaListadoPuestos') ||
    Auth::hasPermission('puestos', 'vistaListadoPuestosDesactivados') ||
    Auth::hasPermission('puestos', 'vistaRotaciones')
): ?>
    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="info-box shadow">
            <span class="info-box-icon bg-success"><i class="fas fa-eye"></i></span>
            <div class="info-box-content">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="info-box-number">Puestos</span>
                    <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapsePuestos" aria-expanded="false" aria-controls="collapsePuestos">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div id="collapsePuestos" class="collapse">
                    <div class="mt-2">
                        <?php if (Auth::hasPermission('puestos', 'vistaCrearPuestos')): ?>
                            <a href="?r=crear_puesto" class="btn btn-block btn-success btn-sm text-white">Crear</a>
                        <?php endif; ?>

                        <?php if (Auth::hasPermission('puestos', 'vistaRotaciones')): ?>
                            <a href="?r=rotaciones_puestos" class="btn btn-block btn-success btn-sm text-white">Asignar Puestos</a>
                        <?php endif; ?>

                        <?php if (Auth::hasPermission('puestos', 'vistaListadoPuestos')): ?>
                            <a href="?r=listado_puestos" class="btn btn-block btn-success btn-sm text-white">Mostrar Activos</a>
                        <?php endif; ?>

                        <?php if (Auth::hasPermission('puestos', 'vistaListadoPuestosDesactivados')): ?>
                            <a href="?r=listado_puestos_inactivos" class="btn btn-block btn-success btn-sm text-white">Mostrar Inactivos</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>