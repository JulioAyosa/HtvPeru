<?php
// app/Views/layouts/admin.php
// Plantilla Maestra del Panel de Administración
$user_name = $_SESSION['user_name'] ?? 'Administrador';
$user_role = $_SESSION['user_role'] ?? 'admin';
$page_title = $page_title ?? 'Panel de Administración';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Administrador HTVPERU</title>
    
    <!-- Fuentes e Iconos -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <!-- Admin CSS Pipeline -->
    <?= \App\Services\AssetManager::css('css/admin.css') ?>
</head>
<body>
    <div class="admin-layout">
        <!-- Inclusión del Sidebar Original (por ahora llamando al legacy, luego lo movemos si es necesario) -->
        <?php 
        // Solución temporal para el sidebar en la migración: usar el que está en la raíz 
        // ya que otros módulos siguen apuntando a él.
        // O mejor: lo incluimos directamente desde la raíz por compatibilidad cruzada.
        include __DIR__ . '/../../../admin_sidebar.php'; 
        ?>

        <main class="admin-main">
            <!-- Header dinámico del admin -->
            <div class="admin-header" style="display:none;"></div>

            <!-- Contenido Inyectado -->
            <?php 
                if (isset($view_content)) {
                    echo $view_content;
                }
            ?>
        </main>
    </div>
</body>
</html>
