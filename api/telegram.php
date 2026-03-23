<?php
// 1. SEGURIDAD
$TOKEN_SECRETO = "aineta"; 
if (!isset($_GET['token']) || $_GET['token'] !== $TOKEN_SECRETO) {
    http_response_code(403);
    die("❌ Acceso denegado.");
}

// 2. DICCIONARIO DE SCRIPTS (Excluyendo mantenimiento)
$tipo = isset($_GET['tipo']) ? strtolower($_GET['tipo']) : '';
$scripts = [
    'academicas' => 'academicas.py',
    'personales' => 'personales.py',
    'ideas'      => 'server_ideas.py',
    'matutinas'  => 'matutinas.py',
    'casa'       => 'alertas_casa.py'
];

// 3. EJECUCIÓN
if (array_key_exists($tipo, $scripts)) {
    $archivo_py = $scripts[$tipo];
    $ruta_script = "/var/www/html/code/scripts_sistema/" . $archivo_py;
    
    // Dispara Python en segundo plano y cierra la conexión al instante
    $comando = escapeshellcmd("/usr/bin/python3 " . $ruta_script);
    shell_exec($comando . " > /dev/null 2>&1 &");
    
    echo "✅ Disparado a Telegram: " . $archivo_py;
} else {
    echo "❌ Tipo no válido.";
}
?>