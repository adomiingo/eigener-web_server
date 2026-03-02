<?php
require_once '../../idiomas.php'; // Motor de idiomas
$db_path = "/var/www/ubungen/kalender.db";
try {
    $db = new PDO("sqlite:$db_path");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die($lang['msg_error_db'] . $e->getMessage()); }

if (isset($_GET['restaurar'])) {
    $id = $_GET['restaurar'];
    $stmt_info = $db->prepare("SELECT betreff, beschreibung, fach, daten FROM completadas WHERE id = ?");
    $stmt_info->execute([$id]);
    $tarea = $stmt_info->fetch(PDO::FETCH_ASSOC);
    if ($tarea) {
        $stmt_insert = $db->prepare("INSERT INTO aufgaben (betreff, beschreibung, fach, daten, zustand) VALUES (?, ?, ?, ?, 'Ausstehen')");
        $stmt_insert->execute([$tarea['betreff'], $tarea['beschreibung'], $tarea['fach'], $tarea['daten']]);
        $stmt_del = $db->prepare("DELETE FROM completadas WHERE id = ?");
        $stmt_del->execute([$id]);
    }
    header("Location: lista_completadas.php"); exit;
}

if (isset($_GET['borrar'])) {
    $stmt = $db->prepare("DELETE FROM completadas WHERE id = ?");
    $stmt->execute([$_GET['borrar']]);
    header("Location: lista_completadas.php"); exit;
}
$stmt = $db->query("SELECT * FROM completadas ORDER BY fecha_completada DESC");
$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="<?php echo isset($_SESSION['idioma_seleccionado']) ? $_SESSION['idioma_seleccionado'] : 'de'; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $lang['historial_titulo']; ?></title>
    <style> /* TU CSS ANTERIOR INTACTO */ body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; color: #334155; padding: 20px; } .container { max-width: 800px; margin: auto; background: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border-top: 5px solid #10b981; } h1 { text-align: center; color: #10b981; } .info-text { text-align: center; font-size: 0.9em; color: #64748b; margin-bottom: 25px; } #buscadorJS { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 0.95rem; margin-bottom: 20px; } table { width: 100%; border-collapse: collapse; margin-top: 10px; } th, td { padding: 15px; text-align: left; border-bottom: 1px solid #f1f5f9; } .btn-action { text-decoration: none; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; margin-right: 5px; } .btn-restaurar { background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; } .btn-borrar { background: #fee2e2; color: #ef4444; border: 1px solid #fecaca; } .empty-msg { text-align: center; color: #94a3b8; margin: 40px 0; font-style: italic; background: #f8fafc; padding: 30px; border-radius: 8px; } .footer-links { margin-top: 30px; display: flex; gap: 15px; } .btn-link { background: #e2e8f0; color: #475569; padding: 12px; text-decoration: none; border-radius: 6px; text-align: center; flex: 1; } .badge-date { background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 12px; font-size: 0.8em; font-weight: bold; } </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $lang['h1_completadas']; ?></h1>
        <p class="info-text"><?php echo $lang['info_completadas']; ?></p>

        <input type="text" id="buscadorJS" placeholder="<?php echo $lang['buscar_historial']; ?>">

        <?php if (count($tareas) > 0): ?>
            <table>
                <thead>
                    <tr><th><?php echo $lang['tarea']; ?></th><th><?php echo $lang['asignatura']; ?></th><th><?php echo $lang['completada_el']; ?></th><th><?php echo $lang['acciones']; ?></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($tareas as $t): ?>
                        <tr class="task-row">
                            <td>
                                <strong><?php echo htmlspecialchars($t['betreff']); ?></strong>
                                <div class="task-details">
                                    <?php if (!empty($t['beschreibung'])): ?>
                                        <p><strong><?php echo $lang['descripcion']; ?>:</strong><br><?php echo nl2br(htmlspecialchars($t['beschreibung'])); ?></p>
                                    <?php else: ?>
                                        <p><em><?php echo $lang['sin_descripcion']; ?></em></p>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><span><?php echo htmlspecialchars($t['fach']); ?></span></td>
                            <td><span class="badge-date"><?php echo date("d-m-Y", strtotime($t['fecha_completada'])); ?></span></td>
                            <td>
                                <a href="lista_completadas.php?restaurar=<?php echo $t['id']; ?>" class="btn-action btn-restaurar" onclick="event.stopPropagation();" title="<?php echo $lang['title_restaurar']; ?>"><?php echo $lang['btn_restaurar']; ?></a>
                                <a href="lista_completadas.php?borrar=<?php echo $t['id']; ?>" class="btn-action btn-borrar" onclick="event.stopPropagation();" title="<?php echo $lang['title_borrar']; ?>"><?php echo $lang['btn_borrar']; ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-msg"><p><?php echo $lang['msg_no_completadas']; ?></p></div>
        <?php endif; ?>

        <div class="footer-links">
            <a href="./agendaMenu.php" class="btn-link"><?php echo $lang['btn_volver_agenda']; ?></a>
            <a href="../estadoServer.php" class="btn-link"><?php echo $lang['btn_ver_panel']; ?></a>
        </div>
    </div>
    <script src="./agenda.js"></script>
</body>
</html>