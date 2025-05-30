<?php
require_once('modelos/usuarios.modelo.php');

class ControladorUsuarios
{

    /*GUARDAR USUARIOS */
    static public function crtGuardarUsuario()
    {
        if (isset($_POST["nombre"])) {

            try {
                $conexion = Conexion::conectar();

                // Verificar si ya hay una transacción activa
                if (!$conexion->inTransaction()) {
                    // Si no hay una transacción activa, iniciar una nueva
                    $conexion->beginTransaction();
                }

                // Guardar el usuario
                $tabla = "usuarios";

                $nombre = trim($_POST["nombre"]);
                $apellido = trim($_POST["apellido"]);
                $dni = trim($_POST["dni"]);
                $pass = password_hash($_POST["pass"], PASSWORD_DEFAULT);
                $f_nac = $_POST["f_nac"];
                $telefono = $_POST["telefono"];
                $tel_emergencia = $_POST["tel_emergencia"];
                $nombre_contacto = $_POST["nombre_contacto"];
                $parentesco = $_POST["parentesco"];
                $domicilio = $_POST["domicilio"];
                $provincia = $_POST["provincia"];
                $rol = $_POST["rol"];

                // Normalizar nombre de archivos (Ej: "JuanPerez")
                $nombreArchivo = preg_replace('/\s+/', '', $nombre . $apellido);

                // Rutas de las imágenes
                $imgPerfil = self::guardarImagen($_FILES["imgPerfil"], "img/perfil/", $nombreArchivo . "Perfil");
                $imgRepriv = self::guardarImagen($_FILES["imgRepriv"], "img/repriv/", $nombreArchivo . "Repriv");


                $datos = array(
                    "nombre" => $nombre,
                    "apellido" => $apellido,
                    "dni" => $dni,
                    "pass" => $pass,
                    "f_nac" => $f_nac,
                    "telefono" => $telefono,
                    "tel_emergencia" => $tel_emergencia,
                    "nombre_contacto" => $nombre_contacto,
                    "parentesco" => $parentesco,
                    "domicilio" => $domicilio,
                    "provincia" => $provincia,
                    "rol" => $rol,
                    "imgPerfil" => $imgPerfil,
                    "imgRepriv" => $imgRepriv,
                    "resetPass" => 1,
                    "activo" => 1
                );

                $respuesta = ModeloUsuarios::mdlGuardarUsuario($tabla, $datos);

                // Confirmar la transacción si no hay errores

                if ($respuesta == "ok") {
                    $conexion->commit();
                    $_SESSION['success_message'] = "Usuario registrado correctamente.";
                } else {
                    throw new Exception("Error al guardar en la base de datos.");
                }
            } catch (Exception $e) {
                // Revertir la transacción en caso de error
                $conexion->rollBack();

                // Manejar el error según sea necesario
                $_SESSION['success_message'] =  $e->getMessage();

                return false;
            }
        }
    }

    /*MODIFICAR USUARIOS */
    static public function crtModificarUsuario()
    {
        
        if (isset($_POST["idUsuario"])) {
            try {
                $conexion = Conexion::conectar();

                if (!$conexion->inTransaction()) {
                    $conexion->beginTransaction();
                }

                $tabla = "usuarios";
                $id_usuario = $_POST["idUsuario"];
                $nombre = trim($_POST["nombre"]);
                $apellido = trim($_POST["apellido"]);
                $dni = trim($_POST["dni"]);
                $f_nac = $_POST["f_nac"];
                $telefono = $_POST["telefono"];
                $tel_emergencia = $_POST["tel_emergencia"];
                $nombre_contacto = $_POST['nombre_contacto'];
                $parentesco = $_POST['parentesco'];
                $domicilio = $_POST["domicilio"];
                $provincia = $_POST["provincia"];
                $rol = $_POST["rol"];
                $resetPass = isset($_POST["resetPass"]) ? 0 : 1; //Cero es para NO restaurar
                $activo = isset($_POST["activo"]) ? 0 : 1;

                // Actualizar imágenes si se subieron nuevas
                $nombreArchivo = preg_replace('/\s+/', '', $nombre . $apellido);

                $imgPerfil = !empty($_FILES["imgPerfil"]["name"]) ? self::guardarImagen($_FILES["imgPerfil"], "img/perfil/", $nombreArchivo . "Perfil") : $_POST["imgPerfilActual"];
                $imgRepriv = !empty($_FILES["imgRepriv"]["name"]) ? self::guardarImagen($_FILES["imgRepriv"], "img/repriv/", $nombreArchivo . "Repriv") : $_POST["imgReprivActual"];

                $datos = array(
                    "id_usuario" => $id_usuario,
                    "nombre" => $nombre,
                    "apellido" => $apellido,
                    "dni" => $dni,
                    "f_nac" => $f_nac,
                    "telefono" => $telefono,
                    "tel_emergencia" => $tel_emergencia,
                    "nombre_contacto" => $nombre_contacto,
                    "parentesco" => $parentesco,
                    "domicilio" => $domicilio,
                    "provincia" => $provincia,
                    "rol" => $rol,
                    "imgPerfil" => $imgPerfil,
                    "imgRepriv" => $imgRepriv,
                    "resetPass" => $resetPass,
                    "activo" => $activo
                );

                $respuesta = ModeloUsuarios::mdlModificarUsuario($tabla, $datos);

                if ($respuesta == "ok") {
                    $conexion->commit();
                    $_SESSION['success_message'] = "Usuario modificado correctamente.";
                } else {
                    throw new Exception("Error al modificar en la base de datos.");
                }
            } catch (Exception $e) {
                $conexion->rollBack();
                $_SESSION['error_message'] = $e->getMessage();
                return false;
            }
        }
    }
    /* FUNCIÓN PARA GUARDAR IMÁGENES */
    static public function guardarImagen($archivo, $directorio, $nombreArchivo)
    {
        if ($archivo["error"] == UPLOAD_ERR_OK) {
            $ext = pathinfo($archivo["name"], PATHINFO_EXTENSION);
            $ext = strtolower($ext);

            // Validar formato de imagen
            $formatosPermitidos = array("jpg", "jpeg", "png", "webp", "avif");
            if (!in_array($ext, $formatosPermitidos)) {
                throw new Exception("Formato de imagen no permitido.");
            }

            // Ruta completa
            $ruta = $directorio . $nombreArchivo . "." . $ext;

            // Mover archivo al directorio
            if (!move_uploaded_file($archivo["tmp_name"], $ruta)) {
                throw new Exception("Error al subir la imagen.");
            }

            return $ruta; // Devuelve la ruta para guardarla en la base de datos
        }

        return null;
    }
}
