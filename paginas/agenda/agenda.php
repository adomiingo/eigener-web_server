<?php
$db_path = "/var/www/ubungen/kalender.db";
$mensaje = "";
$mensaje_tipo = "";

// 1. Conexión a la base de datos
try {
    $db = new PDO("sqlite:$db_path");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// 2. Procesamiento del formulario
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
            $stmt->execute([
                $_POST['betreff'], 
                $_POST['beschreibung'], 
                $_POST['fach'], 
                $_POST['daten']
            ]);
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Tarea - Agenda</title>
    <link rel="stylesheet" href="../../css/agenda.css"> 
</head>
<body>
    <div id="principal">
        <h2>Neue Aufgabe</h2>
        
        <?php if($mensaje): ?>
            <div class="alert <?php echo $mensaje_tipo; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <label for="betreff">Titel:</label>
            <input type="text" id="betreff" name="betreff" required placeholder="Ej: Examen de redes...">
            
            <label for="beschreibung">Beschreibung:</label>
            <textarea id="beschreibung" name="beschreibung" rows="3" placeholder="Añade detalles aquí..."></textarea>
            
            <label for="fach">Fach:</label>
            <select id="fach" name="fach">
                <option value="Redes">Redes Locales</option>
                <option value="Sistemas">Sistemas Operativos</option>
                <option value="Seguridad">Seguridad Informática</option>
                <option value="Web">Aplicaciones Web</option>
            </select>
            
            <label for="daten">Datum:</label>
            <input type="date" id="daten" name="daten" min="<?php echo date('Y-m-d'); ?>" required>
            
            <button type="submit">Speichern</button>
            
            <a href="./modificar.php" style="text-align:center; display:block; margin-top:15px; color:#2a6df4; text-decoration:none; font-weight: 500;">
                Ver lista de tareas →
            </a>
        </form>
    </div>

    <script src="./agenda.js"></script>
</body>
</html>