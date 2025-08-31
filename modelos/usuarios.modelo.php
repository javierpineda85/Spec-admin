<?php

class ModeloUsuarios
{
    // Método para verificar las credenciales de inicio de sesión
    public function authenticate($dni, $password)
    {
        $usuario = $this->getUsuarioPorDni($dni);

        if ($usuario && password_verify($password, $usuario[0]['pass'])) {
            return $usuario; // Devuelve todos los datos del usuario + rol
        }

        return false;
    }

    // Método para obtener datos de usuario por DNI (con JOIN a roles)
    private function getUsuarioPorDni($dni)
    {
        $db = new Conexion;
        $sql = "SELECT 
                    u.idUsuario, 
                    u.nombre, 
                    u.apellido, 
                    u.pass, 
                    u.imgPerfil, 
                    u.rol_id, 
                    r.nombre AS nombreRol, 
                    r.nivel, 
                    r.categoria, 
                    r.reservado
                FROM usuarios u
                JOIN roles r ON u.rol_id = r.id
                WHERE u.dni = :dni
                LIMIT 1";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':dni', $dni, PDO::PARAM_STR);
        $stmt->execute();
        $usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $usuario ?: null;
    }

    // Método para saber en cuál objetivo y ronda trabaja
    public function getAsignacionHoy(int $usuarioId)
    {
        $db = new Conexion;
        $sql = "SELECT 
                    t.idTurno AS turno_id,
                    t.objetivo_id, 
                    m.puesto_id
                FROM turnos t
                LEFT JOIN marcaciones_servicio m 
                    ON m.vigilador_id = t.usuario_id
                    AND DATE(m.fecha_hora) = t.fecha
                WHERE t.usuario_id = :uid
                  AND DATE(t.fecha) = CURDATE()
                ORDER BY t.idTurno ASC
                LIMIT 1";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':uid', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /* INSERTAR USUARIO */
    static public function mdlGuardarUsuario($tabla, $datos)
    {
        $registro = Conexion::conectar()->prepare("
            INSERT INTO $tabla 
                (nombre, apellido, dni, pass, f_nac, telefono, tel_emergencia, nombre_contacto, parentesco, domicilio, provincia, rol_id, imgPerfil, imgRepriv, resetPass, activo) 
            VALUES 
                (:nombre, :apellido, :dni, :pass, :f_nac, :telefono, :tel_emergencia, :nombre_contacto, :parentesco, :domicilio, :provincia, :rol_id, :imgPerfil, :imgRepriv, :resetPass, :activo)
        ");

        $registro->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $registro->bindParam(":apellido", $datos["apellido"], PDO::PARAM_STR);
        $registro->bindParam(":dni", $datos["dni"], PDO::PARAM_STR);
        $registro->bindParam(":pass", $datos["pass"], PDO::PARAM_STR);
        $registro->bindParam(":f_nac", $datos["f_nac"], PDO::PARAM_STR);
        $registro->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
        $registro->bindParam(":tel_emergencia", $datos["tel_emergencia"], PDO::PARAM_STR);
        $registro->bindParam(":nombre_contacto", $datos["nombre_contacto"], PDO::PARAM_STR);
        $registro->bindParam(":parentesco", $datos["parentesco"], PDO::PARAM_STR);
        $registro->bindParam(":domicilio", $datos["domicilio"], PDO::PARAM_STR);
        $registro->bindParam(":provincia", $datos["provincia"], PDO::PARAM_STR);
        $registro->bindParam(":rol_id", $datos["rol_id"], PDO::PARAM_INT);
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
        $stmt = Conexion::conectar()->prepare("
            UPDATE $tabla 
            SET nombre = :nombre, 
                apellido = :apellido, 
                f_nac = :f_nac, 
                telefono = :telefono, 
                tel_emergencia = :tel_emergencia, 
                nombre_contacto = :nombre_contacto, 
                parentesco = :parentesco, 
                domicilio = :domicilio, 
                provincia = :provincia, 
                rol_id = :rol_id, 
                imgPerfil = :imgPerfil, 
                imgRepriv = :imgRepriv, 
                resetPass = :resetPass, 
                activo = :activo 
            WHERE idUsuario = :id_usuario
        ");

        $stmt->bindParam(":id_usuario", $datos["id_usuario"], PDO::PARAM_INT);
        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":apellido", $datos["apellido"], PDO::PARAM_STR);
        $stmt->bindParam(":f_nac", $datos["f_nac"], PDO::PARAM_STR);
        $stmt->bindParam(":telefono", $datos["telefono"], PDO::PARAM_STR);
        $stmt->bindParam(":tel_emergencia", $datos["tel_emergencia"], PDO::PARAM_STR);
        $stmt->bindParam(":nombre_contacto", $datos["nombre_contacto"], PDO::PARAM_STR);
        $stmt->bindParam(":parentesco", $datos["parentesco"], PDO::PARAM_STR);
        $stmt->bindParam(":domicilio", $datos["domicilio"], PDO::PARAM_STR);
        $stmt->bindParam(":provincia", $datos["provincia"], PDO::PARAM_STR);
        $stmt->bindParam(":rol_id", $datos["rol_id"], PDO::PARAM_INT);
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

    /* Reactivar usuario */
    static public function mdlReactivarUsuario($tabla, $idUsuario)
    {
        $sql = "UPDATE $tabla SET activo = 1 WHERE idUsuario = :id";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':id', $idUsuario, PDO::PARAM_INT);
        return $stmt->execute() ? 'ok' : 'error';
    }
}
