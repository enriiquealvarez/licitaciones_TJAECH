<?php
require_once 'includes/config.php';
require_once 'includes/Database.php';

try {
    $db = (new Database())->getConnection();
    if (!$db) {
        die("Could not connect to database.");
    }
    $stmt = $db->query("SELECT id, nombre, email, password, rol, estatus FROM usuarios");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "USERS:\n";
    print_r($users);
    
    // Also run reset:
    $password = 'admin123';
    $email = 'informatica@tjaech.gob.mx';
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $db->exec("DELETE FROM usuarios");
    $query = "INSERT INTO usuarios (nombre, email, password, rol, estatus) VALUES ('Administrador Informática', :email, :hash, 'admin', 'activo')";
    $stmt2 = $db->prepare($query);
    $stmt2->bindParam(':email', $email);
    $stmt2->bindParam(':hash', $hash);
    $stmt2->execute();
    echo "\nRESET DONE.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
