<?php
ob_start(); // permite enviar headers sin interferencias
require_once('modelos/directivas.modelo.php');
require_once __DIR__ . '/../modelos/push.modelo.php';

class ControladorDirectivas
{

    /* GUARDAR DIRECTIVAS */
    static public function crtGuardarDirectiva()
    {
        //Auth::check('directivas', 'crtGuardarDirectiva');
        Auth::check('directivas', 'vistaCrearDirectiva');
        if (isset($_POST["id_objetivo"])) {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            try {
                $conexion = Conexion::conectar();

                if (!$conexion->inTransaction()) {
                    $conexion->beginTransaction();
                }

                // 1. Procesar adjunto
                $rutaAdjunto = null;
                if (isset($_FILES["adjunto"]) && $_FILES["adjunto"]["error"] !== UPLOAD_ERR_NO_FILE) {
                    $nombreBase = "directiva_" . date("YmdHis");
                    $rutaAdjunto = ControladorArchivos::guardarArchivo(
                        $_FILES["adjunto"],
                        "img/directivas/",
                        $nombreBase
                    );
                }

                $tabla = "directivas";

                // 2. Datos a insertar
                $datos = array(
                    "id_objetivo" => $_POST["id_objetivo"],
                    "detalle"     => $_POST["detalle"],
                    "adjunto"     => $rutaAdjunto,
                    "tipo"        => $_POST["tipo"] ?? 'general' // valor por defecto
                );


                // 3. Guardar directiva
                $respuesta = ModeloDirectivas::mdlGuardarDirectiva($tabla, $datos);

                if ($respuesta === "ok") {
                    // ✅ Insertar alertas para supervisores y vigiladores activos
                    $sqlUsuarios = "SELECT u.idUsuario
                                            FROM usuarios u
                                            JOIN roles r ON u.rol_id = r.id
                                            WHERE r.categoria IN ('operativo','referente','supervisor')
                                            AND u.activo = 1
                                            AND r.activo = 1;";
                    $usuarios = $conexion->query($sqlUsuarios)->fetchAll(PDO::FETCH_ASSOC);

                    $sqlAlerta = "INSERT INTO alertas (tipo, mensaje, usuario_id, objetivo_id, leida, creada_en)
                              VALUES ('directiva', :mensaje, :uid, :oid, 0, NOW())";
                    $stmt = $conexion->prepare($sqlAlerta);
                    foreach ($usuarios as $u) {
                        $stmt->execute([
                            ':mensaje' => 'Se ha publicado una nueva directiva.',
                            ':uid' => $u['idUsuario'],
                            ':oid' => $_POST["id_objetivo"]
                        ]);
                    }

                    // Confirmar todo
                    $conexion->commit();

                    $destinatariosPush = array_map(static fn($u) => (int)($u['idUsuario'] ?? 0), $usuarios);
                    ModeloPush::enviarPushAUsuarios($destinatariosPush);

                    ToastifyController::success("Directiva creada exitosamente.");
                } else {
                    $conexion->rollBack();
                    ToastifyController::error("Error al guardar la directiva.");
                }
            } catch (Exception $e) {
                if ($conexion->inTransaction()) {
                    $conexion->rollBack();
                }
                if (!empty($rutaAdjunto) && file_exists($rutaAdjunto)) {
                    unlink($rutaAdjunto);
                }
                ToastifyController::error("Error: " . $e->getMessage());
                return false;
            }
        }
    }


