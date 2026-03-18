<?php
require_once 'includes/config.php';
require_once 'includes/Database.php';

try {
    $db = (new Database())->getConnection();
    if (!$db) die("No db connection.\n");

    try {
        $db->exec("ALTER TABLE licitaciones ADD COLUMN fecha_acta_presentacion DATE NULL AFTER fecha_publicacion");
        echo "fecha_acta_presentacion added.\n";
    } catch (PDOException $e) { echo "fecha_acta_presentacion err: " . $e->getMessage() . "\n"; }

    try {
        $db->exec("ALTER TABLE licitaciones DROP COLUMN fecha_limite");
        echo "fecha_limite dropped.\n";
    } catch (PDOException $e) { echo "fecha_limite drop err: " . $e->getMessage() . "\n"; }

    echo "Migration 2 complete.\n";

} catch (PDOException $e) {
    echo "Connection Error: " . $e->getMessage() . "\n";
}
