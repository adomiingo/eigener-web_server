<?php
$ip_casa = 'motxitorouter.duckdns.org'; // Tu dominio o IP pública
$puerto_rdp = 54321; // El puerto de tu router para RDP
$puerto_wow = 9;
$mac_werkstatt = 'D8-43-AE-4F-75-6C';

$accion = $_GET['accion'] ?? 'estado';

if ($accion === 'estado') {
    // Comprobamos si el PC responde al puerto RDP (Tiempo de espera: 2 segundos)
    $conexion = @fsockopen($ip_casa, $puerto_rdp, $errno, $errstr, 2);
    if ($conexion) {
        fclose($conexion);
        echo 'ON';
    } else {
        echo 'OFF';
    }
} 
elseif ($accion === 'encender') {
    // Lógica del Paquete Mágico
    $mac_limpia = str_replace(array(':', '-'), '', $mac_werkstatt);
    $mac_binario = pack('H12', $mac_limpia);
    $paquete = str_repeat(chr(0xff), 6) . str_repeat($mac_binario, 16);
    
    $ip_resuelta = gethostbyname($ip_casa);
    $fp = @fsockopen('udp://' . $ip_resuelta, $puerto_wow, $errno, $errstr);
    if ($fp) {
        fwrite($fp, $paquete);
        fclose($fp);
        echo 'ENCENDIDO_OK';
    } else {
        echo 'ERROR';
    }
}
elseif ($accion === 'apagar') {
    // Configuramos la llamada al script de Python
    $token = 'WerkstattPower26'; // El mismo que pusiste en Python
    $puerto_apagado = 54322; // El puerto externo del router ZTE
    
    // Construimos la URL: http://motxitorouter.duckdns.org:54322/apagar?token=WerkstattPower26
    $url = "http://" . $ip_casa . ":" . $puerto_apagado . "/apagar?token=" . $token;
    
    // Hacemos la llamada con un tiempo máximo de espera de 3 segundos
    $contexto = stream_context_create(['http' => ['timeout' => 3]]);
    $respuesta = @file_get_contents($url, false, $contexto);
    
    if ($respuesta) {
        echo 'APAGADO_OK'; 
    } else {
        echo 'ERROR';
    }
}
?>