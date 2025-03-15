<?php

class ModeloUsuarios
{
    // Método para verificar las credenciales de inicio de sesión
    public function authenticate($email, $password)
    {
        // Aquí deberías realizar la lógica para verificar las credenciales en tu base de datos
        // Este es solo un ejemplo básico
        $storedPassword = $this->getStoredPasswordByUsername($email);
        if ($storedPassword !== null && password_verify($password, $storedPassword)) {
            // Las credenciales son válidas
            return true;
        } else {
            // Las credenciales no son válidas
            return false;
        }
    }

    // Método para obtener el hash de contraseña almacenado en la base de datos
    private function getStoredPasswordByUsername($username)
    {
        // Aquí deberías implementar la lógica para obtener el hash de contraseña de tu base de datos
        // Este es solo un ejemplo básico
        $users = [
            'john' => '$2y$10$jWQxRc0kLlNhvX52nVpPve.hGzsOR5M10KgIrNzJwXvT4aQxir9jC' // Ejemplo de hash de contraseña
        ];
        return isset($users[$username]) ? $users[$username] : null;
    }

    /*INSERTAR USUARIO */
    static public function mdlGuardarUsuario($tabla, $datos)
    {

        $registro = Conexion::conectar()->prepare("INSERT INTO $tabla (nombre, apellido, dni, pass, f_nac, telefono, tel_emergencia, domicilio, provincia, rol, imgPerfil, imgRepriv, resetPass, activo) 
        VALUES (:nombre, :apellido, :dni, :pass, :f_nac, :telefono, :tel_emergencia, :domicilio, :provincia, :rol, :imgPerfil, :imgRepriv,:resetPass, :activo)");

        $registro->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $registro->bindParam(":apellido", $datos["apellido"], PDO::PARAM_STR);
        $registro->bindParam(":dni", $datos["dni"], PDO::PARAM_STR);
        $registro->bindParam(":pass", $datos["pass"], PDO::PARAM_STR);
        $registro->bindParam(":f_nac", $datos["f_nac"], PDO::PARAM_STR);
        $registro->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
        $registro->bindParam(":tel_emergencia", $datos["tel_emergencia"], PDO::PARAM_STR);
        $registro->bindParam(":domicilio", $datos["domicilio"], PDO::PARAM_STR);
        $registro->bindParam(":provincia", $datos["provincia"], PDO::PARAM_STR);
        $registro->bindParam(":rol", $datos["rol"], PDO::PARAM_STR);
        $registro->bindParam(":imgPerfil", $datos["imgPerfil"], PDO::PARAM_STR);
        $registro->bindParam(":imgRepriv", $datos["imgRepriv"], PDO::PARAM_STR);
        $registro->bindParam(":resetPass", $datos["resetPass"], PDO::PARAM_INT);      
        $registro->bindParam(":activo", $datos["activo"], PDO::PARAM_INT);

        if ($registro->execute()) {

            return "ok";
        } else {
            print_r(Conexion::conectar()->errorInfo());
        }

        $registro->closeCursor();
        $registro = null;
    }


   /* ACTUALIZAR USUARIO */
   static public function mdlModificarUsuario($tabla, $datos)
   {
       $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET nombre = :nombre, apellido = :apellido, dni = :dni, f_nac = :f_nac, telefono = :telefono, tel_emergencia = :tel_emergencia, domicilio = :domicilio, provincia = :provincia, rol = :rol, imgPerfil = :imgPerfil, imgRepriv = :imgRepriv, resetPass = :resetPass, activo = :activo WHERE id_usuario = :id_usuario");

       $stmt->bindParam(":id_usuario", $datos["id_usuario"], PDO::PARAM_INT);
       $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
       $stmt->bindParam(":apellido", $datos["apellido"], PDO::PARAM_STR);
       $stmt->bindParam(":dni", $datos["dni"], PDO::PARAM_STR);
       $stmt->bindParam(":f_nac", $datos["f_nac"], PDO::PARAM_STR);
       $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
       $stmt->bindParam(":tel_emergencia", $datos["tel_emergencia"], PDO::PARAM_STR);
       $stmt->bindParam(":domicilio", $datos["domicilio"], PDO::PARAM_STR);
       $stmt->bindParam(":provincia", $datos["provincia"], PDO::PARAM_STR);
       $stmt->bindParam(":rol", $datos["rol"], PDO::PARAM_STR);
       $stmt->bindParam(":imgPerfil", $datos["imgPerfil"], PDO::PARAM_STR);
       $stmt->bindParam(":imgRepriv", $datos["imgRepriv"], PDO::PARAM_STR);
       $stmt->bindParam(":resetPass", $datos["resetPass"], PDO::PARAM_INT);
       $stmt->bindParam(":activo", $datos["activo"], PDO::PARAM_INT);

       if ($stmt->execute()) {
           return "ok";
       } else {
           print_r(Conexion::conectar()->errorInfo());
       }

       $stmt->closeCursor();
       $stmt = null;
   }
}
