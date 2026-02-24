<?php
// --- 1. LÓGICA DEL BOTÓN DE ALERTAS ---
$mensaje_accion = "";
if (isset($_POST['ejecutar_alertas'])) {
    // Ejecutamos el script de Python. 
    // Nota SMR: Ajusta la ruta si cambiaste el nombre del archivo.
    $comando = escapeshellcmd("python3 /home/socalbert26/alerta_agenda.py");
    $salida = shell_exec($comando . " 2>&1"); 
    $mensaje_accion = "<div class='alert success'>🚀 <strong>Comando ejecutado:</strong><br>" . nl2br(htmlspecialchars($salida)) . "</div>";
}

// --- 2. RECOPILACIÓN DE DATOS DEL SISTEMA (Linux CLI) ---
// Tiempo encendido
$uptime = shell_exec("uptime -p");

// Uso de RAM (Calcula el porcentaje y los MB usados)
$ram_usage = shell_exec("free -m | awk 'NR==2{printf \"%.1f%% (Usado: %s MB)\", $3*100/$2, $3 }'");

// Uso de Disco (Porcentaje usado y espacio libre en la raíz /)
$disk_usage = shell_exec("df -h / | awk '$NF==\"/\"{printf \"%s (Libre: %s)\", $5, $4}'");

// Carga de CPU (Los últimos 1, 5 y 15 minutos)
$cpu_load = shell_exec("uptime | awk -F'load average:' '{ print $2 }'");

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
    <title>SMR Server Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #121212; color: #e0e0e0; padding: 20px; }
        .container { max-width: 900px; margin: auto; }
        h1 { border-bottom: 2px solid #0078D7; padding-bottom: 10px; color: #ffffff; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; }
        .card { background: #1e1e1e; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.3); border-left: 4px solid #0078D7; }
        .card h3 { margin-top: 0; color: #0078D7; font-size: 1.1rem; }
        .card p { font-size: 1.2rem; margin: 10px 0 0 0; font-weight: bold; }
        .btn-run { background-color: #28a745; color: white; border: none; padding: 15px 25px; font-size: 1.1rem; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%; transition: 0.3s; }
        .btn-run:hover { background-color: #218838; transform: scale(1.02); }
        .alert { background: #17a2b8; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .alert.success { background: #28a745; }
        .footer-links { margin-top: 30px; display: flex; gap: 15px; }
        .btn-link { background: #444; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; text-align: center; flex: 1; }
        .btn-link:hover { background: #555; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Panel de Control del Servidor</h1>
        
        <?php echo $mensaje_accion; ?>

        <div class="grid">
            <div class="card">
                <h3>⏱️ Tiempo Activo (Uptime)</h3>
                <p><?php echo htmlspecialchars($uptime); ?></p>
            </div>
            <div class="card">
                <h3>🧠 Uso de RAM</h3>
                <p><?php echo htmlspecialchars($ram_usage); ?></p>
            </div>
            <div class="card">
                <h3>💾 Almacenamiento (Raíz)</h3>
                <p><?php echo htmlspecialchars($disk_usage); ?></p>
            </div>
            <div class="card">
                <h3>⚙️ Carga de CPU</h3>
                <p><?php echo htmlspecialchars($cpu_load); ?></p>
            </div>

            <div class="card" style="border-left-color: #f39c12;">
                <h3>📁 Tamaño SQLite</h3>
                <p><?php echo $db_size; ?></p>
            </div>
            <div class="card" style="border-left-color: #f39c12;">
                <h3>📝 Estado de Tareas</h3>
                <p><?php echo $total_pendientes; ?> Pendientes / <?php echo $total_tareas; ?> Totales</p>
            </div>
        </div>

        <div style="margin-top: 30px; background: #1e1e1e; padding: 20px; border-radius: 8px; border: 1px solid #333;">
            <h3 style="margin-top: 0; color: #fff;">🤖 Disparador Manual de Telegram</h3>
            <p style="color: #aaa; font-size: 0.9rem; margin-bottom: 15px;">Ejecuta el script de Python para escanear las tareas pendientes y enviar las notificaciones individuales ahora mismo.</p>
            <form method="post">
                <button type="submit" name="ejecutar_alertas" class="btn-run">▶️ Ejecutar Python Script</button>
            </form>
        </div>

        <div class="footer-links">
            <a href="index.php" class="btn-link">⬅ Volver a la Agenda</a>
        </div>
    </div>
</body>
</html>