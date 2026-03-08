<?php
// 1. Cargamos el motor de idiomas
require_once '../code/controladores/idiomas.php';

$idioma_actual = isset($_SESSION['idioma_seleccionado']) ? $_SESSION['idioma_seleccionado'] : 'de';
$rotacion = ['cat' => 'de', 'de' => 'en', 'en' => 'es', 'es' => 'cat'];
$siguiente_idioma = isset($rotacion[$idioma_actual]) ? $rotacion[$idioma_actual] : 'de';
$banderas = ['cat' => 'CAT', 'de' => '🇩🇪 DE', 'en' => '🇬🇧 EN', 'es' => '🇪🇸 ES'];
$bandera_mostrar = isset($banderas[$idioma_actual]) ? $banderas[$idioma_actual] : '🇩🇪 DE';

// 2. Fetch de Logs (AJAX)
if (isset($_GET['get_log'])) {
    $log = shell_exec("tail -n 15 /var/log/nginx/access.log 2>&1");
    echo empty($log) ? (isset($lang['msg_esperando_logs']) ? $lang['msg_esperando_logs'] : 'Esperando logs...') : htmlspecialchars($log);
    exit;
}

// 3. Ejecución de Alertas Python
$mensaje_accion = "";
if (isset($_POST['ejecutar_alertas'])) {
    $comando = escapeshellcmd("python3 ../code/scripts_sistema/alertas.py");
    $salida = shell_exec($comando . " 2>&1"); 
    $mensaje_accion = "<div class='alert success'>" . (isset($lang['msg_comando_ejecutado']) ? $lang['msg_comando_ejecutado'] : 'Ejecutado') . "<br>" . nl2br(htmlspecialchars($salida)) . "</div>";
}

// 4. Métricas del Servidor
$uptime = shell_exec("uptime -p");
$ram_usage = shell_exec("free -m | awk 'NR==2{printf \"%.1f%% (Usado: %s MB)\", $3*100/$2, $3 }'");
$db_path = "/var/www/ubungen/kalender.db";
$db_size = file_exists($db_path) ? round(filesize($db_path) / 1024, 2) . " KB" : (isset($lang['msg_no_encontrada']) ? $lang['msg_no_encontrada'] : 'No encontrada');

try {
    $db = new PDO("sqlite:$db_path");
    $total_tareas = $db->query("SELECT COUNT(*) FROM aufgaben")->fetchColumn();
    $total_pendientes = $db->query("SELECT COUNT(*) FROM aufgaben WHERE zustand = 'Ausstehen'")->fetchColumn();
} catch (Exception $e) { 
    $total_tareas = isset($lang['msg_error']) ? $lang['msg_error'] : 'Error'; 
    $total_pendientes = isset($lang['msg_error']) ? $lang['msg_error'] : 'Error'; 
}

