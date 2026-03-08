<?php
// 1. Subimos dos niveles (agenda -> paginas -> raíz) y entramos en controladores
require_once '../../code/controladores/idiomas.php';

$idioma_actual = isset($_SESSION['idioma_seleccionado']) ? $_SESSION['idioma_seleccionado'] : 'de';
            
$rotacion = [
    'cat' => 'de',
    'de'  => 'en',
    'en'  => 'es',
    'es'  => 'cat'
];
            
$siguiente_idioma = isset($rotacion[$idioma_actual]) ? $rotacion[$idioma_actual] : 'de';
            
$banderas = [
    'cat' => 'CAT',
    'de'  => '🇩🇪 DE',
    'en'  => '🇬🇧 EN',
    'es'  => '🇪🇸 ES'
];
$bandera_mostrar = isset($banderas[$idioma_actual]) ? $banderas[$idioma_actual] : '🇩🇪 DE';

// 2. Comprobación de estado del WERKSTATT para el menú unificado
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
    <title><?php echo isset($lang['titulo_agenda_menu']) ? $lang['titulo_agenda_menu'] : 'Erinnerungen Machen'; ?></title>
    <link rel="stylesheet" href="../../css/menu.css">
    
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
        .btn-werkstatt { display: block; width: 100%; text-align: center; padding: 14px; border-radius: 8px; font-size: 1rem; font-weight: 800; letter-spacing: 2px; cursor: pointer; transition: 0.3s ease; border: none; font-family: inherit; margin-bottom: 10px; }
        .btn-werkstatt.offline { background-color: #dc2626; color: #ffffff; }
        .btn-werkstatt.online { background-color: #22c55e; color: #000000; box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3); }
        .btn-werkstatt:hover { transform: translateY(-2px); opacity: 0.9; }

        /* Botón de idioma rotativo en la esquina */
        .btn-lang-cycle {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #ffffff;
            border: 2px solid #e2e8f0;
            color: #475569;
            padding: 8px 16px;
            border-radius: 30px;
            font-weight: bold;
            font-size: 0.95rem;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
            z-index: 1000;
        }
        .btn-lang-cycle:hover {
            background-color: #f8fafc;
            transform: translateY(-2px);
            border-color: #cbd5e1;
            color: #0f172a;
        }

        @media (max-width: 480px) {
            .btn-lang-cycle { top: 10px; right: 10px; padding: 6px 12px; font-size: 0.85rem; }
            .status-circle { top: 12px; left: 12px; }
        }
    </style>
</head>

<body>

    <a href="<?php echo $pc_encendido ? '../../escritorio.php' : '#'; ?>" id="status-circle"
        class="status-circle <?php echo $pc_encendido ? 'online' : 'offline'; ?>"
        title="<?php echo $pc_encendido ? 'Conectar al Escritorio' : 'PC Apagado'; ?>" <?php echo !$pc_encendido ? 'onclick="alert(\'WERKSTATT está apagado.\'); return false;"' : ''; ?>>
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
            <a href="#" class="btn-link" style="background-color: #e2e8f0; font-weight: bold; pointer-events: none;">
                ▶ <?php echo isset($lang['btn_agenda']) ? $lang['btn_agenda'] : 'Agenda'; ?>
            </a>
            <a href="../Personal/index.php" class="btn-link">
                <?php echo isset($lang['btn_personal']) ? $lang['btn_personal'] : 'Personal'; ?>
            </a>
            <a href="../../academico.php" class="btn-link">
                <?php echo isset($lang['btn_academico']) ? $lang['btn_academico'] : 'Académico'; ?>
            </a>
            <a href="../estadoServer.php" class="btn-link">
                <?php echo isset($lang['btn_estado_server']) ? $lang['btn_estado_server'] : 'Estado del servidor'; ?>
            </a>
        </nav>
        
        <hr style="border: 1px solid #e2e8f0; margin: 30px 0;">

        <h2><?php echo isset($lang['menu_acciones']) ? $lang['menu_acciones'] : 'Aktionen (Agenda)'; ?></h2>

        <nav class="menu-container">
            <a href="./crear_tareas.php" class="btn-link"><?php echo isset($lang['btn_crear_recordatorio']) ? $lang['btn_crear_recordatorio'] : 'Crear Recordatorio'; ?></a>
            <a href="./lista_pendientes.php" class="btn-link"><?php echo isset($lang['btn_tareas_pendientes']) ? $lang['btn_tareas_pendientes'] : 'Tareas Pendientes'; ?></a>
            <a href="./lista_completadas.php" class="btn-link"><?php echo isset($lang['btn_tareas_completadas']) ? $lang['btn_tareas_completadas'] : 'Tareas Completadas'; ?></a>
        </nav>

        <div style="text-align:center; margin-top:30px;">
            <a href="../../index.php" class="btn-link" style="background-color: #94a3b8; color: white;">🏠 Volver al Inicio</a> 
        </div>
    </div>

    <script>
        document.getElementById('btn-werkstatt').addEventListener('click', function (e) {
            e.preventDefault();
            // Ruta ajustada para subir dos niveles y entrar en controladores
            fetch('../../code/controladores/control.php?accion=estado')
                .then(response => response.text())
                .then(estado => {
                    if (estado === 'ON') {
                        if (confirm("El equipo WERKSTATT ya está encendido. ¿Deseas apagarlo?")) {
                            fetch('../../code/controladores/control.php?accion=apagar')
                                .then(res => res.text())
                                .then(resultado => {
                                    alert("Orden de apagado enviada.");
                                    setTimeout(() => location.reload(), 3000);
                                });
                        }
                    } else {
                        if (confirm("El equipo WERKSTATT está apagado. ¿Deseas encenderlo?")) {
                            fetch('../../code/controladores/control.php?accion=encender')
                                .then(res => res.text())
                                .then(resultado => {
                                    alert("Enviando Paquete Mágico para despertar a WERKSTATT...");
                                    setTimeout(() => location.reload(), 15000);
                                });
                        }
                    }
                });
        });
    </script>
</body>

</html>