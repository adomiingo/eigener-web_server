<?php
require_once '../idiomas.php'; // Solo sube un nivel porque está en /paginas/

if (isset($_GET['get_log'])) {
    $log = shell_exec("tail -n 15 /var/log/nginx/access.log 2>&1");
    echo empty($log) ? $lang['msg_esperando_logs'] : htmlspecialchars($log);
    exit;
}

$mensaje_accion = "";
if (isset($_POST['ejecutar_alertas'])) {
    $comando = escapeshellcmd("python3 ../code/alertas.py");
    $salida = shell_exec($comando . " 2>&1"); 
    $mensaje_accion = "<div class='alert success'>{$lang['msg_comando_ejecutado']}<br>" . nl2br(htmlspecialchars($salida)) . "</div>";
}

$uptime = shell_exec("uptime -p");
$ram_usage = shell_exec("free -m | awk 'NR==2{printf \"%.1f%% (Usado: %s MB)\", $3*100/$2, $3 }'");

$db_path = "/var/www/ubungen/kalender.db";
$db_size = file_exists($db_path) ? round(filesize($db_path) / 1024, 2) . " KB" : $lang['msg_no_encontrada'];

try {
    $db = new PDO("sqlite:$db_path");
    $total_tareas = $db->query("SELECT COUNT(*) FROM aufgaben")->fetchColumn();
    $total_pendientes = $db->query("SELECT COUNT(*) FROM aufgaben WHERE zustand = 'Ausstehen'")->fetchColumn();
} catch (Exception $e) { $total_tareas = $lang['msg_error']; $total_pendientes = $lang['msg_error']; }
?>
<!DOCTYPE html>
<html lang="<?php echo isset($_SESSION['idioma_seleccionado']) ? $_SESSION['idioma_seleccionado'] : 'de'; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $lang['estado_titulo']; ?></title>
    <style> body { font-family: 'Segoe UI', sans-serif; background-color: #f0f4f8; padding: 20px; margin: 0; } .container { max-width: 900px; margin: auto; background: #ffffff; padding: 40px; border-radius: 12px; } h1, h3 { color: #0284c7; text-align: center; } .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0; } .card { padding: 25px 20px; border-radius: 10px; border: 1px solid #e2e8f0; border-top: 4px solid #0284c7; text-align: center; } .log-wrapper { background: #f8fafc; padding: 20px; margin-bottom: 30px; } .log-terminal { background: #0f172a; color: #38bdf8; font-family: monospace; padding: 15px; height: 200px; overflow-y: auto; white-space: pre-wrap; } .action-section { background: #f0f9ff; padding: 25px; text-align: center; } .btn-run { background-color: #0284c7; color: white; padding: 12px 25px; cursor: pointer; border:none; border-radius: 6px; } .btn-link { background: #e2e8f0; color: #475569; padding: 10px 25px; text-decoration: none; border-radius: 6px; } </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $lang['estado_titulo']; ?></h1>
        <?php echo $mensaje_accion; ?>

        <div class="grid">
            <div class="card"><h3><?php echo $lang['card_uptime']; ?></h3><p><?php echo htmlspecialchars($uptime); ?></p></div>
            <div class="card"><h3><?php echo $lang['card_ram']; ?></h3><p><?php echo htmlspecialchars($ram_usage); ?></p></div>
            <div class="card"><h3><?php echo $lang['card_db']; ?></h3><p><?php echo $db_size; ?></p></div>
            <div class="card"><h3><?php echo $lang['card_tareas']; ?></h3><p><?php echo $total_pendientes; ?> <?php echo $lang['text_pendientes']; ?><br><span style="font-size: 0.9rem; color: #64748b;"><?php echo $lang['text_de']; ?> <?php echo $total_tareas; ?> <?php echo $lang['text_totales']; ?></span></p></div>
        </div>

        <div class="log-wrapper">
            <h3><?php echo $lang['title_live_log']; ?></h3>
            <div id="live-log" class="log-terminal"><?php echo $lang['msg_cargando_logs']; ?></div>
        </div>

        <div class="action-section">
            <h3><?php echo $lang['title_disparador']; ?></h3>
            <p><?php echo $lang['desc_disparador']; ?></p>
            <form method="post"><button type="submit" name="ejecutar_alertas" class="btn-run"><?php echo $lang['btn_ejecutar_python']; ?></button></form>
        </div>

        <div class="footer-links" style="text-align:center; margin-top:30px;">
            <a href="../index.php" class="btn-link"><?php echo $lang['btn_pagina_principal']; ?></a> </div>
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