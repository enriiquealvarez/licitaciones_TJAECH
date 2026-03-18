<?php
// includes/Bitacora.php

class Bitacora {
    private $conn;
    private $table_name = "bitacora";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Registrar una acción
    public function registrar($usuario_id, $accion, $entidad, $entidad_id = null, $detalles = "") {
        $query = "INSERT INTO " . $this->table_name . "
                  SET usuario_id=:usuario_id, accion=:accion, entidad=:entidad, 
                      entidad_id=:entidad_id, detalles=:detalles";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':accion', $accion);
        $stmt->bindParam(':entidad', $entidad);
        $stmt->bindParam(':entidad_id', $entidad_id);
        $stmt->bindParam(':detalles', $detalles);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Obtener últimos registros
    public function obtenerUltimos($limit = 50) {
        $query = "SELECT b.*, u.nombre as usuario_nombre 
                  FROM " . $this->table_name . " b
                  LEFT JOIN usuarios u ON b.usuario_id = u.id
                  ORDER BY b.created_at DESC LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
}
