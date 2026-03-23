<?php
// 0. OBLIGAR AL iPHONE A LEER EN FORMATO UTF-8 (¡La línea mágica para los acentos!)
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
            
            // 1. Decodifica símbolos HTML raros de los periódicos (ej: &aacute; -> á)
            $titulo = html_entity_decode($titulo, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            // 2. Limpiamos el nombre del periódico al final del titular
            $titulo = preg_replace('/ - .*/', '', $titulo);
            
            $noticias_texto .= $titulo . ". ... ";
            $count++;
        }
    }
    return $noticias_texto;
}

// 2. NOTICIAS DE BARCELONA (Últimas 24h - 3 noticias)
$texto_final .= "Noticias más importantes de Barcelona. ";
$url_bcn = "https://news.google.com/rss/search?q=Barcelona+when:24h&hl=es&gl=ES";
$bcn_news = obtener_noticias($url_bcn, 3);
$texto_final .= !empty($bcn_news) ? $bcn_news : "Hoy no hay titulares destacados en Barcelona. ... ";

// 3. NOTICIAS DEL FC BARCELONA (Últimas 12h - 2 noticias)
$texto_final .= "Actualidad del Fútbol Club Barcelona. ";
$url_fcb = "https://news.google.com/rss/search?q=FC+Barcelona+when:12h&hl=es&gl=ES";
$fcb_news = obtener_noticias($url_fcb, 2);
$texto_final .= !empty($fcb_news) ? $fcb_news : "Sin novedades destacables en el equipo en las últimas horas. ... ";

// 4. NOTICIAS DEL MUNDO (Últimas 24h - 3 noticias)
$texto_final .= "Titulares internacionales. ";
$url_mundo = "https://news.google.com/rss/headlines/section/topic/WORLD?hl=es&gl=ES";
$mundo_news = obtener_noticias($url_mundo, 3);
$texto_final .= !empty($mundo_news) ? $mundo_news : "Sin titulares internacionales disponibles en este momento. ... ";

// 5. EL TIEMPO
$lat = 41.1561; 
$lon = 1.1069;
$url_tiempo = "https://api.open-meteo.com/v1/forecast?latitude=$lat&longitude=$lon&daily=temperature_2m_max,temperature_2m_min,precipitation_probability_max&timezone=Europe%2FMadrid&forecast_days=1";

$clima_json = @file_get_contents($url_tiempo);
if ($clima_json) {
    $clima_datos = json_decode($clima_json, true);
    $temp_max = round($clima_datos['daily']['temperature_2m_max'][0]);
    $temp_min = round($clima_datos['daily']['temperature_2m_min'][0]);
    $lluvia_prob = $clima_datos['daily']['precipitation_probability_max'][0];
    
    $texto_final .= "Previsión del tiempo para hoy. Tendremos una temperatura máxima de $temp_max grados, y una mínima de $temp_min grados. ";
    if ($lluvia_prob > 30) {
        $texto_final .= "Hay un $lluvia_prob por ciento de probabilidad de lluvia, te recomiendo llevar paraguas. ... ";
    } else {
        $texto_final .= "La probabilidad de lluvia es muy baja, apenas un $lluvia_prob por ciento. ... ";
    }
} else {
    $texto_final .= "No he podido conectar con el satélite meteorológico en este momento. ... ";
}

// 6. TAREAS MATUTINAS
$db_path = "/var/www/ubungen/kalender.db"; 
try {
    $db = new PDO("sqlite:$db_path");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $db->query("SELECT betreff, fach, beschreibung FROM aufgaben WHERE zustand = 'Ausstehen' AND recordar_matutino = 1 ORDER BY daten ASC");
    $tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $cantidad = count($tareas);
    
    $texto_final .= "Pasando a tu agenda personal. ";
    
    if ($cantidad == 0) {
        $texto_final .= "No tienes tareas matutinas pendientes. Tómate la mañana con calma. ... ";
    } else {
        $texto_final .= "Tienes $cantidad recordatorios para esta mañana. ... ";
        foreach ($tareas as $t) {
            // Limpiamos también las tareas de tu BD por si escribiste algún acento raro
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
    $texto_final .= "Ha habido un problema leyendo tus tareas de la base de datos. ... "; 
}

// 7. DESPEDIDA J.A.R.V.I.S.
$texto_final .= "Recuerda que eres una puta máquina de matar y nadie puede contigo, buena suerte y buenos días";

echo $texto_final;
?>