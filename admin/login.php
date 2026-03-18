<?php
// admin/login.php
session_start();
require_once '../includes/config.php';
require_once '../includes/Database.php';
require_once '../includes/Usuario.php';
require_once '../includes/Auth.php';
require_once '../includes/helpers.php';

if (Auth::isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Por favor, ingrese correo y contraseña.';
    } else {
        $db = (new Database())->getConnection();
        $usuario = new Usuario($db);
        
        if ($usuario->login($email, $password)) {
            // Guardar bitacora
            require_once '../includes/Bitacora.php';
            $bitacora = new Bitacora($db);
            $bitacora->registrar($_SESSION['user_id'], 'login', 'sistema', null, 'Inicio de sesión exitoso');
            
            redirect('index.php');
        } else {
            $error = 'Credenciales incorrectas o cuenta inactiva.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Panel Administrativo TJAECH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        tjaech: '#003366',
                    },
                    fontFamily: {
                        sans: ['"Public Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 flex items-center justify-center h-screen font-sans">

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md border-t-4 border-tjaech">
        <div class="text-center mb-6 flex flex-col items-center">
            <img src="../public/assets/logo_tjaech.png" alt="Logo TJAECH" class="h-16 w-auto object-contain mb-4">
            <h1 class="text-2xl font-bold text-tjaech">Portal Administrativo</h1>
            <p class="text-sm text-gray-500">Gestión de Licitaciones</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                <input type="email" name="email" id="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech sm:text-sm p-2 border" placeholder="informatica@tjaech.gob.mx">
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                <input type="password" name="password" id="password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-tjaech focus:ring-tjaech sm:text-sm p-2 border" placeholder="••••••••">
            </div>

            <button type="submit" class="w-full bg-tjaech text-white py-2 px-4 rounded-md hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-tjaech transition">
                Ingresar
            </button>
        </form>
    </div>

</body>
</html>
