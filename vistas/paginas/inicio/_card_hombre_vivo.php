<?php if (
    Auth::hasPermission('hvivo', 'vistaHombreVivo') ||
    Auth::hasPermission('hvivo', 'vistaListadoReportesHombreVivo') ||
    !in_array($_SESSION['categoria'] ?? '', ['operativo', 'referente'], true)
): ?>
    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="info-box shadow">
            <span class="info-box-icon bg-danger"><i class="far fa-life-ring"></i></span>
            <div class="info-box-content">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="info-box-number">Hombre Vivo</span>
                    <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseHvivo" aria-expanded="false" aria-controls="collapseHvivo">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div id="collapseHvivo" class="collapse">
                    <div class="mt-2">
                        <?php if (Auth::hasPermission('hvivo', 'vistaHombreVivo')): ?>
                            <a href="tel:911" class="btn btn-block btn-danger btn-sm text-white">Llamar 911</a>
                            <a href="?r=reporte_hombre_vivo" class="btn btn-block btn-info btn-sm text-white">Reportar</a>
                        <?php endif; ?>
                        <?php if (Auth::hasPermission('hvivo', 'vistaListadoReportesHombreVivo')): ?>
                            <a href="?r=listado_reportes" class="btn btn-block btn-info btn-sm text-white">Ver reportes</a>
                        <?php endif; ?>
                        <?php if (!in_array($_SESSION['categoria'] ?? '', ['operativo', 'referente'], true)): ?>
                            <a href="?r=configuracion_hvivo" class="btn btn-block btn-secondary btn-sm text-white">Configuración</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
