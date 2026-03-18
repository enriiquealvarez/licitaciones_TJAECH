<?php
// detalle.php
require_once 'includes/config.php';
require_once 'includes/Database.php';
require_once 'includes/Licitacion.php';
require_once 'includes/helpers.php';

$slug = clean($_GET['slug'] ?? '');

if (empty($slug)) {
    redirect('index.php');
}

$db = (new Database())->getConnection();
$licitacionObj = new Licitacion($db);

$licitacion = $licitacionObj->leerPorSlug($slug);

if (!$licitacion) {
    // 404
    header("HTTP/1.0 404 Not Found");
    die("Licitación no encontrada o no disponible temporalmente.");
}

$pdf_bases = APP_URL . '/uploads/pdfs/' . $licitacion['pdf_bases'];
$pdf_presentacion = APP_URL . '/uploads/pdfs/' . $licitacion['pdf_presentacion'];
$pdf_fallo = APP_URL . '/uploads/pdfs/' . $licitacion['pdf_fallo'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- SEO elements -->
    <title><?php echo htmlspecialchars($licitacion['titulo']); ?> - <?php echo APP_NAME; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars(mb_substr($licitacion['descripcion'], 0, 150)); ?>">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        tjaech: '#003366',
                        tjaech_gold: '#B89B58'
                    },
                    fontFamily: {
                        sans: ['"Public Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen text-gray-800 font-sans">

    <!-- Header / Nav -->
    <header class="bg-white shadow-sm border-b-4 border-tjaech">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <a href="<?php echo APP_URL; ?>" class="flex items-center hover:opacity-90 transition">
                    <img src="public/assets/logo_tjaech.png" alt="Logo Tribunal de Justicia Administrativa del Estado de Chiapas" class="h-14 w-auto object-contain mr-4">
                    <div>
                        <h1 class="font-bold text-xl text-tjaech leading-tight">Tribunal de Justicia Administrativa</h1>
                        <span class="text-sm text-gray-500 uppercase tracking-widest block">del Estado de Chiapas</span>
                    </div>
                </a>
                <div class="hidden md:block">
                    <a href="<?php echo APP_URL; ?>/" class="text-sm font-medium text-gray-500 hover:text-tjaech transition">← Volver al Buscador</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Contenido Detalle -->
    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        
        <div class="mb-4">
            <a href="<?php echo APP_URL; ?>/" class="text-tjaech text-sm font-medium hover:underline inline-flex items-center md:hidden">
                ← Volver al buscador
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Sidebar Info -->
            <div class="lg:col-span-1 space-y-6 flex flex-col">
                <div class="bg-white p-6 rounded-lg shadow-sm border-t-4 border-tjaech">
                    <span class="inline-block px-2 py-1 text-xs text-tjaech_gold bg-yellow-50 rounded font-semibold border border-yellow-100 mb-4 uppercase tracking-wider">
                        <?php echo htmlspecialchars($licitacion['tipo_procedimiento']); ?>
                    </span>
                    
                    <h1 class="text-2xl font-bold text-gray-900 mb-6 leading-tight" id="licitacion-titulo">
                        <?php echo htmlspecialchars($licitacion['titulo']); ?>
                    </h1>
                    
                    <div class="space-y-4 text-sm">
                        <div>
                            <span class="block text-gray-500 font-medium">No. Licitación</span>
                            <span class="font-semibold text-gray-900"><?php echo htmlspecialchars($licitacion['numero_licitacion']); ?></span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 border-t border-gray-100 pt-4">
                            <div>
                                <span class="block text-gray-500 font-medium">Publicación</span>
                                <span class="text-gray-900"><?php echo date('d / m / Y', strtotime($licitacion['fecha_publicacion'])); ?></span>
                            </div>
                            <?php if (!empty($licitacion['fecha_acta_presentacion'])): ?>
                            <div>
                                <span class="block text-gray-500 font-medium">Acta de Presentación</span>
                                <span class="text-tjaech font-medium"><?php echo date('d / m / Y', strtotime($licitacion['fecha_acta_presentacion'])); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="border-t border-gray-100 pt-4">
                            <span class="block text-gray-500 font-medium">Área Responsable</span>
                            <span class="text-gray-900"><?php echo htmlspecialchars($licitacion['area_responsable']); ?></span>
                        </div>
                        
                        <?php if (!empty($licitacion['descripcion'])): ?>
                        <div class="border-t border-gray-100 pt-4">
                            <span class="block text-gray-500 font-medium mb-1">Notas / Descripción</span>
                            <p class="text-gray-700 whitespace-pre-line"><?php echo htmlspecialchars($licitacion['descripcion']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="space-y-4 mt-auto">
                    <div class="bg-blue-50 p-4 rounded-lg shadow-sm border border-blue-100 flex flex-col hover:bg-blue-100 transition">
                        <h4 class="font-bold text-tjaech text-sm mb-1">Bases de la Licitación</h4>
                        <div class="flex flex-col space-y-2 mt-2">
                            <div class="flex justify-between items-center text-sm">
                                <button onclick="setPdfViewer('<?php echo $pdf_bases; ?>', 'Bases de la Licitación')" class="text-blue-700 hover:underline font-medium flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg> Ver aquí</button>
                                <a href="<?php echo $pdf_bases; ?>" target="_blank" class="text-blue-700 hover:underline font-medium flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg> Pestaña</a>
                            </div>
                            <div class="flex justify-between items-center text-sm border-t border-blue-200 pt-2">
                                <button onclick="copiarAlPortapapeles('<?php echo $pdf_bases; ?>')" class="text-gray-600 hover:text-black font-semibold flex items-center" title="Copiar URL para compartir"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg> Copiar URL</button>
                                <a href="<?php echo $pdf_bases; ?>" download class="text-gray-600 hover:text-black font-semibold flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg> Descargar</a>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 p-4 rounded-lg shadow-sm border border-blue-100 flex flex-col hover:bg-blue-100 transition">
                        <h4 class="font-bold text-tjaech text-sm mb-1">Acta de Presentación</h4>
                        <div class="flex flex-col space-y-2 mt-2">
                            <div class="flex justify-between items-center text-sm">
                                <button onclick="setPdfViewer('<?php echo $pdf_presentacion; ?>', 'Acta de Presentación')" class="text-blue-700 hover:underline font-medium flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg> Ver aquí</button>
                                <a href="<?php echo $pdf_presentacion; ?>" target="_blank" class="text-blue-700 hover:underline font-medium flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg> Pestaña</a>
                            </div>
                            <div class="flex justify-between items-center text-sm border-t border-blue-200 pt-2">
                                <button onclick="copiarAlPortapapeles('<?php echo $pdf_presentacion; ?>')" class="text-gray-600 hover:text-black font-semibold flex items-center" title="Copiar URL para compartir"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg> Copiar URL</button>
                                <a href="<?php echo $pdf_presentacion; ?>" download class="text-gray-600 hover:text-black font-semibold flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg> Descargar</a>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 p-4 rounded-lg shadow-sm border border-blue-100 flex flex-col hover:bg-blue-100 transition">
                        <h4 class="font-bold text-tjaech text-sm mb-1">Acta de Fallo</h4>
                        <div class="flex flex-col space-y-2 mt-2">
                            <div class="flex justify-between items-center text-sm">
                                <button onclick="setPdfViewer('<?php echo $pdf_fallo; ?>', 'Acta de Fallo')" class="text-blue-700 hover:underline font-medium flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg> Ver aquí</button>
                                <a href="<?php echo $pdf_fallo; ?>" target="_blank" class="text-blue-700 hover:underline font-medium flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg> Pestaña</a>
                            </div>
                            <div class="flex justify-between items-center text-sm border-t border-blue-200 pt-2">
                                <button onclick="copiarAlPortapapeles('<?php echo $pdf_fallo; ?>')" class="text-gray-600 hover:text-black font-semibold flex items-center" title="Copiar URL para compartir"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg> Copiar URL</button>
                                <a href="<?php echo $pdf_fallo; ?>" download class="text-gray-600 hover:text-black font-semibold flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg> Descargar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content PDF Viewer -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden flex flex-col h-[600px] lg:h-auto min-h-[600px]">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center shrink-0">
                    <h3 class="font-bold text-gray-800 flex items-center" id="viewer-title">
                        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>
                        Bases de la Licitación
                    </h3>
                    <a href="<?php echo $pdf_bases; ?>" id="viewer-new-tab" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">Abrir en nueva pestaña</a>
                </div>
                <!-- Native Browser PDF Embedding -->
                <div class="flex-grow w-full bg-gray-200 relative">
                    <object id="pdf-object" data="<?php echo $pdf_bases; ?>" type="application/pdf" width="100%" height="100%" class="absolute inset-0 w-full h-full">
                        <div class="flex flex-col items-center justify-center p-8 h-full bg-white text-center">
                            <p class="text-gray-500 mb-4">El navegador no soporta la visualización de PDFs en línea.</p>
                            <a id="pdf-fallback" href="<?php echo $pdf_bases; ?>" class="text-tjaech font-medium hover:underline">Pulsar aquí para descargar el PDF</a>
                        </div>
                    </object>
                </div>
            </div>
            
            <script>
                function setPdfViewer(url, title) {
                    document.getElementById('pdf-object').setAttribute('data', url);
                    document.getElementById('viewer-title').innerHTML = '<svg class="w-5 h-5 text-red-500 mr-2 inline" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg> ' + title;
                    document.getElementById('viewer-new-tab').href = url;
                    document.getElementById('pdf-fallback').href = url;
                }
                function copiarAlPortapapeles(texto) {
                    navigator.clipboard.writeText(texto).then(function() {
                        alert('URL copiada al portapapeles exitosamente:\n' + texto);
                    }, function(err) {
                        alert('Error al copiar: ' + err);
                    });
                }
            </script>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-300 py-8 border-t-4 border-tjaech_gold mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div>
                <img src="public/assets/logo_tjaech.png" alt="Logo TJAECH" class="h-10 w-auto mb-4 object-contain opacity-90 brightness-0 invert" style="filter: brightness(0) invert(1);">
                <p class="text-sm">Órgano jurisdiccional dotado de plena autonomía para dictar sus fallos y establecer su organización administrativa gubernamental, responsable de resolver las controversias administrativas.</p>
            </div>
            <div>
                <h4 class="text-white font-bold text-lg mb-4">Contacto</h4>
                <ul class="text-sm space-y-2">
                    <li><span class="font-semibold">Dirección:</span> Tuxtla Gutiérrez, Chiapas.</li>
                    <li><span class="font-semibold">Área:</span> Unidad de Apoyo Administrativo</li>
                    <li><span class="font-semibold">Email:</span> contacto@tjaech.gob.mx</li>
                </ul>
            </div>
            <div class="flex items-center lg:justify-end">
                <p class="text-sm text-gray-400">© <?php echo date('Y'); ?> TJAECH.<br>Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>
