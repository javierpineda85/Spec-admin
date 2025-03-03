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
        try {
            $conexion = Conexion::conectar();

            if (!$conexion->inTransaction()) {
                $conexion->beginTransaction();
            }

            $tabla = "rondas";
            $resultados = [];

            // Crear una instancia de la clase para llamar a los métodos de instancia
            $controller = new RondasController();

            foreach ($rondas as $ronda) {
                $datos = [
                    "puesto" => $ronda["puesto"],
                    "objetivo_id" => $ronda["objetivo_id"],
                    "orden_escaneo" => $ronda["orden_escaneo"],
                    "tipo" => $ronda["tipo"]
                ];

                $res = ModeloRondas::mdlGuardarRonda($tabla, $datos);

                // Llamar las funciones de instancia
                $controller->eliminarImagenesQR();
                $controller->eliminarErroresQR();
                $controller->limpiarSesionQR();

                // Mostrar el mensaje de éxito
                echo json_encode(['success' => true, 'message' => 'Rondas guardadas correctamente.']);

                if ($res !== "ok") {
                    throw new Exception("Error al guardar la ronda.");
                }
                $resultados[] = $res;
            }

            $conexion->commit();
            return ['success' => true, 'resultados' => $resultados];
            
            exit();
        } catch (Exception $e) {
            $conexion->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Funciones de instancia
    public function eliminarImagenesQR()
    {
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
        if (isset($_SESSION['qr_codes'])) {
            unset($_SESSION['qr_codes']);  // Eliminar la variable de sesión 'qr_codes'
        }
    }
}
