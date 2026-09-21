<?php
/** Reglas puras compartidas por generación, validación AJAX y guardado. */
final class CronogramaReglas
{
    public const AUSENCIAS = ['F', 'E', 'P', 'L', 'S', 'S.', 'VAC', 'P/EN', 'F/INJ', 'F/JUS'];
    public const PASIVAS = ['GP/D', 'GP/N'];
    public const REFERENCIAS = ['SALA', 'MIC', 'NOTT', 'GUE', 'PER', 'PERR', 'PAL', 'BOS', 'OFI', 'BERM', 'METR', 'TUP', 'H/8', 'C5,6', 'C/5,6', 'ETI'];
    public const ESPECIALES = ['9RF', '9HEX', 'N15', 'BE', 'D/LEM', 'D/GU', 'D/AR', 'D/LUJ', 'D/LH', 'D/GC', 'D/MA', 'N/AR'];

    public static function codigo(string $codigo): string { return strtoupper(trim($codigo)); }

    public static function mes(string $mes): DateTimeImmutable
    {
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/D', $mes)) throw new InvalidArgumentException('El mes no es válido.');
        return new DateTimeImmutable($mes . '-01');
    }

    public static function categoria(string $codigo, int $objetivo = 0, array $siglas = []): string
    {
        $codigo = self::codigo($codigo);
        if ($codigo === '') return 'vacio';
        if (in_array($codigo, self::AUSENCIAS, true)) return 'ausencia';
        if (in_array($codigo, self::PASIVAS, true)) return 'pasiva';
        if (in_array($codigo, ['D', 'N'], true) || preg_match('/^(\d+(?:[.,]\d+)?)H$/D', $codigo)
            || in_array($codigo, self::ESPECIALES, true)) return 'presencia';
        $destinos = array_values(array_filter($siglas, fn($s) => self::codigo($s['sigla']) === $codigo));
        foreach ($destinos as $s) if ((int)$s['objetivo_id'] === $objetivo) return 'presencia';
        if ($destinos || in_array($codigo, self::REFERENCIAS, true)) return 'referencia';
        return 'desconocido';
    }

    public static function horas(string $codigo, array $horasSiglas = []): float
    {
        $codigo = self::codigo($codigo);
        if ($codigo === '' || in_array($codigo, self::AUSENCIAS, true)) return 0;
        if (in_array($codigo, ['D', 'N', 'GP/D', 'GP/N'], true)) return 12;
        if (preg_match('/^(\d+(?:[.,]\d+)?)H$/D', $codigo, $m)) return (float)str_replace(',', '.', $m[1]);
        if (in_array($codigo, ['9RF', '9HEX'], true)) return 9;
        if ($codigo === 'N15') return 15;
        return (float)($horasSiglas[$codigo] ?? 0);
    }

    /** La duración sola no identifica fase diurna/nocturna. */
    public static function fase(string $codigo): ?string
    {
        $codigo = self::codigo($codigo);
        if ($codigo === 'D' || str_starts_with($codigo, 'D/')) return 'D';
        if ($codigo === 'N' || $codigo === 'N15' || str_starts_with($codigo, 'N/')) return 'N';
        if (in_array($codigo, array_merge(self::AUSENCIAS, self::PASIVAS), true)) return 'F';
        return null;
    }

    public static function intervalo(string $fecha, string $entrada, string $salida): array
    {
        $tz = new DateTimeZone('America/Argentina/Buenos_Aires');
        $inicio = new DateTimeImmutable("$fecha $entrada", $tz);
        $fin = new DateTimeImmutable("$fecha $salida", $tz);
        if ($fin <= $inicio) $fin = $fin->modify('+1 day');
        return [$inicio->getTimestamp(), $fin->getTimestamp()];
    }

    /** null indica horario desconocido, no permiso para solapar. */
    public static function comparar(array $nuevo, array $existentes): array
    {
        if (in_array($nuevo['categoria'], ['ausencia', 'pasiva', 'referencia', 'vacio'], true)) return ['estado' => 'libre', 'conflicto' => false];
        $pendientes = [];
        foreach ($existentes as $otro) {
            if ($nuevo['usuario_id'] != $otro['usuario_id'] || $nuevo['objetivo_id'] == $otro['objetivo_id']) continue;
            if (in_array($otro['categoria'], ['ausencia', 'pasiva', 'referencia', 'vacio'], true)) continue;
            if (abs(strtotime($nuevo['fecha']) - strtotime($otro['fecha'])) > 86400) continue;
            $a = $nuevo['intervalo'] ?? null;
            $b = $otro['intervalo'] ?? null;
            // Sin franja, solo pueden descartarse intervalos cuyo día ya queda fuera.
            if (!$a || !$b) {
                $ventanaA = $a ?? self::intervalo($nuevo['fecha'], '00:00:00', '00:00:00');
                $ventanaB = $b ?? self::intervalo($otro['fecha'], '00:00:00', '00:00:00');
                if (!$a) $ventanaA[1] += 86400;
                if (!$b) $ventanaB[1] += 86400;
                if (max($ventanaA[0], $ventanaB[0]) < min($ventanaA[1], $ventanaB[1])) $pendientes[] = $otro;
                continue;
            }
            if (max($a[0], $b[0]) < min($a[1], $b[1])) {
                return ['estado' => 'conflicto', 'conflicto' => true, 'objetivo' => $otro['objetivo'] ?? (string)$otro['objetivo_id'],
                    'codigo_existente' => $otro['codigo_turno'], 'fecha_existente' => $otro['fecha'],
                    'mensaje' => 'El vigilador tiene un turno superpuesto en otro objetivo.'];
            }
        }
        if ($pendientes) return ['estado' => 'pendiente', 'conflicto' => false,
            'mensaje' => 'No se puede descartar un cruce: falta un horario inequívoco de puesto para este vigilador y fecha.'];
        return ['estado' => 'libre', 'conflicto' => false];
    }
}
