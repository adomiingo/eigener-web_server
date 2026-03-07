<?php
// 1. Cargamos el motor de idiomas
require_once 'idiomas.php';

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
$mac_pc = 'D8-43-AE-4F-75-6C';
$ip_pc = '100.80.192.32'; // Tu IP de Tailscale para el Ping y Guacamole
$dominio_duckdns = 'adomiingoagenda.duckdns.org'; 

// 2. Lógica para encender WERKSTATT
$wol_enviado = false;
if (isset($_POST['wake_werkstatt'])) {
    $mac_hex = str_replace(array(':', '-'), '', $mac_pc);
    $mac_bin = pack('H12', $mac_hex);
    $magic_packet = str_repeat(chr(0xff), 6) . str_repeat($mac_bin, 16);

    // Resolvemos la IP pública de tu casa a través de DuckDNS
    $ip_publica_casa = gethostbyname($dominio_duckdns);

    // Enviamos el paquete al puerto 9 UDP de tu IP pública (el que abrimos en el ZTE)
    $fp = @fsockopen('udp://' . $ip_publica_casa, 9, $errno, $errstr, 2);
    if ($fp) {
        fwrite($fp, $magic_packet);
        fclose($fp);
        $wol_enviado = true;
    }
}

// 3. Comprobación de estado (El Chivato)
// Hacemos 1 ping rápido. Si devuelve 0, está encendido.
exec("ping -c 1 -W 1 " . escapeshellarg($ip_pc) . " > /dev/null 2>&1", $output, $resultado_ping);
$pc_encendido = ($resultado_ping === 0);
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
        
        /* El círculo indicador (arriba a la izquierda) */
        .status-circle {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            z-index: 1000;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease;
        }
        
        .status-circle:hover {
            transform: scale(1.1);
        }

        .status-circle.offline {
            background-color: #dc2626; /* Rojo estático */
        }

        .status-circle.online {
            background-color: #22c55e; /* Verde */
            animation: pulse-green 1.5s infinite; /* Intermitencia */
        }

        /* Animación del parpadeo verde */
        @keyframes pulse-green {
            0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }

        /* Botón del menú de Werkstatt */
        .btn-werkstatt {
            display: block;
            width: 100%;
            text-align: center;
            padding: 14px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: 2px;
            cursor: pointer;
            transition: 0.3s ease;
            border: none;
            font-family: inherit;
        }

        .btn-werkstatt.offline {
            background-color: #dc2626; /* Fondo rojo */
            color: #ffffff; /* Letras blancas */
        }

        .btn-werkstatt.online {
            background-color: #22c55e; /* Fondo verde */
            color: #000000; /* Letras negras */
            box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
        }

        .btn-werkstatt:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        @media (max-width: 480px) {
            .status-circle { top: 12px; left: 12px; }
        }
    </style>
</head>

<body>

    <a href="<?php echo $pc_encendido ? 'escritorio.php' : '#'; ?>" 
       id="status-circle" 
       class="status-circle <?php echo $pc_encendido ? 'online' : 'offline'; ?>"
       title="<?php echo $pc_encendido ? 'Conectar al Escritorio' : 'PC Apagado'; ?>"
       <?php echo !$pc_encendido ? 'onclick="alert(\'WERKSTATT está apagado. Enciéndelo desde el menú primero.\'); return false;"' : ''; ?>>
    </a>

    <a href="?lang=<?php echo $siguiente_idioma; ?>" class="btn-lang-cycle" title="Cambiar idioma">
        <?php echo $bandera_mostrar; ?> ↻
    </a>

    <div id="principal">

        <h2><?php echo isset($lang['menu_principal']) ? $lang['menu_principal'] : 'Menú Principal'; ?></h2>

        <nav class="menu-container">
            
            <form method="post" style="margin: 0;" onsubmit="return confirm('¿Confirmar acción sobre WERKSTATT?');">
                <button type="submit" name="wake_werkstatt" class="btn-werkstatt <?php echo $pc_encendido ? 'online' : 'offline'; ?>">
                    WERKSTATT
                </button>
            </form>

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

    <?php if ($wol_enviado && !$pc_encendido): ?>
    <script>
        setTimeout(function() {
            window.location.href = window.location.pathname;
        }, 15000);
    </script>
    <?php endif; ?>

</body>

</html>