    /* MODIFICAR DIRECTIVAS */
    static public function crtModificarDirectiva()
    {
        Auth::check('directivas', 'vistaEditarDirectiva');
        if (isset($_POST["idDirectiva"])) {
            try {
                $conexion = Conexion::conectar();
                if (!$conexion->inTransaction()) {
                    $conexion->beginTransaction();
                }

                $idDirectiva  = intval($_POST["idDirectiva"]);
                $id_objetivo  = intval($_POST["id_objetivo"]);
                $detalle      = $_POST["detalle"];
                $tipo         = $_POST["tipo"]; // ahora obligatorio
                $rutaAdjuntoViejo = $_POST["adjuntoActual"];

                // Procesar nuevo archivo si existe
                if (
                    isset($_FILES["adjunto"]) &&
                    $_FILES["adjunto"]["error"] !== UPLOAD_ERR_NO_FILE
                ) {
                    $nombreBase = "directiva_"  . date("YmdHis");
                    $rutaAdjuntoNuevo = ControladorArchivos::guardarArchivo(
                        $_FILES["adjunto"],
                        "img/directivas/",
                        $nombreBase
                    );
                    if (!empty($rutaAdjuntoViejo) && file_exists($rutaAdjuntoViejo)) {
                        unlink($rutaAdjuntoViejo);
                    }
                    $rutaAdjuntoFinal = $rutaAdjuntoNuevo;
                } else {
                    $rutaAdjuntoFinal = $rutaAdjuntoViejo;
                }

                // Armamos datos completos
                $datos = [
                    "idDirectiva" => $idDirectiva,
                    "id_objetivo" => $id_objetivo,
                    "detalle"     => $detalle,
                    "tipo"        => $tipo,
                    "adjunto"     => $rutaAdjuntoFinal
                ];

                $respuesta = ModeloDirectivas::mdlModificarDirectiva("directivas", $datos);

                if ($respuesta === "ok") {
                    $conexion->commit();
                    ToastifyController::success("Directiva modificada exitosamente.");
                    header("Location:?r=listado_directivas");
                    exit;
                } else {
                    $conexion->rollBack();
                    ToastifyController::error("Error al modificar la directiva.");
                    header("Location: ?r=modificar_directivas&id=" . $idDirectiva);
                    exit;
                }
            } catch (Exception $e) {
                if ($conexion->inTransaction()) {
                    $conexion->rollBack();
                }
                ToastifyController::error("Error: " . $e->getMessage());
                header("Location: ?r=modificar_directivas&id=" . intval($_POST["idDirectiva"]));
                exit;
            }
        }
    }



    static public function crtEliminarDirectiva()
    {
        Auth::check('directivas', 'crtEliminarDirectiva');
        if (isset($_POST['idEliminar'])) {
            // Convertimos a entero para sanear
            $idDirectiva = intval($_POST['idEliminar']);

            try {
                $conexion = Conexion::conectar();
                // Iniciamos transacción
                if (!$conexion->inTransaction()) {
                    $conexion->beginTransaction();
                }

                // Llamamos al modelo para borrar
                $respuesta = ModeloDirectivas::mdlEliminarDirectiva('directivas', $idDirectiva);

                if ($respuesta === 'ok') {
                    $conexion->commit();
                    ToastifyController::success("Directiva eliminada correctamente.");
                } elseif ($respuesta === 'no_permitido') {
                    $conexion->rollBack();
                    ToastifyController::error("No se puede eliminar una directiva general.");
                } else {
                    $conexion->rollBack();
                    ToastifyController::error("No se pudo eliminar la directiva.");
                }
            } catch (Exception $e) {
                if ($conexion->inTransaction()) {
                    $conexion->rollBack();
                }
                ToastifyController::error('Error: ' . $e->getMessage());
            }
        }
    }
    static public function vistaListadoDirectivas()
    {
        Auth::check('directivas', 'vistaListadoDirectivas');
        $db = new Conexion();


        // Recupero rol y, en caso de Vigilador, su objetivo
        $rol = $_SESSION['rol'] ?? '';

        if ($rol === 'Vigilador') {
            // Opción A: lo sacas directo de sesión
            $objetivoId = $_SESSION['objetivo_id'] ?? null;

            if ($objetivoId) {
                $sql = "SELECT d.*, o.nombre FROM directivas d JOIN objetivos o ON d.id_objetivo = o.idObjetivo WHERE d.id_objetivo = :obj ORDER BY d.id_objetivo ";
                $params = [':obj' => $objetivoId];
            } else {
                // Si no tiene objetivo asignado, devolvemos vacío
                $directivas = [];
                include __DIR__ . '/../vistas/paginas/directivas/listado_directivas.php';
                return;
            }
        } else {
            // Para todos los demás roles, sin filtro
            $sql = " SELECT d.*, o.nombre FROM directivas d JOIN objetivos o ON d.id_objetivo = o.idObjetivo ORDER BY d.id_objetivo ";
            $params = [];
        }

        // Ejecuto la consulta
        $directivas = $db->consultas($sql, $params);

        // Cargo la vista
        include __DIR__ . '/../vistas/paginas/directivas/listado_directivas.php';
    }


    static public function vistaCrearDirectiva()
    {
        Auth::check('directivas', 'vistaCrearDirectiva');
        include __DIR__ . '/../vistas/paginas/directivas/crear_directivas.php';
    }
    static public function vistaEditarDirectiva()
    {
        Auth::check('directivas', 'vistaEditarDirectiva');
        include __DIR__ . '/../vistas/paginas/directivas/modificar_directivas.php';
    }
}
