<?php
// 1. Cargamos el motor de idiomas (como index y el motor están en la raíz, no hace falta poner carpetas)
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
    'cat' => 'CAT', // El catalán no tiene emoji oficial universal, usamos las siglas
    'de' => '🇩🇪 DE',
    'en' => '🇬🇧 EN',
    'es' => '🇪🇸 ES'
];
$bandera_mostrar = isset($banderas[$idioma_actual]) ? $banderas[$idioma_actual] : '🇩🇪 DE';
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
        /* Botón de idioma rotativo en la esquina superior derecha */
        .btn-lang-cycle {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #ffffff;
            border: 2px solid #e2e8f0;
            color: #475569;
            padding: 8px 16px;
            border-radius: 30px;
            font-weight: bold;
            font-size: 0.95rem;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
            z-index: 1000;
        }

        .btn-lang-cycle:hover {
            background-color: #f8fafc;
            transform: translateY(-2px);
            border-color: #cbd5e1;
            color: #0f172a;
        }

        .lang-btn.active {
            background-color: #0284c7;
            color: white;
            box-shadow: 0 2px 4px rgba(2, 132, 199, 0.3);
        }

        .lang-btn.inactive {
            background-color: #e2e8f0;
            color: #475569;
        }

        .lang-btn.inactive:hover {
            background-color: #cbd5e1;
            color: #0f172a;
        }
    </style>
</head>

<body>

    <div id="principal">

        <div class="lang-switcher">
            <a href="?lang=<?php echo $siguiente_idioma; ?>" class="btn-lang-cycle" title="Cambiar idioma">
            <?php echo $bandera_mostrar; ?> ↻
        </a>
        </div>

        <h2><?php echo isset($lang['menu_principal']) ? $lang['menu_principal'] : 'Menú Principal'; ?></h2>

        <nav class="menu-container">
            <a href="./paginas/agenda/agendaMenu.php"
                class="btn-link"><?php echo isset($lang['btn_agenda']) ? $lang['btn_agenda'] : 'Agenda'; ?></a>
            <a href="./paginas/Personal/readme.txt"
                class="btn-link"><?php echo isset($lang['btn_personal']) ? $lang['btn_personal'] : 'Personal'; ?></a>
            <a href="academico.php"
                class="btn-link"><?php echo isset($lang['btn_academico']) ? $lang['btn_academico'] : 'Académico'; ?></a>
            <a href="./paginas/estadoServer.php"
                class="btn-link"><?php echo isset($lang['btn_estado_server']) ? $lang['btn_estado_server'] : 'Estado del servidor'; ?></a>
        </nav>
    </div>

</body>

</html>