// 5. Comprobación de estado del WERKSTATT (Solo para el chivato visual)
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
    <title><?php echo isset($lang['estado_titulo']) ? $lang['estado_titulo'] : 'Estado del Servidor'; ?></title>
    
    <link rel="stylesheet" href="../css/menu.css">
    
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

        .btn-lang-cycle { position: absolute; top: 20px; right: 20px; background-color: #ffffff; border: 2px solid #e2e8f0; color: #475569; padding: 8px 16px; border-radius: 30px; font-weight: bold; font-size: 0.95rem; text-decoration: none; box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: all 0.2s ease; z-index: 1000; }
        .btn-lang-cycle:hover { background-color: #f8fafc; transform: translateY(-2px); border-color: #cbd5e1; color: #0f172a; }

        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 30px 0; } 
        .card { padding: 25px 20px; border-radius: 10px; border: 1px solid #e2e8f0; border-top: 4px solid #0284c7; text-align: center; background: #f8fafc; word-wrap: break-word; } 
        .card h3 { color: #0284c7; margin-top: 0; font-size: 1.1rem; }
        .card p { font-size: 1.1rem; font-weight: bold; margin-top: 10px; color: #334155; }
        
        .log-wrapper { background: #f8fafc; padding: 20px; margin-bottom: 30px; border-radius: 8px; border: 1px solid #e2e8f0; } 
        .log-wrapper h3 { color: #0284c7; text-align: center; margin-top: 0; }
        .log-terminal { background: #0f172a; color: #38bdf8; font-family: monospace; padding: 15px; height: 200px; overflow-y: auto; white-space: pre-wrap; border-radius: 6px; font-size: 0.9rem; } 
        
        .action-section { background: #f0f9ff; padding: 25px; text-align: center; border-radius: 8px; border: 1px solid #bae6fd; } 
        .action-section h3 { color: #0284c7; margin-top: 0; }
        .btn-run { background-color: #0284c7; color: white; padding: 12px 25px; cursor: pointer; border: none; border-radius: 6px; font-weight: bold; transition: background 0.2s; } 
        .btn-run:hover { background-color: #0369a1; }
        
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .alert.success { background: #dcfce7; color: #166534; border-left: 4px solid #22c55e; }

        @media (max-width: 900px) { .grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 600px) {
            .grid { grid-template-columns: 1fr; gap: 15px; }
            .card { padding: 15px; }
            .log-wrapper, .action-section { padding: 15px; }
            .log-terminal { font-size: 0.75rem; height: 250px; } 
            .btn-run { width: 100%; box-sizing: border-box; display: block; }
            .status-circle { top: 12px; left: 12px; }
            .btn-lang-cycle { top: 10px; right: 10px; padding: 6px 12px; font-size: 0.85rem; }
        }
    </style>
</head>
<body>
    
    <a href="<?php echo $pc_encendido ? '../escritorio.php' : '#'; ?>" id="status-circle"
        class="status-circle <?php echo $pc_encendido ? 'online' : 'offline'; ?>"
        title="<?php echo $pc_encendido ? 'Conectar al Escritorio' : 'PC Apagado'; ?>" <?php echo !$pc_encendido ? 'onclick="alert(\'WERKSTATT está apagado. Enciéndelo desde el menú principal.\'); return false;"' : ''; ?>>
    </a>

    <a href="?lang=<?php echo $siguiente_idioma; ?>" class="btn-lang-cycle" title="Cambiar idioma">
        <?php echo $bandera_mostrar; ?> ↻
    </a>

    <div id="principal">
        <h2><?php echo isset($lang['estado_titulo']) ? $lang['estado_titulo'] : 'Estado del Servidor'; ?></h2>
        
        <?php echo $mensaje_accion; ?>

        <div class="grid">
            <div class="card"><h3><?php echo isset($lang['card_uptime']) ? $lang['card_uptime'] : 'Uptime'; ?></h3><p><?php echo htmlspecialchars($uptime); ?></p></div>
            <div class="card"><h3><?php echo isset($lang['card_ram']) ? $lang['card_ram'] : 'RAM'; ?></h3><p><?php echo htmlspecialchars($ram_usage); ?></p></div>
            <div class="card"><h3><?php echo isset($lang['card_db']) ? $lang['card_db'] : 'Database'; ?></h3><p><?php echo $db_size; ?></p></div>
            <div class="card">
                <h3><?php echo isset($lang['card_tareas']) ? $lang['card_tareas'] : 'Tareas'; ?></h3>
                <p><?php echo $total_pendientes; ?> <?php echo isset($lang['text_pendientes']) ? $lang['text_pendientes'] : 'Pendientes'; ?><br>
                <span style="font-size: 0.9rem; color: #64748b;"><?php echo isset($lang['text_de']) ? $lang['text_de'] : 'de'; ?> <?php echo $total_tareas; ?> <?php echo isset($lang['text_totales']) ? $lang['text_totales'] : 'Totales'; ?></span></p>
            </div>
        </div>

        <div class="log-wrapper">
            <h3><?php echo isset($lang['title_live_log']) ? $lang['title_live_log'] : 'Live Access Logs'; ?></h3>
            <div id="live-log" class="log-terminal"><?php echo isset($lang['msg_cargando_logs']) ? $lang['msg_cargando_logs'] : 'Cargando...'; ?></div>
        </div>

        <div class="action-section">
            <h3><?php echo isset($lang['title_disparador']) ? $lang['title_disparador'] : 'Disparador de Python'; ?></h3>
            <p><?php echo isset($lang['desc_disparador']) ? $lang['desc_disparador'] : 'Ejecutar alertas manualmente:'; ?></p>
            <form method="post">
                <button type="submit" name="ejecutar_alertas" class="btn-run"><?php echo isset($lang['btn_ejecutar_python']) ? $lang['btn_ejecutar_python'] : 'Ejecutar alertas.py'; ?></button>
            </form>
        </div>

        <div style="text-align:center; margin-top:30px;">
            <a href="../index.php" class="btn-link" style="background-color: #94a3b8; color: white;">🏠 Volver al Inicio</a> 
        </div>
    </div>
    
    <script>
        function fetchLog() {
            fetch('?get_log=1').then(response => response.text()).then(data => {
                const logDiv = document.getElementById('live-log');
                const isScrolledToBottom = logDiv.scrollHeight - logDiv.clientHeight <= logDiv.scrollTop + 1;
                logDiv.innerHTML = data;
                if (isScrolledToBottom) logDiv.scrollTop = logDiv.scrollHeight;
            });
        }
        setInterval(fetchLog, 2000); fetchLog();
    </script>
</body>
</html>