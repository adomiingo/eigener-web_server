<?php
// 0. OBLIGAR AL iPHONE A LEER EN FORMATO UTF-8
header('Content-Type: text/plain; charset=utf-8');

// 1. SEGURIDAD
$TOKEN_SECRETO = "aineta"; 
if (!isset($_GET['token']) || $_GET['token'] !== $TOKEN_SECRETO) {
    http_response_code(403);
    die("Acceso denegado.");
}

$texto_final = "Iniciando resumen diario. ... ";

// --- FUNCIÓN CAZADORA DE NOTICIAS (Con limpieza de acentos) ---
function obtener_noticias($url, $limite) {
    $noticias_texto = "";
    $rss = @simplexml_load_file($url); 
    
    if ($rss && isset($rss->channel->item)) {
        $count = 0;
        foreach ($rss->channel->item as $item) {
            if ($count >= $limite) break;
            $titulo = (string)$item->title;
            
            $titulo = html_entity_decode($titulo, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $titulo = preg_replace('/ - .*/', '', $titulo);
            
            $noticias_texto .= $titulo . ". ... ";
            $count++;
        }
    }
    return $noticias_texto;
}

// 2. NOTICIAS DE BARCELONA (Culturales/Sociales)
$texto_final .= "Agenda y sociedad en Barcelona. ";
$url_bcn = "https://news.google.com/rss/search?q=Barcelona+AND+(cultura+OR+eventos+OR+agenda+OR+sociedad)+when:24h&hl=es&gl=ES";
$bcn_news = obtener_noticias($url_bcn, 3);
$texto_final .= !empty($bcn_news) ? $bcn_news : "Hoy no hay eventos destacados en la agenda de la ciudad. ... ";

// 3. NOTICIAS DEL FC BARCELONA
$texto_final .= "Actualidad del Fútbol Club Barcelona. ";
$url_fcb = "https://news.google.com/rss/search?q=FC+Barcelona+when:12h&hl=es&gl=ES";
$fcb_news = obtener_noticias($url_fcb, 2);
$texto_final .= !empty($fcb_news) ? $fcb_news : "Sin novedades destacables en el equipo. ... ";

// 4. NOTICIAS DEL MUNDO
$texto_final .= "Titulares internacionales. ";
$url_mundo = "https://news.google.com/rss/headlines/section/topic/WORLD?hl=es&gl=ES";
$mundo_news = obtener_noticias($url_mundo, 3);
$texto_final .= !empty($mundo_news) ? $mundo_news : "Sin titulares internacionales en este momento. ... ";

// 5. VERSÍCULO DEL DÍA
$texto_final .= "La palabra de hoy. ";
$url_versiculo = "https://www.biblegateway.com/usage/votd/rss/votd.rdf?31"; // RSS de la Biblia (Reina Valera)
$rss_versiculo = @simplexml_load_file($url_versiculo);
if ($rss_versiculo && isset($rss_versiculo->channel->item[0])) {
    $cita = html_entity_decode((string)$rss_versiculo->channel->item[0]->title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $texto_biblico = html_entity_decode((string)$rss_versiculo->channel->item[0]->description, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    // Limpieza de etiquetas HTML que vienen en la descripción
    $texto_biblico = strip_tags($texto_biblico);
    $texto_final .= "$texto_biblico. Encontrado en $cita. ... ";
} else {
    $texto_final .= "No he podido obtener el versículo de hoy. ... ";
}

// 6. EL TIEMPO (Detallado y por Barrios)
// Coordenadas Centro Barcelona (Plaza Catalunya)
$lat = 41.3879; 
$lon = 2.1699;
$url_tiempo = "https://api.open-meteo.com/v1/forecast?latitude=$lat&longitude=$lon&hourly=temperature_2m,precipitation_probability&daily=temperature_2m_max,temperature_2m_min&timezone=Europe%2FMadrid&forecast_days=1";

$clima_json = @file_get_contents($url_tiempo);
if ($clima_json) {
    $clima_datos = json_decode($clima_json, true);
    
    // Extracción Diaria
    $temp_max = round($clima_datos['daily']['temperature_2m_max'][0]);
    $temp_min = round($clima_datos['daily']['temperature_2m_min'][0]);
    
    // Búsqueda de horas punta (Analizando el array horario)
    $horas = $clima_datos['hourly']['time'];
    $temps_horarias = $clima_datos['hourly']['temperature_2m'];
    
    $max_hora_idx = array_keys($temps_horarias, max($temps_horarias))[0];
    $min_hora_idx = array_keys($temps_horarias, min($temps_horarias))[0];
    
    // Formatear la hora (ej: "2023-10-27T14:00" -> "las 14")
    $hora_maxima = date('G', strtotime($horas[$max_hora_idx]));
    $hora_minima = date('G', strtotime($horas[$min_hora_idx]));
    
    $texto_final .= "Meteorología de Barcelona. En el centro tendremos una máxima de $temp_max grados a las $hora_maxima horas, y una mínima de $temp_min grados sobre las $hora_minima. ";
    
    // Estimación por Barrios (Basado en la altitud y cercanía al mar respecto al centro)
    $temp_guinardo = $temp_max - 1; // Más alto = Ligeramente más fresco
    $temp_trinitat = $temp_max - 1;
    $temp_clot_congr = $temp_max;   // Similar al centro
    
    $texto_final .= "Por zonas: El Clot y Congrés i els Indians mantendrán temperaturas similares al centro, rondando los $temp_clot_congr grados. ";
    $texto_final .= "Hacia zonas más altas, como el Guinardó y la Trinitat Nova, los termómetros se quedarán en torno a los $temp_guinardo grados. ... ";

} else {
    $texto_final .= "No he podido conectar con el satélite meteorológico. ... ";
}

// 7. TAREAS MATUTINAS
$db_path = "/var/www/ubungen/kalender.db"; 
try {
    $db = new PDO("sqlite:$db_path");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $db->query("SELECT betreff, fach, beschreibung FROM aufgaben WHERE zustand = 'Ausstehen' AND recordar_matutino = 1 ORDER BY daten ASC");
    $tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $cantidad = count($tareas);
    
    $texto_final .= "Pasando a tu agenda personal. ";
    
    if ($cantidad == 0) {
        $texto_final .= "No tienes tareas matutinas pendientes. ... ";
    } else {
        $texto_final .= "Tienes $cantidad recordatorios para esta mañana. ... ";
        foreach ($tareas as $t) {
            $nombre = html_entity_decode(trim($t['betreff']), ENT_QUOTES, 'UTF-8');
            $descripcion = html_entity_decode(trim($t['beschreibung']), ENT_QUOTES, 'UTF-8');
            
            $texto_final .= "$nombre. ";
            if (!empty($descripcion)) {
                $texto_final .= "Detalles: $descripcion. ";
            }
            $texto_final .= " ... ";
        }
    }
} catch (PDOException $e) { 
    $texto_final .= "Error al leer tu base de datos. ... "; 
}

// 8. DESPEDIDA J.A.R.V.I.S. (Aleatoria)
$archivo_despedidas = __DIR__ . '/despedidas.txt'; // Busca el txt en la misma carpeta que este php

// Si no existe el archivo, creamos uno con un par de opciones por defecto
if (!file_exists($archivo_despedidas)) {
    $default = "Recuerda que eres una puta máquina de matar y nadie puede contigo, buena suerte y buenos días.\nSistema cerrado, que tengas un excelente día.";
    file_put_contents($archivo_despedidas, $default);
}

// Leer las frases, ignorar líneas vacías y elegir una al azar
$frases = file($archivo_despedidas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if (!empty($frases)) {
    $despedida = $frases[array_rand($frases)];
    $texto_final .= $despedida;
} else {
    $texto_final .= "Buenos días, señor."; // Por si el archivo se queda vacío
}

echo $texto_final;
?>