<div class="card">
    <div class="card-header bg-info text-white">
        <h3 class="card-title">Listado de Talles de Uniforme</h3>
    </div>

    <div class="card-body table-responsive">
        <table id="example1" class="table table-bordered table-striped table-sm">
            <thead class="bg-secondary text-white">
                <tr>
                    <th>Nombre</th>
                    <th>Pantalón</th>
                    <th>Calzado</th>
                    <th>Remera</th>
                    <th>Polar</th>
                    <th>Campera</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($uniformes as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['nombre_completo']) ?></td>
                        <td><?= $u['talle_pantalon'] ?></td>
                        <td><?= $u['talle_calzado'] ?></td>
                        <td><?= $u['talle_remera'] ?></td>
                        <td><?= $u['talle_polar'] ?></td>
                        <td><?= $u['talle_campera'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php
        $totales = [];

        // Recorremos los uniformes sin referencias para evitar errores
        foreach ($uniformes as $u) {
            $mapa = [
                'pantalon' => $u['talle_pantalon'] ?? '',
                'calzado' => $u['talle_calzado'] ?? '',
                'remera'   => $u['talle_remera'] ?? '',
                'polar'    => $u['talle_polar'] ?? '',
                'campera'  => $u['talle_campera'] ?? ''
            ];

            foreach ($mapa as $prenda => $talle) {
                $talle = trim($talle);
                if ($talle !== '') {
                    if (!isset($totales[$prenda])) $totales[$prenda] = [];
                    if (!isset($totales[$prenda][$talle])) {
                        $totales[$prenda][$talle] = 0;
                    }
                    $totales[$prenda][$talle]++;
                }
            }
        }
        ?>

        <div class="card mt-4">
            <div class="card-header bg-dark text-white">
                <h3 class="card-title">Resumen de Talles</h3>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="bg-light">
                        <tr>
                            <th>Prenda</th>
                            <th>Talle</th>
                            <th>Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($totales as $prenda => $conteo): ?>
                            <?php
                            $rowspan = count($conteo);
                            $first = true;
                            ?>
                            <?php foreach ($conteo as $talle => $cantidad): ?>
                                <tr>
                                    <?php if ($first): ?>
                                        <td rowspan="<?= $rowspan ?>"><?= ucfirst($prenda) ?></td>
                                    <?php $first = false;
                                    endif; ?>
                                    <td><?= htmlspecialchars($talle) ?></td>
                                    <td><?= $cantidad ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>


    </div>
</div>