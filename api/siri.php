<?php
// 1. SEGURIDAD
$TOKEN_SECRETO = "aineta"; 
if (!isset($_GET['token']) || $_GET['token'] !== $TOKEN_SECRETO) {
    http_response_code(403);
    die("Acceso denegado.");
}

// 2. CONEXIÓN A BASE DE DATOS
$db_path = "/var/www/ubungen/kalender.db"; 
try {
    $db = new PDO("sqlite:$db_path");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { 
    die("Error al conectar con la base de datos."); 
}

// 3. PREPARAR LA CONSULTA SEGÚN EL TIPO (Añadido 'beschreibung')
$tipo = isset($_GET['tipo']) ? strtolower($_GET['tipo']) : '';
$sql = "";
$intro_siri = "";

switch ($tipo) {
    case 'academicas':
        $sql = "SELECT betreff, fach, beschreibung FROM aufgaben WHERE zustand = 'Ausstehen' AND fach NOT IN ('Personal', 'Server', 'Ideas') ORDER BY daten ASC";
        $intro_siri = "tareas académicas";
        break;
    case 'personales':
        $sql = "SELECT betreff, fach, beschreibung FROM aufgaben WHERE zustand = 'Ausstehen' AND fach = 'Personal' ORDER BY daten ASC";
        $intro_siri = "tareas personales";
        break;
    case 'ideas':
        $sql = "SELECT betreff, fach, beschreibung FROM aufgaben WHERE zustand = 'Ausstehen' AND fach IN ('Server', 'Ideas') ORDER BY daten ASC";
        $intro_siri = "ideas para el servidor";
        break;
    case 'matutinas':
        $sql = "SELECT betreff, fach, beschreibung FROM aufgaben WHERE zustand = 'Ausstehen' AND recordar_matutino = 1 ORDER BY daten ASC";
        $intro_siri = "recordatorios para esta mañana";
        break;
    case 'casa':
        $sql = "SELECT betreff, fach, beschreibung FROM aufgaben WHERE zustand = 'Ausstehen' AND recordar_casa = 1 ORDER BY daten ASC";
        $intro_siri = "tareas para hacer en casa";
        break;
    default:
        die("No reconozco ese tipo de tareas.");
}

// 4. EJECUTAR Y LEER
$stmt = $db->query($sql);
$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
$cantidad = count($tareas);

// 5. CONSTRUIR EL GUION PARA LA VOZ
if ($cantidad == 0) {
    echo "No tienes $intro_siri pendientes. ¡Todo al día!";
} else {
    $texto_siri = "Tienes $cantidad $intro_siri pendientes. ";
    
    $contador = 1;
    foreach ($tareas as $t) {
        // Obtenemos los datos en texto plano
        $nombre = trim($t['betreff']);
        $asignatura = trim($t['fach']);
        $descripcion = trim($t['beschreibung']);
        
        // 1º Parte: El título de la tarea
        if ($tipo == 'personales' || $tipo == 'ideas') {
            $texto_siri .= "Tarea $contador: $nombre. ";
        } else {
            $texto_siri .= "De $asignatura: $nombre. ";
        }
        
        // 2º Parte: La descripción (Solo si existe)
        if (!empty($descripcion)) {
            $texto_siri .= "Detalles: $descripcion. ";
        }
        
        // Pausa imaginaria para que Siri respire antes de la siguiente
        $texto_siri .= " ... "; 
        
        $contador++;
    }
    
    echo $texto_siri;
}
?>