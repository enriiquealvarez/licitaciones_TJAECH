<?php
// admin/usuarios/create.php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/Auth.php';

// Asegurarse de que solo admin pueda entrar
Auth::requireAdmin();

require_once '../../includes/Database.php';
require_once '../../includes/Usuario.php';
require_once '../../includes/Bitacora.php';
require_once '../../includes/helpers.php';

require_once '../layout/header.php';

$error = '';
$db = (new Database())->getConnection();
$usuarioObj = new Usuario($db);
$bitacora = new Bitacora($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = clean($_POST['nombre'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $rol = clean($_POST['rol'] ?? 'editor');
    $estatus = clean($_POST['estatus'] ?? 'activo');

    if (empty($nombre) || empty($email) || empty($password)) {
        $error = "Todos los campos obligatorios deben estar llenos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El formato de correo no es válido.";
    } else {
        try {
            if ($usuarioObj->crear($nombre, $email, $password, $rol, $estatus)) {
                $bitacora->registrar($_SESSION['user_id'], 'crear', 'usuarios', null, "Creó usuario $email");
                set_flash_message('success', 'Usuario creado correctamente.');
                redirect('index.php');
            } else {
                $error = "No se pudo crear el usuario. Posible correo duplicado.";
            }
        } catch (PDOException $e) {
            $error = "Ocurrió un error en la base de datos al guardar (¿El correo ya existe?)";
        }
    }
}
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Nuevo Usuario</h1>
        <p class="text-gray-600">Dar acceso a un nuevo miembro del equipo.</p>
    </div>
    <a href="index.php" class="text-gray-500 hover:text-gray-700">← Volver al listado</a>
</div>

<?php if ($error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<form action="create.php" method="POST" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 max-w-2xl">
    <div class="grid grid-cols-1 gap-6">
        
        <!-- Nombre -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo *</label>
            <input type="text" name="nombre" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border" value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>">
        </div>

        <!-- Correo Electrónico -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico *</label>
            <input type="email" name="email" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            <p class="text-xs text-gray-500 mt-1">Este correo se usará para iniciar sesión.</p>
        </div>

        <!-- Contraseña -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña *</label>
            <input type="password" name="password" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border" placeholder="••••••••">
        </div>

        <!-- Rol -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rol en el Sistema *</label>
            <select name="rol" class="w-full rounded-md border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border">
                <option value="editor">Editor (Puede crear y publicar licitaciones)</option>
                <option value="admin">Administrador (Control total y gestión de usuarios)</option>
            </select>
        </div>

        <!-- Estatus -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Estatus *</label>
            <select name="estatus" class="w-full rounded-md border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech p-2 border">
                <option value="activo">Activo (Puede iniciar sesión)</option>
                <option value="inactivo">Inactivo (Acceso bloqueado)</option>
            </select>
        </div>

    </div>

    <div class="mt-6 pt-5 border-t border-gray-200 flex justify-end">
        <button type="submit" class="bg-tjaech text-white py-2 px-6 rounded shadow hover:bg-blue-800 transition text-lg font-medium">
            Guardar Usuario
        </button>
    </div>
</form>

<?php require_once '../layout/footer.php'; ?>
