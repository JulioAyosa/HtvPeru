<?php
// app/Views/layouts/admin.php
// Plantilla Maestra del Panel de Administración
$user_name = $_SESSION['user_name'] ?? 'Administrador';
$user_role = $_SESSION['user_role'] ?? 'admin';
$page_title = $page_title ?? 'Panel de Administración';

if (!isset($notifications)) {
    try {
        $pending_comments = $pdo->query("SELECT COUNT(*) FROM comentarios WHERE estado = 'pendiente'")->fetchColumn();
        $scheduled_news = $pdo->query("SELECT COUNT(*) FROM noticias WHERE estado_publicacion = 'programado' AND fecha_programada >= NOW() AND deleted_at IS NULL")->fetchColumn();
        $new_users = 0;
        try {
            $new_users = $pdo->query("SELECT COUNT(*) FROM usuarios_publicos WHERE DATE(fecha_registro) = CURDATE()")->fetchColumn();
        } catch(\Exception $e) {}
        
        $notifications = [
            'comments' => $pending_comments,
            'scheduled' => $scheduled_news,
            'users' => $new_users,
            'total' => $pending_comments + $scheduled_news + $new_users
        ];
    } catch(\Exception $e) {
        $notifications = ['comments' => 0, 'scheduled' => 0, 'users' => 0, 'total' => 0];
    }
}
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
        <?php include __DIR__ . '/../../../admin_sidebar.php'; ?>

        <main class="admin-main">
            <!-- Header dinámico del admin -->
            <div class="admin-top-bar" style="display:flex; justify-content:flex-end; padding: 1rem 0; margin-bottom: 2rem; border-bottom: 1px solid var(--border-color);">
                <div style="position:relative; cursor:pointer;" onclick="document.getElementById('notif-dropdown').classList.toggle('show')">
                    <i class="ri-notification-3-line" style="font-size: 1.5rem; color: #475569;"></i>
                    <?php if ($notifications['total'] > 0): ?>
                    <span style="position:absolute; top:-5px; right:-5px; background:var(--danger); color:white; font-size:0.7rem; font-weight:bold; border-radius:50%; width:18px; height:18px; display:flex; align-items:center; justify-content:center;"><?= $notifications['total'] ?></span>
                    <?php endif; ?>
                    
                    <div id="notif-dropdown" style="display:none; position:absolute; right:0; top:35px; width:280px; background:white; border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.1); border:1px solid #e2e8f0; z-index:100; padding:0.5rem 0;">
                        <div style="padding: 0.5rem 1rem; font-weight:bold; border-bottom:1px solid #e2e8f0; font-size:0.9rem;">Notificaciones</div>
                        <?php if ($notifications['comments'] > 0): ?>
                        <a href="/piura_noticias_php/admin/comentarios" style="display:flex; align-items:center; gap:0.5rem; padding: 0.75rem 1rem; text-decoration:none; color:#334155; font-size:0.85rem; border-bottom:1px solid #f1f5f9;"><i class="ri-discuss-line" style="color:#3b82f6;"></i> <?= $notifications['comments'] ?> comentarios pendientes</a>
                        <?php endif; ?>
                        <?php if ($notifications['scheduled'] > 0): ?>
                        <a href="/piura_noticias_php/admin" style="display:flex; align-items:center; gap:0.5rem; padding: 0.75rem 1rem; text-decoration:none; color:#334155; font-size:0.85rem; border-bottom:1px solid #f1f5f9;"><i class="ri-timer-line" style="color:#8b5cf6;"></i> <?= $notifications['scheduled'] ?> noticias programadas</a>
                        <?php endif; ?>
                        <?php if ($notifications['users'] > 0): ?>
                        <a href="/piura_noticias_php/admin/usuarios-publicos" style="display:flex; align-items:center; gap:0.5rem; padding: 0.75rem 1rem; text-decoration:none; color:#334155; font-size:0.85rem;"><i class="ri-user-add-line" style="color:#10b981;"></i> <?= $notifications['users'] ?> usuarios nuevos hoy</a>
                        <?php endif; ?>
                        <?php if ($notifications['total'] == 0): ?>
                        <div style="padding: 1rem; text-align:center; color:#94a3b8; font-size:0.85rem;">No tienes notificaciones pendientes.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <style>
                #notif-dropdown.show { display: block !important; }
                #notif-dropdown a:hover { background: #f8fafc; }
            </style>

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
