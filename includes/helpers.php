<?php
// includes/helpers.php

// Función para limpiar cadenas (XSS)
function clean($data) {
    if (is_null($data)) return '';
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Función para generar slugs amigables (ej: "Licitación Pública" -> "licitacion-publica")
function create_slug($string) {
    // Si la extensión de intl no está presente, intenta sanitizar vía regex
    if (function_exists('transliterator_transliterate')) {
        $string = transliterator_transliterate("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();", $string);
    } else {
        $string = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $string));
    }
    // Reemplaza no alfanumericos
    $string = preg_replace('/[^a-z0-9]+/i', '-', $string);
    return trim($string, '-');
}

// Redirección segura
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Establecer un mensaje flash en sesión
function set_flash_message($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

// Obtener un mensaje flash
function get_flash_message($type) {
    if (isset($_SESSION['flash'][$type])) {
        $msg = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $msg;
    }
    return null;
}
