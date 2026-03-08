<?php
// Motor de idiomas
require_once '../../code/controladores/idiomas.php';

$idioma_actual = isset($_SESSION['idioma_seleccionado']) ? $_SESSION['idioma_seleccionado'] : 'de';
$rotacion = ['cat' => 'de', 'de'  => 'en', 'en'  => 'es', 'es'  => 'cat'];
$siguiente_idioma = isset($rotacion[$idioma_actual]) ? $rotacion[$idioma_actual] : 'de';
$banderas = ['cat' => 'CAT', 'de'  => '🇩🇪 DE', 'en'  => '🇬🇧 EN', 'es'  => '🇪🇸 ES'];
$bandera_mostrar = isset($banderas[$idioma_actual]) ? $banderas[$idioma_actual] : '🇩🇪 DE';

// Comprobación visual de WERKSTATT
$ip_casa = 'motxitorouter.duckdns.org'; 
$puerto_rdp = 54321;
$pc_encendido = false;
$conexion = @fsockopen($ip_casa, $puerto_rdp, $errno, $errstr, 1);
if ($conexion) {
    $pc_encendido = true;
    fclose($conexion);
}
?>
<!DOCTYPE html>
<html lang="<?php echo $idioma_actual; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($lang['titulo_personal']) ? $lang['titulo_personal'] : 'Área Personal'; ?></title>
    <link rel="stylesheet" href="../../css/menu.css">
    
    <style>
        .status-circle { position: absolute; top: 20px; left: 20px; width: 24px; height: 24px; border-radius: 50%; z-index: 1000; cursor: pointer; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); transition: transform 0.2s ease; }
        .status-circle:hover { transform: scale(1.1); }
        .status-circle.offline { background-color: #dc2626; }
        .status-circle.online { background-color: #22c55e; animation: pulse-lila 1.5s infinite; }
        @keyframes pulse-lila {
            0% { opacity: 1; box-shadow: 0 0 0 0 rgba(168, 85, 247, 0.7); }
            70% { opacity: 0.6; box-shadow: 0 0 0 15px rgba(168, 85, 247, 0); }
            100% { opacity: 1; box-shadow: 0 0 0 0 rgba(168, 85, 247, 0); }
        }

        .btn-lang-cycle { position: absolute; top: 20px; right: 20px; background-color: #ffffff; border: 2px solid #e2e8f0; color: #475569; padding: 8px 16px; border-radius: 30px; font-weight: bold; font-size: 0.95rem; text-decoration: none; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05); transition: all 0.2s ease; z-index: 1000; }
        .btn-lang-cycle:hover { background-color: #f8fafc; transform: translateY(-2px); border-color: #cbd5e1; color: #0f172a; }

        @media (max-width: 480px) {
            .btn-lang-cycle { top: 10px; right: 10px; padding: 6px 12px; font-size: 0.85rem; }
            .status-circle { top: 12px; left: 12px; }
        }
    </style>
</head>
<body>

    <a href="<?php echo $pc_encendido ? '../../escritorio.php' : '#'; ?>" id="status-circle"
        class="status-circle <?php echo $pc_encendido ? 'online' : 'offline'; ?>"
        title="<?php echo $pc_encendido ? 'Conectar al Escritorio' : 'PC Apagado'; ?>" <?php echo !$pc_encendido ? 'onclick="alert(\'WERKSTATT está apagado. Enciéndelo desde el menú principal.\'); return false;"' : ''; ?>>
    </a>

    <a href="?lang=<?php echo $siguiente_idioma; ?>" class="btn-lang-cycle" title="Cambiar idioma">
        <?php echo $bandera_mostrar; ?> ↻
    </a>

    <div id="principal">
        <h2><?php echo isset($lang['titulo_personal']) ? $lang['titulo_personal'] : 'Área Personal'; ?></h2>

        <nav class="menu-container">
            <a href="./documentacion_resguardos/" class="btn-link" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white;">
                <?php echo isset($lang['btn_docs']) ? $lang['btn_docs'] : '📁 Documentación y Resguardos'; ?>
            </a>
            
            <a href="./proyecto_ubermensch/index.php" class="btn-link" style="background: linear-gradient(135deg, #10b981, #047857); color: white;">
                <?php echo isset($lang['btn_ubermensch']) ? $lang['btn_ubermensch'] : '🏋️‍♂️ Proyecto Übermensch'; ?>
            </a>
            
            <a href="./proyectos_personales/" class="btn-link" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9); color: white;">
                <?php echo isset($lang['btn_proyectos_pers']) ? $lang['btn_proyectos_pers'] : '💻 Proyectos Personales'; ?>
            </a>
            
            <a href="./recuerdos/" class="btn-link" style="background: linear-gradient(135deg, #ec4899, #be185d); color: white;">
                <?php echo isset($lang['btn_recuerdos']) ? $lang['btn_recuerdos'] : '📸 Recuerdos'; ?>
            </a>
        </nav>

        <div style="text-align:center; margin-top:30px;">
            <a href="../../index.php" class="btn-link" style="background-color: #94a3b8; color: white;">🏠 Volver al Inicio</a> 
        </div>
    </div>
</body>
</html>