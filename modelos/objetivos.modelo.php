<?php

class ModeloObjetivos
{
    /*INSERTAR OBJETIVO */
    static public function mdlGuardarObjetivo($tabla, $d)
    {
        $sql = "INSERT INTO $tabla (nombre,localidad,tipo,latitud,longitud,radio_m) VALUES (:nombre,:localidad,:tipo,:latitud,:longitud,:radio_m)";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(':nombre', $d['nombre'], PDO::PARAM_STR);
        $stmt->bindParam(':localidad', $d['localidad'], PDO::PARAM_STR);
        $stmt->bindParam(':tipo', $d['tipo'], PDO::PARAM_STR);
        $stmt->bindParam(':latitud', $d['latitud']);
        $stmt->bindParam(':longitud', $d['longitud']);
        $stmt->bindParam(':radio_m', $d['radio_m'], PDO::PARAM_INT);
        return $stmt->execute() ? 'ok' : 'error';
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
