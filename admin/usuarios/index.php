<?php
// admin/usuarios/index.php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/Auth.php';

// Asegurarse de que solo admin pueda entrar
Auth::requireAdmin();

require_once '../../includes/Database.php';
require_once '../../includes/Usuario.php';
require_once '../../includes/helpers.php';

require_once '../layout/header.php';

$db = (new Database())->getConnection();
$usuarioObj = new Usuario($db);

$stmt = $usuarioObj->leer();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="mb-6 flex justify-between items-center flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Usuarios</h1>
        <p class="text-gray-600">Administra los accesos al sistema.</p>
    </div>
    <div>
        <a href="create.php" class="bg-tjaech text-white py-2 px-4 rounded shadow hover:bg-blue-800 transition inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Nuevo Usuario
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Correo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estatus</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Registro</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($usuarios as $u): ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <?php echo htmlspecialchars($u['nombre']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php echo htmlspecialchars($u['email']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php if ($u['rol'] === 'admin'): ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Administrador</span>
                        <?php else: ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Editor</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php if ($u['estatus'] === 'activo'): ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
                        <?php else: ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                        <?php echo date('d/m/Y', strtotime($u['created_at'])); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../layout/footer.php'; ?>
