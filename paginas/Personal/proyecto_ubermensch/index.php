<?php
// Motor de idiomas (Ruta corregida: sube 3 niveles)
require_once '../../../code/controladores/idiomas.php';

$idioma_actual = isset($_SESSION['idioma_seleccionado']) ? $_SESSION['idioma_seleccionado'] : 'de';
$rotacion = ['cat' => 'de', 'de'  => 'en', 'en'  => 'es', 'es'  => 'cat'];
$siguiente_idioma = isset($rotacion[$idioma_actual]) ? $rotacion[$idioma_actual] : 'de';
$banderas = ['cat' => 'CAT', 'de'  => '🇩🇪 DE', 'en'  => '🇬🇧 EN', 'es'  => '🇪🇸 ES'];
$bandera_mostrar = isset($banderas[$idioma_actual]) ? $banderas[$idioma_actual] : '🇩🇪 DE';
?>
<!DOCTYPE html>
<html lang="<?php echo $idioma_actual; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($lang['titulo_ubermensch']) ? $lang['titulo_ubermensch'] : 'Proyecto Übermensch'; ?></title>
    <link rel="stylesheet" href="../../../css/menu.css">
    <style>
        .btn-lang-cycle { position: absolute; top: 20px; right: 20px; background-color: #ffffff; border: 2px solid #e2e8f0; color: #475569; padding: 8px 16px; border-radius: 30px; font-weight: bold; font-size: 0.95rem; text-decoration: none; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05); transition: all 0.2s ease; z-index: 1000; }
        .btn-lang-cycle:hover { background-color: #f8fafc; transform: translateY(-2px); border-color: #cbd5e1; color: #0f172a; }
        @media (max-width: 480px) { .btn-lang-cycle { top: 10px; right: 10px; padding: 6px 12px; font-size: 0.85rem; } }
    </style>
</head>
<body>
    
    <a href="?lang=<?php echo $siguiente_idioma; ?>" class="btn-lang-cycle" title="Cambiar idioma">
        <?php echo $bandera_mostrar; ?> ↻
    </a>

    <div id="principal">
        <h2>🏋️‍♂️ <?php echo isset($lang['titulo_ubermensch']) ? $lang['titulo_ubermensch'] : 'Proyecto Übermensch'; ?></h2>
        <p style="text-align: center; color: #64748b; margin-bottom: 25px;">
            <?php echo isset($lang['subtitulo_ubermensch']) ? $lang['subtitulo_ubermensch'] : 'Monitoreo de transformación física'; ?>
        </p>

        <nav class="menu-container">
            <a href="visualizar.php" class="btn-link" style="background: linear-gradient(135deg, #10b981, #047857); color: white;">
                👁️ <?php echo isset($lang['btn_visualizar_progreso']) ? $lang['btn_visualizar_progreso'] : 'Visualizar Progreso'; ?>
            </a>
            <a href="subir.php" class="btn-link">
                📸 <?php echo isset($lang['btn_subir_progreso']) ? $lang['btn_subir_progreso'] : 'Subir Progreso'; ?>
            </a>
            
            <a href="../index.php" class="btn-link" style="margin-top: 20px; background: linear-gradient(135deg, #6c757d, #495057); color: white;">
                ⬅ <?php echo isset($lang['volver_personal']) ? $lang['volver_personal'] : 'Volver a Personal'; ?>
            </a>
        </nav>
    </div>
</body>
</html>