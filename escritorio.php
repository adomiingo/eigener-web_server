<?php
// Cargamos el motor de idiomas para mantener la coherencia
require_once 'code/controladores/idiomas.php';

$idioma_actual = isset($_SESSION['idioma_seleccionado']) ? $_SESSION['idioma_seleccionado'] : 'de';

// 1. Doble check de seguridad: Comprobamos si el PC está encendido antes de cargar Guacamole
$ip_casa = 'motxitorouter.duckdns.org'; 
$puerto_rdp = 54321;
$pc_encendido = false;
$conexion = @fsockopen($ip_casa, $puerto_rdp, $errno, $errstr, 1);
if ($conexion) {
    $pc_encendido = true;
    fclose($conexion);
}

// 2. CONFIGURACIÓN DE GUACAMOLE
// Suponiendo que tu Docker de Guacamole sale por el puerto 8080 del propio servidor:
$url_guacamole = "http://" . $_SERVER['HTTP_HOST'] . ":8080/guacamole/";

// (Si en el futuro usas Nginx para ocultar el puerto 8080, la ruta sería simplemente "/guacamole/")
?>
<!DOCTYPE html>
<html lang="<?php echo $idioma_actual; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WERKSTATT - RDP</title>
    
    <style>
        /* Diseño inmersivo: Ocultamos el scroll y los márgenes */
        body, html { 
            margin: 0; 
            padding: 0; 
            width: 100%; 
            height: 100%; 
            overflow: hidden; 
            background-color: #0f172a; 
            font-family: 'Segoe UI', sans-serif; 
        }
        
        .iframe-container { 
            width: 100%; 
            height: 100vh; 
            border: none; 
            display: block; 
        }
        
        /* Botón flotante camuflado para salir de la sesión */
        .btn-salir { 
            position: absolute; 
            top: 10px; 
            left: 50%; 
            transform: translateX(-50%); 
            background: rgba(15, 23, 42, 0.4); 
            color: #cbd5e1; 
            padding: 6px 20px; 
            text-decoration: none; 
            border-radius: 20px; 
            font-size: 0.85rem;
            font-weight: bold; 
            border: 1px solid rgba(255,255,255,0.1); 
            backdrop-filter: blur(5px); 
            z-index: 9999; 
            transition: 0.3s; 
            opacity: 0.1; /* Casi invisible para no molestar en Windows */
        }
        
        /* Aparece al pasar el ratón por el centro arriba */
        .btn-salir:hover { 
            background: rgba(220, 38, 38, 0.9); 
            color: white;
            opacity: 1; 
            border-color: #f87171; 
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.4);
        }
        
        /* Mensaje de error si se intenta entrar con el PC apagado */
        .offline-msg { 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
            color: #f8fafc; 
            text-align: center; 
        }
        
        .offline-msg h1 { color: #ef4444; }
        .btn-volver { 
            margin-top: 20px; 
            background: #0284c7; 
            color: white; 
            padding: 12px 25px; 
            text-decoration: none; 
            border-radius: 8px; 
            font-weight: bold; 
            transition: 0.2s;
        }
        .btn-volver:hover { background: #0369a1; }
    </style>
</head>
<body>

    <?php if ($pc_encendido): ?>
        <a href="index.php" class="btn-salir" title="Cerrar conexión y volver al panel">✖ Cerrar Conexión</a>
        
        <iframe src="<?php echo $url_guacamole; ?>" class="iframe-container" allowfullscreen></iframe>
        
    <?php else: ?>
        <div class="offline-msg">
            <h1>❌ Enlace Roto</h1>
            <p>El equipo WERKSTATT no responde al ping. Enciéndelo primero desde el menú principal.</p>
            <a href="index.php" class="btn-volver">Volver al Main Brain</a>
        </div>
    <?php endif; ?>

</body>
</html>