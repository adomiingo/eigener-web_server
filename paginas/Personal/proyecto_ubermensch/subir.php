<?php
// Motor de idiomas
require_once '../../../code/controladores/idiomas.php';
$idioma_actual = isset($_SESSION['idioma_seleccionado']) ? $_SESSION['idioma_seleccionado'] : 'de';
$rotacion = ['cat' => 'de', 'de'  => 'en', 'en'  => 'es', 'es'  => 'cat'];
$siguiente_idioma = isset($rotacion[$idioma_actual]) ? $rotacion[$idioma_actual] : 'de';
$banderas = ['cat' => 'CAT', 'de'  => '🇩🇪 DE', 'en'  => '🇬🇧 EN', 'es'  => '🇪🇸 ES'];
$bandera_mostrar = isset($banderas[$idioma_actual]) ? $banderas[$idioma_actual] : '🇩🇪 DE';

$mensaje = "";
$tipo_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['foto'])) {
    $categoria = $_POST['categoria'];
    $archivo = $_FILES['foto'];
    
    // Validamos que sea una imagen
    $permitidos = ['image/jpeg', 'image/png', 'image/webp'];
    if (in_array($archivo['type'], $permitidos)) {
        
        // Generamos un nombre único basado en la fecha
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombre_nuevo = date('Y-m-d_His') . "." . $extension;
        
        $ruta_destino = "uploads/" . $categoria . "/" . $nombre_nuevo;
        
        if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
            $mensaje = isset($lang['msg_progreso_exito']) ? $lang['msg_progreso_exito'] : "¡Progreso subido con éxito!";
            $tipo_msg = "success";
        } else {
            $mensaje = isset($lang['msg_progreso_error']) ? $lang['msg_progreso_error'] : "Error al guardar. Revisa los permisos.";
            $tipo_msg = "error";
        }
    } else {
        $mensaje = isset($lang['msg_formato_invalido']) ? $lang['msg_formato_invalido'] : "Formato no válido. Solo JPG, PNG o WEBP.";
        $tipo_msg = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $idioma_actual; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($lang['titulo_subir']) ? $lang['titulo_subir'] : 'Subir Progreso'; ?></title>
    <link rel="stylesheet" href="../../../css/menu.css">
    <style>
        .btn-lang-cycle { position: absolute; top: 20px; right: 20px; background-color: #ffffff; border: 2px solid #e2e8f0; color: #475569; padding: 8px 16px; border-radius: 30px; font-weight: bold; font-size: 0.95rem; text-decoration: none; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05); transition: all 0.2s ease; z-index: 1000; }
        .btn-lang-cycle:hover { background-color: #f8fafc; transform: translateY(-2px); border-color: #cbd5e1; color: #0f172a; }

        .form-group { margin-bottom: 20px; text-align: left; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #334155; }
        select, input[type="file"] { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 1rem; box-sizing: border-box;}
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; text-align: center; }
        .success { background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #b91c1c; }
        .btn-submit { width: 100%; padding: 15px; border: none; border-radius: 8px; background: #0284c7; color: white; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: 0.2s; }
        .btn-submit:hover { background: #0369a1; }
        
        @media (max-width: 480px) { .btn-lang-cycle { top: 10px; right: 10px; padding: 6px 12px; font-size: 0.85rem; } }
    </style>
</head>
<body>
    
    <a href="?lang=<?php echo $siguiente_idioma; ?>" class="btn-lang-cycle" title="Cambiar idioma">
        <?php echo $bandera_mostrar; ?> ↻
    </a>

    <div id="principal">
        <h2>📸 <?php echo isset($lang['titulo_subir']) ? $lang['titulo_subir'] : 'Subir Progreso'; ?></h2>
        
        <?php if($mensaje): ?>
            <div class="alert <?php echo $tipo_msg; ?>"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label><?php echo isset($lang['label_que_registrar']) ? $lang['label_que_registrar'] : '¿Qué vas a registrar hoy?'; ?></label>
                <select name="categoria" required>
                    <option value="peso/frente">⚖️ <?php echo isset($lang['btn_peso_frente']) ? $lang['btn_peso_frente'] : 'Peso - Vista Frente'; ?></option>
                    <option value="peso/perfil">⚖️ <?php echo isset($lang['btn_peso_perfil']) ? $lang['btn_peso_perfil'] : 'Peso - Vista Perfil'; ?></option>
                    <option value="musculo">💪 <?php echo isset($lang['btn_musculo']) ? $lang['btn_musculo'] : 'Progreso Muscular'; ?></option>
                </select>
            </div>
            
            <div class="form-group">
                <label><?php echo isset($lang['label_selecciona_foto']) ? $lang['label_selecciona_foto'] : 'Selecciona la foto:'; ?></label>
                <input type="file" name="foto" accept="image/*" required>
            </div>
            
            <button type="submit" class="btn-submit"><?php echo isset($lang['btn_subir_foto']) ? $lang['btn_subir_foto'] : 'Subir Foto'; ?></button>
        </form>

        <nav class="menu-container" style="margin-top: 25px;">
            <a href="index.php" class="btn-link" style="background: #e2e8f0; color: #475569;">
                ⬅ <?php echo isset($lang['volver_menu']) ? $lang['volver_menu'] : 'Volver al Menú'; ?>
            </a>
        </nav>
    </div>
</body>
</html>