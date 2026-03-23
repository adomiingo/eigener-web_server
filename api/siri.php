<?php
// 1. SEGURIDAD: Token secreto en la URL
$TOKEN_SECRETO = "aineta"; 

if (!isset($_GET['token']) || $_GET['token'] !== $TOKEN_SECRETO) {
    http_response_code(403);
    die("Acceso denegado.");
}

// 2. CONEXIÓN A LA BASE DE DATOS
$db_path = "/var/www/ubungen/kalender.db"; // Asegúrate de que esta es tu ruta correcta
try {
    $db = new PDO("sqlite:$db_path");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { 
    die("Error al conectar con la base de datos de tu servidor."); 
}

// 3. ¿QUÉ QUIERES QUE TE LEA?
$tipo = isset($_GET['tipo']) ? strtolower($_GET['tipo']) : '';

if ($tipo == 'personales') {
    $stmt = $db->query("SELECT betreff FROM aufgaben WHERE zustand = 'Ausstehen' AND fach = 'Personal' ORDER BY daten ASC");
    $categoria_texto = "personales";
} elseif ($tipo == 'academicas') {
    $stmt = $db->query("SELECT betreff, fach FROM aufgaben WHERE zustand = 'Ausstehen' AND fach NOT IN ('Personal', 'Ideas') ORDER BY daten ASC");
    $categoria_texto = "académicas";
} else {
    die("Lo siento, no reconozco ese tipo de tareas.");
}

$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
$cantidad = count($tareas);

// 4. GENERAR EL TEXTO HABLADO PARA SIRI
if ($cantidad == 0) {
    echo "No tienes tareas $categoria_texto pendientes. ¡Buen trabajo!";
} else {
    $plural = ($cantidad == 1) ? "tarea" : "tareas";
    $texto_siri = "Tienes $cantidad $plural $categoria_texto pendientes. ";
    
    $contador = 1;
    foreach ($tareas as $t) {
        // Limpiamos un poco el texto por si hay caracteres raros que Siri no sepa leer
        $nombre_tarea = htmlspecialchars($t['betreff']);
        
        if ($tipo == 'academicas') {
            $asignatura = $t['fach'];
            $texto_siri .= "De la asignatura $asignatura, $nombre_tarea. ";
        } else {
            $texto_siri .= "Número $contador: $nombre_tarea. ";
        }
        $contador++;
    }
    
    // Esto es lo que Siri leerá en voz alta
    echo $texto_siri;
}
?>