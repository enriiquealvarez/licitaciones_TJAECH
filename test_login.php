<?php
require_once 'includes/config.php';
require_once 'includes/Database.php';
require_once 'includes/Usuario.php';

try {
    $db = (new Database())->getConnection();
    if (!$db) {
        die("Could not connect to database.");
    }
    $email = 'informatica@tjaech.gob.mx';
    $password = 'admin123';
    
    echo "Testing login for $email with password $password\n";
    $query = "SELECT id, nombre, password, rol, estatus FROM usuarios WHERE email = ? LIMIT 0,1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "User found in database.\n";
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Estatus: " . $row['estatus'] . "\n";
        echo "Hash: " . $row['password'] . "\n";
        
        if ($row['estatus'] === 'activo') {
            echo "User is active.\n";
        } else {
            echo "User is NOT active.\n";
        }
        
        if (password_verify($password, $row['password'])) {
            echo "Password verify SUCCESS.\n";
        } else {
            echo "Password verify FAILED.\n";
        }
        
        // Test Usuario object:
        $usuario = new Usuario($db);
        if ($usuario->login($email, $password)) {
            echo "Usuario->login() SUCCESS. \n";
        } else {
            echo "Usuario->login() FAILED. \n";
        }
        
    } else {
        echo "User NOT found in database via email $email.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
