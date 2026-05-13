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
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>const APP_BASE = '<?= APP_BASE ?>';</script>
</head>
<body>
    <div class="admin-layout">
        <!-- Inclusión del Sidebar Original (por ahora llamando al legacy, luego lo movemos si es necesario) -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="admin-main">
            <!-- Header dinámico del admin -->
            <div class="admin-top-bar">
                <div style="position:fixed; bottom:2rem; right:2rem; z-index:9999; cursor:pointer; background: white; width: 60px; height: 60px; border-radius: 50%; box-shadow: 0 10px 25px rgba(0,0,0,0.15); display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color); transition: transform 0.2s;" onclick="document.getElementById('notif-dropdown').classList.toggle('show')" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    <i class="ri-notification-3-line" style="font-size: 1.8rem; color: #475569;"></i>
                    <?php if ($notifications['total'] > 0): ?>
                    <span style="position:absolute; top:-2px; right:-2px; background:var(--danger); color:white; font-size:0.8rem; font-weight:bold; border-radius:50%; width:22px; height:22px; display:flex; align-items:center; justify-content:center; box-shadow: 0 2px 5px rgba(239, 68, 68, 0.5);"><?= $notifications['total'] ?></span>
                    <?php endif; ?>
                    
                    <div id="notif-dropdown" style="display:none; position:absolute; bottom:75px; right:0; width:300px; background:white; border-radius:12px; box-shadow:0 10px 40px rgba(0,0,0,0.2); border:1px solid #e2e8f0; z-index:100; padding:0; overflow:hidden;">
                        <div style="padding: 1rem; font-weight:bold; border-bottom:1px solid #e2e8f0; font-size:0.95rem; background: #f8fafc; color:#0f172a;"><i class="ri-notification-badge-fill" style="color:var(--primary-color);"></i> Notificaciones</div>
                        <?php if ($notifications['comments'] > 0): ?>
                        <a href="<?= APP_BASE ?>/admin/comentarios" style="display:flex; align-items:center; gap:0.75rem; padding: 1rem; text-decoration:none; color:#334155; font-size:0.9rem; border-bottom:1px solid #f1f5f9;"><i class="ri-discuss-line" style="color:#3b82f6; font-size: 1.2rem;"></i> <span><strong><?= $notifications['comments'] ?></strong> comentarios pendientes</span></a>
                        <?php endif; ?>
                        <?php if ($notifications['scheduled'] > 0): ?>
                        <a href="<?= APP_BASE ?>/admin" style="display:flex; align-items:center; gap:0.75rem; padding: 1rem; text-decoration:none; color:#334155; font-size:0.9rem; border-bottom:1px solid #f1f5f9;"><i class="ri-timer-line" style="color:#8b5cf6; font-size: 1.2rem;"></i> <span><strong><?= $notifications['scheduled'] ?></strong> noticias programadas</span></a>
                        <?php endif; ?>
                        <?php if ($notifications['users'] > 0): ?>
                        <a href="<?= APP_BASE ?>/admin/usuarios-publicos" style="display:flex; align-items:center; gap:0.75rem; padding: 1rem; text-decoration:none; color:#334155; font-size:0.9rem;"><i class="ri-user-add-line" style="color:#10b981; font-size: 1.2rem;"></i> <span><strong><?= $notifications['users'] ?></strong> usuarios nuevos hoy</span></a>
                        <?php endif; ?>
                        <?php if ($notifications['total'] == 0): ?>
                        <div style="padding: 1.5rem; text-align:center; color:#94a3b8; font-size:0.9rem;"><i class="ri-check-double-line" style="font-size:2rem; display:block; margin-bottom:0.5rem; color:#cbd5e1;"></i>No tienes notificaciones.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <style>
                #notif-dropdown.show { display: block !important; }
                #notif-dropdown a:hover { background: #f8fafc; }
                html[data-admin-theme="dark"] .admin-top-bar > div { background: #1e293b !important; border-color: #334155 !important; }
                html[data-admin-theme="dark"] .admin-top-bar i.ri-notification-3-line { color: #cbd5e1 !important; }
                html[data-admin-theme="dark"] #notif-dropdown { background: #0f172a !important; border-color: #334155 !important; }
                html[data-admin-theme="dark"] #notif-dropdown > div:first-child { background: #1e293b !important; color: #f1f5f9 !important; border-bottom-color: #334155 !important; }
                html[data-admin-theme="dark"] #notif-dropdown a { color: #cbd5e1 !important; border-bottom-color: #334155 !important; }
                html[data-admin-theme="dark"] #notif-dropdown a:hover { background: #1e293b !important; }
            </style>

            <!-- Contenido Inyectado -->
            <?php 
                if (isset($view_content)) {
                    echo $view_content;
                }
            ?>
        </main>
    </div>

    <!-- Global UI Scripts -->
    <script>
        // 1. SweetAlert2 Toast Global Config
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        // 2. Auto-convert native alerts to Toasts
        document.addEventListener("DOMContentLoaded", () => {
            const alerts = document.querySelectorAll('.alert, .alert-success, .alert-error, .alert-info');
            alerts.forEach(alert => {
                // Ignore alerts that are inside a modal or specifically marked to keep
                if(alert.closest('.modal-wrapper') || alert.dataset.keep) return;
                
                alert.style.display = 'none'; // Hide native alert
                let type = 'success';
                let text = alert.innerText.trim();
                if(alert.classList.contains('alert-error') || text.toLowerCase().includes('error')) type = 'error';
                else if(alert.classList.contains('alert-info')) type = 'info';
                
                Toast.fire({
                    icon: type,
                    title: text
                });
            });
        });

        // 3. Global function for Confirm Dialogs (SweetAlert2)
        window.confirmDelete = function(event, message, urlOrFormId) {
            event.preventDefault();
            Swal.fire({
                title: '¿Estás seguro?',
                text: message || "Esta acción es destructiva y no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#9ca3af',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    popup: 'cfg-panel'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    if (urlOrFormId && (urlOrFormId.startsWith('http') || urlOrFormId.startsWith('/'))) {
                        window.location.href = urlOrFormId;
                    } else if (urlOrFormId) {
                        document.getElementById(urlOrFormId).submit();
                    } else {
                        // Fallback: use event target href if available
                        const targetUrl = event.currentTarget.getAttribute('href');
                        if (targetUrl && targetUrl !== '#') window.location.href = targetUrl;
                        else if (event.currentTarget.closest('form')) event.currentTarget.closest('form').submit();
                    }
                }
            });
            return false;
        }
    </script>
</body>
</html>
