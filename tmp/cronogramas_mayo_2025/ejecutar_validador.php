<?php
declare(strict_types=1);
// Ejecuta el endpoint original en un proceso desechable; reemplaza solo su conexión.
$case = json_decode(file_get_contents($argv[1]),true,512,JSON_THROW_ON_ERROR);
final class Conexion {
    public static function conectar(): self { return new self(); }
    public function prepare(string $sql): self {
        if (!preg_match('/^\s*SELECT\b/i',$sql)) throw new RuntimeException('Solo SELECT');
        return $this;
    }
    private array $rows = [];
    public function execute(array $params): void {
        global $case;
        [$user,$date,$objective]=$params;
        $this->rows=array_values(array_filter($case['existentes'],fn($r)=>$r['usuario_id']===$user && $r['fecha']===$date && $r['objetivo_id']!==$objective));
    }
    public function fetchAll(int $mode): array { return $this->rows; }
}
$_GET=$case['consulta'];
$source=file_get_contents(dirname(__DIR__,2).'/vistas/paginas/cronogramas/validar-turno-global.php');
$source=preg_replace('/^require __DIR__ .*modelos\/conexion\.php.*;.*$/m','// Conexion sustituida por adaptador de lectura en memoria.',$source,-1,$count);
if ($count!==1) throw new RuntimeException('No se identificó la única inclusión de conexión');
eval('?>'.$source);
