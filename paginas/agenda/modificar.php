<?php
$db_path = "/var/www/ubungen/kalender.db";
try {
    $db = new PDO("sqlite:$db_path");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Error: " . $e->getMessage()); }

// --- LÓGICA DE ACCIONES (BORRAR Y EDITAR ESTADO) ---
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM aufgaben WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: " . $_SERVER['PHP_SELF']); // Vuelve al archivo actual dinámicamente
    exit; // Detiene la ejecución después de redirigir
}

if (isset($_GET['toggle'])) {
    $nuevo_estado = ($_GET['st'] == 'Ausstehen') ? 'Erledigt' : 'Ausstehen';
    $stmt = $db->prepare("UPDATE aufgaben SET zustand = ? WHERE id = ?");
    $stmt->execute([$nuevo_estado, $_GET['toggle']]);
    header("Location: " . $_SERVER['PHP_SELF']); // Vuelve al archivo actual dinámicamente
    exit; // Detiene la ejecución
}

// --- LÓGICA DE FILTRADO ---
$query_parts = [];
$params = [];
if (!empty($_GET['f_fach'])) { $query_parts[] = "fach = ?"; $params[] = $_GET['f_fach']; }
if (!empty($_GET['f_zustand'])) { $query_parts[] = "zustand = ?"; $params[] = $_GET['f_zustand']; }

$sql = "SELECT * FROM aufgaben";
if (count($query_parts) > 0) { $sql .= " WHERE " . implode(" AND ", $query_parts); }
$sql .= " ORDER BY daten ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$aufgaben = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Tareas</title>
    <link rel="stylesheet" href="../../css/agenda.css">
</head>
<body>
    <div id="principal" style="max-width: 800px;">
        <h2>Aufgabenliste</h2>
        
        <div class="filter-section">
            <form method="get" style="display: flex; gap: 10px;">
                <select name="f_fach">
                    <option value="">Fächer</option>
                    <option value="Redes">Redes</option>
                    <option value="Sistemas">Sistemas</option>
                    <option value="Web">Web</option>
                </select>
                <select name="f_zustand">
                    <option value="">Status</option>
                    <option value="Ausstehen">Ausstehen</option>
                    <option value="Erledigt">Erledigt</option>
                </select>
                <button type="submit" style="margin:0; padding: 5px 15px;">Filtern</button>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>" style="font-size: 12px; align-self: center;">Limpiar</a>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Aufgabe</th>
                    <th>Fach / Datum</th>
                    <th>Estado</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($aufgaben as $row): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['betreff']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['fach']); ?><br><small><?php echo date("d.m.Y", strtotime($row['daten'])); ?></small></td>
                    <td class="status-<?php echo strtolower($row['zustand']); ?>"><?php echo htmlspecialchars($row['zustand']); ?></td>
                    <td>
                        <a href="?toggle=<?php echo $row['id']; ?>&st=<?php echo $row['zustand']; ?>" class="btn-action btn-status" title="Cambiar estado">✔</a>
                        <a href="?delete=<?php echo $row['id']; ?>" class="btn-action btn-del" onclick="return confirm('¿Borrar?')" title="Eliminar">🗑</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>
        <a href="subir_agenda.php" class="btn-link" style="text-decoration:none; display:inline-block; padding:10px;">+ Nueva Tarea</a>
    </div>
</body>
</html>