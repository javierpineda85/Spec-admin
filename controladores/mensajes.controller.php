<?php
//require_once('modelos/mensajes.modelo.php');
require_once __DIR__ . "/../modelos/mensajes.modelo.php";

class ControladorMensajes
{
    static public function crtGuardarMensaje()
    {
        Auth::check('mensajes', 'crtGuardarMensaje');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $id_remitente       = (int)($_POST["id_remitente"] ?? 0);
            $id_destinatario    = (int)($_POST["id_destinatario"] ?? 0);
            $contenido          = trim($_POST["contenidoMensaje"] ?? '');
            $idMensajeOriginal  = isset($_POST['idMensajeOriginal']) ? (int)$_POST['idMensajeOriginal'] : null;
            $fecha              = date('Y-m-d H:i:s');

            // Validaciones básicas
            if (!$id_remitente || !$id_destinatario || $contenido === '') {
                ToastifyController::error('Faltan datos para enviar el mensaje');
                return null;
            }

            // Validar permisos con la nueva lógica
            if (!self::puedeEnviar($id_remitente, $id_destinatario, $idMensajeOriginal)) {
                ToastifyController::error('No tienes permiso para enviar este mensaje');
                return null;
            }

            // Guardar objetivo_id del remitente para validaciones futuras
            $objetivo_id = $_SESSION['objetivo_id'] ?? null;

            // Datos para guardar
            $datos = [
                "id_remitente"     => $id_remitente,
                "id_destinatario"  => $id_destinatario,
                "contenidoMensaje" => $contenido,
                "fechaMensaje"     => $fecha,
                "objetivo_id"      => $objetivo_id
            ];

            // Guardar mensaje
            $respuesta = ModeloMensajes::mdlGuardarMensaje($datos);

            if ($respuesta === "ok") {
                ToastifyController::success('Mensaje enviado con éxito');
            } else {
                ToastifyController::error('Error al enviar el mensaje');
            }

            return $respuesta;
        }
        return null;
    }

    //bandeja de entrada
    static public function crtMostrarMensajesRecibidos($item, $valor)
    {
        Auth::check('mensajes', 'crtMostrarMensajesEnviados'); //para no afectar lo ya creado pero deberia tener su propio permiso
        $respuesta = ModeloMensajes::mdlMostrarMensajes($item, $valor);
        return $respuesta;
    }

    static public function crtMostrarMensajesEnviados($item, $valor)
    {
        Auth::check('mensajes', 'crtMostrarMensajesEnviados');
        $respuesta = ModeloMensajes::mdlMostrarMensajesEnviados($item, $valor);
        return $respuesta;

        exit;
    }
    static public function crtMostrarUnMensaje($id)
    {
        Auth::check('mensajes', 'crtMostrarUnMensaje');
        $respuesta = ModeloMensajes::mdlMostrarUnMensaje($id);
        return $respuesta;

        exit;
    }
    static public function crtMarcarLeido($id)
    {
        //Auth::check('mensajes', 'crtMarcarLeido'); 
        return ModeloMensajes::mdlMarcarLeido($id);
    }
    static public function crtMarcarNoLeido($id)
    {
        //Auth::check('mensajes', 'crtMarcarNoLeido'); 
        return ModeloMensajes::mdlMarcarNoLeido($id);
    }

    static public function puedeEnviar($id_remitente, $id_destinatario, $idMensajeOriginal = null)
    {
        $db = new Conexion();
        $modeloUsuarios = new ModeloUsuarios();

        // === Datos del remitente ===
        if ($id_remitente === $_SESSION['idUsuario']) {
            $rem = [
                'idUsuario'   => $_SESSION['idUsuario'],
                'nivel'       => $_SESSION['nivel'],
                'categoria'   => $_SESSION['categoria'],
                'reservado'   => $_SESSION['reservado'],
                'objetivo_id' => $_SESSION['objetivo_id'] ?? null
            ];
        } else {
            $rem = $db->consultas("
                    SELECT u.idUsuario, r.nivel, r.categoria, r.reservado
                    FROM usuarios u
                    JOIN roles r ON u.rol_id = r.id
                    WHERE u.idUsuario = ?
                ", [$id_remitente])[0] ?? null;

            if ($rem && in_array($rem['categoria'], ['operativo', 'referente'])) {
                $asigRem = $modeloUsuarios->getAsignacionHoy($rem['idUsuario']);
                $rem['objetivo_id'] = $asigRem['objetivo_id'] ?? null;
            } else {
                $rem['objetivo_id'] = null;
            }
        }

        // === Datos del destinatario ===
        if ($id_destinatario === $_SESSION['idUsuario']) {
            $dest = [
                'idUsuario'   => $_SESSION['idUsuario'],
                'nivel'       => $_SESSION['nivel'],
                'categoria'   => $_SESSION['categoria'],
                'reservado'   => $_SESSION['reservado'],
                'objetivo_id' => $_SESSION['objetivo_id'] ?? null
            ];
        } else {
            $dest = $db->consultas("
                    SELECT u.idUsuario, r.nivel, r.categoria, r.reservado
                    FROM usuarios u
                    JOIN roles r ON u.rol_id = r.id
                    WHERE u.idUsuario = ?
                ", [$id_destinatario])[0] ?? null;

            if ($dest && in_array($dest['categoria'], ['operativo', 'referente'])) {
                $asigDest = $modeloUsuarios->getAsignacionHoy($dest['idUsuario']);
                $dest['objetivo_id'] = $asigDest['objetivo_id'] ?? null;
            } else {
                $dest['objetivo_id'] = null;
            }
        }

        if (!$rem || !$dest) return false;

        // === Reglas de envío ===
        if ((int)$rem['reservado'] === 1) return true;
        if (in_array($rem['categoria'], ['supervisor', 'direccion'])) return true;

        if (in_array($rem['categoria'], ['operativo', 'referente'])) {
            if ($idMensajeOriginal) {
                $msg = $db->consultas("SELECT objetivo_id FROM mensajes WHERE idMensaje = ?", [$idMensajeOriginal]);
                $objetivoMensaje = $msg[0]['objetivo_id'] ?? null;
                if ($objetivoMensaje && $objetivoMensaje != $rem['objetivo_id']) {
                    return false;
                }
            }
            return (in_array($dest['categoria'], ['operativo', 'referente']) &&
                $rem['objetivo_id'] == $dest['objetivo_id']);
        }

        if (in_array($rem['categoria'], ['administrativo', 'direccion'])) {
            return true;
        }

        if (
            in_array($rem['categoria'], ['operativo', 'referente']) &&
            in_array($dest['categoria'], ['administrativo', 'direccion'])
        ) {

            $hayMensajePrevio = $db->consultas(
                "SELECT COUNT(*) AS total FROM mensajes WHERE remitente_id = ? AND destinatario_id = ?",
                [$id_destinatario, $id_remitente]
            )[0]['total'] ?? 0;

            if ($hayMensajePrevio > 0) {
                $yaRespondio = $db->consultas(
                    "SELECT COUNT(*) AS total FROM mensajes WHERE remitente_id = ? AND destinatario_id = ?",
                    [$id_remitente, $id_destinatario]
                )[0]['total'] ?? 0;
                return $yaRespondio < 1;
            }
            return false;
        }

        return true;
    }

    static public function obtenerDestinatariosDisponibles($id_remitente, $idMensajeOriginal = null)
    {
        $db = new Conexion();
        $modeloUsuarios = new ModeloUsuarios();

        // Datos del remitente
        if ($id_remitente === $_SESSION['idUsuario']) {
            $remCategoria = $_SESSION['categoria'];
            $remObjetivo  = $_SESSION['objetivo_id'] ?? null;
        } else {
            $rem = $db->consultas("
                    SELECT r.categoria
                    FROM usuarios u
                    JOIN roles r ON u.rol_id = r.id
                    WHERE u.idUsuario = ?
                ", [$id_remitente])[0] ?? null;

            $remCategoria = $rem['categoria'] ?? null;
            if (in_array($remCategoria, ['operativo', 'referente'])) {
                $asigRem = $modeloUsuarios->getAsignacionHoy($id_remitente);
                $remObjetivo = $asigRem['objetivo_id'] ?? null;
            } else {
                $remObjetivo = null;
            }
        }

        $usuarios = $db->consultas("
                SELECT u.idUsuario, u.nombre, u.apellido, r.categoria, r.nivel, r.reservado
                FROM usuarios u
                JOIN roles r ON u.rol_id = r.id
                WHERE u.idUsuario != ?
                ORDER BY u.apellido, u.nombre
            ", [$id_remitente]) ?? [];

        $disponibles = [];

        foreach ($usuarios as $u) {
            $destObjetivo = null;
            if (in_array($u['categoria'], ['operativo', 'referente'])) {
                $asigDest = $modeloUsuarios->getAsignacionHoy($u['idUsuario']);
                $destObjetivo = $asigDest['objetivo_id'] ?? null;
            }

            if (
                in_array($remCategoria, ['operativo', 'referente']) &&
                in_array($u['categoria'], ['operativo', 'referente']) &&
                $remObjetivo && $destObjetivo != $remObjetivo
            ) {
                continue;
            }

            if (self::puedeEnviar($id_remitente, $u['idUsuario'], $idMensajeOriginal)) {
                $disponibles[] = $u;
            }
        }

        return $disponibles;
    }
}
