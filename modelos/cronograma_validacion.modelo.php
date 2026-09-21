<?php
require_once __DIR__ . '/../core/CronogramaReglas.php';

final class ModeloCronogramaValidacion
{
    private $db;
    private array $siglas;
    private array $horarios = [];

    public function __construct($db)
    {
        $this->db = $db;
        $this->siglas = $db->query('SELECT objetivo_id, sigla, horas FROM objetivo_siglas WHERE activo = 1')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function siglas(): array { return $this->siglas; }

    public function preparar(array $turno): array
    {
        $turno['categoria'] = CronogramaReglas::categoria($turno['codigo_turno'], (int)$turno['objetivo_id'], $this->siglas);
        $turno['intervalo'] = null;
        if (!in_array($turno['categoria'], ['presencia', 'desconocido'], true)) return $turno;
        $obj = (int)$turno['objetivo_id'];
        if (!isset($this->horarios[$obj])) {
            $s = $this->db->prepare('SELECT pt.puesto_id, pt.numero_turno, pt.hora_entrada, pt.hora_salida FROM puestos_turnos pt JOIN puestos p ON p.idPuesto = pt.puesto_id WHERE p.objetivo_id = ? AND p.activo = 1');
            $s->execute([$obj]);
            $this->horarios[$obj] = $s->fetchAll(PDO::FETCH_ASSOC);
        }
        $s = $this->db->prepare('SELECT puesto_id FROM rotaciones_puestos WHERE usuario_id = ? AND objetivo_id = ? AND fecha = ? AND codigo_turno = ?');
        $s->execute([$turno['usuario_id'], $obj, $turno['fecha'], $turno['codigo_turno']]);
        $puestos = array_column($s->fetchAll(PDO::FETCH_ASSOC), 'puesto_id');
        $codigo = CronogramaReglas::codigo($turno['codigo_turno']);
        $numero = $codigo === 'D' ? 1 : ($codigo === 'N' ? 2 : null);
        $horas = [];
        foreach ($this->siglas as $sigla) if ((int)$sigla['objetivo_id'] === $obj) $horas[CronogramaReglas::codigo($sigla['sigla'])] = $sigla['horas'];
        $duracion = CronogramaReglas::horas($codigo, $horas);
        $opciones = [];
        foreach ($this->horarios[$obj] as $h) {
            if ($puestos && !in_array($h['puesto_id'], $puestos)) continue;
            if ($numero !== null && (int)$h['numero_turno'] !== $numero) continue;
            $intervalo = CronogramaReglas::intervalo($turno['fecha'], $h['hora_entrada'], $h['hora_salida']);
            if ($numero === null && ($duracion <= 0 || abs(($intervalo[1] - $intervalo[0]) / 3600 - $duracion) > .02)) continue;
            $opciones[implode(':', $intervalo)] = $intervalo;
        }
        if (count($opciones) === 1) $turno['intervalo'] = array_values($opciones)[0];
        return $turno;
    }

    public function validar(array $turno): array
    {
        $nuevo = $this->preparar($turno);
        if (!in_array($nuevo['categoria'], ['presencia', 'desconocido'], true)) return ['estado' => 'libre', 'conflicto' => false];
        $fecha = new DateTimeImmutable($turno['fecha']);
        $s = $this->db->prepare('SELECT t.usuario_id, t.objetivo_id, t.fecha, t.codigo_turno, o.nombre AS objetivo FROM turnos t JOIN objetivos o ON o.idObjetivo = t.objetivo_id WHERE t.usuario_id = ? AND t.objetivo_id <> ? AND t.fecha BETWEEN ? AND ?');
        $s->execute([$turno['usuario_id'], $turno['objetivo_id'], $fecha->modify('-1 day')->format('Y-m-d'), $fecha->modify('+1 day')->format('Y-m-d')]);
        return CronogramaReglas::comparar($nuevo, array_map([$this, 'preparar'], $s->fetchAll(PDO::FETCH_ASSOC)));
    }
}
