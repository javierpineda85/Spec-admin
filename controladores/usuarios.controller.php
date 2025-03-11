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
                    "domicilio" => $domicilio,
                    "provincia" => $provincia,
                    "rol" => $rol,
                    "imgPerfil" => $imgPerfil,
                    "imgRepriv" => $imgRepriv,
                    "resetPass" => 1,
                    "activo" =>1
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

    /*MODIFICAR USUARIO */
    static public function crtModificarUsuario(){

        if (isset($_POST["nombreUsuario"])) {
            
            $tabla = "usuarios";
            $datos = array(
                "idUsuario"        => $_POST["idUsuario"],
                "nombreUsuario"    => $_POST["nombreUsuario"],
                "apellidoUsuario"  => $_POST["apellidoUsuario"],
                "emailUsuario"     => $_POST["emailUsuario"],
                "rol"              => $_POST["rol"]
            );
    
            // Validar si el campo de contraseña está presente y no está vacío
            if (isset($_POST["passUsuario"]) && !empty($_POST["passUsuario"])) {
                // Si la contraseña está presente y no está vacía, agregamos el campo a los datos
                $datos["passUsuario"] = $_POST["passUsuario"];
            }
    
            $respuesta = ModeloUsuarios::mdlModificarUsuario($tabla, $datos);
            $_SESSION['success_message'] = 'Usuario modificado exitosamente';
            return $respuesta;
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
