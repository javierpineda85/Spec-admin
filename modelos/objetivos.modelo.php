<?php

class ModeloObjetivos
{
    /*INSERTAR OBJETIVO */
    static public function mdlGuardarObjetivo($tabla, $d)
    {
        $sql = "INSERT INTO $tabla (nombre,latitud,longitud,radio_m,localidad,tipo, activo) VALUES (:nombre, :latitud, :longitud, :radio_m, :localidad, :tipo, :activo)";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':nombre', $d['nombre'], PDO::PARAM_STR);
        $stmt->bindParam(':latitud', $d['latitud']);
        $stmt->bindParam(':longitud', $d['longitud']);
        $stmt->bindParam(':radio_m', $d['radio_m'], PDO::PARAM_INT);
        $stmt->bindParam(':localidad', $d['localidad'], PDO::PARAM_STR);
        $stmt->bindParam(':tipo', $d['tipo'], PDO::PARAM_STR);
        $stmt->bindParam(':activo', 1, PDO::PARAM_INT);


        return $stmt->execute() ? 'ok' : 'error';
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
