
<?php

class ControladorArchivos
{
    /**
     * Guarda un archivo (imagen) en el servidor y devuelve la ruta.
     * Lanza excepción si el formato no es válido o si falla el movimiento.
     *
     * @param array  $archivo      ‒ El elemento $_FILES["campo"].
     * @param string $directorio   ‒ Carpeta donde guardar (debe existir o tener permisos para crear).
     * @param string $nombreBase   ‒ Nombre base para el archivo (sin extensión).
     * @return string              ‒ Ruta completa (directorio + nombre) donde se guardó.
     * @throws Exception           ‒ Si hay error de envío o extensión no permitida.
     */
    static public function guardarArchivo($archivo, $directorio, $nombreBase)
    {
       // Auth::check('archivos', 'guardarArchvios');

        // 1) Verificar que no venga vacío ni con error de “no file”
        if (!isset($archivo) || $archivo["error"] === UPLOAD_ERR_NO_FILE) {
            // No subieron nada: devolvemos null para que el controlador decida si deja la ruta previa
            return null;
        }

        // 2) Verificar que no tenga errores de carga
        if ($archivo["error"] !== UPLOAD_ERR_OK) {
            throw new Exception("Error al recibir el archivo (Código: " . $archivo["error"] . ").");
        }

        // 3) Validar extensión
        $ext = pathinfo($archivo["name"], PATHINFO_EXTENSION);
        $ext = strtolower($ext);

        $formatosPermitidos = ["jpg", "jpeg", "png", "webp", "avif"];
        if (!in_array($ext, $formatosPermitidos)) {
            throw new Exception("Formato no permitido: .$ext. Solo se aceptan imágenes (jpg, png, webp, avif).");
        }

        // 4) Generar un nombre único (para evitar que dos “JuanPérezPerfil.jpg” colisionen)
        //    Por ejemplo, agregamos un timestamp o uniqid.
        $nombreUnico = $nombreBase . "_" .  date("YmdHis") . "." . $ext;

        // 5) Preparar directorio (asegurar que termine con “/”)
        $directorio = rtrim($directorio, "/") . "/";

        // Si el directorio no existe, lo creamos (modo tradicional: mkdir con recursión)
        if (!is_dir($directorio)) {
            if (!mkdir($directorio, 0755, true)) {
                throw new Exception("No se pudo crear el directorio: " . $directorio);
            }
        }

        // 6) Ruta final donde guardamos el archivo
        $rutaFinal = $directorio . $nombreUnico;

        // 7) Mover el archivo desde la carpeta temporal al destino definitivo
        if (!move_uploaded_file($archivo["tmp_name"], $rutaFinal)) {
            throw new Exception("Error al mover el archivo al directorio destino.");
        }

        // 8) Devolver ruta para almacenar en BD (puedes guardar relativa o absoluta según convenga)
        return $rutaFinal;
    }
}
