<?php
$db_path = "/var/www/ubungen/kalender.db";
try {
    $db = new PDO("sqlite:$db_path");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Error de conexión: " . $e->getMessage()); }

// 1. PROCESAR EL FORMULARIO CUANDO LE DAS A GUARDAR (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $betreff = $_POST['betreff'];
    $beschreibung = $_POST['beschreibung'];
    $fach = $_POST['fach'];
    $daten = $_POST['daten'];

    // Actualizamos la tarea en la base de datos
    $stmt = $db->prepare("UPDATE aufgaben SET betreff = ?, beschreibung = ?, fach = ?, daten = ? WHERE id = ?");
    $stmt->execute([$betreff, $beschreibung, $fach, $daten, $id]);

    // Devolvemos al usuario a la agenda principal
    header("Location: modificar.php");
    exit;
}

// 2. CARGAR LOS DATOS ACTUALES CUANDO ENTRAS A LA PÁGINA (GET)
if (!isset($_GET['id'])) {
    header("Location: modificar.php");
    exit;
}

$id = $_GET['id'];
$stmt = $db->prepare("SELECT * FROM aufgaben WHERE id = ?");
$stmt->execute([$id]);
$tarea = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tarea) {
    die("❌ Error: Tarea no encontrada en la base de datos.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Tarea</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f4f8; color: #334155; padding: 20px; display: flex; justify-content: center; }
        .container { background: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 500px; border-top: 5px solid #0284c7; }
        h2 { color: #0284c7; margin-top: 0; text-align: center; margin-bottom: 25px; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem; color: #475569; }
        input[type="text"], input[type="date"], textarea, select { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; font-family: inherit; transition: border-color 0.3s; }
        input:focus, textarea:focus, select:focus { border-color: #0284c7; outline: none; box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1); }
        
        textarea { resize: vertical; min-height: 100px; }
        
        .btn-group { display: flex; gap: 10px; margin-top: 30px; }
        .btn { flex: 1; padding: 12px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; text-align: center; text-decoration: none; transition: background 0.2s; }
        .btn-save { background-color: #0284c7; color: white; }
        .btn-save:hover { background-color: #0369a1; }
        .btn-cancel { background-color: #e2e8f0; color: #475569; }
        .btn-cancel:hover { background-color: #cbd5e1; color: #0f172a; }
    </style>
</head>
<body>
    <div class="container">
        <h2>✏️ Editar Tarea</h2>
        <form method="POST" action="editar.php">
            <input type="hidden" name="id" value="<?php echo $tarea['id']; ?>">

            <div class="form-group">
                <label>Título (Betreff):</label>
                <input type="text" name="betreff" required value="<?php echo htmlspecialchars($tarea['betreff']); ?>">
            </div>

            <div class="form-group">
                <label>Asignatura / Categoría (Fach):</label>
                <input type="text" name="fach" required value="<?php echo htmlspecialchars($tarea['fach']); ?>">
            </div>

            <div class="form-group">
                <label>Fecha Límite (Daten):</label>
                <input type="date" name="daten" required value="<?php echo htmlspecialchars($tarea['daten']); ?>">
            </div>

            <div class="form-group">
                <label>Descripción (Beschreibung):</label>
                <textarea name="beschreibung"><?php echo htmlspecialchars($tarea['beschreibung']); ?></textarea>
            </div>

            <div class="btn-group">
                <a href="modificar.php" class="btn btn-cancel">❌ Cancelar</a>
                <button type="submit" class="btn btn-save">💾 Guardar Cambios</button>
            </div>
        </form>
    </div>
</body>
</html>