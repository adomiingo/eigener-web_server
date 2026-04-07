<?php
// Motor de idiomas (Ruta actualizada)
require_once '../../code/controladores/idiomas.php';
$idioma_actual = isset($_SESSION['idioma_seleccionado']) ? $_SESSION['idioma_seleccionado'] : 'de';

$db_path = "/var/www/ubungen/kalender.db";

try {
    $db = new PDO("sqlite:$db_path");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die($lang['msg_error_db'] . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $rec_casa = isset($_POST['recordar_casa']) ? 1 : 0;
    $rec_matutino = isset($_POST['recordar_matutino']) ? 1 : 0;

    $stmt = $db->prepare("UPDATE aufgaben SET betreff = ?, beschreibung = ?, fach = ?, daten = ?, recordar_casa = ?, recordar_matutino = ? WHERE id = ?");
    $stmt->execute([
        $_POST['betreff'],
        $_POST['beschreibung'],
        $_POST['fach'],
        $_POST['daten'],
        $rec_casa,
        $rec_matutino,
        $_POST['id']
    ]);

    // --- GATILLO PARA ACTUALIZAR LOS GUIONES DE SIRI ---
    exec('python3 /var/www/html/api/guion_academico.py > /dev/null 2>&1 &');
    exec('python3 /var/www/html/api/guion_citas.py > /dev/null 2>&1 &');
    exec('python3 /var/www/html/api/guion_personal.py > /dev/null 2>&1 &');

    header("Location: lista_pendientes.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: lista_pendientes.php");
    exit;
}

$stmt = $db->prepare("SELECT * FROM aufgaben WHERE id = ?");
$stmt->execute([$_GET['id']]);
$tarea = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tarea) {
    die(isset($lang['msg_error_no_encontrada']) ? $lang['msg_error_no_encontrada'] : 'Tarea no encontrada');
}
?>
<!DOCTYPE html>
<html lang="<?php echo $idioma_actual; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($lang['editar_titulo']) ? $lang['editar_titulo'] : 'Editar Tarea'; ?></title>
    <link rel="stylesheet" href="../../css/agenda.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f0f4f8; padding: 20px; display: flex; justify-content: center; }
        .container { background: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05); width: 100%; max-width: 500px; border-top: 5px solid #0284c7; }
        h2 { color: #0284c7; text-align: center; }
        label { display: block; font-weight: 600; margin-bottom: 8px; }
        input[type="text"], input[type="date"], textarea, select, input[list] { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 20px; box-sizing: border-box; }
        .notificaciones-box { background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 20px; display: flex; flex-direction: column; gap: 12px; }
        .noti-item { display: flex; align-items: center; gap: 10px; }
        .noti-item input[type="checkbox"] { width: 20px; height: 20px; margin: 0; cursor: pointer; accent-color: #0284c7; }
        .noti-item label { margin: 0; font-weight: 500; cursor: pointer; color: #334155; }
        .btn-group { display: flex; gap: 10px; }
        .btn { flex: 1; height: 48px; margin: 0; padding: 0; border: 1px solid transparent; border-radius: 8px; font-weight: bold; font-family: inherit; font-size: 1rem; text-decoration: none; cursor: pointer; transition: 0.2s; box-sizing: border-box; display: inline-flex; justify-content: center; align-items: center; vertical-align: middle; }
        .btn-save { background-color: #0284c7; color: white; }
        .btn-save:hover { background-color: #0369a1; }
        .btn-cancel { background-color: #e2e8f0; color: #475569; }
        .btn-cancel:hover { background-color: #cbd5e1; }
    </style>
</head>

<body>
    <div class="container">
        <h2><?php echo isset($lang['editar_titulo']) ? $lang['editar_titulo'] : 'Editar Tarea'; ?></h2>
        <form method="POST" action="editar.php">
            <input type="hidden" name="id" value="<?php echo $tarea['id']; ?>">

            <label><?php echo isset($lang['label_titulo_edit']) ? $lang['label_titulo_edit'] : 'Título'; ?></label>
            <input type="text" name="betreff" required value="<?php echo htmlspecialchars($tarea['betreff']); ?>">

            <label><?php echo isset($lang['label_fach_edit']) ? $lang['label_fach_edit'] : 'Categoría'; ?></label>
            <input list="categorias-existentes" name="fach" id="fach" required autocomplete="off" value="<?php echo htmlspecialchars($tarea['fach']); ?>">
            
            <datalist id="categorias-existentes">
                <option value="Citas">
                <option value="Personal">
                <option value="Académico">
                <?php
                try {
                    $stmt_cat = $db->query("SELECT DISTINCT fach FROM aufgaben WHERE fach IS NOT NULL AND fach != '' ORDER BY fach");
                    $categorias_default = ['Citas', 'Personal', 'Académico'];
                    while ($row = $stmt_cat->fetch(PDO::FETCH_ASSOC)) {
                        if (!in_array($row['fach'], $categorias_default)) {
                            echo '<option value="' . htmlspecialchars($row['fach']) . '">';
                        }
                    }
                } catch (PDOException $e) {}
                ?>
            </datalist>

            <label><?php echo isset($lang['label_fecha_edit']) ? $lang['label_fecha_edit'] : 'Fecha'; ?></label>
            <input type="date" name="daten" required value="<?php echo htmlspecialchars($tarea['daten']); ?>">

            <label>Opciones de Notificación</label>
            <div class="notificaciones-box">
                <div class="noti-item">
                    <input type="checkbox" id="rec_casa" name="recordar_casa" value="1" <?php if (isset($tarea['recordar_casa']) && $tarea['recordar_casa'] == 1) echo 'checked'; ?>>
                    <label for="rec_casa">🛋️ Recordar en casa</label>
                </div>
                <div class="noti-item">
                    <input type="checkbox" id="rec_matutino" name="recordar_matutino" value="1" <?php if (isset($tarea['recordar_matutino']) && $tarea['recordar_matutino'] == 1) echo 'checked'; ?>>
                    <label for="rec_matutino">☕ Recordatorio Matutino (08:30)</label>
                </div>
            </div>

            <label><?php echo isset($lang['label_desc_edit']) ? $lang['label_desc_edit'] : 'Descripción'; ?></label>
            <textarea name="beschreibung" rows="4"><?php echo htmlspecialchars($tarea['beschreibung']); ?></textarea>

            <div class="btn-group">
                <a href="lista_pendientes.php" class="btn btn-cancel"><?php echo isset($lang['btn_cancelar']) ? $lang['btn_cancelar'] : 'Cancelar'; ?></a>
                <button type="submit" class="btn btn-save"><?php echo isset($lang['btn_guardar_cambios']) ? $lang['btn_guardar_cambios'] : 'Guardar'; ?></button>
            </div>
        </form>
    </div>
</body>
</html>