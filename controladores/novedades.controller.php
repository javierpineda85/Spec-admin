<?php
ob_start(); // Para que los headers no tiren error

require_once('modelos/novedades.modelo.php');

class NovedadesController
{
    /**
     * Registra una novedad asociada a un usuario y objetivo, con manejo de transacción y errores.
     */
    public static function crtRegistrar()
    {
        Auth::check('novedades', 'crtRegistrar');
        if (session_status() === PHP_SESSION_NONE) session_start();
        $vigilador_id =  $_POST['vigilador_id'] ?? null;
        $objetivo_id =  $_POST['objetivo_id'] ?? null;
        $detalle = trim($_POST['detalle'] ?? '');
        $fecha = date('Y-m-d');
        $hora = date('H:i');

        if (! $vigilador_id || ! $detalle) {
            $_SESSION['error_message'] = 'Faltan datos obligatorios.';
            header('Location:?r=crear_novedad');
            exit;
        }

        try {
            $pdo = Conexion::conectar();
            if (! $pdo->inTransaction())  $pdo->beginTransaction();

            // Manejo de archivo
            $ruta = ControladorArchivos::guardarArchivo($_FILES['adjunto'] ?? [], 'img/novedades', 'novedad_' . time());

            $sql = "INSERT INTO novedades (vigilador_id, objetivo_id, fecha, hora, detalle, adjunto) VALUES (:v, :o, :f, :h, :d, :a)";
            $stmt =  $pdo->prepare($sql);
            $stmt->execute([
                ':v' =>  $vigilador_id,
                ':o' =>  $objetivo_id,
                ':f' =>  $fecha,
                ':h' =>  $hora,
                ':d' =>  $detalle,
                ':a' =>  $ruta
            ]);

            $pdo->commit();
            $_SESSION['success_message'] = 'Novedad registrada correctamente.';
            header('Location:?r=crear_novedad');
            exit;
        } catch (Exception  $e) {
            if (isset($pdo) &&  $pdo->inTransaction())  $pdo->rollBack();
            $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
            header('Location:?r=crear_novedad');
            exit;
        }
    }

    static public function vistaListadoNovedades()
    {
        Auth::check('novedades', 'vistaListadoNovedades');
        $db  = new Conexion();
        $rol = $_SESSION['rol'] ?? '';

        if ($rol === 'Vigilador') {
            // Asumimos que guardaste el objetivo del vigilador en sesión
            $objetivoId = $_SESSION['objetivo_id'] ?? null;
            if (! $objetivoId) {
                // Si no tiene objetivo asignado, devolvemos un array vacío
                $novedades = [];
                include __DIR__ . '/../vistas/paginas/novedades/listado_novedades.php';
                return;
            }
            // Sólo las novedades de su objetivo
            $sql = "SELECT n.idNovedad, o.nombre  AS objetivo, CONCAT(u.apellido,' ',u.nombre) AS creado_por, n.fecha, n.hora, n.detalle, n.created_at, n.adjunto FROM novedades n LEFT JOIN objetivos o ON n.objetivo_id = o.idObjetivo LEFT JOIN usuarios u ON n.vigilador_id = u.idUsuario WHERE n.objetivo_id = :obj ORDER BY n.fecha DESC, n.hora DESC ";
            $params = [':obj' => $objetivoId];
        } else {
            // Todos las novedades
            $sql = "SELECT n.idNovedad, o.nombre AS objetivo,CONCAT(u.apellido,' ',u.nombre) AS creado_por, n.fecha, n.hora, n.detalle, n.created_at, n.adjunto FROM novedades n LEFT JOIN objetivos o ON n.objetivo_id = o.idObjetivo LEFT JOIN usuarios u ON n.vigilador_id = u.idUsuario  ORDER BY n.fecha DESC, n.hora DESC ";
            $params = [];
        }


        $novedades = $db->consultas($sql, $params);

        include __DIR__ . '/../vistas/paginas/novedades/listado_novedades.php';
    }
    static public function vistaListadoEntradaSalida()
    {
        Auth::check('novedades', 'vistaListadoEntradaSalida');
        $db = new Conexion();
        $sql = " SELECT m.idMarcacion,CONCAT(u.apellido, ' ', u.nombre) AS vigilador, o.nombre AS objetivo, m.tipo_evento, m.fecha_hora, m.latitud, m.longitud, m.created_at FROM marcaciones_servicio m JOIN usuarios u ON m.vigilador_id = u.idUsuario LEFT JOIN objetivos o ON m.objetivo_id = o.idObjetivo ORDER BY m.fecha_hora DESC ";

        $marcaciones = $db->consultas($sql);
        include __DIR__ . '/../vistas/paginas/novedades/listado_entradaSalidas.php';
        return;
    }

    static public function vistaCrearNovedades()
    {
        Auth::check('novedades', 'vistaCrearNovedades');
        include __DIR__ . '/../vistas/paginas/novedades/crear_novedades.php';
    }

}
