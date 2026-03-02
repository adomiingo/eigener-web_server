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

// LA RUTA CORREGIDA HACIA TUS DICCIONARIOS
require_once __DIR__ . "/paginas/diccionarios/" . $idioma_actual . ".php";
?>