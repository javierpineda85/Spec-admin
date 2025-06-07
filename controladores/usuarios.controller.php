<?php

require_once ('archivos.controller.php');   
require_once ('modelos/usuarios.modelo.php'); 

class ControladorUsuarios
{
    /* GUARDAR USUARIOS */
    static public function crtGuardarUsuario()
    {
        if (isset($_POST["nombre"])) {
            try {
                $conexion = Conexion::conectar();

                // Iniciar transacción si no hay una activa
                if (!$conexion->inTransaction()) {
                    $conexion->beginTransaction();
                }

                $tabla = "usuarios";

                $nombre   = trim($_POST["nombre"]);
                $apellido = trim($_POST["apellido"]);
                $dni      = trim($_POST["dni"]);
                $pass     = password_hash($_POST["pass"], PASSWORD_DEFAULT);
                $f_nac    = $_POST["f_nac"];
                $telefono = $_POST["telefono"];
                $tel_emergencia  = $_POST["tel_emergencia"];
                $nombre_contacto = $_POST["nombre_contacto"];
                $parentesco      = $_POST["parentesco"];
                $domicilio       = $_POST["domicilio"];
                $provincia       = $_POST["provincia"];
                $rol        = $_POST["rol"];

                // Normalizar nombre de archivo (sin espacios)
                $nombreArchivo = preg_replace('/\s+/', '', $nombre . $apellido);

                // --- Aquí llamamos a ControladorArchivos::guardarArchivo() ---
                // Rutas ya definidas: "img/perfil/" y "img/repriv/"
                $imgPerfil = ControladorArchivos::guardarArchivo(
                    $_FILES["imgPerfil"],
                    "img/perfil/",
                    $nombreArchivo . "Perfil"
                );

                $imgRepriv = ControladorArchivos::guardarArchivo(
                    $_FILES["imgRepriv"],
                    "img/repriv/",
                    $nombreArchivo . "Repriv"
                );
                // ------------------------------------------------------------

                $datos = array(
                    "nombre"         => $nombre,
                    "apellido"       => $apellido,
                    "dni"            => $dni,
                    "pass"           => $pass,
                    "f_nac"          => $f_nac,
                    "telefono"       => $telefono,
                    "tel_emergencia" => $tel_emergencia,
                    "nombre_contacto"=> $nombre_contacto,
                    "parentesco"     => $parentesco,
                    "domicilio"      => $domicilio,
                    "provincia"      => $provincia,
                    "rol"            => $rol,
                    "imgPerfil"      => $imgPerfil,   // puede ser null si no subieron nada
                    "imgRepriv"      => $imgRepriv,   // idem
                    "resetPass"      => 1,
                    "activo"         => 1
                );

                $respuesta = ModeloUsuarios::mdlGuardarUsuario($tabla, $datos);

                if ($respuesta === "ok") {
                    $conexion->commit();
                    $_SESSION['success_message'] = "Usuario registrado correctamente.";
                } else {
                    // Si el modelo devolvió “error” u otro string, hacemos rollback
                    throw new Exception("Error al guardar en la base de datos.");
                }

            } catch (Exception $e) {
                // Rollback y mostrar mensaje
                if ($conexion->inTransaction()) {
                    $conexion->rollBack();
                }
                $_SESSION['error_message'] = $e->getMessage();
                return false;
            }
        }
    }

    /* MODIFICAR USUARIOS */
    static public function crtModificarUsuario()
    {
        if (isset($_POST["idUsuario"])) {
            try {
                $conexion = Conexion::conectar();

                if (!$conexion->inTransaction()) {
                    $conexion->beginTransaction();
                }

                $tabla      = "usuarios";
                $id_usuario = $_POST["idUsuario"];
                $nombre     = trim($_POST["nombre"]);
                $apellido   = trim($_POST["apellido"]);
                $dni        = trim($_POST["dni"]);
                $f_nac      = $_POST["f_nac"];
                $telefono   = $_POST["telefono"];
                $tel_emergencia  = $_POST["tel_emergencia"];
                $nombre_contacto = $_POST["nombre_contacto"];
                $parentesco      = $_POST["parentesco"];
                $domicilio       = $_POST["domicilio"];
                $provincia       = $_POST["provincia"];
                $rol             = $_POST["rol"];
                $resetPass = isset($_POST["resetPass"]) ? 0 : 1; // 0: NO restaurar, 1: sí
                $activo    = isset($_POST["activo"])    ? 0 : 1; // 0: inactivo, 1: activo

                // Nombre base para los archivos nuevos (sin espacios)
                $nombreArchivo = preg_replace('/\s+/', '', $nombre . $apellido);

                // Si suben nueva imgPerfil, la guardamos; si no, tomamos la ruta actual que venga oculta en el form
                $imgPerfil = !empty($_FILES["imgPerfil"]["name"])
                    ? ControladorArchivos::guardarArchivo(
                        $_FILES["imgPerfil"],
                        "img/perfil/",
                        $nombreArchivo . "Perfil"
                      )
                    : $_POST["imgPerfilActual"];

                // Lo mismo para imgRepriv
                $imgRepriv = !empty($_FILES["imgRepriv"]["name"])
                    ? ControladorArchivos::guardarArchivo(
                        $_FILES["imgRepriv"],
                        "img/repriv/",
                        $nombreArchivo . "Repriv"
                      )
                    : $_POST["imgReprivActual"];

                $datos = array(
                    "id_usuario"     => $id_usuario,
                    "nombre"         => $nombre,
                    "apellido"       => $apellido,
                    "dni"            => $dni,
                    "f_nac"          => $f_nac,
                    "telefono"       => $telefono,
                    "tel_emergencia" => $tel_emergencia,
                    "nombre_contacto"=> $nombre_contacto,
                    "parentesco"     => $parentesco,
                    "domicilio"      => $domicilio,
                    "provincia"      => $provincia,
                    "rol"            => $rol,
                    "imgPerfil"      => $imgPerfil,
                    "imgRepriv"      => $imgRepriv,
                    "resetPass"      => $resetPass,
                    "activo"         => $activo
                );

                $respuesta = ModeloUsuarios::mdlModificarUsuario($tabla, $datos);

                if ($respuesta === "ok") {
                    $conexion->commit();
                    $_SESSION['success_message'] = "Usuario modificado correctamente.";
                } else {
                    throw new Exception("Error al modificar en la base de datos.");
                }

            } catch (Exception $e) {
                if ($conexion->inTransaction()) {
                    $conexion->rollBack();
                }
                $_SESSION['error_message'] = $e->getMessage();
                return false;
            }
        }
    }

}
