<?php
require_once '../../idiomas.php'; // Motor de idiomas
$db_path = "/var/www/ubungen/kalender.db";
$mensaje = "";
$mensaje_tipo = "";

try {
    $db = new PDO("sqlite:$db_path");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die($lang['msg_error_db'] . $e->getMessage()); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fecha_seleccionada = $_POST['daten'];
    $hoy = date('Y-m-d');
    if ($fecha_seleccionada < $hoy) {
        $mensaje = $lang['msg_fecha_pasado'];
        $mensaje_tipo = "error";
    } else {
        try {
            $sql = "INSERT INTO aufgaben (betreff, beschreibung, fach, daten, zustand) VALUES (?, ?, ?, ?, 'Ausstehen')";
            $stmt = $db->prepare($sql);
            $stmt->execute([$_POST['betreff'], $_POST['beschreibung'], $_POST['fach'], $_POST['daten']]);
            $mensaje = $lang['msg_tarea_guardada'];
            $mensaje_tipo = "success";
        } catch (PDOException $e) {
            $mensaje = $lang['msg_error_db'] . $e->getMessage();
            $mensaje_tipo = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo isset($_SESSION['idioma_seleccionado']) ? $_SESSION['idioma_seleccionado'] : 'de'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['nueva_tarea_titulo']; ?></title>
    <link rel="stylesheet" href="../../css/agenda.css">
</head>
<body>
    <div id="principal">
        <h2><?php echo $lang['nueva_tarea_titulo']; ?></h2>
        <?php if($mensaje): ?>
        <div class="alert <?php echo $mensaje_tipo; ?>"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <form method="post">
            <label for="betreff"><?php echo $lang['label_titel']; ?></label>
            <input type="text" id="betreff" name="betreff" required placeholder="<?php echo $lang['placeholder_titel']; ?>">

            <label for="beschreibung"><?php echo $lang['label_descripcion']; ?></label>
            <textarea id="beschreibung" name="beschreibung" rows="3" placeholder="<?php echo $lang['placeholder_descripcion']; ?>"></textarea>

            <label for="fach"><?php echo $lang['label_fach']; ?></label>
            <select id="fach" name="fach">
                <option value="Redes">Redes Locales</option>
                <option value="Sistemas">Sistemas Operativos</option>
                <option value="Seguridad">Seguridad Informática</option>
                <option value="Web">Aplicaciones Web</option>
                <option value="Personal">Personal</option>
                <option value="Server">Server Idea</option>
            </select>

            <label for="daten"><?php echo $lang['label_datum']; ?></label>
            <input type="date" id="daten" name="daten" min="<?php echo date('Y-m-d'); ?>" required>

            <button type="submit"><?php echo $lang['btn_guardar']; ?></button>

            <a href="./lista_pendientes.php" style="text-align:center; margin-bottom: 15px;display:block; margin-top:15px; color:#2a6df4; text-decoration:none; font-weight: 500;">
                <?php echo $lang['link_ver_lista']; ?>
            </a>
        </form>
        <div style="display: flex; gap: 15px; margin-bottom: 25px;">
            <a href="./agendaMenu.php" class="btn-link" style="margin-top: 0; flex: 1; padding: 10px; font-size: 0.9rem; background: linear-gradient(135deg, #6c757d, #495057);">
                <?php echo $lang['volver']; ?>
            </a>
            <a href="../../index.php" class="btn-link" style="margin-top: 0; flex: 1; padding: 10px; font-size: 0.9rem;">
                <?php echo $lang['inicio']; ?>
            </a>
        </div>
    </div>
    <script src="./agenda.js"></script>
</body>
</html>