<?php
require_once 'includes/config.php';
require_once 'includes/Database.php';

try {
    $db = (new Database())->getConnection();
    // Test the strictness of assigning "" to the NOT NULL columns
    $query = "INSERT INTO licitaciones 
              (numero_licitacion, titulo, tipo_procedimiento, anio, fecha_publicacion, fecha_limite, area_responsable, pdf_bases, pdf_presentacion, pdf_fallo, slug, estatus)
              VALUES 
              ('TEST-123', 'Test Titulo', 'Adjudicación Directa', 2026, '2026-03-18', '2026-04-18', 'Test Area', 'test_bases.pdf', '', '', 'test-123', 'publicado')";
    $db->exec($query);
    echo "Insert successful.\n";
    // Also test an update
    $id = $db->lastInsertId();
    $update = "UPDATE licitaciones SET pdf_bases='test2.pdf', pdf_presentacion='', pdf_fallo='' WHERE id=$id";
    $db->exec($update);
    echo "Update successful.\n";
    // Clean up
    $db->exec("DELETE FROM licitaciones WHERE id=$id");
    echo "Delete successful.\n";
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
