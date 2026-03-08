<?php
// Motor de idiomas y configuración (Ruta actualizada)
require_once '../../code/controladores/idiomas.php';

$idioma_actual = isset($_SESSION['idioma_seleccionado']) ? $_SESSION['idioma_seleccionado'] : 'de';
$rotacion = ['cat' => 'de', 'de' => 'en', 'en' => 'es', 'es' => 'cat'];
$siguiente_idioma = isset($rotacion[$idioma_actual]) ? $rotacion[$idioma_actual] : 'de';
$banderas = ['cat' => 'CAT', 'de' => '🇩🇪 DE', 'en' => '🇬🇧 EN', 'es' => '🇪🇸 ES'];
$bandera_mostrar = isset($banderas[$idioma_actual]) ? $banderas[$idioma_actual] : '🇩🇪 DE';

$db_path = "/var/www/ubungen/kalender.db";

try {
    $db = new PDO("sqlite:$db_path");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// --- CONFIGURACIÓN TELEGRAM (Intacta) ---
$TELEGRAM_TOKEN = "8794845655:AAG2FGe4LPWaYBxganYF4pTYC0uIyTLqpTg";
$CHAT_ID = "5181963608";

function enviar_telegram($mensaje) {
    global $TELEGRAM_TOKEN, $CHAT_ID;
    $url = "https://api.telegram.org/bot" . $TELEGRAM_TOKEN . "/sendMessage";
    $datos = ['chat_id' => $CHAT_ID, 'text' => $mensaje, 'parse_mode' => 'Markdown'];
    $opciones = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($datos)
        ]
    ];
    $contexto = stream_context_create($opciones);
    file_get_contents($url, false, $contexto);
}

