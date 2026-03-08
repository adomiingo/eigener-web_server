<?php
// 1. Cargamos el motor de idiomas (Ruta actualizada a la nueva carpeta)
require_once __DIR__ . '/code/controladores/idiomas.php';

$idioma_actual = isset($_SESSION['idioma_seleccionado']) ? $_SESSION['idioma_seleccionado'] : 'de';

// Rotación estricta de idiomas
$rotacion = [
    'cat' => 'de',
    'de' => 'en',
    'en' => 'es',
    'es' => 'cat'
];
$siguiente_idioma = isset($rotacion[$idioma_actual]) ? $rotacion[$idioma_actual] : 'de';

$banderas = [
    'cat' => 'CAT',
    'de' => '🇩🇪 DE',
    'en' => '🇬🇧 EN',
    'es' => '🇪🇸 ES'
];
$bandera_mostrar = isset($banderas[$idioma_actual]) ? $banderas[$idioma_actual] : '🇩🇪 DE';

// --- CONFIGURACIÓN WERKSTATT ---
$ip_casa = 'motxitorouter.duckdns.org'; // Unificamos para usar siempre tu DDNS
$puerto_rdp = 54321; // El puerto de tu router para RDP

// 2. Comprobación de estado optimizada (Rápido, 1 segundo max, sin depender de Tailscale)
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
    <title><?php echo isset($lang['titulo_index']) ? $lang['titulo_index'] : 'Main Brain'; ?></title>
    <link rel="stylesheet" href="./css/menu.css">

    <style>
        /* =========================================
           ESTILOS ESPECIALES PARA WERKSTATT
           ========================================= */
        .status-circle { position: absolute; top: 20px; left: 20px; width: 24px; height: 24px; border-radius: 50%; z-index: 1000; cursor: pointer; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); transition: transform 0.2s ease; }
        .status-circle:hover { transform: scale(1.1); }
        .status-circle.offline { background-color: #dc2626; }
        .status-circle.online { background-color: #22c55e; animation: pulse-lila 1.5s infinite; }
        @keyframes pulse-lila {
            0% { opacity: 1; box-shadow: 0 0 0 0 rgba(168, 85, 247, 0.7); }
            70% { opacity: 0.6; box-shadow: 0 0 0 15px rgba(168, 85, 247, 0); }
            100% { opacity: 1; box-shadow: 0 0 0 0 rgba(168, 85, 247, 0); }
        }
        .btn-werkstatt { display: block; width: 100%; text-align: center; padding: 14px; border-radius: 8px; font-size: 1rem; font-weight: 800; letter-spacing: 2px; cursor: pointer; transition: 0.3s ease; border: none; font-family: inherit; }
        .btn-werkstatt.offline { background-color: #dc2626; color: #ffffff; }
        .btn-werkstatt.online { background-color: #22c55e; color: #000000; box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3); }
        .btn-werkstatt:hover { transform: translateY(-2px); opacity: 0.9; }
        @media (max-width: 480px) { .status-circle { top: 12px; left: 12px; } }
    </style>
</head>

<body>

    <a href="<?php echo $pc_encendido ? 'escritorio.php' : '#'; ?>" id="status-circle"
        class="status-circle <?php echo $pc_encendido ? 'online' : 'offline'; ?>"
        title="<?php echo $pc_encendido ? 'Conectar al Escritorio' : 'PC Apagado'; ?>" <?php echo !$pc_encendido ? 'onclick="alert(\'WERKSTATT está apagado. Enciéndelo desde el menú primero.\'); return false;"' : ''; ?>>
    </a>

    <a href="?lang=<?php echo $siguiente_idioma; ?>" class="btn-lang-cycle" title="Cambiar idioma">
        <?php echo $bandera_mostrar; ?> ↻
    </a>

    <div id="principal">

        <h2><?php echo isset($lang['menu_principal']) ? $lang['menu_principal'] : 'Menú Principal'; ?></h2>

        <nav class="menu-container">

            <button type="button" id="btn-werkstatt" class="btn-werkstatt <?php echo $pc_encendido ? 'online' : 'offline'; ?>">
                WERKSTATT
            </button>

            <a href="./paginas/agenda/agendaMenu.php" class="btn-link">
                <?php echo isset($lang['btn_agenda']) ? $lang['btn_agenda'] : 'Agenda'; ?>
            </a>

            <a href="./paginas/Personal/index.php" class="btn-link">
                <?php echo isset($lang['btn_personal']) ? $lang['btn_personal'] : 'Personal'; ?>
            </a>

            <a href="academico.php" class="btn-link">
                <?php echo isset($lang['btn_academico']) ? $lang['btn_academico'] : 'Académico'; ?>
            </a>

            <a href="./paginas/estadoServer.php" class="btn-link">
                <?php echo isset($lang['btn_estado_server']) ? $lang['btn_estado_server'] : 'Estado del servidor'; ?>
            </a>
        </nav>
    </div>

    <script>
        document.getElementById('btn-werkstatt').addEventListener('click', function (e) {
            e.preventDefault();

            // 1. Preguntamos al servidor si el PC está ON u OFF (Ruta ajustada)
            fetch('code/controladores/control.php?accion=estado')
                .then(response => response.text())
                .then(estado => {
                    if (estado === 'ON') {
                        // 2. Si está encendido, lanzamos la pregunta de seguridad
                        if (confirm("El equipo WERKSTATT ya está encendido. ¿Deseas apagarlo?")) {
                            fetch('code/controladores/control.php?accion=apagar')
                                .then(res => res.text())
                                .then(resultado => {
                                    alert("Orden de apagado enviada.");
                                    setTimeout(() => location.reload(), 3000); // Recarga para actualizar colores
                                });
                        }
                    } else {
                        // 3. Si está apagado, lanzamos el Wake On LAN
                        if (confirm("El equipo WERKSTATT está apagado. ¿Deseas encenderlo?")) {
                            fetch('code/controladores/control.php?accion=encender')
                                .then(res => res.text())
                                .then(resultado => {
                                    alert("Enviando Paquete Mágico para despertar a WERKSTATT...");
                                    setTimeout(() => location.reload(), 15000); // Recarga para actualizar colores
                                });
                        }
                    }
                });
        });
    </script>
</body>

</html>