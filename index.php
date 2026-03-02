<?php
// 1. Cargamos el motor de idiomas (como index y el motor están en la raíz, no hace falta poner carpetas)
require_once 'idiomas.php';
?>
<!DOCTYPE html>
<html lang="<?php echo isset($_SESSION['idioma_seleccionado']) ? $_SESSION['idioma_seleccionado'] : 'de'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($lang['titulo_index']) ? $lang['titulo_index'] : 'Main Brain'; ?></title>
    <link rel="stylesheet" href="./css/menu.css">
    
    <style>
        /* Estilos rápidos para que el selector de idiomas encaje bien en el menú principal */
        .lang-switcher { text-align: center; margin-bottom: 25px; }
        .lang-btn { text-decoration: none; padding: 6px 12px; border-radius: 6px; font-weight: bold; font-size: 0.9rem; margin: 0 5px; transition: 0.2s; display: inline-block; }
        .lang-btn.active { background-color: #0284c7; color: white; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.3); }
        .lang-btn.inactive { background-color: #e2e8f0; color: #475569; }
        .lang-btn.inactive:hover { background-color: #cbd5e1; color: #0f172a; }
    </style>
</head>
<body>

    <div id="principal">
        
        <div class="lang-switcher">
            <?php $idioma_actual = isset($_SESSION['idioma_seleccionado']) ? $_SESSION['idioma_seleccionado'] : 'de'; ?>
            <a href="?lang=cat" class="lang-btn <?php echo $idioma_actual == 'cat' ? 'active' : 'inactive'; ?>">CAT</a>
            <a href="?lang=es" class="lang-btn <?php echo $idioma_actual == 'es' ? 'active' : 'inactive'; ?>">ES</a>
            <a href="?lang=en" class="lang-btn <?php echo $idioma_actual == 'en' ? 'active' : 'inactive'; ?>">EN</a>
            <a href="?lang=de" class="lang-btn <?php echo $idioma_actual == 'de' ? 'active' : 'inactive'; ?>">DE</a>
        </div>

        <h2><?php echo isset($lang['menu_principal']) ? $lang['menu_principal'] : 'Menú Principal'; ?></h2>
        
        <nav class="menu-container">
            <a href="./paginas/agenda/agendaMenu.html" class="btn-link"><?php echo isset($lang['btn_agenda']) ? $lang['btn_agenda'] : 'Agenda'; ?></a>
            <a href="./paginas/Personal/readme.txt" class="btn-link"><?php echo isset($lang['btn_personal']) ? $lang['btn_personal'] : 'Personal'; ?></a>
            <a href="academico.php" class="btn-link"><?php echo isset($lang['btn_academico']) ? $lang['btn_academico'] : 'Académico'; ?></a>
            <a href="./paginas/estadoServer.php" class="btn-link"><?php echo isset($lang['btn_estado_server']) ? $lang['btn_estado_server'] : 'Estado del servidor'; ?></a>
        </nav>
    </div>

</body>
</html>