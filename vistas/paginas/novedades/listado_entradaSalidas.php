<?php
function obtenerCoordenadasDesdeMapData(string $mapData)
{
    $coords = json_decode($mapData, true);
    return [
        'lat' => isset($coords['lat']) ? floatval($coords['lat']) : null,
        'lng' => isset($coords['lng']) ? floatval($coords['lng']) : null
    ];
}

?>
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
                                <th style="text-align:center;">Acciones</th>
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
                                    <td style="text-align:center;">
                                        <?php

                                        // Extraer coordenadas desde map_data
                                        $coordenadas = obtenerCoordenadasDesdeMapData($m['map_data']);
                                        $lat = $coordenadas['lat'];
                                        $lng = $coordenadas['lng'];

                                        if ($lat !== null && $lng !== null): ?>
                                            <button class="btn btn-sm btn-outline-primary ver-mapa-btn"
                                                data-lat="<?= htmlspecialchars($lat) ?>"
                                                data-lng="<?= htmlspecialchars($lng) ?>"
                                                data-objetivo="<?= htmlspecialchars($m['objetivo']) ?>"
                                                data-evento="<?= htmlspecialchars(ucfirst($m['tipo_evento'])) ?>"
                                                data-fecha="<?= htmlspecialchars(date('d-m-Y H:i', strtotime($m['fecha_hora']))) ?>">
                                                📍 Ver mapa
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">Sin ubicación</span>
                                        <?php endif; ?>
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
<div class="modal fade" id="modalMapa" tabindex="-1" role="dialog" aria-labelledby="modalMapaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalMapaLabel">Ubicación de la Marcación</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div id="mapaMarcacion" style="height: 400px;"></div>
            </div>
        </div>
    </div>
</div>
<script>
    let mapa = null;
    let marcador = null;

    function verMapa(data) {
        $('#modalMapa').modal('show');

        setTimeout(() => {
            if (!mapa) {
                mapa = L.map('mapaMarcacion').setView([data.lat, data.lng], 17);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(mapa);
            } else {
                mapa.setView([data.lat, data.lng], 17);
                if (marcador) mapa.removeLayer(marcador);
            }

            marcador = L.marker([data.lat, data.lng])
                .addTo(mapa)
                .bindPopup(`<strong>${data.evento}</strong><br>${data.objetivo}<br>${data.fecha}`)
                .openPopup();
        }, 300); // Esperamos a que el modal se renderice
    }
    document.querySelectorAll('.ver-mapa-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = {
                lat: parseFloat(this.dataset.lat),
                lng: parseFloat(this.dataset.lng),
                objetivo: this.dataset.objetivo,
                evento: this.dataset.evento,
                fecha: this.dataset.fecha
            };
            verMapa(data);
        });
    });
</script>