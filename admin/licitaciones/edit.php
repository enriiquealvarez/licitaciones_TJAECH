<?php
// admin/licitaciones/edit.php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/Database.php';
require_once '../../includes/Licitacion.php';
require_once '../../includes/Bitacora.php';
require_once '../../includes/helpers.php';

require_once '../layout/header.php';

$db = (new Database())->getConnection();
$licitacionObj = new Licitacion($db);
$bitacora = new Bitacora($db);

$error = '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id) {
    redirect('index.php');
}

$licitacion = $licitacionObj->leerUnico($id);
if (!$licitacion) {
    set_flash_message('error', 'Licitación no encontrada.');
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero_licitacion = clean($_POST['numero_licitacion']);
    $titulo = clean($_POST['titulo']);
    $descripcion = clean($_POST['descripcion']);
    $tipo_procedimiento = clean($_POST['tipo_procedimiento']);
    $anio = intval($_POST['anio']);
    $fecha_publicacion = clean($_POST['fecha_publicacion']);
    $fecha_limite = clean($_POST['fecha_limite']);
    $area_responsable = clean($_POST['area_responsable']);
    $estatus = clean($_POST['estatus']);

    if (empty($numero_licitacion) || empty($titulo) || empty($tipo_procedimiento) || empty($anio) || empty($fecha_publicacion) || empty($fecha_limite) || empty($area_responsable)) {
        $error = "Todos los campos obligatorios deben estar llenos.";
    } elseif ($numero_licitacion !== $licitacion['numero_licitacion'] && $licitacionObj->existeNumero($numero_licitacion, $id)) {
        $error = "El número de licitación ya existe en el sistema en otro registro.";
    }

    $archivos_pdf = [
        'pdf_bases' => 'Bases de la Licitación',
        'pdf_presentacion' => 'Acta de Presentación de Propuestas',
        'pdf_fallo' => 'Acta de Fallo'
    ];
    
    $pdfs_finales = [
        'pdf_bases' => $licitacion['pdf_bases'],
        'pdf_presentacion' => $licitacion['pdf_presentacion'],
        'pdf_fallo' => $licitacion['pdf_fallo']
    ];
    
    // Procesar nuevos archivos si se suben
    if (empty($error)) {
        foreach ($archivos_pdf as $campo => $nombre_legible) {
            if (isset($_FILES[$campo]) && $_FILES[$campo]['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES[$campo]['tmp_name'];
                $file_name = $_FILES[$campo]['name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file_tmp);
                finfo_close($finfo);

                if ($file_ext !== 'pdf' || $mime_type !== 'application/pdf') {
                    $error = "El archivo para $nombre_legible debe ser un documento PDF válido.";
                    break;
                } else {
                    $new_filename = uniqid('lic_' . $campo . '_', true) . '.pdf';
                    if (move_uploaded_file($file_tmp, UPLOAD_DIR . $new_filename)) {
                        // Borrar archivo anterior si existe
                        if (!empty($licitacion[$campo]) && file_exists(UPLOAD_DIR . $licitacion[$campo])) {
                            unlink(UPLOAD_DIR . $licitacion[$campo]);
                        }
                        $pdfs_finales[$campo] = $new_filename;
                    } else {
                        $error = "Error al subir el nuevo archivo $nombre_legible.";
                        break;
                    }
                }
            }
        }
    }

    if (empty($error) && $estatus === 'publicado') {
        if (empty($pdfs_finales['pdf_bases']) || empty($pdfs_finales['pdf_presentacion']) || empty($pdfs_finales['pdf_fallo'])) {
            $error = "No puede publicar una licitación sin sus 3 documentos PDF obligatorios.";
        }
    }

    if (empty($error)) {
        $datos = [
            'numero_licitacion' => $numero_licitacion,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'tipo_procedimiento' => $tipo_procedimiento,
            'anio' => $anio,
            'fecha_publicacion' => $fecha_publicacion,
            'fecha_limite' => $fecha_limite,
            'area_responsable' => $area_responsable,
            'pdf_bases' => $pdfs_finales['pdf_bases'],
            'pdf_presentacion' => $pdfs_finales['pdf_presentacion'],
            'pdf_fallo' => $pdfs_finales['pdf_fallo'],
            'estatus' => $estatus,
            'actualizado_por' => $_SESSION['user_id']
        ];
        
        // Regenerar slug si cambia el titulo
        if ($titulo !== $licitacion['titulo']) {
            $slug_base = create_slug($titulo);
            $slug = $slug_base;
            $counter = 1;
            $existente = $licitacionObj->leerPorSlug($slug);
            while ($existente && $existente['id'] != $id) {
                $slug = $slug_base . '-' . $counter;
                $counter++;
                $existente = $licitacionObj->leerPorSlug($slug);
            }
            $datos['slug'] = $slug;
        }

        if ($licitacionObj->actualizar($id, $datos)) {
            $bitacora->registrar($_SESSION['user_id'], 'editar', 'licitaciones', $id, "Editó licitación $numero_licitacion");
            set_flash_message('success', 'Licitación actualizada exitosamente.');
            redirect('index.php');
        } else {
            $error = "Error al actualizar en la base de datos.";
        }
    }
} else {
    // Rellenar $_POST inicial
    $_POST = $licitacion;
}
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Editar Licitación</h1>
        <p class="text-gray-600">Modifica la información o sube un PDF actualizado.</p>
    </div>
    <a href="index.php" class="text-gray-500 hover:text-gray-700">← Cancelar y volver</a>
</div>

<?php if ($error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<form action="edit.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 max-w-4xl">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Mismos campos de create.php ajustados -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Número de Licitación *</label>
            <input type="text" name="numero_licitacion" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border" value="<?php echo htmlspecialchars($_POST['numero_licitacion'] ?? ''); ?>">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ejercicio (Año) *</label>
            <input type="number" name="anio" required min="2000" max="2100" class="w-full rounded-md border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border" value="<?php echo htmlspecialchars($_POST['anio'] ?? ''); ?>">
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Título / Nombre de la Licitación *</label>
            <input type="text" name="titulo" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border" value="<?php echo htmlspecialchars($_POST['titulo'] ?? ''); ?>">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Procedimiento *</label>
            <select name="tipo_procedimiento" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border">
                <option value="Licitación Pública Estatal">Licitación Pública Estatal</option>
                <option value="Licitación Pública Nacional">Licitación Pública Nacional</option>
                <option value="Invitación Restringida a Tres Proveedores">Invitación Restringida a Tres Proveedores</option>
                <option value="Adjudicación Directa">Adjudicación Directa</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Área Solicitante/Responsable *</label>
            <input type="text" name="area_responsable" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border" value="<?php echo htmlspecialchars($_POST['area_responsable'] ?? ''); ?>">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Publicación *</label>
            <input type="date" name="fecha_publicacion" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border" value="<?php echo htmlspecialchars($_POST['fecha_publicacion'] ?? ''); ?>">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Límite *</label>
            <input type="date" name="fecha_limite" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border" value="<?php echo htmlspecialchars($_POST['fecha_limite'] ?? ''); ?>">
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción Corta / Notas</label>
            <textarea name="descripcion" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border"><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
        </div>

        <!-- Archivos PDF Reemplazar -->
        <div class="md:col-span-2 space-y-4">
            <h3 class="text-sm font-medium text-gray-700 border-b pb-2">Documentos Adjuntos</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Bases -->
                <div class="bg-gray-50 p-4 rounded border">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bases de la Licitación</label>
                    <?php if (!empty($licitacion['pdf_bases'])): ?>
                        <div class="mb-3 p-2 bg-blue-50 border border-tjaech text-xs text-tjaech rounded flex justify-between items-center">
                            <span class="truncate w-32">📄 <?php echo htmlspecialchars($licitacion['pdf_bases']); ?></span>
                            <a href="<?php echo APP_URL; ?>/uploads/pdfs/<?php echo $licitacion['pdf_bases']; ?>" target="_blank" class="hover:underline font-semibold text-blue-700">Ver</a>
                        </div>
                    <?php else: ?>
                        <div class="mb-3 text-sm text-red-500">Ningún documento subido.</div>
                    <?php endif; ?>
                    <input type="file" name="pdf_bases" accept=".pdf" class="block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-tjaech hover:file:bg-blue-100 p-1 border rounded border-gray-300">
                </div>

                <!-- Presentación -->
                <div class="bg-gray-50 p-4 rounded border">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Acta de Presentación</label>
                    <?php if (!empty($licitacion['pdf_presentacion'])): ?>
                        <div class="mb-3 p-2 bg-blue-50 border border-tjaech text-xs text-tjaech rounded flex justify-between items-center">
                            <span class="truncate w-32">📄 <?php echo htmlspecialchars($licitacion['pdf_presentacion']); ?></span>
                            <a href="<?php echo APP_URL; ?>/uploads/pdfs/<?php echo $licitacion['pdf_presentacion']; ?>" target="_blank" class="hover:underline font-semibold text-blue-700">Ver</a>
                        </div>
                    <?php else: ?>
                        <div class="mb-3 text-sm text-red-500">Ningún documento subido.</div>
                    <?php endif; ?>
                    <input type="file" name="pdf_presentacion" accept=".pdf" class="block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-tjaech hover:file:bg-blue-100 p-1 border rounded border-gray-300">
                </div>

                <!-- Fallo -->
                <div class="bg-gray-50 p-4 rounded border">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Acta de Fallo</label>
                    <?php if (!empty($licitacion['pdf_fallo'])): ?>
                        <div class="mb-3 p-2 bg-blue-50 border border-tjaech text-xs text-tjaech rounded flex justify-between items-center">
                            <span class="truncate w-32">📄 <?php echo htmlspecialchars($licitacion['pdf_fallo']); ?></span>
                            <a href="<?php echo APP_URL; ?>/uploads/pdfs/<?php echo $licitacion['pdf_fallo']; ?>" target="_blank" class="hover:underline font-semibold text-blue-700">Ver</a>
                        </div>
                    <?php else: ?>
                        <div class="mb-3 text-sm text-red-500">Ningún documento subido.</div>
                    <?php endif; ?>
                    <input type="file" name="pdf_fallo" accept=".pdf" class="block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-tjaech hover:file:bg-blue-100 p-1 border rounded border-gray-300">
                </div>
            </div>
        </div>

        <div class="md:col-span-2 bg-gray-50 p-4 rounded-md border border-gray-200 flex items-center justify-between">
            <div>
                <h4 class="text-lg font-medium text-gray-900">Estatus de la Licitación</h4>
            </div>
            <div class="flex items-center space-x-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="estatus" value="borrador" class="form-radio text-yellow-600 h-5 w-5" <?php echo ($_POST['estatus'] == 'borrador') ? 'checked' : ''; ?>>
                    <span class="ml-2 mt-1 text-gray-700 font-medium">Borrador</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="estatus" value="publicado" class="form-radio text-green-600 h-5 w-5" <?php echo ($_POST['estatus'] == 'publicado') ? 'checked' : ''; ?>>
                    <span class="ml-2 mt-1 text-green-700 font-medium">Publicado</span>
                </label>
            </div>
        </div>

    </div>

    <div class="mt-6 pt-5 border-t border-gray-200 flex justify-end">
        <button type="submit" class="bg-tjaech text-white py-2 px-6 rounded shadow hover:bg-blue-800 transition text-lg font-medium">
            Guardar Cambios
        </button>
    </div>
</form>

<script>
    <?php if (isset($_POST['tipo_procedimiento'])): ?>
        document.querySelector('select[name="tipo_procedimiento"]').value = "<?php echo addslashes($_POST['tipo_procedimiento']); ?>";
    <?php endif; ?>
</script>

<?php require_once '../layout/footer.php'; ?>
