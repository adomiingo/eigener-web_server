<?php
session_start();

// Si el usuario cambia de idioma
if (isset($_GET['lang'])) {
    $_SESSION['idioma_seleccionado'] = $_GET['lang'];
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// Idioma por defecto
$idioma_actual = isset($_SESSION['idioma_seleccionado']) ? $_SESSION['idioma_seleccionado'] : 'de';

// LA RUTA CORREGIDA: Sube de 'controladores' -> 'code' -> 'raíz' y entra en 'diccionarios'
$ruta_diccionario = __DIR__ . "/../../diccionarios/" . $idioma_actual . ".php";

if (file_exists($ruta_diccionario)) {
    require_once $ruta_diccionario;
} else {
    // Escudo por si el archivo del diccionario se borra por accidente
    $lang = [];
}
?>