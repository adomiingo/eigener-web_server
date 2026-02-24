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
    header("Location: " . $_SERVER['PHP_SELF']); 
    exit; 
}

if (isset($_GET['toggle'])) {
    $nuevo_estado = ($_GET['st'] == 'Ausstehen') ? 'Erledigt' : 'Ausstehen';
    $stmt = $db->prepare("UPDATE aufgaben SET zustand = ? WHERE id = ?");
    $stmt->execute([$nuevo_estado, $_GET['toggle']]);
    header("Location: " . $_SERVER['PHP_SELF']); 
    exit; 
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Tareas - Modificar</title>
    <link rel="stylesheet" href="../../css/agenda.css">
    
    <style>
        .btn-undo { background: #f39c12; } /* Botón naranja de deshacer */
        .task-row { cursor: pointer; transition: all 0.3s ease; }
        .task-row:hover { background-color: #f4f8ff; }
        .task-row.expanded { transform: scale(1.02); box-shadow: 0 8px 20px rgba(0, 90, 180, 0.12); background-color: #ffffff; z-index: 10; position: relative; }
        .task-details { display: none; margin-top: 10px; padding-top: 10px; border-top: 1px dashed #d0e2ff; font-size: 0.9rem; color: #444; }
        .task-row.expanded .task-details { display: block; animation: fadeIn 0.4s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
    <div id="principal" style="max-width: 800px;">
        <h2>Aufgabenliste</h2>
        
        <div class="filter-section">
            <form method="get" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <select name="f_fach">
                    <option value="">Fächer</option>
                    <option value="Redes" <?php if(@$_GET['f_fach']=='Redes') echo 'selected'; ?>>Redes</option>
                    <option value="Sistemas" <?php if(@$_GET['f_fach']=='Sistemas') echo 'selected'; ?>>Sistemas</option>
                    <option value="Web" <?php if(@$_GET['f_fach']=='Web') echo 'selected'; ?>>Web</option>
                </select>
                <select name="f_zustand">
                    <option value="">Status</option>
                    <option value="Ausstehen" <?php if(@$_GET['f_zustand']=='Ausstehen') echo 'selected'; ?>>Ausstehen</option>
                    <option value="Erledigt" <?php if(@$_GET['f_zustand']=='Erledigt') echo 'selected'; ?>>Erledigt</option>
                </select>
                <button type="submit" style="margin:0; padding: 5px 15px; width: auto;">Filtern</button>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>" style="font-size: 12px; align-self: center;">Limpiar</a>
            </form>
        </div>

        <div style="margin-bottom: 15px;">
            <input type="text" id="buscadorJS" placeholder="🔍 Buscar tarea rápidamente por título..." style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #d0e2ff; font-size: 0.95rem;">
        </div>

        <table>
            <thead>
                <tr>
                    <th>Aufgabe</th>
                    <th>Fach</th>
                    <th>Estado</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($aufgaben as $row): ?>
                
                <tr class="task-row">
                    <td>
                        <strong><?php echo htmlspecialchars($row['betreff']); ?></strong>
                        
                        <div class="task-details">
                            <p><strong>Beschreibung:</strong> <?php echo nl2br(htmlspecialchars($row['beschreibung'])); ?></p>
                            <p style="margin-top: 5px;"><strong>Datum:</strong> <?php echo date("d.m.Y", strtotime($row['daten'])); ?></p>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($row['fach']); ?></td>
                    <td class="status-<?php echo strtolower($row['zustand']); ?>"><?php echo htmlspecialchars($row['zustand']); ?></td>
                    <td>
                        <?php if ($row['zustand'] == 'Ausstehen'): ?>
                            <a href="?toggle=<?php echo $row['id']; ?>&st=<?php echo $row['zustand']; ?>" class="btn-action btn-status" title="Marcar como Completado">✔</a>
                        <?php else: ?>
                            <a href="?toggle=<?php echo $row['id']; ?>&st=<?php echo $row['zustand']; ?>" class="btn-action btn-undo" title="Deshacer (Pendiente)">↺</a>
                        <?php endif; ?>

                        <a href="?delete=<?php echo $row['id']; ?>" class="btn-action btn-del" title="Eliminar">🗑</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <br>
        <a href="agenda.php" class="btn-link" style="text-decoration:none; display:block; text-align:center; padding:12px;">+ Neue Aufgabe</a>
    </div>

    <script src=".agenda.js"></script>
</body>
</html>