<?php
session_start(); // Arrancamos la memoria del servidor para este usuario

// Si el usuario ha pulsado en un botón de cambiar idioma (?lang=es o ?lang=de)
if (isset($_GET['lang'])) {
    // Guardamos su elección en la sesión
    $_SESSION['idioma_seleccionado'] = $_GET['lang'];
    
    // Recargamos la página limpiando la URL para que no se quede el ?lang= ahí atascado
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// Si no ha elegido nada, por defecto le ponemos alemán (para forzarte a estudiar) o el que ya tuviera guardado
$idioma_actual = isset($_SESSION['idioma_seleccionado']) ? $_SESSION['idioma_seleccionado'] : 'de';

// Cargamos el diccionario correspondiente
require_once __DIR__ . "/lang/" . $idioma_actual . ".php";
?>