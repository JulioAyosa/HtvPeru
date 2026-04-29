<style>
/* Estilos globales del sidebar integrados para evitar inconsistencias entre módulos */
.admin-sidebar { width: 260px; background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%); color: white; padding: 1.25rem 1rem; display: flex; flex-direction: column; position: fixed; height: 100vh; overflow-y:auto; box-shadow: 4px 0 15px rgba(0,0,0,0.1); border-right: 1px solid rgba(255,255,255,0.05); box-sizing: border-box; z-index: 100; left: 0; top: 0; }
.admin-logo { font-size: 1.5rem; font-weight: 800; color: white; margin-bottom: 1.25rem; padding-left: 1rem; display: flex; align-items: center; gap: 0.75rem; }
.admin-logo span { color: var(--primary-color, #2563eb); }
.admin-nav { flex-grow: 1; }
.admin-nav ul { list-style: none; padding: 0; margin: 0; }
.admin-nav ul li a { display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 1rem; color: #9ca3af; border-radius: 0.5rem; margin-bottom: 0.25rem; font-size: 0.9rem; transition: all 0.2s ease; text-decoration: none; font-weight: 500; }
.admin-nav ul li a:hover, .admin-nav ul li a.active { background: linear-gradient(90deg, var(--primary-color, #2563eb) 0%, var(--primary-hover, #1d4ed8) 100%); color: white; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2); transform: translateX(5px); }
.admin-user { padding: 1rem; background-color: rgba(255,255,255,0.05); border-radius: 0.5rem; display: flex; align-items: center; gap: 1rem; font-size: 0.875rem; margin-top: 1rem; }
.admin-layout { display: flex; min-height: 100vh; background-color: #f8fafc; margin: 0; padding: 0; }
.admin-main { flex-grow: 1; margin-left: 260px; padding: 2rem; width: calc(100% - 260px); box-sizing: border-box; }
</style>

<aside class="admin-sidebar no-print">
            <div class="admin-logo">
                <img src="/piura_noticias_php/img/logo.webp" alt="Logo" style="height:36px; width:auto; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">
                <div style="display: flex; flex-direction: column;">
                    <div style="line-height: 1;">HTV<span style="color:var(--primary-color, #3b82f6);">PERU</span></div>
                    <span style="font-size: 0.55rem; color: #9ca3af; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; margin-top: 2px;">Una Mirada al Mundo</span>
                </div>
            </div>
            <nav class="admin-nav">
                <ul>
                    <li><a href="/piura_noticias_php/admin" <?php echo ($_SERVER['REQUEST_URI']==='/piura_noticias_php/admin'||$_SERVER['REQUEST_URI']==='/piura_noticias_php/admin/'||strpos($_SERVER['REQUEST_URI'],'/admin/dashboard')!==false)?'class="active"':''; ?>><i class="ri-article-line"></i> Noticias</a></li>
                    
                    <?php if (has_permission('manage_media')): ?>
                    <li><a href="/piura_noticias_php/admin/multimedia" <?php echo (strpos($_SERVER['REQUEST_URI'],'/admin/multimedia')!==false)?'class="active"':''; ?>><i class="ri-folder-image-line"></i> Multimedia</a></li>
                    <?php endif; ?>
                    
                    <?php if (has_permission('manage_comments')): ?>
                    <li><a href="/piura_noticias_php/admin/comentarios" <?php echo (strpos($_SERVER['REQUEST_URI'],'/admin/comentarios')!==false)?'class="active"':''; ?>><i class="ri-discuss-line"></i> Comentarios</a></li>
                    <?php endif; ?>
                    
                    <li><a href="/piura_noticias_php/admin/contenidos" <?php echo (strpos($_SERVER['REQUEST_URI'],'/admin/contenidos')!==false)?'class="active"':''; ?>><i class="ri-table-2"></i> Planificador</a></li>
                    
                    <?php if (has_permission('view_reports')): ?>
                    <li><a href="/piura_noticias_php/admin/reportes" <?php echo (strpos($_SERVER['REQUEST_URI'],'/admin/reportes')!==false)?'class="active"':''; ?>><i class="ri-bar-chart-box-line"></i> Informe Gerencial</a></li>
                    <li><a href="/piura_noticias_php/admin/actividad" <?php echo (strpos($_SERVER['REQUEST_URI'],'/admin/actividad')!==false)?'class="active"':''; ?>><i class="ri-history-line"></i> Actividad Log</a></li>
                    <?php endif; ?>

                    <?php if (has_permission('manage_polls')): ?>
                    <li><a href="/piura_noticias_php/admin/encuestas" <?php echo (strpos($_SERVER['REQUEST_URI'],'/admin/encuestas')!==false)?'class="active"':''; ?>><i class="ri-bar-chart-2-line"></i> Encuestas</a></li>
                    <?php endif; ?>

                    <?php if (has_permission('manage_newsletters')): ?>
                    <li><a href="/piura_noticias_php/admin/boletines" <?php echo (strpos($_SERVER['REQUEST_URI'],'/admin/boletines')!==false)?'class="active"':''; ?>><i class="ri-mail-send-line"></i> Boletines</a></li>
                    <?php endif; ?>

                    <?php if (has_permission('manage_categories')): ?>
                    <li><a href="/piura_noticias_php/admin/categorias" <?php echo (strpos($_SERVER['REQUEST_URI'],'/admin/categorias')!==false)?'class="active"':''; ?>><i class="ri-price-tag-3-line"></i> Categorías</a></li>
                    <?php endif; ?>
                    
                    <?php if (has_permission('manage_ads')): ?>
                    <li><a href="/piura_noticias_php/admin/publicidad" <?php echo (strpos($_SERVER['REQUEST_URI'],'/admin/publicidad')!==false)?'class="active"':''; ?>><i class="ri-advertisement-line"></i> Publicidad</a></li>
                    <?php endif; ?>

                    <?php if (has_permission('manage_users')): ?>
                    <li><a href="/piura_noticias_php/admin/usuarios" <?php echo (strpos($_SERVER['REQUEST_URI'],'/admin/usuarios')!==false && strpos($_SERVER['REQUEST_URI'],'/admin/usuarios-publicos')===false)?'class="active"':''; ?>><i class="ri-user-settings-line"></i> Staff / Admins</a></li>
                    <li><a href="/piura_noticias_php/admin/usuarios-publicos" <?php echo (strpos($_SERVER['REQUEST_URI'],'/admin/usuarios-publicos')!==false)?'class="active"':''; ?>><i class="ri-team-line"></i> Usuarios Lectores</a></li>
                    <?php endif; ?>

                    <?php if (has_permission('manage_roles')): ?>
                    <li><a href="/piura_noticias_php/admin/roles" <?php echo (strpos($_SERVER['REQUEST_URI'],'/admin/roles')!==false)?'class="active"':''; ?>><i class="ri-shield-keyhole-line"></i> Roles y Permisos</a></li>
                    <?php endif; ?>

                    <?php if (has_permission('manage_news')): ?>
                    <li><a href="/piura_noticias_php/admin/papelera" <?php echo (strpos($_SERVER['REQUEST_URI'],'/admin/papelera')!==false)?'class="active"':''; ?>><i class="ri-delete-bin-line"></i> Papelera</a></li>
                    <?php endif; ?>

                    <?php if (has_permission('system_tools')): ?>
                    <li><a href="/piura_noticias_php/admin/respaldos" <?php echo (strpos($_SERVER['REQUEST_URI'],'/admin/respaldos')!==false)?'class="active"':''; ?>><i class="ri-database-2-line"></i> Respaldos</a></li>
                    <li><a href="/piura_noticias_php/admin/optimizador" <?php echo (strpos($_SERVER['REQUEST_URI'],'/admin/optimizador')!==false)?'class="active"':''; ?>><i class="ri-rocket-2-line"></i> Optimizador</a></li>
                    <?php endif; ?>

                    <?php if (has_permission('manage_pages')): ?>
                    <li><a href="/piura_noticias_php/admin/paginas" <?php echo (strpos($_SERVER['REQUEST_URI'],'/admin/paginas')!==false)?'class="active"':''; ?>><i class="ri-pages-line"></i> Páginas Estáticas</a></li>
                    <?php endif; ?>

                    <?php if (has_permission('manage_settings')): ?>
                    <li><a href="/piura_noticias_php/admin/configuracion" <?php echo (strpos($_SERVER['REQUEST_URI'],'/admin/configuracion')!==false)?'class="active"':''; ?>><i class="ri-settings-3-line"></i> Configuración</a></li>
                    <?php endif; ?>
                    
                    <li><a href="/piura_noticias_php/index.php" target="_blank"><i class="ri-global-line"></i> Ver Sitio</a></li>
                </ul>
            </nav>
            <div class="admin-user">
                <div><i class="ri-user-settings-fill" style="font-size: 1.5rem; color: var(--primary-color, #3b82f6);"></i></div>
                <div>
                    <strong style="color:white;"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></strong><br>
                    <?php
                        $user_role_display = 'Usuario';
                        if (isset($_SESSION['rol_id'])) {
                            try {
                                $stmt_r = $pdo->prepare("SELECT nombre FROM roles WHERE id = ?");
                                $stmt_r->execute([$_SESSION['rol_id']]);
                                $rol_db = $stmt_r->fetchColumn();
                                if ($rol_db) $user_role_display = $rol_db;
                            } catch(Exception $e) {}
                        }
                    ?>
                    <span style="color: #9ca3af; font-size: 0.75rem;"><?php echo strtoupper($user_role_display); ?></span><br>
                    <div style="margin-top:0.25rem; display:flex; gap:0.5rem; flex-wrap:wrap;">
                        <a href="/piura_noticias_php/admin/perfil" style="color: #60a5fa; font-size: 0.75rem; text-decoration:none;"><i class="ri-edit-circle-line"></i> Mi Perfil</a>
                        <a href="/piura_noticias_php/admin?logout=true" style="color: #ef4444; font-size: 0.75rem; text-decoration:none;"><i class="ri-logout-box-r-line"></i> Salir</a>
                    </div>
                </div>
            </div>
        </aside>
        <script>
            // Heartbeat asíncrono para mantener sesión viva en el dashboard
            setInterval(() => {
                fetch('api/heartbeat')
                    .then(r => r.json())
                    .then(d => { if(d.status === 'expired') window.location.reload(); })
                    .catch(e => console.error('Heartbeat falló', e));
            }, 900000); // 15 minutos
        </script>
