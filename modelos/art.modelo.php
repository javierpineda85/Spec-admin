<?php
class ModeloArt
{
    public static function mdlGuardarArt($tabla, $datos)
    {
        $db = Conexion::conectar();

        $sql = "INSERT INTO $tabla 
                    (razon_social, cuit_empresa, telefono_empresa, empresa_aseguradora, cuit_aseguradora, nro_poliza, telefono_aseguradora)
                    VALUES 
                    (:razon_social, :cuit_empresa, :telefono_empresa, :empresa_aseguradora, :cuit_aseguradora, :nro_poliza, :telefono_aseguradora)";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(":razon_social", $datos['razon_social']);
        $stmt->bindParam(":cuit_empresa", $datos['cuit_empresa']);
        $stmt->bindParam(":telefono_empresa", $datos['telefono_empresa']);
        $stmt->bindParam(":empresa_aseguradora", $datos['empresa_aseguradora']);
        $stmt->bindParam(":cuit_aseguradora", $datos['cuit_aseguradora']);
        $stmt->bindParam(":nro_poliza", $datos['nro_poliza']);
        $stmt->bindParam(":telefono_aseguradora", $datos['telefono_aseguradora']);
        if ($stmt->execute()) {
            return 'ok';
        } else {
            return 'error';
        }
    }

    public static function mdlObtenerArtPorUsuario($tabla, $usuarioId)
    {
        $sql = "SELECT * FROM $tabla WHERE usuario_id = :uid LIMIT 1";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':uid', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function mdlObtenerArtPorId($tabla, $idArt)
    {
        $sql = "SELECT * FROM $tabla WHERE idArt = :id";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':id', $idArt, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function mdlObtenerUltimaArt()
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT * FROM art ORDER BY fecha_alta DESC LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function mdlEditarArt($tabla, $datos)
    {
        try {
            $db = Conexion::conectar();

            $sql = "UPDATE $tabla SET 
                    razon_social = :razon_social,
                    cuit_empresa = :cuit_empresa,
                    telefono_empresa = :telefono_empresa,
                    empresa_aseguradora = :empresa_aseguradora,
                    cuit_aseguradora = :cuit_aseguradora,
                    nro_poliza = :nro_poliza,
                    telefono_aseguradora = :telefono_aseguradora
                WHERE idArt = :idArt";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(":razon_social", $datos['razon_social']);
            $stmt->bindParam(":cuit_empresa", $datos['cuit_empresa']);
            $stmt->bindParam(":telefono_empresa", $datos['telefono_empresa']);
            $stmt->bindParam(":empresa_aseguradora", $datos['empresa_aseguradora']);
            $stmt->bindParam(":cuit_aseguradora", $datos['cuit_aseguradora']);
            $stmt->bindParam(":nro_poliza", $datos['nro_poliza']);
            $stmt->bindParam(":telefono_aseguradora", $datos['telefono_aseguradora']);
            $stmt->bindParam(":idArt", $datos['idArt'], PDO::PARAM_INT);

            return $stmt->execute() ? 'ok' : 'error';
        } catch (PDOException $e) {
            error_log("Error al editar ART: " . $e->getMessage());
            return 'error';
        }
    }
}
