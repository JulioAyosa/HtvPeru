<?php
require_once 'conexion.php';
http_response_code(404);

// Cargar logos y configuraciones
$configs = [];
$stmt_cfg = $pdo->query("SELECT clave, valor FROM configuracion");
while ($row = $stmt_cfg->fetch()) { $configs[$row['clave']] = $row['valor']; }

$site_name = $configs['nombre_sitio'] ?? 'HTVPERU';
$site_color = $configs['color_primario'] ?? '#2563eb';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada - <?php echo htmlspecialchars($site_name); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <?= \App\Services\AssetManager::css('css/style.css') ?>
    <style>
        .error-container { min-height: 70vh; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 2rem; }
        .error-code { font-size: 8rem; font-weight: 800; color: <?php echo htmlspecialchars($site_color); ?>; line-height: 1; margin-bottom: 1rem; text-shadow: 4px 4px 0px rgba(0,0,0,0.05); }
        .error-title { font-size: 2rem; font-weight: 800; color: #1e293b; margin-bottom: 1rem; }
        .error-desc { color: #64748b; font-size: 1.1rem; max-width: 500px; margin-bottom: 2rem; }
        .btn-home { background-color: <?php echo htmlspecialchars($site_color); ?>; color: white; padding: 1rem 2rem; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: transform 0.2s, box-shadow 0.2s; }
        .btn-home:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(37,99,235,0.3); color: white; }
    </style>
</head>
<body>
    <?php include 'includes/header_global.php'; ?>

    <main class="container error-container">
        <div class="error-code">404</div>
        <h1 class="error-title">Página no encontrada</h1>
        <p class="error-desc">Lo sentimos, no pudimos encontrar la página que estás buscando. Puede que haya sido movida, eliminada, o que la dirección sea incorrecta.</p>
        <a href="index.php" class="btn-home"><i class="ri-home-4-line"></i> Volver a la Portada</a>
    </main>

    <?php include 'includes/footer_global.php'; ?>
</body>
</html>
