<?php
// === ZONA DE CONFIGURACIÓN ===
$mac_werkstatt = 'D8-43-AE-4F-75-6C';
$ip_o_dominio_casa = 'motxitorouter.duckdns.org'; 
$puerto_wow = 9; // El puerto UDP que abriste en el ZTE
// =============================

function enviarPaqueteMagico($mac, $ip, $puerto) {
    $mac_limpia = str_replace(array(':', '-'), '', $mac);
    
    if (!ctype_xdigit($mac_limpia) || strlen($mac_limpia) != 12) {
        return "Error: Formato de MAC inválido. Revisa cómo la has escrito.";
    }

    $mac_binario = pack('H12', $mac_limpia);
    $paquete = str_repeat(chr(0xff), 6) . str_repeat($mac_binario, 16);
    $ip_resuelta = gethostbyname($ip);

    // Disparamos el paquete por UDP
    $fp = @fsockopen('udp://' . $ip_resuelta, $puerto, $errno, $errstr);
    if ($fp) {
        fwrite($fp, $paquete);
        fclose($fp);
        return "Connexion stablished with :($ip_resuelta), good luck pilot";
    } else {
        return "Error al abrir el túnel: $errstr ($errno)";
    }
}

echo enviarPaqueteMagico($mac_werkstatt, $ip_o_dominio_casa, $puerto_wow);
?>