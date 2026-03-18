<?php
// admin/licitaciones/index.php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/Database.php';
require_once '../../includes/Licitacion.php';
require_once '../../includes/helpers.php';

require_once '../layout/header.php';

$db = (new Database())->getConnection();
$licitacion = new Licitacion($db);
$stmt = $licitacion->leerTodosAdmin();
$licitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="mb-6 flex justify-between items-center flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Licitaciones</h1>
        <p class="text-gray-600">Administra, pública y edita las licitaciones emitidas.</p>
    </div>
    <div>
        <a href="create.php" class="bg-tjaech text-white py-2 px-4 rounded shadow hover:bg-blue-800 transition inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Nueva Licitación
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <?php if (count($licitaciones) > 0): ?>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número/Año</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fechas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estatus</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($licitaciones as $lic): ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                        <?php echo htmlspecialchars($lic['numero_licitacion']); ?><br>
                        <span class="text-xs text-gray-500 font-normal">Año: <?php echo $lic['anio']; ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900 line-clamp-2" title="<?php echo htmlspecialchars($lic['titulo']); ?>">
                            <?php echo htmlspecialchars($lic['titulo']); ?>
                        </div>
                        <div class="text-xs text-blue-600 mt-1"><?php echo htmlspecialchars($lic['tipo_procedimiento']); ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div>Pub: <?php echo date('d/m/Y', strtotime($lic['fecha_publicacion'])); ?></div>
                        <?php if (!empty($lic['fecha_acta_presentacion'])): ?>
                            <div class="text-gray-700 font-medium mt-1">Acta: <?php echo date('d/m/Y', strtotime($lic['fecha_acta_presentacion'])); ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php if ($lic['estatus'] === 'publicado'): ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Publicado</span>
                        <?php else: ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Borrador</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <?php if ($lic['estatus'] === 'publicado'): ?>
                        <a href="<?php echo APP_URL; ?>/detalle.php?slug=<?php echo htmlspecialchars($lic['slug']); ?>" target="_blank" class="text-blue-600 hover:text-blue-900 mr-3" title="Ver en portal">Ver</a>
                        <?php endif; ?>
                        
                        <a href="edit.php?id=<?php echo $lic['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</a>
                        
                        <!-- Usar un form para seguridad CSRF al eliminar -->
                        <form action="delete.php" method="POST" class="inline-block" onsubmit="return confirm('¿Está seguro de eliminar esta licitación? Esta acción no se puede deshacer.');">
                            <input type="hidden" name="id" value="<?php echo $lic['id']; ?>">
                            <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="p-10 text-center text-gray-500">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay licitaciones</h3>
        <p class="mt-1 text-sm text-gray-500">Comienza creando una nueva licitación.</p>
        <div class="mt-6">
            <a href="create.php" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-tjaech hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-tjaech">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nueva Licitación
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../layout/footer.php'; ?>
