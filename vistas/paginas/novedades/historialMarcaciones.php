<?php
function obtenerCoordenadasDesdeMarcacion(array $row)
{
    if (isset($row['map_data'])) {
        $coords = json_decode($row['map_data'], true);
        return [
            'lat' => isset($coords['lat']) ? floatval($coords['lat']) : null,
            'lng' => isset($coords['lng']) ? floatval($coords['lng']) : null
        ];
    }
    return [
        'lat' => isset($row['latitud']) ? floatval($row['latitud']) : null,
        'lng' => isset($row['longitud']) ? floatval($row['longitud']) : null
    ];
}


$db = new Conexion();
$sql = "SELECT u.idUsuario, u.apellido, u.nombre
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.id
                WHERE r.categoria = 'operativo' AND u.activo = 1
                ORDER BY u.apellido, u.nombre";
$vigiladores = $db->consultas($sql);

$filtros = [
    'vigilador' => $_POST['vigilador'] ?? '',
    'desde'     => $_POST['desde'] ?? '',
    'hasta'     => $_POST['hasta'] ?? ''
];


?>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="card-title">Historial de Marcaciones</h3>
            </div>
            <div class="card-body">

                <!-- Filtros -->
                <form action="" method="POST" class="form-inline mb-3">
                    <label class="mr-2">Vigilador</label>
                    <select name="vigilador" class="form-control mr-3" required>
                        <option value="" disabled <?= !$filtros['vigilador'] ? 'selected' : '' ?>>Selecciona un vigilador</option>
                        <?php foreach ($vigiladores as $v): ?>
                            <option value="<?= $v['idUsuario'] ?>" <?= $filtros['vigilador'] == $v['idUsuario'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars("{$v['apellido']} {$v['nombre']}") ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label class="mr-2">Desde</label>
                    <input type="date" name="desde" class="form-control mr-3" value="<?= htmlspecialchars($filtros['desde']) ?>" required>

                    <label class="mr-2">Hasta</label>
                    <input type="date" name="hasta" class="form-control mr-3" value="<?= htmlspecialchars($filtros['hasta']) ?>" required>

                    <button type="submit" class="btn btn-primary">Buscar</button>
                </form>

                <?php if (!empty($marcaciones)): ?>
                    <div id="mapa" style="width: 95%; height: 480px;"></div>
                <?php else: ?>
                    <div class="alert alert-info">Selecciona un vigilador y rango de fechas para ver el mapa.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($marcaciones)) :

    $agrupados = [];

    foreach ($marcaciones as $m) {
        if (empty($m['latitud']) || empty($m['longitud'])) continue;

        $lat = (float)$m['latitud'];
        $lng = (float)$m['longitud'];
        $key = $lat . ',' . $lng;

        if (!isset($agrupados[$key])) {
            $agrupados[$key] = [
                'lat' => $lat,
                'lng' => $lng,
                'entradas' => 0,
                'salidas'  => 0
            ];
        }

        // Contamos cada registro individual
        if (strtolower($m['tipo_evento']) === 'entrada') {
            $agrupados[$key]['entradas']++;
        } else {
            $agrupados[$key]['salidas']++;
        }
    }

?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const map = L.map('mapa').setView([-32.88, -68.80], 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            function crearIcono(color, total) {
                return L.divIcon({
                    className: '',
                    html: `
              <div style="position:relative;width:25px;height:41px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="41" viewBox="0 0 25 41">
                  <path d="M12.5 0C5.6 0 0 5.6 0 12.5c0 9.4 12.5 28.5 12.5 28.5S25 21.9 25 12.5C25 5.6 19.4 0 12.5 0z"
                        fill="${color}"/>
                  <circle cx="12.5" cy="12.5" r="5" fill="white"/>
                </svg>
                <span style="
                  position:absolute;top:-5px;right:-5px;
                  background:#333;color:#fff;
                  font-size:10px;font-weight:bold;
                  padding:2px 4px;border-radius:10px;
                ">${total}</span>
              </div>
            `,
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [0, -41]
                });
            }

            const marcadores = [
                <?php foreach ($agrupados as $p) :
                    $total = $p['entradas'] + $p['salidas']; // Suma definitiva del grupo
                    if ($p['entradas'] > $p['salidas']) {
                        $color = '#4CAF50'; // verde
                    } elseif ($p['salidas'] > $p['entradas']) {
                        $color = '#F44336'; // rojo
                    } else {
                        $color = '#2196F3'; // azul
                    }
                    echo "{ lat: {$p['lat']}, lng: {$p['lng']}, entradas: {$p['entradas']}, salidas: {$p['salidas']}, total: {$total}, color: '" . htmlspecialchars($color, ENT_QUOTES) . "' },\n";
                endforeach; ?>
            ];

            console.log("Marcadores recibidos:", marcadores);

            marcadores.forEach(m => {
                const icono = crearIcono(m.color, m.total);
                const partes = [];
                if (m.entradas > 0) partes.push(`Entradas: ${m.entradas}`);
                if (m.salidas > 0) partes.push(`Salidas: ${m.salidas}`);

                L.marker([m.lat, m.lng], {
                        icon: icono
                    })
                    .bindPopup(partes.join('<br>') || 'Sin datos')
                    .addTo(map);
            });

            if (marcadores.length > 0) {
                map.fitBounds(marcadores.map(m => [m.lat, m.lng]));
            }
        });
    </script>
<?php endif; ?>