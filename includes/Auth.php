<?php
// includes/Auth.php

require_once 'config.php';
require_once 'helpers.php';

class Auth {
    // Comprobar si hay un usuario logueado
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // Obtener información básica del usuario logueado
    public static function getUser() {
        if (self::isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'nombre' => $_SESSION['user_nombre'],
                'rol' => $_SESSION['user_rol']
            ];
        }
        return null;
    }

    // Comprobar si es admin
    public static function isAdmin() {
        return isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin';
    }

    // Proteger ruta requerida: redirige al login si no está logueado
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            redirect(APP_URL . '/admin/login.php');
        }
    }

    // Proteger ruta requerida: redirige si no es admin
    public static function requireAdmin() {
        self::requireLogin();
        if (!self::isAdmin()) {
            die("Acceso denegado. Se requiere nivel de administrador.");
        }
    }
}
