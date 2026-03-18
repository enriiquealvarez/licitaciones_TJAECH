<?php
// admin/licitaciones/delete.php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/Database.php';
require_once '../../includes/Licitacion.php';
require_once '../../includes/Bitacora.php';
require_once '../../includes/helpers.php';
require_once '../../includes/Auth.php';

Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    if ($id) {
        $db = (new Database())->getConnection();
        $licitacionObj = new Licitacion($db);
        $bitacora = new Bitacora($db);
        
        $licitacion = $licitacionObj->leerUnico($id);
        
        if ($licitacion) {
            // Eliminar archivo físico
            if (!empty($licitacion['pdf_principal']) && file_exists(UPLOAD_DIR . $licitacion['pdf_principal'])) {
                unlink(UPLOAD_DIR . $licitacion['pdf_principal']);
            }
            
            if ($licitacionObj->eliminar($id)) {
                $bitacora->registrar($_SESSION['user_id'], 'eliminar', 'licitaciones', $id, "Eliminó licitación " . $licitacion['numero_licitacion']);
                set_flash_message('success', 'Licitación eliminada correctamente.');
            } else {
                set_flash_message('error', 'Ocurrió un error al eliminar el registro.');
            }
        }
    }
}

redirect('index.php');
