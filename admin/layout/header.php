<?php
// admin/layout/header.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__.'/../../includes/Auth.php';
Auth::requireLogin();
$user = Auth::getUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo - TJAECH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        tjaech: '#003366',
                        tjaech_light: '#004a99'
                    },
                    fontFamily: {
                        sans: ['"Public Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-tjaech text-white flex flex-col h-full shadow-lg">
        <div class="h-16 flex items-center justify-center border-b border-tjaech_light bg-white p-2">
            <img src="<?php echo APP_URL; ?>/public/assets/logo_tjaech.png" alt="TJAECH" class="h-full w-auto object-contain">
        </div>
        
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <a href="<?php echo APP_URL; ?>/admin/index.php" class="block px-4 py-2 rounded transition hover:bg-tjaech_light">
                Dashboard
            </a>
            <a href="<?php echo APP_URL; ?>/admin/licitaciones/index.php" class="block px-4 py-2 rounded transition hover:bg-tjaech_light">
                Licitaciones
            </a>
            <?php if (Auth::isAdmin()): ?>
            <div class="pt-4 mt-4 border-t border-tjaech_light">
                <span class="px-4 text-xs uppercase tracking-wider text-gray-400">Administración</span>
                <a href="<?php echo APP_URL; ?>/admin/usuarios/index.php" class="mt-2 block px-4 py-2 rounded transition hover:bg-tjaech_light">
                    Usuarios
                </a>
            </div>
            <?php endif; ?>
        </nav>
        
        <div class="p-4 border-t border-tjaech_light">
            <div class="mb-2 truncate text-sm">
                Hola, <span class="font-semibold"><?php echo htmlspecialchars($user['nombre']); ?></span>
            </div>
            <a href="<?php echo APP_URL; ?>/admin/logout.php" class="block w-full text-center bg-red-600 hover:bg-red-700 text-white py-1.5 rounded transition text-sm">
                Cerrar Sesión
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Topbar -->
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 z-10 shrink-0">
            <h2 class="text-xl font-semibold text-gray-800">
                Sistema de Licitaciones
            </h2>
            <div class="flex items-center space-x-4">
                <a href="<?php echo APP_URL; ?>/" target="_blank" class="text-sm font-medium text-tjaech hover:text-blue-800 flex items-center">
                    Ver Portal Público &rarr;
                </a>
            </div>
        </header>

        <!-- Contenedor Principal (scrollable) -->
        <div class="flex-1 p-6 overflow-y-auto">
            <?php 
                $flash_success = get_flash_message('success');
                $flash_error = get_flash_message('error');
            ?>
            <?php if ($flash_success): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm">
                    <?php echo htmlspecialchars($flash_success); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($flash_error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow-sm">
                    <?php echo htmlspecialchars($flash_error); ?>
                </div>
            <?php endif; ?>
