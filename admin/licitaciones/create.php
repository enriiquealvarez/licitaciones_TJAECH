<?php
// admin/licitaciones/create.php
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
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero_licitacion = clean($_POST['numero_licitacion']);
    $titulo = clean($_POST['titulo']);
    $descripcion = clean($_POST['descripcion']);
    $tipo_procedimiento = clean($_POST['tipo_procedimiento']);
    $anio = intval($_POST['anio']);
    $fecha_publicacion = clean($_POST['fecha_publicacion']);
    $fecha_acta_presentacion = clean($_POST['fecha_acta_presentacion'] ?? '');
    $area_responsable = clean($_POST['area_responsable']);
    $estatus = clean($_POST['estatus']); // borrador o publicado

    // Validar requeridos
    if (empty($numero_licitacion) || empty($titulo) || empty($tipo_procedimiento) || empty($anio) || empty($fecha_publicacion) || empty($area_responsable)) {
        $error = "Todos los campos obligatorios deben estar llenos.";
    } elseif ($licitacionObj->existeNumero($numero_licitacion)) {
        $error = "El número de licitación ya existe en el sistema.";
    }

    $archivos_pdf = [
        'pdf_bases' => 'Bases de la Licitación',
        'pdf_presentacion' => 'Acta de Presentación de Propuestas',
        'pdf_fallo' => 'Acta de Fallo'
    ];
    $pdfs_subidos = [
        'pdf_bases' => '',
        'pdf_presentacion' => '',
        'pdf_fallo' => ''
    ];

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
                    if (!is_dir(UPLOAD_DIR)) {
                        mkdir(UPLOAD_DIR, 0755, true);
                    }
                    
                    if (move_uploaded_file($file_tmp, UPLOAD_DIR . $new_filename)) {
                        $pdfs_subidos[$campo] = $new_filename;
                    } else {
                        $error = "Error al subir el archivo $nombre_legible.";
                        break;
                    }
                }
            }
        }
    }

    if (empty($error) && $estatus === 'publicado') {
        if (empty($pdfs_subidos['pdf_bases'])) {
            $error = "No puede publicar una licitación sin al menos las Bases de la Licitación.";
        }
    }

    if (empty($error)) {
        // Preparar slug unico
        $slug_base = create_slug($titulo);
        $slug = $slug_base;
        $counter = 1;
        while ($licitacionObj->leerPorSlug($slug)) {
            $slug = $slug_base . '-' . $counter;
            $counter++;
        }

        $datos = [
            'numero_licitacion' => $numero_licitacion,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'tipo_procedimiento' => $tipo_procedimiento,
            'anio' => $anio,
            'fecha_publicacion' => $fecha_publicacion,
            'fecha_acta_presentacion' => $fecha_acta_presentacion,
            'area_responsable' => $area_responsable,
            'pdf_bases' => $pdfs_subidos['pdf_bases'],
            'pdf_presentacion' => $pdfs_subidos['pdf_presentacion'],
            'pdf_fallo' => $pdfs_subidos['pdf_fallo'],
            'slug' => $slug,
            'estatus' => $estatus,
            'creado_por' => $_SESSION['user_id']
        ];

        $id = $licitacionObj->crear($datos);
        if ($id) {
            $bitacora->registrar($_SESSION['user_id'], 'crear', 'licitaciones', $id, "Creó licitación $numero_licitacion");
            set_flash_message('success', 'Licitación registrada exitosamente.');
            redirect('index.php');
        } else {
            $error = "Error en la base de datos al guardar la licitación.";
        }
    }
}
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Nueva Licitación</h1>
        <p class="text-gray-600">Complete el formulario para dar de alta un procedimiento.</p>
    </div>
    <a href="index.php" class="text-gray-500 hover:text-gray-700">← Volver al listado</a>
</div>

