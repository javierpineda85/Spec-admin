<?php
/**
 * scanPermsAll.php
 *
 * Recorre uno o más directorios/archivos PHP, extrae todos los slugs usados
 * en Auth::hasPermission('módulo','slug') y los compara contra la lista
 * oficial de permisos (módulo → slug) que se lee desde un archivo SQL/CSV.
 *
 * Uso:
 *   php scanPermsAll.php <ruta1> [<ruta2> …] <permisos_sql_file>
 *
 * Ejemplo:
 *   php scanPermsAll.php ../controllers ../models ../views . roles_bd.txt
 */

if ($argc < 3) {
    fwrite(STDERR, "Uso: php scanPermsAll.php <code_path1> [<code_path2> …] <permisos_sql_file>\n");
    exit(1);
}

// El último argumento es el archivo de permisos
$permFileRaw = array_pop($argv);
$permFile    = realpath($permFileRaw);

if (!$permFile || !is_file($permFile)) {
    fwrite(STDERR, "Error: no se encontró el archivo de permisos: {$permFileRaw}\n");
    exit(1);
}

// Parsear permisos desde el SQL (módulo, slug)
$validMap = [];   // módulo => [ slug => true, … ]
foreach (file($permFile) as $line) {
    // Busca líneas con formato: (id, 'módulo', 'slug',
    if (preg_match("/\(\s*\d+\s*,\s*'([^']+)'\s*,\s*'([^']+)'/", $line, $m)) {
        list(, $module, $slug) = $m;
        $validMap[$module][$slug] = true;
    }
}

// Recorrer rutas de código (directorios o archivos sueltos)
$usedMap = [];    // módulo => [ slug => true, … ]
$paths   = array_slice($argv, 1);

foreach ($paths as $pathRaw) {
    $path = realpath($pathRaw);
    if (!$path) {
        fwrite(STDERR, "Aviso: ruta no encontrada: {$pathRaw}\n");
        continue;
    }

    $iterator = $path;
    // Si es carpeta, recorrer recursivamente
    if (is_dir($path)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path)
        );
    } else {
        // Si es archivo PHP, envolvemos en un ArrayIterator
        $iterator = new ArrayIterator([$path]);
    }

    foreach ($iterator as $file) {
        $filePath = is_object($file) ? $file->getRealPath() : $file;
        if (!is_file($filePath) || pathinfo($filePath, PATHINFO_EXTENSION) !== 'php') {
            continue;
        }

        $content = file_get_contents($filePath);
        // Extraer Auth::hasPermission('módulo','slug')
        if (preg_match_all(
            "/Auth::hasPermission\(\s*'([^']+)'\s*,\s*'([^']+)'\s*\)/",
            $content, $matches, PREG_SET_ORDER
        )) {
            foreach ($matches as $m) {
                list(, $module, $slug) = $m;
                $usedMap[$module][$slug] = true;
            }
        }
    }
}

// Comparar y reportar
ksort($validMap);
ksort($usedMap);

foreach (array_unique(array_merge(array_keys($validMap), array_keys($usedMap))) as $module) {
    $validSlugs = isset($validMap[$module]) ? array_keys($validMap[$module]) : [];
    $usedSlugs  = isset($usedMap[$module])  ? array_keys($usedMap[$module])  : [];

    $diffCode = array_diff($usedSlugs, $validSlugs);
    $diffBD   = array_diff($validSlugs, $usedSlugs);

    echo "=== Módulo: {$module} ===\n";
    echo "- Slugs en código:   " . (empty($usedSlugs) ? '— ninguno —' : implode(', ', $usedSlugs)) . "\n";
    echo "- Slugs en BD:       " . (empty($validSlugs) ? '— ninguno —' : implode(', ', $validSlugs)) . "\n";
    echo "- Usados en código NO en BD:\n";
    echo   $diffCode ? "    • " . implode("\n    • ", $diffCode) . "\n" : "    — ninguno —\n";
    echo "- En BD NO usados en código:\n";
    echo   $diffBD   ? "    • " . implode("\n    • ", $diffBD)   . "\n\n" : "    — ninguno —\n\n";
}
