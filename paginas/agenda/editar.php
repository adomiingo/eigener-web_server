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
    // Transformamos las casillas (Checkbox): 1 si están marcadas, 0 si no
    $rec_casa = isset($_POST['recordar_casa']) ? 1 : 0;
    $rec_matutino = isset($_POST['recordar_matutino']) ? 1 : 0;

    // Actualizamos la consulta para incluir las nuevas columnas
    $stmt = $db->prepare("UPDATE aufgaben SET betreff = ?, beschreibung = ?, fach = ?, daten = ?, recordar_casa = ?, recordar_matutino = ? WHERE id = ?");
    $stmt->execute([
        $_POST['betreff'],
        $_POST['beschreibung'],
        $_POST['fach'],
        $_POST['daten'],
        $rec_casa,          // Nuevo
        $rec_matutino,      // Nuevo
        $_POST['id']
    ]);

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
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f4f8;
            padding: 20px;
            display: flex;
            justify-content: center;
        }

        .container {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 500px;
            border-top: 5px solid #0284c7;
        }

        h2 {
            color: #0284c7;
            text-align: center;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="date"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            margin-bottom: 20px;
            box-sizing: border-box;
        }

        /* Estilos para las nuevas opciones de notificaciones */
        .notificaciones-box {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .noti-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .noti-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin: 0;
            cursor: pointer;
            accent-color: #0284c7;
        }

        .noti-item label {
            margin: 0;
            font-weight: 500;
            cursor: pointer;
            color: #334155;
        }

        .btn-group {
            display: flex;
            gap: 10px;
        }

        .btn {
            flex: 1;
            height: 48px;
            /* 🛠️ FIJAMOS LA ALTURA A LA FUERZA */
            padding: 0 12px;
            /* 🛠️ CAMBIAMOS A 0 SUPERIOR E INFERIOR */
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            transition: 0.2s;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 1rem;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .btn-save {
            background-color: #0284c7;
            color: white;
        }

        .btn-save:hover {
            background-color: #0369a1;
        }

        .btn-cancel {
            background-color: #e2e8f0;
            color: #475569;
        }

        .btn-cancel:hover {
            background-color: #cbd5e1;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2><?php echo isset($lang['editar_titulo']) ? $lang['editar_titulo'] : 'Editar Tarea'; ?></h2>
        <form method="POST" action="editar.php">
            <input type="hidden" name="id" value="<?php echo $tarea['id']; ?>">

            <label><?php echo isset($lang['label_titulo_edit']) ? $lang['label_titulo_edit'] : 'Título'; ?></label>
            <input type="text" name="betreff" required value="<?php echo htmlspecialchars($tarea['betreff']); ?>">

            <label><?php echo isset($lang['label_fach_edit']) ? $lang['label_fach_edit'] : 'Asignatura'; ?></label>
            <select name="fach" required>
                <option value="Redes" <?php if ($tarea['fach'] == 'Redes')
                    echo 'selected'; ?>>Redes Locales</option>
                <option value="Sistemas" <?php if ($tarea['fach'] == 'Sistemas')
                    echo 'selected'; ?>>Sistemas Operativos
                </option>
                <option value="Seguridad" <?php if ($tarea['fach'] == 'Seguridad')
                    echo 'selected'; ?>>Seguridad
                    Informática</option>
                <option value="Web" <?php if ($tarea['fach'] == 'Web')
                    echo 'selected'; ?>>Aplicaciones Web</option>
                <option value="Personal" <?php if ($tarea['fach'] == 'Personal')
                    echo 'selected'; ?>>Personal</option>
                <option value="Server" <?php if ($tarea['fach'] == 'Server')
                    echo 'selected'; ?>>Server Idea</option>
                <?php
                $opciones_validas = ['Redes', 'Sistemas', 'Seguridad', 'Web', 'Personal', 'Server'];
                if (!in_array($tarea['fach'], $opciones_validas) && !empty($tarea['fach'])): ?>
                    <option value="<?php echo htmlspecialchars($tarea['fach']); ?>" selected>
                        <?php echo htmlspecialchars($tarea['fach']); ?>
                        <?php echo isset($lang['categoria_antigua']) ? $lang['categoria_antigua'] : '(Antigua)'; ?>
                    </option>
                <?php endif; ?>
            </select>

            <label><?php echo isset($lang['label_fecha_edit']) ? $lang['label_fecha_edit'] : 'Fecha'; ?></label>
            <input type="date" name="daten" required value="<?php echo htmlspecialchars($tarea['daten']); ?>">

            <label>Opciones de Notificación</label>
            <div class="notificaciones-box">
                <div class="noti-item">
                    <input type="checkbox" id="rec_casa" name="recordar_casa" value="1" <?php if (isset($tarea['recordar_casa']) && $tarea['recordar_casa'] == 1)
                        echo 'checked'; ?>>
                    <label for="rec_casa">🛋️ Recordar en casa</label>
                </div>
                <div class="noti-item">
                    <input type="checkbox" id="rec_matutino" name="recordar_matutino" value="1" <?php if (isset($tarea['recordar_matutino']) && $tarea['recordar_matutino'] == 1)
                        echo 'checked'; ?>>
                    <label for="rec_matutino">☕ Recordatorio Matutino (08:30)</label>
                </div>
            </div>

            <label><?php echo isset($lang['label_desc_edit']) ? $lang['label_desc_edit'] : 'Descripción'; ?></label>
            <textarea name="beschreibung" rows="4"><?php echo htmlspecialchars($tarea['beschreibung']); ?></textarea>

            <div class="btn-group">
                <a href="lista_pendientes.php"
                    class="btn btn-cancel"><?php echo isset($lang['btn_cancelar']) ? $lang['btn_cancelar'] : 'Cancelar'; ?></a>
                <button type="submit"
                    class="btn btn-save"><?php echo isset($lang['btn_guardar_cambios']) ? $lang['btn_guardar_cambios'] : 'Guardar'; ?></button>
            </div>
        </form>
    </div>
</body>

</html>