<?php if ($error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<form action="create.php" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 max-w-4xl">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Número de Licitación -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Número de Licitación *</label>
            <input type="text" name="numero_licitacion" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border" value="<?php echo htmlspecialchars($_POST['numero_licitacion'] ?? ''); ?>">
            <p class="text-xs text-gray-500 mt-1">Ej: LPE-TJAECH-001-2026</p>
        </div>

        <!-- Año -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ejercicio (Año) *</label>
            <input type="number" name="anio" required min="2000" max="2100" class="w-full rounded-md border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border" value="<?php echo htmlspecialchars($_POST['anio'] ?? date('Y')); ?>">
        </div>

        <!-- Título -->
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Título / Nombre de la Licitación *</label>
            <input type="text" name="titulo" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border" value="<?php echo htmlspecialchars($_POST['titulo'] ?? ''); ?>">
        </div>

        <!-- Tipo Procedimiento -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Procedimiento *</label>
            <select name="tipo_procedimiento" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border">
                <option value="">Seleccione...</option>
                <option value="Licitación Pública Estatal">Licitación Pública Estatal</option>
                <option value="Licitación Pública Nacional">Licitación Pública Nacional</option>
                <option value="Invitación Restringida a Tres Proveedores">Invitación Restringida a Tres Proveedores</option>
            </select>
        </div>

        <!-- Área Responsable -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Área Solicitante/Responsable *</label>
            <input type="text" name="area_responsable" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border" value="<?php echo htmlspecialchars($_POST['area_responsable'] ?? ''); ?>">
        </div>

        <!-- Fechas -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Publicación *</label>
            <input type="date" name="fecha_publicacion" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border" value="<?php echo htmlspecialchars($_POST['fecha_publicacion'] ?? date('Y-m-d')); ?>">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Día del Acta de Presentación</label>
            <input type="date" name="fecha_acta_presentacion" class="w-full rounded-md border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border" value="<?php echo htmlspecialchars($_POST['fecha_acta_presentacion'] ?? ''); ?>">
            <p class="text-xs text-gray-500 mt-1">Opcional. Se puede agregar posteriormente.</p>
        </div>

        <!-- Descripción -->
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción Corta / Notas</label>
            <textarea name="descripcion" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border"><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
        </div>

        <!-- Archivos PDF -->
        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Bases de Licitación -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bases de la Licitación</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md bg-gray-50 hover:bg-gray-100 transition cursor-pointer" onclick="document.getElementById('pdf_bases').click()">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-8 w-8 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        <div class="flex text-sm text-gray-600 justify-center">
                            <label for="pdf_bases" class="relative cursor-pointer bg-white rounded-md font-medium text-tjaech hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-tjaech p-1">
                                <span>Subir PDF</span>
                                <input id="pdf_bases" name="pdf_bases" type="file" class="sr-only" accept=".pdf">
                            </label>
                        </div>
                        <p id="file-name-display-bases" class="text-xs text-green-600 font-semibold mt-2 hidden truncate w-32 mx-auto"></p>
                    </div>
                </div>
            </div>

            <!-- Acta de Presentación -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Acta de Presentación</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md bg-gray-50 hover:bg-gray-100 transition cursor-pointer" onclick="document.getElementById('pdf_presentacion').click()">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-8 w-8 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        <div class="flex text-sm text-gray-600 justify-center">
                            <label for="pdf_presentacion" class="relative cursor-pointer bg-white rounded-md font-medium text-tjaech hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-tjaech p-1">
                                <span>Subir PDF</span>
                                <input id="pdf_presentacion" name="pdf_presentacion" type="file" class="sr-only" accept=".pdf">
                            </label>
                        </div>
                        <p id="file-name-display-presentacion" class="text-xs text-green-600 font-semibold mt-2 hidden truncate w-32 mx-auto"></p>
                    </div>
                </div>
            </div>

            <!-- Acta de Fallo -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Acta de Fallo</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md bg-gray-50 hover:bg-gray-100 transition cursor-pointer" onclick="document.getElementById('pdf_fallo').click()">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-8 w-8 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        <div class="flex text-sm text-gray-600 justify-center">
                            <label for="pdf_fallo" class="relative cursor-pointer bg-white rounded-md font-medium text-tjaech hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-tjaech p-1">
                                <span>Subir PDF</span>
                                <input id="pdf_fallo" name="pdf_fallo" type="file" class="sr-only" accept=".pdf">
                            </label>
                        </div>
                        <p id="file-name-display-fallo" class="text-xs text-green-600 font-semibold mt-2 hidden truncate w-32 mx-auto"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estatus -->
        <div class="md:col-span-2 bg-gray-50 p-4 rounded-md border border-gray-200 flex items-center justify-between">
            <div>
                <h4 class="text-lg font-medium text-gray-900">Visibilidad de la Licitación</h4>
                <p class="text-sm text-gray-500">Puedes guardar como borrador y continuar más tarde, o publicarla inmediatamente en el portal (requiere PDF).</p>
            </div>
            <div class="flex items-center space-x-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="estatus" value="borrador" class="form-radio text-tjaech h-5 w-5" <?php echo (!isset($_POST['estatus']) || $_POST['estatus'] == 'borrador') ? 'checked' : ''; ?>>
                    <span class="ml-2 mt-1 text-gray-700 font-medium">Borrador</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="estatus" value="publicado" class="form-radio text-green-600 h-5 w-5" <?php echo (isset($_POST['estatus']) && $_POST['estatus'] == 'publicado') ? 'checked' : ''; ?>>
                    <span class="ml-2 mt-1 text-green-700 font-medium">Publicar al Guardar</span>
                </label>
            </div>
        </div>

    </div>

    <div class="mt-6 pt-5 border-t border-gray-200 flex justify-end">
        <button type="submit" class="bg-tjaech text-white py-2 px-6 rounded shadow hover:bg-blue-800 transition text-lg font-medium">
            Guardar Licitación
        </button>
    </div>
</form>

<script>
    function setupFileInput(id, displayId) {
        document.getElementById(id).addEventListener('change', function(e) {
            var fileName = e.target.files[0] ? e.target.files[0].name : '';
            var display = document.getElementById(displayId);
            if (fileName) {
                display.textContent = fileName;
                display.classList.remove('hidden');
            } else {
                display.classList.add('hidden');
            }
        });
    }

    setupFileInput('pdf_bases', 'file-name-display-bases');
    setupFileInput('pdf_presentacion', 'file-name-display-presentacion');
    setupFileInput('pdf_fallo', 'file-name-display-fallo');

    // Mantener estado del select
    <?php if (isset($_POST['tipo_procedimiento'])): ?>
        document.querySelector('select[name="tipo_procedimiento"]').value = "<?php echo addslashes($_POST['tipo_procedimiento']); ?>";
    <?php endif; ?>
</script>

<?php require_once '../layout/footer.php'; ?>
