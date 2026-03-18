<?php
require_once 'includes/config.php';
require_once 'includes/Database.php';

try {
    $db = (new Database())->getConnection();
    
    // Add new columns
    $query = "ALTER TABLE licitaciones 
              ADD COLUMN pdf_bases VARCHAR(255) NOT NULL AFTER area_responsable,
              ADD COLUMN pdf_presentacion VARCHAR(255) NOT NULL AFTER pdf_bases,
              ADD COLUMN pdf_fallo VARCHAR(255) NOT NULL AFTER pdf_presentacion";
    $db->exec($query);
    echo "Added new columns correctly.\n";
    
    // Optional: Drop the old column if we don't need it at all anymore
    // By requirement we're replacing it with the 3 new ones.
    $queryDrop = "ALTER TABLE licitaciones DROP COLUMN pdf_principal";
    $db->exec($queryDrop);
    echo "Dropped pdf_principal correctly.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
