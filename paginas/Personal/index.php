<?php
// Subimos dos niveles (Personal -> paginas -> raíz) para encontrar el motor de idiomas
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
    <title><?php echo isset($lang['titulo_personal']) ? $lang['titulo_personal'] : 'Área Personal'; ?></title>
    <link rel="stylesheet" href="../../css/menu.css">
</head>

<body>

    <a href="?lang=<?php echo $siguiente_idioma; ?>" class="btn-lang-cycle" title="Cambiar idioma">
        <?php echo $bandera_mostrar; ?> ↻
    </a>

    <div id="principal">
        <h2><?php echo isset($lang['titulo_personal']) ? $lang['titulo_personal'] : 'Área Personal'; ?></h2>

        <nav class="menu-container">
            <a href="./documentacion_resguardos/" class="btn-link" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <?php echo isset($lang['btn_docs']) ? $lang['btn_docs'] : '📁 Documentación y Resguardos'; ?>
            </a>
            
            <a href="./proyecto_ubermensch/" class="btn-link" style="background: linear-gradient(135deg, #10b981, #047857);">
                <?php echo isset($lang['btn_ubermensch']) ? $lang['btn_ubermensch'] : '🏋️‍♂️ Proyecto Übermensch'; ?>
            </a>
            
            <a href="./proyectos_personales/" class="btn-link" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9);">
                <?php echo isset($lang['btn_proyectos_pers']) ? $lang['btn_proyectos_pers'] : '💻 Proyectos Personales'; ?>
            </a>
            
            <a href="./recuerdos/" class="btn-link" style="background: linear-gradient(135deg, #ec4899, #be185d);">
                <?php echo isset($lang['btn_recuerdos']) ? $lang['btn_recuerdos'] : '📸 Recuerdos'; ?>
            </a>
            
            <a href="../../index.php" class="btn-link" style="margin-top: 25px; background: linear-gradient(135deg, #6c757d, #495057);">
                ⬅ <?php echo isset($lang['volver']) ? $lang['volver'] : 'Atrás'; ?>
            </a>
        </nav>
    </div>

</body>

</html>