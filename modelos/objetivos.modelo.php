<?php

class ModeloObjetivos
{
    /*INSERTAR OBJETIVO */
    static public function mdlGuardarObjetivo($tabla, $d)
    {
        $conexion = Conexion::conectar(); // cambia la forma para poder obtener el ultimo id
        $sql = "INSERT INTO $tabla (nombre,latitud,longitud,radio_m,localidad,tipo, activo) VALUES (:nombre, :latitud, :longitud, :radio_m, :localidad, :tipo, :activo)";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':nombre', $d['nombre'], PDO::PARAM_STR);
        $stmt->bindParam(':latitud', $d['latitud']);
        $stmt->bindParam(':longitud', $d['longitud']);
        $stmt->bindParam(':radio_m', $d['radio_m'], PDO::PARAM_INT);
        $stmt->bindParam(':localidad', $d['localidad'], PDO::PARAM_STR);
        $stmt->bindParam(':tipo', $d['tipo'], PDO::PARAM_STR);
        $stmt->bindValue(':activo', 1, PDO::PARAM_INT);


        if ($stmt->execute()) {
            return $conexion->lastInsertId(); // ← retorna el ID real insertado
        } else {
            return false;
        }
    }
    /* GUARDAR VIGILADORES EN OBJETIVO */
    static public function mdlGuardarVigiladoresObjetivo($objetivo_id, $vigiladores)
    {
        $db = Conexion::conectar();
        $sql = "INSERT INTO objetivo_vigiladores (objetivo_id, vigilador_id) VALUES (:oid, :vid)";
        $stmt = $db->prepare($sql);

        foreach ($vigiladores as $vid) {
            $stmt->bindParam(':oid', $objetivo_id, PDO::PARAM_INT);
            $stmt->bindParam(':vid', $vid, PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    /* GUARDAR REFERENTES EN OBJETIVO */
    static public function mdlGuardarReferentesObjetivo($objetivo_id, $referentes)
    {
        $db = Conexion::conectar();
        $sql = "INSERT INTO objetivo_referentes (objetivo_id, referente_id) VALUES (:oid, :rid)";
        $stmt = $db->prepare($sql);

        foreach ($referentes as $rid) {
            $stmt->bindParam(':oid', $objetivo_id, PDO::PARAM_INT);
            $stmt->bindParam(':rid', $rid, PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    /*MODIFICAR OBJETIVO */
    static public function mdlModificarObjetivo($tabla, $d)
    {
        $sql = "UPDATE $tabla SET nombre=:nombre,localidad=:localidad,tipo=:tipo, latitud=:latitud,longitud=:longitud,radio_m=:radio_m WHERE idObjetivo=:idObjetivo";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':idObjetivo', $d['idObjetivo'], PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $d['nombre'], PDO::PARAM_STR);
        $stmt->bindParam(':localidad', $d['localidad'], PDO::PARAM_STR);
        $stmt->bindParam(':tipo', $d['tipo'], PDO::PARAM_STR);
        $stmt->bindParam(':latitud', $d['latitud']);
        $stmt->bindParam(':longitud', $d['longitud']);
        $stmt->bindParam(':radio_m', $d['radio_m'], PDO::PARAM_INT);
        return $stmt->execute() ? 'ok' : 'error';
    }
    /* ELIMINAR VIGILADORES */
    static public function mdlEliminarVigiladoresObjetivo($idObjetivo)
    {
        $stmt = Conexion::conectar()->prepare("DELETE FROM objetivo_vigiladores WHERE objetivo_id = ?");
        return $stmt->execute([$idObjetivo]);
    }

    /* ELIMINAR REFERENTES */
    static public function mdlEliminarReferentesObjetivo($idObjetivo)
    {
        $stmt = Conexion::conectar()->prepare("DELETE FROM objetivo_referentes WHERE objetivo_id = ?");
        return $stmt->execute([$idObjetivo]);
    }
    // Obtener IDs de vigiladores por objetivo // vista de objetivos
    static public function mdlObtenerVigiladoresPorObjetivo($idObjetivo)
    {
        $stmt = Conexion::conectar()->prepare("SELECT vigilador_id FROM objetivo_vigiladores WHERE objetivo_id = ?");
        $stmt->execute([$idObjetivo]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    // Obtener IDs de referentes por objetivo // vista de objetivos
    static public function mdlObtenerReferentesPorObjetivo($idObjetivo)
    {
        $stmt = Conexion::conectar()->prepare("SELECT referente_id FROM objetivo_referentes WHERE objetivo_id = ?");
        $stmt->execute([$idObjetivo]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    //Obtener los vigiladores para las vistas de cronogramas
    static public function mdlObtenerVigiladoresParaCronograma($idObjetivo)
    {
        $stmt = Conexion::conectar()->prepare("SELECT ov.vigilador_id AS idUsuario
        FROM objetivo_vigiladores ov
        WHERE ov.objetivo_id = ?");
        $stmt->execute([$idObjetivo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //Obtner los referentes para Cronogramas
    static public function mdlObtenerReferentesParaCronograma($idObjetivo)
    {
        $stmt = Conexion::conectar()->prepare(" SELECT orf.referente_id AS idUsuario
        FROM objetivo_referentes orf
        WHERE orf.objetivo_id = ?");
        $stmt->execute([$idObjetivo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /** DESACTIVAR (soft-delete) UN OBJETIVO **/
    static public function mdlDesactivarObjetivo($tabla, $idObjetivo)
    {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET activo = 0 WHERE idObjetivo = :id");
        $stmt->bindParam(':id', $idObjetivo, PDO::PARAM_INT);
        return $stmt->execute() ? 'ok' : 'error';
    }

    /** RESACTIVAR (soft-delete) UN OBJETIVO **/
    static public function mdlReactivarObjetivo($tabla, $idObjetivo)
    {
        $stmt = Conexion::conectar()->prepare("UPDATE $tabla SET activo = 1 WHERE idObjetivo = :id");
        $stmt->bindParam(':id', $idObjetivo, PDO::PARAM_INT);
        return $stmt->execute() ? 'ok' : 'error';
    }
}
