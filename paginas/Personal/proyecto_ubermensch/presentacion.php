<?php
// Motor de idiomas
require_once '../../../code/controladores/idiomas.php';
$idioma_actual = isset($_SESSION['idioma_seleccionado']) ? $_SESSION['idioma_seleccionado'] : 'de';

// 1. Validamos la categoría por seguridad
$cat = isset($_GET['cat']) ? $_GET['cat'] : '';
$categorias_validas = ['peso/frente', 'peso/perfil', 'musculo'];

if (!in_array($cat, $categorias_validas)) {
    die(isset($lang['msg_categoria_invalida']) ? $lang['msg_categoria_invalida'] : "Categoría no válida o acceso denegado.");
}

// 2. Buscamos las fotos en el directorio
$directorio = "uploads/" . $cat . "/";
$fotos = [];

if (is_dir($directorio)) {
    $archivos = scandir($directorio);
    foreach ($archivos as $archivo) {
        $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
        // Solo cogemos imágenes reales
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $fotos[] = $directorio . $archivo;
        }
    }
}

// Ordenamos alfabéticamente (fechas Y-m-d)
sort($fotos);
?>
<!DOCTYPE html>
<html lang="<?php echo $idioma_actual; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($lang['titulo_presentacion']) ? $lang['titulo_presentacion'] : 'Presentación de Progreso'; ?></title>
    <style>
        /* Diseño inmersivo oscuro para ver bien las fotos */
        body { margin: 0; padding: 0; background-color: #000; color: #fff; font-family: sans-serif; height: 100vh; display: flex; flex-direction: column; overflow: hidden; }
        
        /* Barra superior minimalista */
        .top-bar { padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.6); position: absolute; top: 0; width: 100%; box-sizing: border-box; z-index: 10; }
        .btn-back { background: rgba(255,255,255,0.2); color: white; padding: 8px 15px; text-decoration: none; border-radius: 20px; font-weight: bold; transition: 0.2s; }
        .btn-back:hover { background: rgba(255,255,255,0.4); }
        .contador { font-weight: bold; opacity: 0.8; }

        /* Contenedor de la foto */
        .slider-container { flex: 1; display: flex; justify-content: center; align-items: center; position: relative; width: 100%; height: 100%; }
        .foto { max-width: 100%; max-height: 100vh; display: none; object-fit: contain; }
        .foto.activa { display: block; animation: fade 0.5s ease; }
        
        @keyframes fade { from { opacity: 0; } to { opacity: 1; } }

        /* Botones laterales invisibles */
        .zona-click { position: absolute; top: 0; bottom: 0; width: 50%; z-index: 5; cursor: pointer; }
        .zona-click.izq { left: 0; }
        .zona-click.der { right: 0; }
        
        /* Mensaje si no hay fotos */
        .empty-msg { text-align: center; color: #666; font-size: 1.2rem; margin-top: 50vh; transform: translateY(-50%); }
    </style>
</head>
<body>

    <div class="top-bar">
        <a href="visualizar.php" class="btn-back">⬅ <?php echo isset($lang['volver']) ? $lang['volver'] : 'Volver'; ?></a>
        <?php if (count($fotos) > 0): ?>
            <div class="contador"><span id="num-actual">1</span> / <?php echo count($fotos); ?></div>
        <?php endif; ?>
    </div>

    <?php if (count($fotos) > 0): ?>
        <div class="slider-container">
            <div class="zona-click izq" onclick="cambiarFoto(-1)"></div>
            <div class="zona-click der" onclick="cambiarFoto(1)"></div>

            <?php foreach ($fotos as $index => $ruta): ?>
                <img src="<?php echo htmlspecialchars($ruta); ?>" class="foto <?php echo $index === 0 ? 'activa' : ''; ?>">
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-msg">
            <p>📷 <?php echo isset($lang['msg_no_fotos']) ? $lang['msg_no_fotos'] : 'Aún no has subido fotos a esta categoría.'; ?></p>
            <a href="subir.php" style="color: #38bdf8; text-decoration: none; font-weight:bold;">
                <?php echo isset($lang['link_subir_primera']) ? $lang['link_subir_primera'] : 'Ir a subir la primera'; ?>
            </a>
        </div>
    <?php endif; ?>

    <script>
        let fotos = document.querySelectorAll('.foto');
        let actual = 0;
        let numActualSpan = document.getElementById('num-actual');

        function cambiarFoto(direccion) {
            if (fotos.length === 0) return;
            fotos[actual].classList.remove('activa');
            actual += direccion;
            
            if (actual >= fotos.length) actual = 0;
            if (actual < 0) actual = fotos.length - 1;
            
            fotos[actual].classList.add('activa');
            if(numActualSpan) numActualSpan.innerText = actual + 1;
        }

        // Cambio automático cada 3 segundos (Modo presentación real)
        <?php if (count($fotos) > 1): ?>
            setInterval(() => cambiarFoto(1), 3000);
        <?php endif; ?>
    </script>
</body>
</html>