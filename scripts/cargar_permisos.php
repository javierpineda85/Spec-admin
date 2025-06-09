<?php
// scripts/cargar_permisos.php

// 1) Nos movemos al root del proyecto
chdir(__DIR__ . '/..');

// 2) Conexión
require_once 'modelos/Conexion.php';
$db = new Conexion;

// 3) (Opcional) limpiar la tabla
// $db->consultas("TRUNCATE TABLE permissions");

// 4) Recorrer recursivamente controladores
$rii = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator('controladores', RecursiveDirectoryIterator::SKIP_DOTS)
);

$files = [];
foreach ($rii as $file) {
    if ($file->isFile() && preg_match('/\.controller\.php$/', $file->getFilename())) {
        $files[] = $file->getPathname();
    }
}

// 5) Por cada archivo, extraer métodos con token_get_all
foreach ($files as $file) {
    $contents = file_get_contents($file);
    $tokens   = token_get_all($contents);

    // Derivar nombre de clase según convención FooController
    $base      = strtolower(basename($file, '.controller.php'));
    $ctrlNames  = [
        ucfirst($base) . 'Controller',    // Turnos → TurnosController
        'Controlador' . ucfirst($base),   // Turnos → ControladorTurnos
    ];

    $inClass     = false;
    $methodNames = [];

    for ($i = 0, $n = count($tokens); $i < $n; $i++) {
        $t = $tokens[$i];

        // Detectar class FooController
        if (is_array($t) && $t[0] === T_CLASS) {
            // Buscar el siguiente T_STRING
            for ($j = $i + 1; $j < $n; $j++) {
                if (is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
                    $found = $tokens[$j][1];
                    // encendemos inClass si coincide con cualquiera de los dos patrones
                    $inClass = in_array($found, $ctrlNames, true);
                    break;
                }
            }
        }

        // Dentro de la clase, buscar T_FUNCTION
        if ($inClass && is_array($t) && $t[0] === T_FUNCTION) {
            // saltamos whitespace, &, static/public/etc.
            $k = $i + 1;
            while (
                isset($tokens[$k]) &&
                (
                    (is_array($tokens[$k]) && in_array($tokens[$k][0], [T_WHITESPACE, T_STATIC, T_PUBLIC, T_PROTECTED, T_PRIVATE], true))
                    || $tokens[$k] === '&'
                )
            ) {
                $k++;
            }
            // el siguiente T_STRING es el nombre del método
            if (isset($tokens[$k]) && is_array($tokens[$k]) && $tokens[$k][0] === T_STRING) {
                $name = $tokens[$k][1];
                if ($name !== '__construct') {
                    $methodNames[] = $name;
                }
            }
        }

        // 6) Insertar en BD (INSERT IGNORE para no duplicar)
        foreach (array_unique($methodNames) as $action) {
            $db->consultas(
                "INSERT IGNORE INTO permissions (controlador, accion) VALUES (?, ?)",
                [$base, $action]
            );
        }
    }
}

echo "✔ Permisos recargados en la tabla permissions.\n";
