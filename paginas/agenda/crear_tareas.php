<?php
// Motor de idiomas (Ruta actualizada)
require_once '../../code/controladores/idiomas.php';
$idioma_actual = isset($_SESSION['idioma_seleccionado']) ? $_SESSION['idioma_seleccionado'] : 'de';

$db_path = "/var/www/ubungen/kalender.db";
$mensaje = "";
$mensaje_tipo = "";

try {
    $db = new PDO("sqlite:$db_path");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { 
    die($lang['msg_error_db'] . $e->getMessage()); 
}

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
            
            // --- GATILLO PARA ACTUALIZAR LOS GUIONES DE SIRI ---
            exec('python3 /var/www/html/api/guion_academico.py > /dev/null 2>&1 &');
            exec('python3 /var/www/html/api/guion_citas.py > /dev/null 2>&1 &');
            exec('python3 /var/www/html/api/guion_personal.py > /dev/null 2>&1 &');
            
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
<html lang="<?php echo $idioma_actual; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($lang['nueva_tarea_titulo']) ? $lang['nueva_tarea_titulo'] : 'Nueva Tarea'; ?></title>
    <link rel="stylesheet" href="../../css/agenda.css">
</head>
<body>
    <div id="principal">
        <h2><?php echo isset($lang['nueva_tarea_titulo']) ? $lang['nueva_tarea_titulo'] : 'Nueva Tarea'; ?></h2>
        
        <?php if($mensaje): ?>
            <div class="alert <?php echo $mensaje_tipo; ?>"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <form method="post">
            <label for="betreff"><?php echo isset($lang['label_titel']) ? $lang['label_titel'] : 'Título'; ?></label>
            <input type="text" id="betreff" name="betreff" required placeholder="<?php echo isset($lang['placeholder_titel']) ? $lang['placeholder_titel'] : 'Ej: Comprar pan'; ?>">

            <label for="beschreibung"><?php echo isset($lang['label_descripcion']) ? $lang['label_descripcion'] : 'Descripción'; ?></label>
            <textarea id="beschreibung" name="beschreibung" rows="3" placeholder="<?php echo isset($lang['placeholder_descripcion']) ? $lang['placeholder_descripcion'] : 'Detalles adicionales...'; ?>"></textarea>

            <label for="fach"><?php echo isset($lang['label_fach']) ? $lang['label_fach'] : 'Categoría'; ?></label>
            <input list="categorias-existentes" name="fach" id="fach" placeholder="Ej: Citas, Personal, etc." required autocomplete="off">
            
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
                } catch (PDOException $e) {
                }
                ?>
            </datalist>

            <label for="daten"><?php echo isset($lang['label_datum']) ? $lang['label_datum'] : 'Fecha'; ?></label>
            <input type="date" id="daten" name="daten" min="<?php echo date('Y-m-d'); ?>" required>

            <button type="submit"><?php echo isset($lang['btn_guardar']) ? $lang['btn_guardar'] : 'Guardar'; ?></button>

            <a href="./lista_pendientes.php" style="text-align:center; margin-bottom: 15px; display:block; margin-top:15px; color:#2a6df4; text-decoration:none; font-weight: 500;">
                <?php echo isset($lang['link_ver_lista']) ? $lang['link_ver_lista'] : 'Ver tareas pendientes'; ?>
            </a>
        </form>
        
        <div style="display: flex; gap: 15px; margin-bottom: 25px;">
            <a href="./agendaMenu.php" class="btn-link" style="margin-top: 0; flex: 1; padding: 10px; text-align: center; text-decoration: none; border-radius: 6px; font-size: 0.9rem; background: linear-gradient(135deg, #6c757d, #495057); color: white;">
                ⬅ <?php echo isset($lang['volver']) ? $lang['volver'] : 'Atrás'; ?>
            </a>
            <a href="../../index.php" class="btn-link" style="margin-top: 0; flex: 1; padding: 10px; text-align: center; text-decoration: none; border-radius: 6px; font-size: 0.9rem; background: #e2e8f0; color: #475569;">
                🏠 <?php echo isset($lang['inicio']) ? $lang['inicio'] : 'Inicio'; ?>
            </a>
        </div>
    </div>
    <script src="./agenda.js"></script>
</body>
</html>