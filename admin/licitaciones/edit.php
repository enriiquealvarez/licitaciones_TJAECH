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

    $pdf_principal = $licitacion['pdf_principal'];
    
    // Procesar nuevo archivo si se sube
    if (empty($error) && isset($_FILES['pdf_principal']) && $_FILES['pdf_principal']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['pdf_principal']['tmp_name'];
        $file_name = $_FILES['pdf_principal']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_tmp);
        finfo_close($finfo);

        if ($file_ext !== 'pdf' || $mime_type !== 'application/pdf') {
            $error = "El archivo principal debe ser un documento PDF válido.";
        } else {
            $new_filename = uniqid('lic_', true) . '.pdf';
            if (move_uploaded_file($file_tmp, UPLOAD_DIR . $new_filename)) {
                // Borrar archivo anterior si existe
                if (!empty($licitacion['pdf_principal']) && file_exists(UPLOAD_DIR . $licitacion['pdf_principal'])) {
                    unlink(UPLOAD_DIR . $licitacion['pdf_principal']);
                }
                $pdf_principal = $new_filename;
            } else {
                $error = "Error al subir el nuevo archivo PDF.";
            }
        }
    }

    if (empty($error) && empty($pdf_principal) && $estatus === 'publicado') {
        $error = "No puede publicar una licitación sin su PDF principal.";
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
            'pdf_principal' => $pdf_principal,
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

        <!-- PDF Principal Reemplazar -->
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">PDF Principal Actual</label>
            <?php if (!empty($licitacion['pdf_principal'])): ?>
                <div class="mb-3 p-3 bg-blue-50 border border-tjaech text-sm text-tjaech rounded flex justify-between items-center">
                    <span>📄 <?php echo htmlspecialchars($licitacion['pdf_principal']); ?></span>
                    <a href="<?php echo APP_URL; ?>/uploads/pdfs/<?php echo $licitacion['pdf_principal']; ?>" target="_blank" class="hover:underline font-semibold text-blue-700">Ver PDF</a>
                </div>
            <?php else: ?>
                <div class="mb-3 text-sm text-red-500">Ningún documento subido.</div>
            <?php endif; ?>

            <label class="block text-sm font-medium text-gray-700 mb-1 mt-4">Reemplazar PDF (Opcional)</label>
            <input type="file" name="pdf_principal" accept=".pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-tjaech hover:file:bg-blue-100 p-2 border rounded border-gray-300">
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
