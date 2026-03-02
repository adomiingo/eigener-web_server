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