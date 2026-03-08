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
    <title><?php echo isset($lang['titulo_visualizar']) ? $lang['titulo_visualizar'] : 'Elegir Progreso'; ?></title>
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
        <h2>👁️ <?php echo isset($lang['h2_visualizar']) ? $lang['h2_visualizar'] : '¿Qué quieres visualizar?'; ?></h2>
        
        <nav class="menu-container">
            <a href="presentacion.php?cat=peso/frente" class="btn-link">
                ⚖️ <?php echo isset($lang['btn_peso_frente']) ? $lang['btn_peso_frente'] : 'Peso - Vista Frente'; ?>
            </a>
            
            <a href="presentacion.php?cat=peso/perfil" class="btn-link">
                ⚖️ <?php echo isset($lang['btn_peso_perfil']) ? $lang['btn_peso_perfil'] : 'Peso - Vista Perfil'; ?>
            </a>
            
            <a href="presentacion.php?cat=musculo" class="btn-link" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white;">
                💪 <?php echo isset($lang['btn_musculo']) ? $lang['btn_musculo'] : 'Progreso Muscular'; ?>
            </a>
            
            <a href="index.php" class="btn-link" style="margin-top: 25px; background: linear-gradient(135deg, #6c757d, #495057); color: white;">
                ⬅ <?php echo isset($lang['volver']) ? $lang['volver'] : 'Volver'; ?>
            </a>
        </nav>
    </div>
</body>
</html>