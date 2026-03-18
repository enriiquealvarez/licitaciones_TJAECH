<?php
// includes/Usuario.php

class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Autenticar usuario
    public function login($email, $password) {
        $query = "SELECT id, nombre, password, rol, estatus FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row['estatus'] === 'activo' && password_verify($password, $row['password'])) {
                // Iniciar sesión
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_nombre'] = $row['nombre'];
                $_SESSION['user_rol'] = $row['rol'];
                // Regenerar ID para prevenir fixation
                session_regenerate_id(true);
                return true;
            }
        }
        return false;
    }

    // Obtener todos los usuarios (para panel)
    public function leer() {
        $query = "SELECT id, nombre, email, rol, estatus, created_at FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    // Obtener un usuario por ID
    public function leerUnico($id) {
        $query = "SELECT id, nombre, email, rol, estatus FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear un nuevo usuario
    public function crear($nombre, $email, $password, $rol, $estatus) {
        $query = "INSERT INTO " . $this->table_name . " SET nombre=?, email=?, password=?, rol=?, estatus=?";
        $stmt = $this->conn->prepare($query);
        $hash = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt->bindParam(1, $nombre);
        $stmt->bindParam(2, $email);
        $stmt->bindParam(3, $hash);
        $stmt->bindParam(4, $rol);
        $stmt->bindParam(5, $estatus);

        return $stmt->execute();
    }
    
    // Cambiar estado o rol del usuario
    public function actualizarEstatusOCambiarRol($id, $rol, $estatus) {
        $query = "UPDATE " . $this->table_name . " SET rol=?, estatus=? WHERE id=?";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(1, $rol);
        $stmt->bindParam(2, $estatus);
        $stmt->bindParam(3, $id);

        return $stmt->execute();
    }
}
