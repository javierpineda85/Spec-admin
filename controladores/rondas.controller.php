<?php
ob_start(); //permite enviar los headers sin interferencias

// Usamos dirname(__DIR__) para obtener la ruta correcta
require_once dirname(__DIR__) . '/modelos/rondas.modelo.php';
require_once dirname(__DIR__) . '/modelos/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class RondasController
{
    // Guardar rondas
    static public function crtGuardarRondas($rondas)
    {
        Auth::check('rondas', 'crtGuardarRondas');
        $db = Conexion::conectar();
        try {
            $db->beginTransaction();
            $resultados = [];

            foreach ($rondas as $ronda) {
                $datos = [
                    'puesto'        => $ronda['puesto'],
                    'objetivo_id'   => $ronda['objetivo_id'],
                    'tipo'          => $ronda['tipo'],
                    'orden_escaneo' => $ronda['orden_escaneo']
                ];
                $res = ModeloRondas::mdlGuardarRonda('rondas', $datos);
                if (!is_numeric($res) || intval($res) <= 0) {
                    throw new Exception("Error al guardar ronda: " . $res);
                }
                $resultados[] = intval($res);
            }

            $db->commit();
            // Limpiar sesión solo **después** de confirmar el commit
            unset($_SESSION['qr_codes']);
            return ['success' => true, 'inserted_ids' => $resultados];
        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Funciones de instancia
    public function eliminarImagenesQR()
    {
        Auth::check('rondas', 'eliminarImagenesQR');
        $directorio = __DIR__ . '/../../img/qrcodes';  // Ruta de la carpeta 'img/qrcodes'
        $archivos = glob($directorio . '/*.png');  // Obtener todos los archivos PNG

        foreach ($archivos as $archivo) {
            if (is_file($archivo)) {
                unlink($archivo);  // Eliminar archivo
            }
        }
    }

    public function eliminarErroresQR()
    {
        Auth::check('rondas', 'eliminarErroresQR');
        $directorio = __DIR__ . '/../../libraries/phpqrcode';  // Ruta de la carpeta 'phpqrcode'
        $archivos = glob($directorio . '/*.log');  // Obtener todos los archivos de errores (si existen)

        foreach ($archivos as $archivo) {
            if (is_file($archivo)) {
                unlink($archivo);  // Eliminar archivo
            }
        }
    }

    public function limpiarSesionQR()
    {
        Auth::check('rondas', 'limpiarSesionQR');
        if (isset($_SESSION['qr_codes'])) {
            unset($_SESSION['qr_codes']);  // Eliminar la variable de sesión 'qr_codes'
        }
    }

    static public function crtDesactivarRonda($idRonda)
    {
        Auth::check('rondas', 'crtDesactivarRonda');
        $res = ModeloRondas::mdlDesactivarRonda('rondas', $idRonda);
        if ($res === 'ok') {
            $_SESSION['success_message'] = 'Ronda desactivada correctamente.';
        } else {
            $_SESSION['error_message'] = 'Error al desactivar ronda: ' . $res;
        }
    }


    static public function crtActualizarRonda()
    {
        Auth::check('rondas', 'crtActualizarRonda');
        // 1️⃣ Recoger y validar datos
        $idRonda    = intval($_POST['idRonda']     ?? 0);
        $puesto     = trim($_POST['puesto']        ?? '');
        $objetivoId = intval($_POST['objetivo_id'] ?? 0);
        $tipo       = trim($_POST['tipo']          ?? '');
        $orden      = intval($_POST['orden']       ?? 0);

        if (!$idRonda || !$puesto || !$objetivoId || !$tipo || !$orden) {
            $_SESSION['error_message'] = 'Faltan datos para actualizar la ronda.';
            header("Location: ?r=editar_ronda&id={$idRonda}");
            exit;
        }

        // 2️⃣ Armar array para el modelo
        $datos = [
            'idRonda'       => $idRonda,
            'puesto'        => $puesto,
            'objetivo_id'   => $objetivoId,
            'tipo'          => $tipo,
            'orden_escaneo' => $orden
        ];

        // 3️⃣ Ejecutar UPDATE
        $res = ModeloRondas::mdlActualizarRonda('rondas', $datos);

        if ($res === 'ok') {
            // 4️⃣ Al actualizar con éxito, limpiamos viejos QRs de sesión
            unset($_SESSION['qr_codes']);

            // 5️⃣ Recuperar datos actualizados de la ronda
            $db   = Conexion::conectar();
            $stmt = $db->prepare("SELECT r.idRonda,
                       r.puesto,
                       r.objetivo_id,
                       r.tipo,
                       r.orden_escaneo,
                       o.nombre AS objetivo_nombre
                  FROM rondas r
                  JOIN objetivos o 
                    ON r.objetivo_id = o.idObjetivo
                 WHERE r.idRonda = ?
            ");
            $stmt->execute([$idRonda]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);

            // 6️⃣ Generar nuevo QR en sesión
            $_SESSION['qr_codes'][] = [
                'idRonda'         => $r['idRonda'],
                'objetivo_id'     => $r['objetivo_id'],
                'objetivo_nombre' => $r['objetivo_nombre'],
                'puesto'          => $r['puesto'],
                'orden'           => $r['orden_escaneo'],
                'tipo'            => $r['tipo']
            ];

            // 7️⃣ Feedback y redirección a impresión
            $_SESSION['success_message'] = 'Ronda actualizada. Ya puedes imprimir el nuevo QR.';
            header("Location: ?r=imprimir_qr");
            exit;
        } else {
            // 8️⃣ En caso de error en el UPDATE, volvemos al formulario
            $_SESSION['error_message'] = 'Error al actualizar: ' . $res;
            header("Location: ?r=editar_ronda&id={$idRonda}");
            exit;
        }
    }

    static public function vistaListadoRondas()
    {
        Auth::check('rondas', 'vistaListadoRondas');
        $db = new Conexion;
        $sql = " SELECT r.idRonda, r.puesto, r.objetivo_id, r.tipo, o.nombre AS objetivo
                  FROM rondas r
                  JOIN objetivos o ON r.objetivo_id = o.idObjetivo
                  WHERE r.status = 'active'
                  ORDER BY r.objetivo_id, r.orden_escaneo";
        $rondas = $db->consultas($sql);
        include __DIR__ . '/../vistas/paginas/rondas/listado_rondas.php';
        return;
    }

    static public function vistaCrearRondas()
    {
        Auth::check('rondas', 'vistaCrearRondas');
        include __DIR__ . '/../vistas/paginas/rondas/crear_rondas.php';
        return;
    }
        static public function vistaEditarRondas()
    {
        Auth::check('rondas', 'vistaEditarRondas');
        include __DIR__ . '/../vistas/paginas/rondas/editar_rondas.php';
        return;
    }

}
