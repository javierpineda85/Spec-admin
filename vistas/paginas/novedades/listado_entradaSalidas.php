<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title">Reporte de Ingresos y Salidas a los Servicios</h3>
                </div>
                <div class="card-body">

                    <!-- Botones de filtro rápido -->
                    <div class="btn-group mb-3">
                        <button class="btn btn-sm btn-outline-success" onclick="filtrarColor('badge-success')">En rango</button>
                        <button class="btn btn-sm btn-outline-warning" onclick="filtrarColor('badge-warning')">Tarde ≤ 30 min</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="filtrarColor('badge-danger')">Fuera de rango</button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="filtrarColor('badge-secondary')">Muy temprano</button>
                        <button class="btn btn-sm btn-outline-info" onclick="filtrarColor('badge-info')">Extra no remunerado</button>
                        <button class="btn btn-sm btn-outline-dark" onclick="filtrarColor('')">Todos</button>
                    </div>

                    <table id="example1" class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th style="text-align:center;">Vigilador</th>
                                <th style="text-align:center;">Objetivo</th>
                                <th style="text-align:center;">Evento</th>
                                <th style="text-align:center;">Fecha</th>
                                <th style="text-align:center;">Hora</th>
                                <th style="text-align:center;">Estado</th>
                                <th style="text-align:center;">Latitud</th>
                                <th style="text-align:center;">Longitud</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($marcaciones as $m): ?>
                                <tr>
                                    <td style="vertical-align:middle; text-align:center;">
                                        <?= htmlspecialchars($m['vigilador']) ?>
                                    </td>
                                    <td style="vertical-align:middle; text-align:center;">
                                        <?= htmlspecialchars($m['objetivo'] ?? '—') ?>
                                    </td>
                                    <td style="vertical-align:middle; text-align:center;">
                                        <?= ucfirst(htmlspecialchars($m['tipo_evento'])) ?>
                                    </td>
                                    <td style="vertical-align:middle; text-align:center;">
                                        <?= date('d-m-Y', strtotime($m['fecha_hora'])) ?>
                                    </td>
                                    <td style="vertical-align:middle; text-align:center;">
                                        <?= date('H:i', strtotime($m['fecha_hora'])) ?>
                                    </td>
                                    <td style="vertical-align:middle; text-align:center;">
                                        <?php if (!empty($m['badge'])): ?>
                                            <span class="badge <?= $m['badge'][1] ?>">
                                                <?= $m['badge'][0] ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="vertical-align:middle; text-align:center;">
                                        <?= htmlspecialchars($m['latitud']) ?>
                                    </td>
                                    <td style="vertical-align:middle; text-align:center;">
                                        <?= htmlspecialchars($m['longitud']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <script>
                        function filtrarColor(clase) {
                            document.querySelectorAll('#example1 tbody tr').forEach(tr => {
                                if (!clase) {
                                    tr.style.display = '';
                                } else {
                                    const badge = tr.querySelector('.badge');
                                    tr.style.display = badge && badge.classList.contains(clase) ? '' : 'none';
                                }
                            });
                        }
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>