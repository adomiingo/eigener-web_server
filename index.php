<?php
// 1. Cargamos el motor de idiomas
require_once 'idiomas.php';

$idioma_actual = isset($_SESSION['idioma_seleccionado']) ? $_SESSION['idioma_seleccionado'] : 'de';

// 1. Definimos la rotación estricta
$rotacion = [
    'cat' => 'de',
    'de' => 'en',
    'en' => 'es',
    'es' => 'cat'
];

// Calculamos cuál es el enlace que debe tener el botón (el siguiente idioma)
$siguiente_idioma = isset($rotacion[$idioma_actual]) ? $rotacion[$idioma_actual] : 'de';

// 2. Definimos qué texto/bandera mostrar para representar el idioma ACTUAL
$banderas = [
    'cat' => 'CAT',
    'de' => '🇩🇪 DE',
    'en' => '🇬🇧 EN',
    'es' => '🇪🇸 ES'
];
$bandera_mostrar = isset($banderas[$idioma_actual]) ? $banderas[$idioma_actual] : '🇩🇪 DE';
?>
<!DOCTYPE html>
<html lang="<?php echo $idioma_actual; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($lang['titulo_index']) ? $lang['titulo_index'] : 'Main Brain'; ?></title>
    <link rel="stylesheet" href="./css/menu.css">
</head>

<body>

    <a href="?lang=<?php echo $siguiente_idioma; ?>" class="btn-lang-cycle" title="Cambiar idioma">
        <?php echo $bandera_mostrar; ?> ↻
    </a>

    <div id="principal">

        <h2><?php echo isset($lang['menu_principal']) ? $lang['menu_principal'] : 'Menú Principal'; ?></h2>

        <nav class="menu-container">
            <a href="./paginas/agenda/agendaMenu.php" class="btn-link">
                <?php echo isset($lang['btn_agenda']) ? $lang['btn_agenda'] : 'Agenda'; ?>
            </a>
            
            <a href="./paginas/Personal/index.php" class="btn-link">
                <?php echo isset($lang['btn_personal']) ? $lang['btn_personal'] : 'Personal'; ?>
            </a>
            
            <a href="academico.php" class="btn-link">
                <?php echo isset($lang['btn_academico']) ? $lang['btn_academico'] : 'Académico'; ?>
            </a>
            
            <a href="./paginas/estadoServer.php" class="btn-link">
                <?php echo isset($lang['btn_estado_server']) ? $lang['btn_estado_server'] : 'Estado del servidor'; ?>
            </a>
        </nav>
    </div>

</body>

</html>