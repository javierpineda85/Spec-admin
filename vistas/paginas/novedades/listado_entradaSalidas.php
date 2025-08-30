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
                    <div class="row">
                        <div class="mb-3 col-sm-12 col-md-5">
                            <label>Desde:</label>
                            <input type="date" id="filtroDesde" class="form-control form-control-sm d-inline-block" style="width:auto;">
                            <label>Hasta:</label>
                            <input type="date" id="filtroHasta" class="form-control form-control-sm d-inline-block" style="width:auto;">
                            <button class="btn btn-sm btn-primary" onclick="filtrarPorFecha()">Filtrar</button>
                            <button class="btn btn-sm btn-secondary" onclick="limpiarFiltroFecha()">Limpiar</button>
                        </div>
                        <!-- Botones de filtro rápido -->
                        <div class="btn-group mb-3 col-sm-12 col-md-7">
                            <button class="btn btn-sm btn-outline-success" onclick="filtrarColor('badge-success')">En rango</button>
                            <button class="btn btn-sm btn-outline-warning" onclick="filtrarColor('badge-warning')">Tarde ≤ 30 min</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="filtrarColor('badge-danger')">Fuera de rango</button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="filtrarColor('badge-secondary')">Muy temprano</button>
                            <button class="btn btn-sm btn-outline-info" onclick="filtrarColor('badge-info')">Extra no remunerado</button>
                            <button class="btn btn-sm btn-outline-dark" onclick="filtrarColor('')">Todos</button>
                        </div>

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
                                            <span class="badge <?= $m['badge']['color'] ?>">
                                                <?= $m['badge']['estado'] ?>
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
                        function filtrarColor(filtro) {
                            const mapClassToEstado = {
                                'badge-success': 'En rango',
                                'badge-warning': 'Tarde ≤ 30 min',
                                'badge-danger': 'Fuera de rango',
                                'badge-secondary': 'Muy temprano',
                                'badge-info': 'Extra no remunerado'
                            };

                            const normalize = s => (s || '')
                                .toLowerCase()
                                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                                .trim();

                            // Si el botón es "Todos" (filtro vacío)
                            if (!filtro) {
                                document.querySelectorAll('#example1 tbody tr').forEach(tr => tr.style.display = '');
                                return;
                            }

                            const targetEstado = mapClassToEstado[filtro] || null;

                            document.querySelectorAll('#example1 tbody tr').forEach(tr => {
                                const badge = tr.querySelector('.badge');
                                if (!badge) {
                                    tr.style.display = 'none';
                                    return;
                                }

                                // 1) Si el botón corresponde a un estado conocido, filtramos por el texto del badge
                                if (targetEstado) {
                                    const textoBadge = normalize(badge.textContent);
                                    tr.style.display = (textoBadge === normalize(targetEstado)) ? '' : 'none';
                                    return;
                                }

                                // 2) Fallback: filtrado por clase (soporta Bootstrap 4 y 5)
                                const nombre = filtro.replace(/^badge-/, '').replace(/^bg-/, '').replace(/^text-bg-/, '');
                                const variantes = [`badge-${nombre}`, `bg-${nombre}`, `text-bg-${nombre}`];

                                const clases = badge.classList;
                                const coincideClase = variantes.some(v => clases.contains(v));

                                tr.style.display = coincideClase ? '' : 'none';
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
    document.addEventListener('DOMContentLoaded', function() {
        // Evitar registrar filtros múltiples si la vista se monta más de una vez
        if (!window.__filtroES_registrado) {
            let filtroEstado = '';

            // Filtro global, pero limitado solo a la tabla con id="example1"
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'example1') return true;

                const desde = document.getElementById('filtroDesde')?.value || '';
                const hasta = document.getElementById('filtroHasta')?.value || '';
                const fechaTexto = data[3]; // Columna Fecha (dd-mm-yyyy)
                let estadoTexto = data[5] || ''; // Columna Estado (badge)

                if (!fechaTexto) return false;

                // Normalizar estado (por si viene con HTML del badge)
                estadoTexto = $('<div>').html(estadoTexto).text().trim();

                // Parseo de fecha dd-mm-yyyy
                const [dia, mes, anio] = fechaTexto.split('-');
                const fecha = new Date(`${anio}-${mes}-${dia}T00:00:00`);

                let mostrar = true;

                // Filtrado por fecha (rango inclusivo)
                if (desde) {
                    const fDesde = new Date(desde + 'T00:00:00');
                    if (fecha < fDesde) mostrar = false;
                }
                if (hasta) {
                    const fHasta = new Date(hasta + 'T23:59:59');
                    if (fecha > fHasta) mostrar = false;
                }

                // Filtrado por estado (texto exacto de los botones)
                if (filtroEstado) {
                    const norm = s => s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
                    if (norm(estadoTexto) !== norm(filtroEstado)) mostrar = false;
                }

                return mostrar;
            });

            // Guardar bandera para no volver a registrar este filtro
            window.__filtroES_registrado = true;

            // Helper seguro: dibuja solo si la tabla ya está inicializada en otro script
            function drawExample() {
                if ($.fn.DataTable.isDataTable('#example1')) {
                    $('#example1').DataTable().draw(false);
                }
            }

            // Eventos de filtrado por fecha
            $('#filtroDesde, #filtroHasta').on('change', drawExample);

            // Exponer funciones usadas por los botones
            window.filtrarColor = function(estadoClass) {
                const mapClassToEstado = {
                    'badge-success': 'En rango',
                    'badge-warning': 'Tarde ≤ 30 min',
                    'badge-danger': 'Fuera de rango',
                    'badge-secondary': 'Muy temprano',
                    'badge-info': 'Extra no remunerado'
                };
                filtroEstado = mapClassToEstado[estadoClass] || ''; // vacío => todos
                drawExample();
            };

            window.filtrarPorFecha = drawExample;

            window.limpiarFiltroFecha = function() {
                $('#filtroDesde').val('');
                $('#filtroHasta').val('');
                drawExample();
            };

            // Opcional: cuando DataTables termine de inicializar, forzar un draw para aplicar filtros actuales
            $(document).one('init.dt', function(e, settings) {
                if (settings.nTable.id === 'example1') drawExample();
            });
        }

        // --- Mapa (Leaflet) ---
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
            }, 300);
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
    });
</script>