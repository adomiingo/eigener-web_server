<?php
// Subimos dos niveles (agenda -> paginas -> raíz) para encontrar el motor
require_once '../../idiomas.php';

$idioma_actual = isset($_SESSION['idioma_seleccionado']) ? $_SESSION['idioma_seleccionado'] : 'de';
            
$rotacion = [
    'cat' => 'de',
    'de'  => 'en',
    'en'  => 'es',
    'es'  => 'cat'
];
            
$siguiente_idioma = isset($rotacion[$idioma_actual]) ? $rotacion[$idioma_actual] : 'de';
            
$banderas = [
    'cat' => 'CAT',
    'de'  => '🇩🇪 DE',
    'en'  => '🇬🇧 EN',
    'es'  => '🇪🇸 ES'
];
$bandera_mostrar = isset($banderas[$idioma_actual]) ? $banderas[$idioma_actual] : '🇩🇪 DE';
?>
<!DOCTYPE html>
<html lang="<?php echo $idioma_actual; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($lang['titulo_agenda_menu']) ? $lang['titulo_agenda_menu'] : 'Erinnerungen Machen'; ?></title>
    <link rel="stylesheet" href="../../css/menu.css">
</head>

<body>

    <a href="?lang=<?php echo $siguiente_idioma; ?>" class="btn-lang-cycle" title="Cambiar idioma">
        <?php echo $bandera_mostrar; ?> ↻
    </a>

    <div id="principal">
        <h2><?php echo isset($lang['menu_acciones']) ? $lang['menu_acciones'] : 'Aktionen'; ?></h2>

        <nav class="menu-container">
            <a href="./crear_tareas.php" class="btn-link"><?php echo isset($lang['btn_crear_recordatorio']) ? $lang['btn_crear_recordatorio'] : 'Crear Recordatorio'; ?></a>
            <a href="./lista_pendientes.php" class="btn-link"><?php echo isset($lang['btn_tareas_pendientes']) ? $lang['btn_tareas_pendientes'] : 'Tareas Pendientes'; ?></a>
            <a href="./lista_completadas.php" class="btn-link"><?php echo isset($lang['btn_tareas_completadas']) ? $lang['btn_tareas_completadas'] : 'Tareas Completadas'; ?></a>
            <a href="../../index.php" class="btn-link"><?php echo isset($lang['inicio']) ? $lang['inicio'] : 'Inicio'; ?></a>
        </nav>
    </div>

</body>

</html>