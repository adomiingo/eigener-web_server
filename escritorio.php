<?php
// Cargamos el motor de idiomas para la pantalla de error
require_once 'code/controladores/idiomas.php';
$idioma_actual = isset($_SESSION['idioma_seleccionado']) ? $_SESSION['idioma_seleccionado'] : 'de';

// 1. Doble check de seguridad: Comprobamos si el PC está encendido
$ip_casa = 'motxitorouter.duckdns.org'; 
$puerto_rdp = 54321;
$pc_encendido = false;

$conexion = @fsockopen($ip_casa, $puerto_rdp, $errno, $errstr, 1);
if ($conexion) {
    $pc_encendido = true;
    fclose($conexion);
}

// 2. LA MAGIA: Si está encendido, te mandamos directo a Guacamole
if ($pc_encendido) {
    // 👇 SUSTITUYE ESTA URL POR LA QUE USAS NORMALMENTE PARA ENTRAR A GUACAMOLE 👇
    $url_guacamole = "http://adomiingoagenda.duckdns.org:54321/guacamole/"; 
    
    header("Location: " . $url_guacamole);
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $idioma_actual; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WERKSTATT - RDP</title>
    
    <style>
        body, html { 
            margin: 0; padding: 0; width: 100%; height: 100%; 
            background-color: #0f172a; font-family: 'Segoe UI', sans-serif; 
        }
        .offline-msg { 
            display: flex; flex-direction: column; align-items: center; 
            justify-content: center; height: 100vh; color: #f8fafc; text-align: center; 
        }
        .offline-msg h1 { color: #ef4444; }
        .btn-volver { 
            margin-top: 20px; background: #0284c7; color: white; padding: 12px 25px; 
            text-decoration: none; border-radius: 8px; font-weight: bold; transition: 0.2s;
        }
        .btn-volver:hover { background: #0369a1; }
    </style>
</head>
<body>
    <div class="offline-msg">
        <h1>❌ Enlace Roto</h1>
        <p>El equipo WERKSTATT no responde al ping. Enciéndelo primero desde el menú principal.</p>
        <a href="index.php" class="btn-volver">Volver al Main Brain</a>
    </div>
</body>
</html>