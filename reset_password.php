<?php
// reset_password.php
require_once 'includes/config.php';
require_once 'includes/Database.php';

try {
    $db = (new Database())->getConnection();
    $password = 'admin123';
    $email = 'informatica@tjaech.gob.mx';
    $hash = password_hash($password, PASSWORD_BCRYPT);
    
    // 1. Borrar todos los usuarios para empezar limpio
    $db->exec("DELETE FROM usuarios");
    
    // 2. Insertar el nuevo usuario administrador
    $query = "INSERT INTO usuarios (nombre, email, password, rol, estatus) VALUES ('Administrador Informática', :email, :hash, 'admin', 'activo')";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':hash', $hash);
    
    if($stmt->execute()) {
        echo "Éxito: Se ha creado el usuario '$email' con la contraseña '$password'\n";
    } else {
        echo "Error al crear el usuario en la BD.\n";
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
