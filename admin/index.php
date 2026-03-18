<?php
// admin/index.php
session_start();
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/Licitacion.php';
require_once '../includes/helpers.php';

require_once 'layout/header.php';

$db = (new Database())->getConnection();
$licitacion = new Licitacion($db);

// Métricas rápidas
$stmt = $licitacion->leerTodosAdmin();
$total_licitaciones = $stmt->rowCount();

$publicadas = 0;
$borradores = 0;

$licitaciones_recientes = [];
$count = 0;

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($row['estatus'] === 'publicado') {
        $publicadas++;
    } else {
        $borradores++;
    }
    
    if ($count < 5) {
        $licitaciones_recientes[] = $row;
        $count++;
    }
}
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
    <p class="text-gray-600">Resumen y métricas del sistema.</p>
</div>

<!-- Tarjetas de Métricas -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex flex-col justify-between">
        <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider">Total de Licitaciones</h3>
        <span class="text-4xl font-bold text-gray-800 mt-2"><?php echo $total_licitaciones; ?></span>
    </div>
    
    <div class="bg-green-50 rounded-lg shadow-sm border border-green-200 p-6 flex flex-col justify-between">
        <h3 class="text-green-700 text-sm font-medium uppercase tracking-wider">Publicadas</h3>
        <span class="text-4xl font-bold text-green-800 mt-2"><?php echo $publicadas; ?></span>
    </div>
    
    <div class="bg-yellow-50 rounded-lg shadow-sm border border-yellow-200 p-6 flex flex-col justify-between">
        <h3 class="text-yellow-700 text-sm font-medium uppercase tracking-wider">Borradores</h3>
        <span class="text-4xl font-bold text-yellow-800 mt-2"><?php echo $borradores; ?></span>
    </div>
</div>

<!-- Licitaciones Recientes -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
        <h3 class="text-lg font-medium text-gray-800">Licitaciones Recientes</h3>
        <a href="licitaciones/index.php" class="text-sm text-tjaech hover:underline">Ver todas</a>
    </div>
    
    <?php if (count($licitaciones_recientes) > 0): ?>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estatus</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Alta</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($licitaciones_recientes as $lic): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <?php echo htmlspecialchars($lic['numero_licitacion']); ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <div class="max-w-xs md:max-w-md truncate" title="<?php echo htmlspecialchars($lic['titulo']); ?>">
                            <?php echo htmlspecialchars($lic['titulo']); ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php if ($lic['estatus'] === 'publicado'): ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Publicado</span>
                        <?php else: ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Borrador</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo date('d/m/Y', strtotime($lic['created_at'])); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="p-6 text-center text-gray-500">
        No hay licitaciones registradas aún.
    </div>
    <?php endif; ?>
</div>

<?php require_once 'layout/footer.php'; ?>
