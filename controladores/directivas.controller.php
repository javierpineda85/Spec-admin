<?php
ob_start(); // permite enviar headers sin interferencias
require_once('modelos/directivas.modelo.php');

class ControladorDirectivas
{
    /* GUARDAR DIRECTIVAS */
    static public function crtGuardarDirectiva()
    {
        Auth::check('directivas', 'crtGuardarDirectiva');
        if (isset($_POST["id_objetivo"])) {
            // Para mensajes de sesión
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            try {
                $conexion = Conexion::conectar();

                // Iniciar transacción si no hay una activa
                if (!$conexion->inTransaction()) {
                    $conexion->beginTransaction();
                }

                // 1. Procesar adjunto si existe
                $rutaAdjunto = null;
                if (isset($_FILES["adjunto"]) && $_FILES["adjunto"]["error"] !== UPLOAD_ERR_NO_FILE) {
                    // Generamos un nombre base único para el archivo, p. ej. "directiva_20250606204530"
                    $nombreBase = "directiva_" . date("YmdHis");
                    $rutaAdjunto = ControladorArchivos::guardarArchivo(
                        $_FILES["adjunto"],
                        "img/directivas/",
                        $nombreBase
                    );
                }

                $tabla = "directivas";

                // 2. Preparar datos a insertar en la BD
                $datos = array(
                    "id_objetivo" => $_POST["id_objetivo"],
                    // Limpiar saltos de línea (ya lo hacías en el modelo)
                    "detalle"     => $_POST["detalle"],
                    // Puede ser nulo si no subieron nada
                    "adjunto"     => $rutaAdjunto
                );

                // 3. Llamar al modelo para guardar
                $respuesta = ModeloDirectivas::mdlGuardarDirectiva($tabla, $datos);

                if ($respuesta === "ok") {
                    // Confirmar transacción
                    $conexion->commit();
                    $_SESSION['success_message'] = 'Directiva creada exitosamente';
                } else {
                    // Si algo falla, revirtiendo cambios
                    $conexion->rollBack();
                    $_SESSION['error_message'] = 'Error al guardar la directiva.';
                }
            } catch (Exception $e) {
                // Rollback en caso de excepción
                if ($conexion->inTransaction()) {
                    $conexion->rollBack();
                }
                // Puedes, por ejemplo, borrar el archivo si lo habías movido y hubo un error después
                if (!empty($rutaAdjunto) && file_exists($rutaAdjunto)) {
                    unlink($rutaAdjunto);
                }
                $_SESSION['error_message'] = "Error: " . $e->getMessage();
                return false;
            }
        }
    }

    /* MODIFICAR DIRECTIVAS */
    static public function crtModificarDirectiva()
    {
        Auth::check('directivas', 'crtModificarDirectiva');
        if (isset($_POST["idDirectiva"])) {
            try {
                $conexion = Conexion::conectar();
                if (!$conexion->inTransaction()) {
                    $conexion->beginTransaction();
                }

                // 1) Obtener los valores que vienen del form
                $idDirectiva  = intval($_POST["idDirectiva"]);
                $id_objetivo  = intval($_POST["id_objetivo"]);
                $detalle      = $_POST["detalle"];
                // Ruta actual en BD (hidden input)
                $rutaAdjuntoViejo = $_POST["adjuntoActual"];

                // 2) Procesar posible nuevo archivo
                if (
                    isset($_FILES["adjunto"]) &&
                    $_FILES["adjunto"]["error"] !== UPLOAD_ERR_NO_FILE
                ) {
                    // Generar un nombre base único para este archivo
                    $nombreBase = "directiva_"  . date("YmdHis");
                    $rutaAdjuntoNuevo = ControladorArchivos::guardarArchivo(
                        $_FILES["adjunto"],
                        "img/directivas/",
                        $nombreBase
                    );
                    // Si se guardó bien, borramos el antiguo (opcional)
                    if (!empty($rutaAdjuntoViejo) && file_exists($rutaAdjuntoViejo)) {
                        unlink($rutaAdjuntoViejo);
                    }
                    $rutaAdjuntoFinal = $rutaAdjuntoNuevo;
                } else {
                    // No subió nada: quedamos con la ruta vieja
                    $rutaAdjuntoFinal = $rutaAdjuntoViejo;
                }

                // 3) Preparar array para el modelo
                $datos = [
                    "idDirectiva" => $idDirectiva,
                    "id_objetivo" => $id_objetivo,
                    "detalle"     => $detalle,
                    "adjunto"     => $rutaAdjuntoFinal   // puede ser cadena vacía o NULL
                ];

                // 4) Llamar al modelo para actualizar
                $respuesta = ModeloDirectivas::mdlModificarDirectiva("directivas", $datos);

                if ($respuesta === "ok") {
                    $conexion->commit();
                    $_SESSION['success_message'] = 'Directiva modificada exitosamente';
                    header("Location:?r=listado_directivas");
                    exit;
                } else {
                    $conexion->rollBack();
                    $_SESSION['error_message'] = 'Error al modificar la directiva';
                    header("Location: ?r=modificar_directivas&id=" . $idDirectiva);
                    exit;
                }
            } catch (Exception $e) {
                if ($conexion->inTransaction()) {
                    $conexion->rollBack();
                }
                $_SESSION['error_message'] = "Error: " . $e->getMessage();
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
                    $_SESSION['success_message'] = 'Directiva eliminada correctamente.';
                } else {
                    $conexion->rollBack();
                    $_SESSION['error_message'] = 'No se pudo eliminar la directiva.';
                }
            } catch (Exception $e) {
                if ($conexion->inTransaction()) {
                    $conexion->rollBack();
                }
                $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
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
        include __DIR__ . '/../vistas/paginas/directivas/crear_directiva.php';
    }
    static public function vistaEditarDirectiva()
    {
        Auth::check('directivas', 'vistaEditarDirectiva');
        include __DIR__ . '/../vistas/paginas/directivas/modificar_directiva.php';
    }
}
