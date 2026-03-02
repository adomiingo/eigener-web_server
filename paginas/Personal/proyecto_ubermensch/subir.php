<?php
$mensaje = "";
$tipo_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['foto'])) {
    $categoria = $_POST['categoria'];
    $archivo = $_FILES['foto'];
    
    // Validamos que sea una imagen
    $permitidos = ['image/jpeg', 'image/png', 'image/webp'];
    if (in_array($archivo['type'], $permitidos)) {
        
        // Generamos un nombre único basado en la fecha (Ej: 2026-03-02_143000.jpg)
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombre_nuevo = date('Y-m-d_His') . "." . $extension;
        
        $ruta_destino = "uploads/" . $categoria . "/" . $nombre_nuevo;
        
        if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
            $mensaje = "¡Progreso subido con éxito!";
            $tipo_msg = "success";
        } else {
            $mensaje = "Error al guardar la imagen en el servidor. Revisa los permisos.";
            $tipo_msg = "error";
        }
    } else {
        $mensaje = "Formato no válido. Solo JPG, PNG o WEBP.";
        $tipo_msg = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Progreso</title>
    <link rel="stylesheet" href="../../../css/menu.css">
    <style>
        .form-group { margin-bottom: 20px; text-align: left; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #334155; }
        select, input[type="file"] { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 1rem; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; text-align: center; }
        .success { background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #b91c1c; }
        .btn-submit { width: 100%; padding: 15px; border: none; border-radius: 8px; background: #0284c7; color: white; font-size: 1.1rem; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
    <div id="principal">
        <h2>📸 Subir Progreso</h2>
        
        <?php if($mensaje): ?>
            <div class="alert <?php echo $tipo_msg; ?>"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>¿Qué vas a registrar hoy?</label>
                <select name="categoria" required>
                    <option value="peso/frente">⚖️ Peso - Vista Frente</option>
                    <option value="peso/perfil">⚖️ Peso - Vista Perfil</option>
                    <option value="musculo">💪 Músculo</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Selecciona la foto:</label>
                <input type="file" name="foto" accept="image/*" required>
            </div>
            
            <button type="submit" class="btn-submit">Subir Foto</button>
        </form>

        <nav class="menu-container" style="margin-top: 25px;">
            <a href="index.php" class="btn-link" style="background: #e2e8f0; color: #475569;">⬅ Volver al Menú</a>
        </nav>
    </div>
</body>
</html>