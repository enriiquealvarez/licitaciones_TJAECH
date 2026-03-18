<?php
require_once 'includes/config.php';
require_once 'includes/Database.php';

try {
    $db = (new Database())->getConnection();

    if (!$db) {
        die("No db connection.\n");
    }

    try {
        $db->exec("ALTER TABLE licitaciones ADD COLUMN pdf_bases VARCHAR(255) NOT NULL AFTER area_responsable");
        echo "pdf_bases added.\n";
    } catch (PDOException $e) { echo "pdf_bases err: " . $e->getMessage() . "\n"; }

    try {
        $db->exec("ALTER TABLE licitaciones ADD COLUMN pdf_presentacion VARCHAR(255) NOT NULL AFTER pdf_bases");
        echo "pdf_presentacion added.\n";
    } catch (PDOException $e) { echo "pdf_presentacion err: " . $e->getMessage() . "\n"; }

    try {
        $db->exec("ALTER TABLE licitaciones ADD COLUMN pdf_fallo VARCHAR(255) NOT NULL AFTER pdf_presentacion");
        echo "pdf_fallo added.\n";
    } catch (PDOException $e) { echo "pdf_fallo err: " . $e->getMessage() . "\n"; }

    try {
        $db->exec("ALTER TABLE licitaciones DROP COLUMN pdf_principal");
        echo "pdf_principal dropped.\n";
    } catch (PDOException $e) { echo "pdf_principal drop err: " . $e->getMessage() . "\n"; }

    echo "Migration complete.\n";

} catch (PDOException $e) {
    echo "Connection Error: " . $e->getMessage() . "\n";
}
