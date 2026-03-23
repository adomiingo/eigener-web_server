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

// 3. Ejecución de Alertas Python Individuales
$mensaje_accion = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['script_python'])) {
    $script_elegido = $_POST['script_python'];
    $scripts_validos = [
        'general' => 'alertas.py',
        'academicas' => 'academicas.py',
        'personales' => 'personales.py',
        'ideas' => 'server_ideas.py',
        'matutinas' => 'matutinas.py',
        'casa' => 'alertas_casa.py'
    ];

    if (array_key_exists($script_elegido, $scripts_validos)) {
        $archivo_py = $scripts_validos[$script_elegido];
        $comando = escapeshellcmd("python3 ../code/scripts_sistema/" . $archivo_py);
        $salida = shell_exec($comando . " 2>&1"); 
        
        $titulos = [
            'general' => 'Alertas Generales',
            'academicas' => 'Alertas Académicas',
            'personales' => 'Alertas Personales',
            'ideas' => 'Ideas del Servidor',
            'matutinas' => 'Recordatorios Matutinos',
            'casa' => 'Recordatorios en Casa'
        ];
        
        $titulo_ejecutado = $titulos[$script_elegido];
        $mensaje_accion = "<div class='alert success'><strong>{$titulo_ejecutado}:</strong> " . (isset($lang['msg_comando_ejecutado']) ? $lang['msg_comando_ejecutado'] : 'Ejecutado correctamente.') . "<br><br><span style='font-family:monospace; font-size:0.9em;'>" . nl2br(htmlspecialchars($salida)) . "</span></div>";
    }
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

// 5. Comprobación de estado del WERKSTATT
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
        /* Reset y contención para PC */
        body { background-color: #f1f5f9; margin: 0; padding: 20px; font-family: 'Segoe UI', system-ui, sans-serif; display: flex; justify-content: center; }
        
        #principal { width: 100%; max-width: 1000px; background: #ffffff; padding: 40px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); position: relative; }
        h2 { color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; margin-top: 0; font-size: 1.8rem; text-align: center; }
        
        /* Controles Top */
        .status-circle { position: absolute; top: 25px; left: 25px; width: 24px; height: 24px; border-radius: 50%; z-index: 1000; cursor: pointer; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); transition: transform 0.2s ease; }
        .status-circle:hover { transform: scale(1.1); }
        .status-circle.offline { background-color: #ef4444; }
        .status-circle.online { background-color: #22c55e; animation: pulse-verde 2s infinite; }
        @keyframes pulse-verde { 0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); } 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); } }

        .btn-lang-cycle { position: absolute; top: 20px; right: 25px; background-color: #ffffff; border: 2px solid #e2e8f0; color: #475569; padding: 8px 16px; border-radius: 30px; font-weight: bold; font-size: 0.95rem; text-decoration: none; box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: all 0.2s ease; }
        .btn-lang-cycle:hover { background-color: #f8fafc; transform: translateY(-2px); border-color: #cbd5e1; color: #0f172a; }

        /* Grid de métricas */
        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 30px 0; } 
        .card { padding: 25px 20px; border-radius: 12px; border: 1px solid #e2e8f0; border-top: 4px solid #0284c7; text-align: center; background: #f8fafc; box-shadow: 0 2px 5px rgba(0,0,0,0.02); } 
        .card h3 { color: #64748b; margin: 0 0 10px 0; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .card p { font-size: 1.2rem; font-weight: 700; margin: 0; color: #0f172a; }
        
        /* Terminal Logs */
        .log-wrapper { background: #f8fafc; padding: 20px; margin-bottom: 30px; border-radius: 12px; border: 1px solid #e2e8f0; } 
        .log-wrapper h3 { color: #334155; margin: 0 0 15px 0; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; }
        .log-terminal { background: #0f172a; color: #38bdf8; font-family: 'Consolas', monospace; padding: 15px; height: 180px; overflow-y: auto; white-space: pre-wrap; border-radius: 8px; font-size: 0.85rem; line-height: 1.4; } 
        
        /* Disparadores Python */
        .action-section { background: #f0f9ff; padding: 25px; border-radius: 12px; border: 1px solid #bae6fd; } 
        .action-section h3 { color: #0369a1; margin: 0 0 20px 0; text-align: center; font-size: 1.2rem; }
        
        .scripts-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
        .btn-run { background-color: #ffffff; color: #0284c7; padding: 12px 15px; cursor: pointer; border: 2px solid #38bdf8; border-radius: 8px; font-weight: 600; font-size: 0.95rem; transition: all 0.2s ease; display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; box-sizing: border-box; } 
        .btn-run:hover { background-color: #0284c7; color: #ffffff; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(2, 132, 199, 0.2); }
        .btn-run.master { grid-column: 1 / -1; background-color: #0284c7; color: white; border-color: #0284c7; }
        .btn-run.master:hover { background-color: #0369a1; }

        /* Alertas de resultado */
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 25px; animation: slideIn 0.3s ease-out; }
        .alert.success { background: #dcfce7; color: #166534; border-left: 5px solid #22c55e; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        /* Botón volver */
        .btn-back { display: inline-block; background-color: #64748b; color: white; padding: 10px 25px; border-radius: 30px; text-decoration: none; font-weight: bold; transition: 0.2s; margin-top: 30px; }
        .btn-back:hover { background-color: #475569; }

        /* Responsive */
        @media (max-width: 850px) { 
            .grid, .scripts-grid { grid-template-columns: repeat(2, 1fr); } 
            .btn-run.master { grid-column: span 2; }
        }
        @media (max-width: 600px) {
            body { padding: 10px; }
            #principal { padding: 25px 15px; border-radius: 12px; }
            h2 { font-size: 1.4rem; padding-top: 30px; }
            .grid { grid-template-columns: 1fr; gap: 15px; }
            .scripts-grid { grid-template-columns: 1fr; }
            .btn-run.master { grid-column: 1; }
            .card { padding: 15px; }
            .log-terminal { height: 250px; font-size: 0.75rem; } 
            .status-circle { top: 15px; left: 15px; }
            .btn-lang-cycle { top: 15px; right: 15px; padding: 6px 12px; font-size: 0.85rem; }
        }
    </style>
</head>
<body>
    <div id="principal">
        <a href="<?php echo $pc_encendido ? '../escritorio.php' : '#'; ?>" id="status-circle"
            class="status-circle <?php echo $pc_encendido ? 'online' : 'offline'; ?>"
            title="<?php echo $pc_encendido ? 'Conectar al Escritorio' : 'PC Apagado'; ?>" <?php echo !$pc_encendido ? 'onclick="alert(\'WERKSTATT está apagado. Enciéndelo desde el menú principal.\'); return false;"' : ''; ?>>
        </a>

        <a href="?lang=<?php echo $siguiente_idioma; ?>" class="btn-lang-cycle" title="Cambiar idioma">
            <?php echo $bandera_mostrar; ?> ↻
        </a>

        <h2><?php echo isset($lang['estado_titulo']) ? $lang['estado_titulo'] : 'Estado del Servidor'; ?></h2>
        
        <?php echo $mensaje_accion; ?>

        <div class="grid">
            <div class="card"><h3><?php echo isset($lang['card_uptime']) ? $lang['card_uptime'] : 'Uptime'; ?></h3><p><?php echo htmlspecialchars($uptime); ?></p></div>
            <div class="card"><h3><?php echo isset($lang['card_ram']) ? $lang['card_ram'] : 'RAM'; ?></h3><p><?php echo htmlspecialchars($ram_usage); ?></p></div>
            <div class="card"><h3><?php echo isset($lang['card_db']) ? $lang['card_db'] : 'Database'; ?></h3><p><?php echo $db_size; ?></p></div>
            <div class="card">
                <h3><?php echo isset($lang['card_tareas']) ? $lang['card_tareas'] : 'Tareas'; ?></h3>
                <p><?php echo $total_pendientes; ?> <span style="font-size: 0.9rem; font-weight: normal; color: #64748b;">pendientes</span><br>
                <span style="font-size: 0.8rem; font-weight: normal; color: #94a3b8;">de <?php echo $total_tareas; ?> totales</span></p>
            </div>
        </div>

        <div class="log-wrapper">
            <h3>📡 Access Logs (Nginx)</h3>
            <div id="live-log" class="log-terminal"><?php echo isset($lang['msg_cargando_logs']) ? $lang['msg_cargando_logs'] : 'Cargando...'; ?></div>
        </div>

        <div class="action-section">
            <h3>Disparadores de Telegram</h3>
            <form method="post" class="scripts-grid">
                <button type="submit" name="script_python" value="matutinas" class="btn-run">☕ Mañaneros</button>
                <button type="submit" name="script_python" value="casa" class="btn-run">🛋️ En Casa</button>
                <button type="submit" name="script_python" value="academicas" class="btn-run">🎓 Académicas</button>
                <button type="submit" name="script_python" value="personales" class="btn-run">🏠 Personales</button>
                <button type="submit" name="script_python" value="ideas" class="btn-run">💡 Ideas Server</button>
                <button type="submit" name="script_python" value="general" class="btn-run master">🚀 Disparar Todo (General)</button>
            </form>
        </div>

        <div style="text-align:center;">
            <a href="../index.php" class="btn-back">🏠 Volver al Inicio</a> 
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