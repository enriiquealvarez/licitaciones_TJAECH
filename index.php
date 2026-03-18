<?php
// index.php
require_once 'includes/config.php';
require_once 'includes/Database.php';
require_once 'includes/Licitacion.php';
require_once 'includes/helpers.php';

$db = (new Database())->getConnection();
$licitacionObj = new Licitacion($db);

// Capturar filtros
$search = clean($_GET['q'] ?? '');
$anio_filter = clean($_GET['anio'] ?? '');
$tipo_filter = clean($_GET['tipo'] ?? '');

$stmt = $licitacionObj->leerPublicas($search, $anio_filter, $tipo_filter);
$licitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener años unicos para el filtro (podria hacerse con query, pero para simplicidad lo pasamos estatico aca o desde DB)
$anios = range(date('Y'), 2020);
$tipos_procedimiento = [
    'Licitación Pública Estatal',
    'Licitación Pública Nacional',
    'Invitación Restringida a Tres Proveedores'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
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
                <div class="flex items-center">
                    <img src="public/assets/logo_tjaech.png" alt="Logo Tribunal de Justicia Administrativa del Estado de Chiapas" class="h-14 w-auto object-contain mr-4">
                    <div>
                        <h1 class="font-bold text-xl text-tjaech leading-tight">Tribunal de Justicia Administrativa</h1>
                        <span class="text-sm text-gray-500 uppercase tracking-widest block">del Estado de Chiapas</span>
                    </div>
                </div>
                <div>
                    <a href="admin/login.php" class="text-sm font-medium text-tjaech hover:text-tjaech_gold transition">Acceso Institucional</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Banner -->
    <div class="bg-tjaech text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold mb-4">Portal Público de Licitaciones</h2>
            <p class="text-blue-100 max-w-2xl text-lg">
                Consulte las licitaciones vigentes, invitaciones restringidas y adjudicaciones directas emitidas por la Unidad de Apoyo Administrativo.
            </p>
        </div>
    </div>

    <!-- Contenido -->
    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        
        <!-- Filtros y Buscador -->
        <div class="bg-white p-6 rounded-lg shadow-sm mb-8 border border-gray-100">
            <form action="index.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="md:col-span-2">
                    <label for="q" class="block text-sm font-medium text-gray-700 mb-1">Buscar por título o número</label>
                    <input type="text" name="q" id="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Ej: Adquisición de Papelería..." class="w-full rounded border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border">
                </div>
                
                <div>
                    <label for="anio" class="block text-sm font-medium text-gray-700 mb-1">Año</label>
                    <select name="anio" id="anio" class="w-full rounded border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border bg-white">
                        <option value="">Todos</option>
                        <?php foreach($anios as $a): ?>
                            <option value="<?php echo $a; ?>" <?php echo $anio_filter == $a ? 'selected' : ''; ?>><?php echo $a; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select name="tipo" id="tipo" class="w-full rounded border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border bg-white text-sm">
                        <option value="">Todos</option>
                        <?php foreach($tipos_procedimiento as $tp): ?>
                            <option value="<?php echo $tp; ?>" <?php echo $tipo_filter == $tp ? 'selected' : ''; ?>><?php echo htmlspecialchars($tp); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-4 mt-2 flex justify-end space-x-3">
                    <a href="index.php" class="py-2 px-4 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 transition">Limpiar</a>
                    <button type="submit" class="py-2 px-6 bg-tjaech text-white rounded text-sm hover:bg-blue-800 transition font-medium">Buscar Protocolos</button>
                </div>
            </form>
        </div>

        <!-- Resultados -->
        <div>
            <h3 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2">Resultados de Licitaciones <span class="text-gray-400 text-base font-normal ml-2">(<?php echo count($licitaciones); ?> encontrados)</span></h3>

            <?php if (count($licitaciones) > 0): ?>
                <div class="divide-y divide-gray-200 border-t border-b border-gray-200">
                    <?php foreach ($licitaciones as $lic): ?>
                        <div class="py-6 flex flex-col md:flex-row md:items-center justify-between hover:bg-gray-50 transition px-4 -mx-4 rounded">
                            <div class="flex-1 mb-4 md:mb-0 md:pr-6">
                                <span class="inline-block px-2 py-1 text-xs text-tjaech_gold bg-yellow-50 rounded-full font-semibold border border-yellow-100 mb-2">
                                    <?php echo htmlspecialchars($lic['tipo_procedimiento']); ?>
                                </span>
                                <a href="<?php echo htmlspecialchars($lic['slug']); ?>" class="block mt-1 text-lg font-bold text-gray-900 hover:text-tjaech hover:underline transition">
                                    <?php echo htmlspecialchars($lic['titulo']); ?>
                                </a>
                                <p class="text-gray-500 text-sm mt-1">
                                    <strong>No:</strong> <?php echo htmlspecialchars($lic['numero_licitacion']); ?> 
                                    <span class="mx-2 text-gray-300">|</span> 
                                    <strong>Publicación:</strong> <?php echo date('d/m/Y', strtotime($lic['fecha_publicacion'])); ?>
                                    <span class="mx-2 text-gray-300">|</span> 
                                    <?php if (!empty($lic['fecha_acta_presentacion'])): ?>
                                        <strong>Acta:</strong> <span class="text-gray-900 font-medium"><?php echo date('d/m/Y', strtotime($lic['fecha_acta_presentacion'])); ?></span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="shrink-0 flex items-center">
                                <a href="<?php echo htmlspecialchars($lic['slug']); ?>" class="text-sm bg-gray-100 text-gray-700 hover:bg-tjaech hover:text-white border border-gray-200 py-2 px-4 rounded font-medium transition flex items-center">
                                    Ver Detalles
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-16 bg-white rounded-lg border border-gray-100 border-dashed">
                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900">No se encontraron resultados</h3>
                    <p class="mt-1 text-gray-500">Pruebe ajustando los filtros de búsqueda o eliminando los criterios actuales.</p>
                </div>
            <?php endif; ?>
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
                    <li><span class="font-semibold">Dirección:</span> Boulevard Belisario Domínguez No. 1713, Colonia Xamaipak. Tuxtla Gutiérrez, Chiapas.</li>
                    <li><span class="font-semibold">Área:</span> Unidad de Apoyo Administrativo</li>
                    <li><span class="font-semibold">Email:</span> unidad@tjaech.gob.mx</li>
                </ul>
            </div>
            <div class="flex items-center lg:justify-end">
                <p class="text-sm text-gray-400">© <?php echo date('Y'); ?> TJAECH.<br>Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>
