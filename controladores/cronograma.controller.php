<?php

require_once('modelos/cronograma.modelo.php');

class ControladorCronograma
{

    static public function crtSubirCrono()
    {
        if (!empty($_FILES["imgCrono"]["name"])) {
            try {
                $conexion = Conexion::conectar();
    
                if (!$conexion->inTransaction()) {
                    $conexion->beginTransaction();
                }
    
                $tabla = "cronogramas";
                $objetivo_id = trim($_POST["id_objetivo"]);
                $fecha = date("Y-m-d H:i:s");
    
                // Normalizar nombre de archivos
                $nombreArchivo = preg_replace('/\s+/', '', $_FILES["imgCrono"]["name"]);
    
                // Guardar imagen
                $imgCrono = self::guardarImagen($_FILES["imgCrono"], "img/cronogramas/", $nombreArchivo . "Cronograma");
    
                $datos = array(
                    "objetivo_id" => $objetivo_id,
                    "fechaCarga" => $fecha,
                    "img_crono" => $imgCrono
                );
    
                $respuesta = ModeloCronograma::mdlSubirCrono($tabla, $datos);
    
                if ($respuesta == "ok") {
                    $conexion->commit();
                    $_SESSION['success_message'] = "Cronograma registrado correctamente.";
                } else {
                    throw new Exception("Error al guardar en la base de datos.");
                }
            } catch (Exception $e) {
                $conexion->rollBack();
                $_SESSION['success_message'] = $e->getMessage();
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
