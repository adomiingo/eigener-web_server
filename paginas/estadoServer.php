<?php
// --- 0. ENDPOINT PARA EL LOG EN VIVO (AJAX) ---
if (isset($_GET['get_log'])) {
    // Leemos las últimas 15 líneas del log de accesos de Nginx
    $log = shell_exec("tail -n 15 /var/log/nginx/access.log 2>&1");
    if (empty($log)) {
        echo "Esperando registros... (O el usuario web no tiene permisos de lectura en /var/log/nginx/access.log)";
    } else {
        echo htmlspecialchars($log);
    }
    exit; // Detenemos la ejecución aquí para devolver solo texto al JavaScript
}

// --- 1. LÓGICA DEL BOTÓN DE ALERTAS ---
$mensaje_accion = "";
if (isset($_POST['ejecutar_alertas'])) {
    $comando = escapeshellcmd("python3 ../code/alertas.py");
    $salida = shell_exec($comando . " 2>&1"); 
    $mensaje_accion = "<div class='alert success'>🚀 <strong>Comando ejecutado:</strong><br>" . nl2br(htmlspecialchars($salida)) . "</div>";
}

// --- 2. RECOPILACIÓN DE DATOS DEL SISTEMA ---
$uptime = shell_exec("uptime -p");
$ram_usage = shell_exec("free -m | awk 'NR==2{printf \"%.1f%% (Usado: %s MB)\", $3*100/$2, $3 }'");

// --- 3. INFORMACIÓN DE LA BASE DE DATOS ---
$db_path = "/var/www/ubungen/kalender.db";
$db_size = file_exists($db_path) ? round(filesize($db_path) / 1024, 2) . " KB" : "No encontrada";

try {
    $db = new PDO("sqlite:$db_path");
    $stmt_tot = $db->query("SELECT COUNT(*) FROM aufgaben");
    $total_tareas = $stmt_tot->fetchColumn();
    
    $stmt_pend = $db->query("SELECT COUNT(*) FROM aufgaben WHERE zustand = 'Ausstehen'");
    $total_pendientes = $stmt_pend->fetchColumn();
} catch (Exception $e) {
    $total_tareas = "Error";
    $total_pendientes = "Error";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BRAIN STATUS</title>
    <style>
        /* Estética Minimalista: Blanco y Azul */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f4f8; color: #334155; padding: 20px; margin: 0; }
        .container { max-width: 900px; margin: auto; background: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        
        h1 { color: #0284c7; font-weight: 300; text-align: center; margin-top: 0; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0; letter-spacing: 1px; }
        h3 { color: #0284c7; margin-top: 0; font-size: 1rem; text-transform: uppercase; letter-spacing: 0.5px; }
        
        /* Grid de Tarjetas */
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 30px; margin-bottom: 40px; }
        .card { background: #ffffff; padding: 25px 20px; border-radius: 10px; border: 1px solid #e2e8f0; border-top: 4px solid #0284c7; text-align: center; transition: transform 0.2s; }
        .card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(2, 132, 199, 0.1); }
        .card p { font-size: 1.3rem; margin: 10px 0 0 0; font-weight: bold; color: #0f172a; }

        /* Terminal de Logs */
        .log-wrapper { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 20px; margin-bottom: 30px; }
        .log-terminal { background: #0f172a; color: #38bdf8; font-family: 'Courier New', Courier, monospace; padding: 15px; border-radius: 6px; height: 200px; overflow-y: auto; font-size: 0.85rem; white-space: pre-wrap; line-height: 1.4; }
        
        /* Sección de Acción */
        .action-section { background: #f0f9ff; padding: 25px; border-radius: 8px; border: 1px solid #bae6fd; text-align: center; }
        .action-section h3 { color: #0369a1; }
        .action-section p { color: #475569; font-size: 0.95rem; margin-bottom: 20px; }
        
        /* Botones y Alertas */
        .btn-run { background-color: #0284c7; color: white; border: none; padding: 12px 25px; font-size: 1rem; border-radius: 6px; cursor: pointer; font-weight: 600; transition: background 0.3s; }
        .btn-run:hover { background-color: #0369a1; }
        .alert { background: #e0f2fe; color: #0369a1; padding: 15px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #0284c7; }
        .alert.success { background: #dcfce7; color: #166534; border-left-color: #22c55e; }
        
        .footer-links { margin-top: 30px; text-align: center; }
        .btn-link { display: inline-block; background: #e2e8f0; color: #475569; padding: 10px 25px; text-decoration: none; border-radius: 6px; font-weight: 500; transition: background 0.2s; }
        .btn-link:hover { background: #cbd5e1; color: #0f172a; }
    </style>
</head>
<body>
    <div class="container">
        <h1>MAIN BRAIN</h1>
        
        <?php echo $mensaje_accion; ?>

        <div class="grid">
            <div class="card">
                <h3>⏱️ Uptime</h3>
                <p><?php echo htmlspecialchars($uptime); ?></p>
            </div>
            <div class="card">
                <h3>🧠 RAM Usage</h3>
                <p><?php echo htmlspecialchars($ram_usage); ?></p>
            </div>
            <div class="card" style="border-top-color: #0ea5e9;">
                <h3>📁 Tamaño DB</h3>
                <p><?php echo $db_size; ?></p>
            </div>
            <div class="card" style="border-top-color: #0ea5e9;">
                <h3>📝 Estado Tareas</h3>
                <p><?php echo $total_pendientes; ?> Pendientes<br><span style="font-size: 0.9rem; color: #64748b; font-weight: normal;">de <?php echo $total_tareas; ?> Totales</span></p>
            </div>
        </div>

        <div class="log-wrapper">
            <h3>📡 Server Live Log (Nginx Access)</h3>
            <div id="live-log" class="log-terminal">Cargando registros del servidor...</div>
        </div>

        <div class="action-section">
            <h3>🤖 Disparador Manual de Telegram</h3>
            <p>Ejecuta el script de Python para escanear las tareas pendientes y enviar las notificaciones individuales ahora mismo.</p>
            <form method="post">
                <button type="submit" name="ejecutar_alertas" class="btn-run">▶️ Ejecutar Python Script</button>
            </form>
        </div>

        <div class="footer-links">
            <a href="../index.html" class="btn-link">⬅ Página principal</a>
        </div>
    </div>

    <script>
        function fetchLog() {
            fetch('?get_log=1')
                .then(response => response.text())
                .then(data => {
                    const logDiv = document.getElementById('live-log');
                    const isScrolledToBottom = logDiv.scrollHeight - logDiv.clientHeight <= logDiv.scrollTop + 1;
                    
                    logDiv.innerHTML = data;
                    
                    // Solo hace autoscroll hacia abajo si el usuario ya estaba abajo del todo
                    if (isScrolledToBottom) {
                        logDiv.scrollTop = logDiv.scrollHeight;
                    }
                })
                .catch(error => console.error('Error obteniendo el log:', error));
        }

        // Actualiza el log cada 2 segundos (2000 milisegundos)
        setInterval(fetchLog, 2000);
        fetchLog(); // Ejecución inmediata al cargar la página
    </script>
</body>
</html>