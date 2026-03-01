<?php
$db_path = "/var/www/ubungen/kalender.db";
try {
    $db = new PDO("sqlite:$db_path");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Error de conexión: " . $e->getMessage()); }

// --- LÓGICA DE ACCIONES ---
if (isset($_GET['restaurar'])) {
    $id = $_GET['restaurar'];
    $stmt_info = $db->prepare("SELECT betreff, beschreibung, fach, daten FROM completadas WHERE id = ?");
    $stmt_info->execute([$id]);
    $tarea = $stmt_info->fetch(PDO::FETCH_ASSOC);
    
    if ($tarea) {
        $stmt_insert = $db->prepare("INSERT INTO aufgaben (betreff, beschreibung, fach, daten, zustand) VALUES (?, ?, ?, ?, 'Ausstehen')");
        $stmt_insert->execute([$tarea['betreff'], $tarea['beschreibung'], $tarea['fach'], $tarea['daten']]);
        
        $stmt_del = $db->prepare("DELETE FROM completadas WHERE id = ?");
        $stmt_del->execute([$id]);
    }
    header("Location: lista_completadas.php");
    exit;
}

if (isset($_GET['borrar'])) {
    $stmt = $db->prepare("DELETE FROM completadas WHERE id = ?");
    $stmt->execute([$_GET['borrar']]);
    header("Location: lista_completadas.php");
    exit;
}

// --- OBTENER LAS TAREAS COMPLETADAS ---
$stmt = $db->query("SELECT * FROM completadas ORDER BY fecha_completada DESC");
$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Tareas</title>
    
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; color: #334155; padding: 20px; margin: 0; box-sizing: border-box; }
.container { max-width: 800px; margin: auto; background: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-top: 5px solid #10b981; box-sizing: border-box; }
h1 { text-align: center; color: #10b981; margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0; }
.info-text { text-align: center; font-size: 0.9em; color: #64748b; margin-bottom: 25px; }

/* Buscador */
.buscador-container { margin-bottom: 20px; }
#buscadorJS { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 0.95rem; box-sizing: border-box; outline: none; transition: border-color 0.3s; }
#buscadorJS:focus { border-color: #10b981; }

/* Tabla Base (Para PC) */
table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
th, td { padding: 15px; text-align: left; border-bottom: 1px solid #f1f5f9; word-wrap: break-word; }
th { background-color: #f8fafc; color: #475569; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }

/* Efectos dinámicos */
.task-row { cursor: pointer; transition: all 0.3s ease; }
.task-row:hover { background-color: #f1f5f9; }
.task-row.expanded { transform: scale(1.02); box-shadow: 0 8px 20px rgba(16, 185, 129, 0.12); background-color: #ffffff; z-index: 10; position: relative; }

.task-details { display: none; margin-top: 10px; padding-top: 10px; border-top: 1px dashed #cbd5e1; font-size: 0.9rem; color: #475569; }
.task-row.expanded .task-details { display: block; animation: fadeIn 0.4s ease; }

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Botones */
.btn-action { text-decoration: none; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; margin-right: 5px; display: inline-block; transition: 0.2s; box-sizing: border-box; }
.btn-restaurar { background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; }
.btn-restaurar:hover { background: #bae6fd; color: #0369a1; }
.btn-borrar { background: #fee2e2; color: #ef4444; border: 1px solid #fecaca; }
.btn-borrar:hover { background: #fecaca; color: #dc2626; }

.empty-msg { text-align: center; color: #94a3b8; margin: 40px 0; font-style: italic; background: #f8fafc; padding: 30px; border-radius: 8px; }
.footer-links { margin-top: 30px; display: flex; gap: 15px; flex-wrap: wrap; }
.btn-link { background: #e2e8f0; color: #475569; padding: 12px; text-decoration: none; border-radius: 6px; text-align: center; flex: 1; min-width: 120px; font-weight: 500; transition: 0.2s; }
.btn-link:hover { background: #cbd5e1; color: #0f172a; }
.badge-date { background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 12px; font-size: 0.8em; font-weight: bold; }

/* ========================================= */
/* 📱 MODO MÓVIL (Magia Responsive SMR)      */
/* ========================================= */
@media (max-width: 768px) {
    body { padding: 10px; }
    .container { padding: 15px; }
    
    /* Forzamos que la tabla se comporte como bloques (Tarjetas) */
    table, thead, tbody, th, td, tr { display: block; width: 100%; box-sizing: border-box; }
    
    /* Ocultamos las cabeceras porque en móvil se sobreentienden */
    thead tr { display: none; }
    
    tr.task-row { 
        margin-bottom: 15px; 
        border: 1px solid #cbd5e1; 
        border-radius: 8px; 
        padding: 15px; 
        background: #fff;
    }
    
    /* 🚨 LA SOLUCIÓN: Quitamos el scale que rompe la pantalla en móviles */
    .task-row.expanded { 
        transform: none; 
        border-left: 4px solid #10b981; 
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.1);
    }
    
    td { padding: 5px 0; border: none; }
    
    /* Título de la tarea (Primera celda) */
    td:first-child { border-bottom: 1px dashed #e2e8f0; padding-bottom: 10px; margin-bottom: 10px; }
    
    /* Asignatura y fecha uno al lado del otro */
    td:nth-child(2), td:nth-child(3) { display: inline-block; margin-right: 15px; width: auto; }
    
    /* Botones de acción: ocupan todo el ancho disponible repartido a partes iguales */
    td:last-child { display: flex; gap: 10px; margin-top: 15px; }
    .btn-action { flex: 1; text-align: center; margin: 0; padding: 10px; }
}</style>
</head>
<body>
    <div class="container">
        <h1>✅ Tareas Completadas</h1>
        <p class="info-text">Historial de tareas finalizadas. Se eliminarán automáticamente del servidor pasados 15 días.</p>

        <div class="buscador-container">
            <input type="text" id="buscadorJS" placeholder="🔍 Buscar tarea en el historial...">
        </div>

        <?php if (count($tareas) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Tarea</th>
                        <th>Asignatura</th>
                        <th>Completada el</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tareas as $t): ?>
                        <tr class="task-row">
                            <td>
                                <strong style="color: #0f172a;"><?php echo htmlspecialchars($t['betreff']); ?></strong>
                                
                                <div class="task-details">
                                    <?php if(!empty($t['beschreibung'])): ?>
                                        <p><strong>Descripción:</strong><br><?php echo nl2br(htmlspecialchars($t['beschreibung'])); ?></p>
                                    <?php else: ?>
                                        <p><em>Sin descripción.</em></p>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><span style="color: #475569; font-weight: 500;"><?php echo htmlspecialchars($t['fach']); ?></span></td>
                            <td>
                                <span class="badge-date"><?php echo date("d-m-Y", strtotime($t['fecha_completada'])); ?></span>
                            </td>
                            <td>
    <a href="lista_completadas.php?restaurar=<?php echo $t['id']; ?>" class="btn-action btn-restaurar" onclick="event.stopPropagation();" title="Devolver a la Agenda principal">♻️ Restaurar</a>
    <a href="lista_completadas.php?borrar=<?php echo $t['id']; ?>" class="btn-action btn-borrar" onclick="event.stopPropagation();" title="Eliminar del servidor permanentemente">🗑️ Borrar</a>
</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-msg">
                <p>No hay tareas completadas en el historial reciente.</p>
            </div>
        <?php endif; ?>

        <div class="footer-links">
            <a href="./agendaMenu.html" class="btn-link">⬅ Volver a la Agenda</a>
            <a href="../estadoServer.php" class="btn-link">📊 Ver Panel de Servidor</a>
        </div>
    </div>

    <script src="./agenda.js"></script>
</body>
</html>