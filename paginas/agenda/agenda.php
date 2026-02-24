<?php
$db_path = "/var/www/ubungen/kalender.db";
$mensaje = "";
$mensaje_tipo = "";

try {
    $db = new PDO("sqlite:$db_path");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fecha_seleccionada = $_POST['daten'];
    $hoy = date('Y-m-d');

    if ($fecha_seleccionada < $hoy) {
        $mensaje = "Fehler: Das Datum darf nicht in der Vergangenheit liegen.";
        $mensaje_tipo = "error";
    } else {
        try {
            $sql = "INSERT INTO aufgaben (betreff, beschreibung, fach, daten, zustand) VALUES (?, ?, ?, ?, 'Ausstehen')";
            $stmt = $db->prepare($sql);
            $stmt->execute([$_POST['betreff'], $_POST['beschreibung'], $_POST['fach'], $_POST['daten']]);
            $mensaje = "Aufgabe erfolgreich gespeichert!";
            $mensaje_tipo = "success";
        } catch (PDOException $e) {
            $mensaje = "Datenbankfehler: " . $e->getMessage();
            $mensaje_tipo = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Tarea</title>
    <link rel="stylesheet" href="../../css/agenda.css"> </head>
<body>
    <div id="principal">
        <h2>Neue Aufgabe</h2>
        
        <?php if($mensaje): ?>
            <div class="alert <?php echo $mensaje_tipo; ?>"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <form method="post">
            <label>Titel:</label>
            <input type="text" name="betreff" required>
            
            <label>Beschreibung:</label>
            <textarea name="beschreibung" rows="3"></textarea>
            
            <label>Fach:</label>
            <select name="fach">
                <option value="Redes">Redes Locales</option>
                <option value="Sistemas">Sistemas Operativos</option>
                <option value="Seguridad">Seguridad Informática</option>
                <option value="Web">Aplicaciones Web</option>
            </select>
            
            <label>Datum:</label>
            <input type="date" name="daten" min="<?php echo date('Y-m-d'); ?>" required>
            
            <button type="submit">Speichern</button>
            <a href="./modificar.php" style="text-align:center; display:block; margin-top:15px; color:#2a6df4; text-decoration:none;">Ver lista de tareas →</a>
        </form>
    </div>
</body>
</html>