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
}
