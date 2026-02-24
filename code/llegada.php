<?php
// 1. SEGURIDAD: Token secreto en la URL
$TOKEN_SECRETO = "aineta"; 

if (!isset($_GET['token']) || $_GET['token'] !== $TOKEN_SECRETO) {
    http_response_code(403);
    die("❌ Acceso denegado. Token incorrecto.");
}

// 2. CONFIGURACIÓN BASE DE DATOS Y TELEGRAM
$db_path = "/var/www/ubungen/kalender.db";
try {
    $db = new PDO("sqlite:$db_path");
} catch (PDOException $e) { die("Error DB: " . $e->getMessage()); }

$TELEGRAM_TOKEN = "8794845655:AAG2FGe4LPWaYBxganYF4pTYC0uIyTLqpTg";
$CHAT_ID = "5181963608";

function enviar_telegram($mensaje) {
    global $TELEGRAM_TOKEN, $CHAT_ID;
    $url = "https://api.telegram.org/bot" . $TELEGRAM_TOKEN . "/sendMessage";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['chat_id' => $CHAT_ID, 'text' => $mensaje, 'parse_mode' => 'Markdown']));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    curl_close($ch);
}

// 3. LÓGICA DE BÚSQUEDA Y ENVÍO (Igual que tu Python)
$hoy = date('Y-m-d');
$stmt = $db->query("SELECT betreff, fach, daten FROM aufgaben WHERE zustand = 'Ausstehen' ORDER BY daten ASC");
$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($tareas) > 0) {
    foreach ($tareas as $t) {
        $betreff = $t['betreff'];
        $fach = $t['fach'];
        $daten = $t['daten'];
        $fecha_formato = date("d-m-Y", strtotime($daten));

        if ($daten == $hoy) {
            $mensaje = "🚨 *¡URGENTE PARA HOY!*\n⚠️ *$fach*: $betreff";
        } elseif (strtolower($fach) == 'personal') {
            $mensaje = "🏠 *PERSONAL PENDIENTE*\n🔹 $betreff\n📅 Fecha: $fecha_formato";
        } else {
            $mensaje = "🎓 *ACADÉMICA PENDIENTE*\n📚 *$fach*: $betreff\n📅 Fecha: $fecha_formato";
        }

        enviar_telegram($mensaje);
        usleep(300000); // Pausa de 0.3 segundos
    }
    echo "✅ Mensajes enviados a Telegram correctamente.";
} else {
    echo "📭 No hay tareas pendientes.";
}
?>