<?php
// Forzar codificación para que Siri lea bien los acentos
header('Content-Type: text/plain; charset=utf-8');

// 1. SEGURIDAD
$TOKEN_SECRETO = "aineta"; 
if (!isset($_GET['token']) || $_GET['token'] !== $TOKEN_SECRETO) {
    http_response_code(403);
    die("Acceso denegado. Token inválido.");
}

// 2. SELECCIÓN DE CATEGORÍA
$tipo = isset($_GET['tipo']) ? strtolower($_GET['tipo']) : '';
$ruta_archivo = "";

switch ($tipo) {
    case 'academico':
        $ruta_archivo = "/var/www/html/api/guion_academico.txt";
        break;
    case 'citas':
        $ruta_archivo = "/var/www/html/api/guion_citas.txt";
        break;
    case 'personal':
        $ruta_archivo = "/var/www/html/api/guion_personal.txt";
        break;
    case 'matutino': // Para el noticiario de las 08:30
        $ruta_archivo = "/var/www/html/api/noticiario_hoy.txt";
        break;
    default:
        die("Señor, no reconozco ese tipo de guion.");
}

// 3. LEER Y DEVOLVER EL TEXTO
if (file_exists($ruta_archivo)) {
    echo file_get_contents($ruta_archivo);
} else {
    echo "El guion solicitado no está disponible o la agenda está vacía.";
}
?>