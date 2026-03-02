<?php
require_once '../../idiomas.php'; // Motor de idiomas
$db_path = "/var/www/ubungen/kalender.db";
try {
    $db = new PDO("sqlite:$db_path");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die($lang['msg_error_db'] . $e->getMessage()); }

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $stmt = $db->prepare("UPDATE aufgaben SET betreff = ?, beschreibung = ?, fach = ?, daten = ? WHERE id = ?");
    $stmt->execute([$_POST['betreff'], $_POST['beschreibung'], $_POST['fach'], $_POST['daten'], $_POST['id']]);
    header("Location: lista_pendientes.php"); // RUTA CORREGIDA
    exit;
}

if (!isset($_GET['id'])) { header("Location: lista_pendientes.php"); exit; } // RUTA CORREGIDA

$stmt = $db->prepare("SELECT * FROM aufgaben WHERE id = ?");
$stmt->execute([$_GET['id']]);
$tarea = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tarea) { die($lang['msg_error_no_encontrada']); }
?>
<!DOCTYPE html>
<html lang="<?php echo isset($_SESSION['idioma_seleccionado']) ? $_SESSION['idioma_seleccionado'] : 'de'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['editar_titulo']; ?></title>
    <link rel="stylesheet" href="../../css/agenda.css">
    <style> body { font-family: 'Segoe UI', sans-serif; background-color: #f0f4f8; padding: 20px; display: flex; justify-content: center; } .container { background: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 500px; border-top: 5px solid #0284c7; } h2 { color: #0284c7; text-align: center; } label { display: block; font-weight: 600; margin-bottom: 8px; } input, textarea, select { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 20px;} .btn-group { display: flex; gap: 10px; } .btn { flex: 1; padding: 12px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; text-align: center; text-decoration: none; } .btn-save { background-color: #0284c7; color: white; } .btn-cancel { background-color: #e2e8f0; color: #475569; }</style>
</head>
<body>
    <div class="container">
        <h2><?php echo $lang['editar_titulo']; ?></h2>
        <form method="POST" action="editar.php">
            <input type="hidden" name="id" value="<?php echo $tarea['id']; ?>">

            <label><?php echo $lang['label_titulo_edit']; ?></label>
            <input type="text" name="betreff" required value="<?php echo htmlspecialchars($tarea['betreff']); ?>">

            <label><?php echo $lang['label_fach_edit']; ?></label>
            <select name="fach" required>
                <option value="Redes" <?php if ($tarea['fach'] == 'Redes') echo 'selected'; ?>>Redes Locales</option>
                <option value="Sistemas" <?php if ($tarea['fach'] == 'Sistemas') echo 'selected'; ?>>Sistemas Operativos</option>
                <option value="Seguridad" <?php if ($tarea['fach'] == 'Seguridad') echo 'selected'; ?>>Seguridad Informática</option>
                <option value="Web" <?php if ($tarea['fach'] == 'Web') echo 'selected'; ?>>Aplicaciones Web</option>
                <option value="Personal" <?php if ($tarea['fach'] == 'Personal') echo 'selected'; ?>>Personal</option>
                <option value="Server" <?php if ($tarea['fach'] == 'Server') echo 'selected'; ?>>Server Idea</option>
                <?php $opciones_validas = ['Redes', 'Sistemas', 'Seguridad', 'Web', 'Personal', 'Server'];
                if (!in_array($tarea['fach'], $opciones_validas) && !empty($tarea['fach'])): ?>
                    <option value="<?php echo htmlspecialchars($tarea['fach']); ?>" selected><?php echo htmlspecialchars($tarea['fach']); ?> <?php echo $lang['categoria_antigua']; ?></option>
                <?php endif; ?>
            </select>

            <label><?php echo $lang['label_fecha_edit']; ?></label>
            <input type="date" name="daten" required value="<?php echo htmlspecialchars($tarea['daten']); ?>">

            <label><?php echo $lang['label_desc_edit']; ?></label>
            <textarea name="beschreibung"><?php echo htmlspecialchars($tarea['beschreibung']); ?></textarea>

            <div class="btn-group">
                <a href="lista_pendientes.php" class="btn btn-cancel"><?php echo $lang['btn_cancelar']; ?></a>
                <button type="submit" class="btn btn-save"><?php echo $lang['btn_guardar_cambios']; ?></button>
            </div>
        </form>
    </div>
</body>
</html>