// --- LÓGICA DE ACCIONES (BORRAR Y EDITAR ESTADO) ---
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM aufgaben WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['toggle'])) {
    $id_tarea = $_GET['toggle'];
    $nuevo_estado = $_GET['st'];

    if ($nuevo_estado == 'Ausstehen') {
        $hoy = date('Y-m-d');
        
        $stmt_info = $db->prepare("SELECT id, betreff, beschreibung, fach, daten FROM aufgaben WHERE id = ?");
        $stmt_info->execute([$id_tarea]);
        $tarea = $stmt_info->fetch(PDO::FETCH_ASSOC);

        $stmt_insert = $db->prepare("INSERT INTO completadas (betreff, beschreibung, fach, daten, fecha_completada) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert->execute([$tarea['betreff'], $tarea['beschreibung'], $tarea['fach'], $tarea['daten'], $hoy]);

        $stmt_del = $db->prepare("DELETE FROM aufgaben WHERE id = ?");
        $stmt_del->execute([$id_tarea]);

        $mensaje = "✅ *Tarea Completada y Archivada*\nHas terminado: *" . $tarea['betreff'] . "* (" . $tarea['fach'] . ")";
        enviar_telegram($mensaje);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// --- LÓGICA DE FILTRADO ---
$query_parts = [];
$params = [];
if (!empty($_GET['f_fach'])) {
    $query_parts[] = "fach = ?";
    $params[] = $_GET['f_fach'];
}

$sql = "SELECT * FROM aufgaben";
if (count($query_parts) > 0) {
    $sql .= " WHERE " . implode(" AND ", $query_parts);
}
$sql .= " ORDER BY daten ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$aufgaben = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="<?php echo $idioma_actual; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($lang['titulo_lista']) ? $lang['titulo_lista'] : 'Pendientes'; ?></title>
    <link rel="stylesheet" href="../../css/agenda.css">

    <style>
        .btn-undo { background: #f39c12; }
        .task-row { cursor: pointer; transition: all 0.3s ease; }
        .task-row:hover { background-color: #f4f8ff; }
        .task-row.expanded { transform: scale(1.02); box-shadow: 0 8px 20px rgba(0, 90, 180, 0.12); background-color: #ffffff; z-index: 10; position: relative; }
        .task-details { display: none; margin-top: 10px; padding-top: 10px; border-top: 1px dashed #d0e2ff; font-size: 0.9rem; color: #444; }
        .task-row.expanded .task-details { display: block; animation: fadeIn 0.4s ease; }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Botón de idioma rotativo */
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
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            transition: all 0.2s ease;
            z-index: 1000;
        }
        .btn-lang-cycle:hover {
            background-color: #f8fafc;
            transform: translateY(-2px);
            border-color: #cbd5e1;
            color: #0f172a;
        }
        
        @media (max-width: 768px) {
            body { padding: 10px; }
            #principal, .container { padding: 15px; width: 100%; box-sizing: border-box; }
            table, thead, tbody, th, td, tr { display: block; width: 100%; box-sizing: border-box; }
            thead tr { display: none; }
            tr.task-row { margin-bottom: 20px; border: 1px solid #cbd5e1; border-radius: 10px; padding: 15px; background: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
            .task-row.expanded { transform: none; border-left: 4px solid #0284c7; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.15); }
            td { padding: 8px 0; border: none !important; text-align: left; }
            td:first-child { border-bottom: 1px dashed #e2e8f0 !important; padding-bottom: 12px; margin-bottom: 10px; font-size: 1.1rem; }
            td:nth-child(2), td:nth-child(3) { display: inline-block; margin-right: 15px; width: auto; font-size: 0.9rem; }
            td:last-child { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 15px; justify-content: space-between; }
            .btn-action { flex: 1; text-align: center; margin: 0; padding: 12px 5px; font-size: 0.9rem; }
            .footer-links, div[style*="display: flex; gap: 15px"] { flex-direction: column !important; gap: 10px !important; }
            .btn-link { width: 100%; box-sizing: border-box; }
        }
    </style>
</head>

<body>
    <a href="?lang=<?php echo $siguiente_idioma; ?>" class="btn-lang-cycle" title="Cambiar idioma">
        <?php echo $bandera_mostrar; ?> ↻
    </a>

    <div id="principal" style="max-width: 800px; position: relative;">

        <h2><?php echo isset($lang['titulo_lista']) ? $lang['titulo_lista'] : 'Pendientes'; ?></h2>

        <div class="filter-section">
            <form method="get" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <select name="f_fach">
                    <option value=""><?php echo isset($lang['asignatura']) ? $lang['asignatura'] : 'Asignatura'; ?></option>
                    <option value="Redes" <?php if (@$_GET['f_fach'] == 'Redes') echo 'selected'; ?>>Redes</option>
                    <option value="Sistemas" <?php if (@$_GET['f_fach'] == 'Sistemas') echo 'selected'; ?>>Sistemas</option>
                    <option value="Web" <?php if (@$_GET['f_fach'] == 'Web') echo 'selected'; ?>>Web</option>
                </select>
                <button type="submit" style="margin:0; padding: 5px 15px; width: auto;"><?php echo isset($lang['boton_filtrar']) ? $lang['boton_filtrar'] : 'Filtrar'; ?></button>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>" style="font-size: 12px; align-self: center; text-decoration: none; color: #475569;"><?php echo isset($lang['boton_limpiar']) ? $lang['boton_limpiar'] : 'Limpiar'; ?></a>
            </form>
        </div>

        <div style="margin-bottom: 15px; margin-top: 15px;">
            <input type="text" id="buscadorJS" placeholder="<?php echo isset($lang['buscar_placeholder']) ? $lang['buscar_placeholder'] : 'Buscar...'; ?>"
                style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #d0e2ff; font-size: 0.95rem; box-sizing: border-box;">
        </div>

        <table>
            <thead>
                <tr>
                    <th><?php echo isset($lang['tarea']) ? $lang['tarea'] : 'Tarea'; ?></th>
                    <th><?php echo isset($lang['asignatura']) ? $lang['asignatura'] : 'Asignatura'; ?></th>
                    <th><?php echo isset($lang['estado']) ? $lang['estado'] : 'Estado'; ?></th>
                    <th><?php echo isset($lang['acciones']) ? $lang['acciones'] : 'Acciones'; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($aufgaben as $row): ?>
                    <tr class="task-row">
                        <td>
                            <strong><?php echo htmlspecialchars($row['betreff']); ?></strong>
                            <div class="task-details">
                                <p><strong><?php echo isset($lang['descripcion']) ? $lang['descripcion'] : 'Descripción'; ?>:</strong>
                                    <br><?php echo nl2br(htmlspecialchars($row['beschreibung'])); ?></p>
                                <p style="margin-top: 5px;"><strong><?php echo isset($lang['fecha']) ? $lang['fecha'] : 'Fecha'; ?>:</strong>
                                    <?php echo date("d.m.Y", strtotime($row['daten'])); ?></p>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($row['fach']); ?></td>
                        <td class="status-<?php echo strtolower($row['zustand']); ?>">
                            <?php echo htmlspecialchars($row['zustand']); ?>
                        </td>
                        <td>
                            <a href="?toggle=<?php echo $row['id']; ?>&st=<?php echo $row['zustand']; ?>" class="btn-action btn-completar" onclick="event.stopPropagation();">✅</a>
                            <a href="editar.php?id=<?php echo $row['id']; ?>" class="btn-action" onclick="event.stopPropagation();">✏️</a>
                            <a href="?delete=<?php echo $row['id']; ?>" class="btn-action btn-borrar" onclick="event.stopPropagation();">🗑️</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <br>
        <a href="./crear_tareas.php" class="btn-link"
            style="text-decoration:none; display:block; text-align:center; padding:12px; margin-bottom: 25px; border-radius: 6px; font-weight: bold;"><?php echo isset($lang['nueva_tarea']) ? $lang['nueva_tarea'] : 'Nueva Tarea'; ?></a>

        <div style="display: flex; gap: 15px; margin-bottom: 25px; margin-top: 5px;">
            <a href="./agendaMenu.php" class="btn-link"
                style="margin-top: 0; flex: 1; padding: 10px; font-size: 0.9rem; background: linear-gradient(135deg, #6c757d, #495057); color: white; text-align: center; text-decoration: none; border-radius: 6px;">
                ⬅ <?php echo isset($lang['volver']) ? $lang['volver'] : 'Atrás'; ?>
            </a>

            <a href="../../index.php" class="btn-link"
                style="margin-top: 0; flex: 1; padding: 10px; font-size: 0.9rem; text-align: center; text-decoration: none; background: #e2e8f0; color: #475569; border-radius: 6px;">
                🏠 <?php echo isset($lang['inicio']) ? $lang['inicio'] : 'Inicio'; ?>
            </a>
        </div>
    </div>

    <script src="./agenda.js"></script>
</body>
</html>