<?php if (Auth::hasPermission('noticias', 'vistaCumple')): ?>
  <div class="col-6 col-md-6 col-lg-3">
      <div class="info-box shadow">
            <span class="info-box-icon bg-info"><i class="fas fa-bullhorn"></i></span>
            <div class="info-box-content">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="info-box-number">Noticias</span>
                    <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseNoticias" aria-expanded="false" aria-controls="collapseNoticias">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div id="collapseNoticias" class="collapse">
                    <div class="mt-2">
                        <a href="?r=cumpleanos" class="btn btn-block btn-info btn-sm text-white">Cumpleaños del Mes</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
