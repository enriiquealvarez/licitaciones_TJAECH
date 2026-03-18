<?php
// admin/logout.php
session_start();
require_once '../includes/config.php';
require_once '../includes/helpers.php';

// Limpiar sesión
$_SESSION = [];
session_destroy();

redirect('login.php');
