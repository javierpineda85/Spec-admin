<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
chdir(dirname(__DIR__, 2));
require 'modelos/conexion.php';
require 'controladores/cronograma.controller.php';

$metodo = new ReflectionMethod(ControladorCronogramas::class, 'calcularHorasEnVentana');
$calcular = static fn(string $inicio, string $fin, string $desde, string $hasta): float =>
    $metodo->invoke(null, new DateTime($inicio), new DateTime($fin), $desde, $hasta);
$casos = [];
$probar = static function(string $nombre, float $esperado, float $obtenido) use (&$casos): void {
    $casos[] = ['caso'=>$nombre, 'correcto'=>abs($esperado-$obtenido)<0.0001, 'esperado'=>$esperado, 'obtenido'=>$obtenido];
};

$probar('madrugada_completa', 6, $calcular('2025-05-02 00:00:00','2025-05-02 06:00:00','22:00:00','06:00:00'));
$probar('turno_nocturno_completo', 8, $calcular('2025-05-01 22:00:00','2025-05-02 06:00:00','22:00:00','06:00:00'));
$probar('turno_mixto_nocturnas', 1, $calcular('2025-05-01 21:00:00','2025-05-01 23:00:00','22:00:00','06:00:00'));
$probar('turno_mixto_diurnas', 1, $calcular('2025-05-01 21:00:00','2025-05-01 23:00:00','06:00:00','22:00:00'));
$probar('jornada_multidia_nocturnas', 16, $calcular('2025-05-01 20:00:00','2025-05-03 08:00:00','22:00:00','06:00:00'));
$probar('jornada_multidia_diurnas', 20, $calcular('2025-05-01 20:00:00','2025-05-03 08:00:00','06:00:00','22:00:00'));
$probar('rango_invalido', 0, $calcular('2025-05-02 06:00:00','2025-05-02 06:00:00','22:00:00','06:00:00'));

$resultado = ['casos'=>count($casos),'correctos'=>count(array_filter($casos,fn($c)=>$c['correcto'])),'detalle'=>$casos];
file_put_contents('output/cronogramas_mayo_2025/regresion_horas_reporte.json', json_encode($resultado, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo json_encode(['casos'=>$resultado['casos'],'correctos'=>$resultado['correctos']], JSON_UNESCAPED_UNICODE), PHP_EOL;
exit($resultado['casos']===$resultado['correctos'] ? 0 : 1);
