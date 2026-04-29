<?php
// app/Views/admin/configuracion/index.php
// Variables: $msg, $configs, $categorias_select
?>

<style>
    .cfg-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
    @media(max-width: 1024px) { .cfg-grid { grid-template-columns: 1fr; } }
    
    .cfg-panel { background: white; padding: 2rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); margin-bottom: 2rem; border-top: 4px solid var(--primary-color); }
    .cfg-panel h3 { margin-top: 0; font-size: 1.1rem; color: #1e293b; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; }
    
    .form-group { margin-bottom: 1.5rem; }
    .form-group label { display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 0.15rem; color: #475569; }
    .form-group input[type="text"], .form-group input[type="email"], .form-group input[type="url"], .form-group input[type="color"], .form-group input[type="number"] { width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 4px; font-family: inherit; box-sizing: border-box; }
    .form-group input[type="file"] { width: 100%; padding: 0.5rem; border: 1px dashed #cbd5e1; border-radius: 4px; background: #f8fafc; box-sizing: border-box; }
    .form-group select { width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; }
    
    .img-preview { background: #f1f5f9; padding: 1rem; text-align: center; border-radius: 4px; margin-bottom: 0.15rem; border: 1px solid #e2e8f0; }
    .img-preview img { max-width: 100%; max-height: 80px; object-fit: contain; }
    
    .btn-save { background: var(--primary-color); color: white; border: none; padding: 1rem 2rem; border-radius: 6px; font-size: 1rem; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; transition: background 0.2s; }
    .btn-save:hover { background: #1d4ed8; }
    .alert { background: #dcfce7; color: #166534; padding: 1rem; border-radius: 6px; font-weight: 600; margin-bottom: 1.5rem; border-left: 4px solid #22c55e; }
</style>

<div class="admin-header">
    <div>
        <h1 style="margin:0;">Configuración General</h1>
        <p style="color: var(--text-muted); margin-top:0.5rem;">Ajustes globales del sitio y SEO.</p>
    </div>
</div>

<?php if($msg): ?>
    <div class="alert"><i class="ri-check-line"></i> <?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<form method="POST" action="<?= base_url('/') ?>admin/configuracion/action" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save_config">
    <?php echo csrf_field(); ?>
    
    <div class="cfg-grid">
        <!-- PANEL IDENTIDAD -->
        <div class="cfg-panel">
            <h3><i class="ri-fingerprint-line"></i> Identidad y Apariencia</h3>
            
            <div class="form-group">
                <label>Título del Portal (SEO Title)</label>
                <input type="text" name="site_title" value="<?php echo htmlspecialchars($configs['site_title'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label>Eslogan</label>
                <input type="text" name="site_slogan" value="<?php echo htmlspecialchars($configs['site_slogan'] ?? ''); ?>">
            </div>
            
            <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div>
                    <label>Logo Principal</label>
                    <div class="img-preview">
                        <img src="<?php echo htmlspecialchars('<?= base_url('/') ?>' . ($configs['logo_url'] ?? 'img/logo.webp')); ?>" alt="Logo">
                    </div>
                    <input type="file" name="logo_upload" accept="image/*">
                </div>
                <div>
                    <label>Favicon (Ícono pestaña)</label>
                    <div class="img-preview" style="background:white;">
                        <img src="<?php echo htmlspecialchars('<?= base_url('/') ?>' . ($configs['favicon_url'] ?? 'img/logo.webp')); ?>" alt="Favicon" style="height:32px;">
                    </div>
                    <input type="file" name="favicon_upload" accept=".png,.ico,.svg">
                </div>
            </div>

            <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div>
                    <label>Marca de Agua (.PNG Transparente)</label>
                    <div class="img-preview" style="background:var(--bg-main);">
                        <?php if(!empty($configs['watermark_url'])): ?>
                        <img src="<?php echo htmlspecialchars('<?= base_url('/') ?>' . $configs['watermark_url']); ?>" alt="Watermark" style="max-height: 40px;">
                        <?php else: ?>
                        <span style="font-size:0.75rem; color:var(--text-muted);">Sin marca de agua</span>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="watermark_upload" accept=".png">
                </div>
                <div>
                    <label>Fondo Expandido para la Portada (Header)</label>
                    <div class="img-preview" style="background:var(--bg-main);">
                        <?php if(!empty($configs['header_bg_url'])): ?>
                        <img src="<?php echo htmlspecialchars('<?= base_url('/') ?>' . $configs['header_bg_url']); ?>" alt="Header BG" style="max-height: 40px; border-radius: 4px;">
                        <?php else: ?>
                        <span style="font-size:0.75rem; color:var(--text-muted);">Cabecera sólida (Sin fondo)</span>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="header_bg_upload" accept=".png,.jpg,.jpeg,.webp">
                </div>
            </div>

            <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div>
                    <label>Estado de la Marca de Agua</label>
                    <select name="watermark_estado" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px; margin-top:20px;">
                        <option value="inactivo" <?php echo ($configs['watermark_estado'] ?? 'inactivo') === 'inactivo' ? 'selected' : ''; ?>>Inactivo (No marcar fotos)</option>
                        <option value="activo" <?php echo ($configs['watermark_estado'] ?? 'inactivo') === 'activo' ? 'selected' : ''; ?>>Activo (Inyectar en subidas nuevas)</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div>
                    <label>Color Primario (Marca)</label>
                    <div style="display:flex; gap:0.5rem;">
                        <input type="color" name="color_primario" value="<?php echo htmlspecialchars($configs['color_primario'] ?? '#2563eb'); ?>" style="width:50px; padding:2px; height:42px;">
                        <input type="text" value="<?php echo htmlspecialchars($configs['color_primario'] ?? '#2563eb'); ?>" disabled style="background:#f8fafc;">
                    </div>
                </div>
                <div>
                    <label>Color Secundario (Hover)</label>
                    <div style="display:flex; gap:0.5rem;">
                        <input type="color" name="color_secundario" value="<?php echo htmlspecialchars($configs['color_secundario'] ?? '#1e40af'); ?>" style="width:50px; padding:2px; height:42px;">
                        <input type="text" value="<?php echo htmlspecialchars($configs['color_secundario'] ?? '#1e40af'); ?>" disabled style="background:#f8fafc;">
                    </div>
                </div>
            </div>
        </div>

        <!-- PANEL HEADER HEIGHT -->
        <div class="cfg-panel">
            <h3><i class="ri-layout-top-line"></i> Personalización de Cabecera</h3>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label>Altura de la Cabecera (px)</label>
                    <input type="number" name="header_height" value="<?php echo htmlspecialchars($configs['header_height'] ?? '100'); ?>" min="60" max="300" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;">
                    <small style="color:var(--text-muted); display:block; margin-top:0.25rem;">Por defecto es 100.</small>
                </div>
                <div class="form-group">
                    <label>Tamaño del Logo (Escala)</label>
                    <input type="number" name="header_logo_scale" value="<?php echo htmlspecialchars($configs['header_logo_scale'] ?? '1.0'); ?>" min="0.5" max="2.5" step="0.1" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;">
                    <small style="color:var(--text-muted); display:block; margin-top:0.25rem;">Ej. 1.0 (Normal), 1.2 (20% más grande).</small>
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
                <div class="form-group">
                    <label>Ancho del Buscador (px)</label>
                    <input type="number" name="header_search_width" value="<?php echo htmlspecialchars($configs['header_search_width'] ?? '280'); ?>" min="200" max="800" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;">
                    <small style="color:var(--text-muted); display:block; margin-top:0.25rem;">Por defecto es 280. Ej. 350 o 400.</small>
                </div>
                <div class="form-group">
                    <label>Separación de Botones (rem)</label>
                    <input type="number" name="header_actions_gap" value="<?php echo htmlspecialchars($configs['header_actions_gap'] ?? '1.0'); ?>" min="0.5" max="3.0" step="0.1" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;">
                    <small style="color:var(--text-muted); display:block; margin-top:0.25rem;">Espacio entre iconos. Por defecto es 1.0.</small>
                </div>
            </div>
        </div>

        <!-- PANEL SEO & ANALYTICS -->
        <div class="cfg-panel">
            <h3><i class="ri-line-chart-line"></i> Analíticas y SEO</h3>
            
            <div class="form-group">
                <label>Google Analytics ID (Ej: G-XXXXXXXXXX)</label>
                <input type="text" name="google_analytics_id" value="<?php echo htmlspecialchars($configs['google_analytics_id'] ?? ''); ?>" placeholder="Opcional">
            </div>
            
            <div class="form-group">
                <label>ID del Píxel de Meta/Facebook</label>
                <input type="text" name="facebook_pixel_id" value="<?php echo htmlspecialchars($configs['facebook_pixel_id'] ?? ''); ?>" placeholder="Opcional">
            </div>
            
            <h3 style="margin-top:2rem;"><i class="ri-contacts-book-line"></i> Contacto y Footer</h3>
            
            <div class="form-group" style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div>
                    <label>Email Público</label>
                    <input type="email" name="contact_email" value="<?php echo htmlspecialchars($configs['contact_email'] ?? ''); ?>">
                </div>
                <div>
                    <label>Teléfono / WhatsApp</label>
                    <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($configs['contact_phone'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label>Texto Legal / Derechos (Footer)</label>
                <input type="text" name="footer_text" value="<?php echo htmlspecialchars($configs['footer_text'] ?? ''); ?>">
            </div>
        </div>

        <!-- PANEL SOCIAL MEDIA & TV -->
        <div class="cfg-panel" style="grid-column: 1 / -1;">
            <h3><i class="ri-share-line"></i> Redes Sociales</h3>
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem;">
                <div style="flex:1; min-width:200px;">
                    <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom: 0.15rem;"><i class="ri-facebook-circle-fill" style="color:#1877f2;"></i> Enlace Facebook</label>
                    <input type="url" name="social_facebook" value="<?php echo htmlspecialchars($configs['social_facebook'] ?? ''); ?>" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;">
                </div>
                <div style="flex:1; min-width:200px;">
                    <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom: 0.15rem;"><i class="ri-twitter-x-fill" style="color:#000;"></i> Enlace X (Twitter)</label>
                    <input type="url" name="social_twitter" value="<?php echo htmlspecialchars($configs['social_twitter'] ?? ''); ?>" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;">
                </div>
                <div style="flex:1; min-width:200px;">
                    <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom: 0.15rem;"><i class="ri-instagram-line" style="color:#e1306c;"></i> Enlace Instagram</label>
                    <input type="url" name="social_instagram" value="<?php echo htmlspecialchars($configs['social_instagram'] ?? ''); ?>" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;">
                </div>
                <div style="flex:1; min-width:200px;">
                    <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom: 0.15rem;"><i class="ri-youtube-fill" style="color:#ff0000;"></i> Enlace YouTube</label>
                    <input type="url" name="social_youtube" value="<?php echo htmlspecialchars($configs['social_youtube'] ?? ''); ?>" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;">
                </div>
                <div style="flex:1; min-width:200px;">
                    <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom: 0.15rem;"><i class="ri-tiktok-fill" style="color:#000;"></i> Enlace TikTok</label>
                    <input type="url" name="social_tiktok" value="<?php echo htmlspecialchars($configs['social_tiktok'] ?? ''); ?>" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;">
                </div>
                <div style="flex:1; min-width:200px;">
                    <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom: 0.15rem;"><i class="ri-twitch-fill" style="color:#9146ff;"></i> Enlace Twitch</label>
                    <input type="url" name="social_twitch" value="<?php echo htmlspecialchars($configs['social_twitch'] ?? ''); ?>" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;">
                </div>
                <div style="flex:1; min-width:200px;">
                    <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom: 0.15rem;"><i class="ri-live-fill" style="color:#53fc18;"></i> Enlace Kick</label>
                    <input type="url" name="social_kick" value="<?php echo htmlspecialchars($configs['social_kick'] ?? ''); ?>" placeholder="https://kick.com/..." style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;">
                </div>
                <div style="flex:1; min-width:200px;">
                    <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom: 0.15rem;"><i class="ri-threads-fill" style="color:#000;"></i> Enlace Threads</label>
                    <input type="url" name="social_threads" value="<?php echo htmlspecialchars($configs['social_threads'] ?? ''); ?>" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;">
                </div>
                <div style="flex:1; min-width:200px;">
                    <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom: 0.15rem;"><i class="ri-telegram-fill" style="color:#2ca5e0;"></i> Enlace Telegram</label>
                    <input type="url" name="social_telegram" value="<?php echo htmlspecialchars($configs['social_telegram'] ?? ''); ?>" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;">
                </div>
                <div style="flex:1; min-width:200px;">
                    <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom: 0.15rem;"><i class="ri-discord-fill" style="color:#5865F2;"></i> Enlace Discord</label>
                    <input type="url" name="social_discord" value="<?php echo htmlspecialchars($configs['social_discord'] ?? ''); ?>" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;">
                </div>
                <div style="flex:1; min-width:200px;">
                    <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom: 0.15rem;"><i class="ri-pinterest-fill" style="color:#E60023;"></i> Enlace Pinterest</label>
                    <input type="url" name="social_pinterest" value="<?php echo htmlspecialchars($configs['social_pinterest'] ?? ''); ?>" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;">
                </div>
                <div style="flex:1; min-width:200px;">
                    <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom: 0.15rem;"><i class="ri-spotify-fill" style="color:#1DB954;"></i> Enlace Spotify</label>
                    <input type="url" name="social_spotify" value="<?php echo htmlspecialchars($configs['social_spotify'] ?? ''); ?>" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;">
                </div>
                <div style="flex:1; min-width:200px;">
                    <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom: 0.15rem;"><i class="ri-linkedin-fill" style="color:#0a66c2;"></i> Enlace LinkedIn</label>
                    <input type="url" name="social_linkedin" value="<?php echo htmlspecialchars($configs['social_linkedin'] ?? ''); ?>" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;">
                </div>
                <div style="flex:1; min-width:200px;">
                    <label style="font-weight:600; font-size:0.85rem; display:block; margin-bottom: 0.15rem;"><i class="ri-whatsapp-line" style="color:#25d366;"></i> Nro. WhatsApp (+51...)</label>
                    <input type="text" name="social_whatsapp" value="<?php echo htmlspecialchars($configs['social_whatsapp'] ?? ''); ?>" placeholder="Para el botón flotante" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;">
                </div>
            </div>
        </div>
        
        <div class="cfg-panel" style="grid-column: 1 / -1;">
            <h3><i class="ri-tv-2-line"></i> Señal de TV En Vivo / Portada Dinámica</h3>
            <div class="cfg-grid">
                <div class="form-group">
                    <label>Enlace de Transmisión (YouTube Embed / Twitch / m3u8)</label>
                    <input type="url" name="tv_envivo_url" value="<?php echo htmlspecialchars($configs['tv_envivo_url'] ?? ''); ?>" placeholder="https://www.youtube.com/embed/LIVE_ID" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;">
                </div>
                <div class="form-group">
                    <label>Estado del Botón "TV En Vivo"</label>
                    <select name="tv_envivo_estado" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;">
                        <option value="activo" <?php echo ($configs['tv_envivo_estado'] ?? '') === 'activo' ? 'selected' : ''; ?>>Encendido (Al aire)</option>
                        <option value="inactivo" <?php echo ($configs['tv_envivo_estado'] ?? '') === 'inactivo' ? 'selected' : ''; ?>>Apagado (Ocultar botón)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Categoría Urgente / Breaking</label>
                    <select name="cat_urgente" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;">
                        <?php foreach($categorias_select as $cs): ?>
                        <option value="<?php echo htmlspecialchars($cs['nombre']); ?>" <?php echo ($configs['cat_urgente'] ?? '') === $cs['nombre'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cs['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Categoría Carrusel In-Scroll</label>
                    <select name="cat_carrusel" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;">
                        <?php foreach($categorias_select as $cs): ?>
                        <option value="<?php echo htmlspecialchars($cs['nombre']); ?>" <?php echo ($configs['cat_carrusel'] ?? '') === $cs['nombre'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cs['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- NEW: SCRIPTS CUSTOM -->
        <div class="cfg-panel" style="grid-column: 1 / -1;">
            <h3><i class="ri-code-s-slash-line"></i> Inyector de Scripts (Zero-Code)</h3>
            <p style="font-size:0.85rem; color:var(--text-muted); margin-top:-0.5rem; margin-bottom:1rem;">Permite inyectar Píxeles, Chats (Tawk.to) o AdSense sin modificar index.php</p>
            <div class="cfg-grid">
                <div class="form-group">
                    <label>&lt;head&gt; Scripts (Ej. Google Analytics / Meta Pixel)</label>
                    <textarea name="script_header" rows="4" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px; font-family:monospace;"><?php echo htmlspecialchars($configs['script_header'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label>&lt;body&gt; Scripts (Ej. Chatbots / AdSense / Verificadores)</label>
                    <textarea name="script_footer" rows="4" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px; font-family:monospace;"><?php echo htmlspecialchars($configs['script_footer'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

        <!-- NEW: SEO OPEN GRAPH -->
        <div class="cfg-panel">
            <h3><i class="ri-share-forward-box-line"></i> Social Media & Open Graph</h3>
            <div class="form-group">
                <label>Meta Descripción Global (Para Google/Facebook)</label>
                <textarea name="seo_og_desc" rows="3" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;"><?php echo htmlspecialchars($configs['seo_og_desc'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>Miniatura Predeterminada (WhatsApp / Facebook)</label>
                <div class="img-preview" style="background:var(--bg-main);">
                    <?php if(!empty($configs['seo_og_image'])): ?>
                    <img src="<?php echo htmlspecialchars('<?= base_url('/') ?>' . $configs['seo_og_image']); ?>" alt="og:image" style="max-height: 80px;">
                    <?php else: ?>
                    <span style="font-size:0.75rem; color:var(--text-muted);">Sin imagen general</span>
                    <?php endif; ?>
                </div>
                <input type="file" name="seo_og_image_upload" accept=".png,.jpg,.jpeg">
                <small style="color:var(--text-muted);">Ideal 1200x630px para vistas previas.</small>
            </div>
        </div>

        <!-- NEW: AVISOS Y COOKIES -->
        <div class="cfg-panel">
            <h3><i class="ri-notification-badge-line"></i> Avisos Top-Bar y Cookies</h3>
            <div class="form-group">
                <label>Barra Superior (Aviso Parroquial / Eventos)</label>
                <select name="alert_top_estado" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px; margin-bottom: 0.15rem;">
                    <option value="inactivo" <?php echo ($configs['alert_top_estado'] ?? 'inactivo') === 'inactivo' ? 'selected' : ''; ?>>Oculto</option>
                    <option value="activo" <?php echo ($configs['alert_top_estado'] ?? 'inactivo') === 'activo' ? 'selected' : ''; ?>>Mostrar marquesina</option>
                </select>
                <input type="text" name="alert_top_texto" value="<?php echo htmlspecialchars($configs['alert_top_texto'] ?? ''); ?>" placeholder="Texto del aviso..." style="margin-bottom: 0.15rem;">
                <input type="url" name="alert_top_url" value="<?php echo htmlspecialchars($configs['alert_top_url'] ?? ''); ?>" placeholder="https://... (Link opcional)">
            </div>
            <hr style="border-top:1px solid var(--border-color); margin:1.5rem 0;">
            <div class="form-group">
                <label>Banner GDPR (Ley de Cookies)</label>
                <select name="cookie_banner_estado" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:4px;">
                    <option value="inactivo" <?php echo ($configs['cookie_banner_estado'] ?? 'inactivo') === 'inactivo' ? 'selected' : ''; ?>>Desactivado</option>
                    <option value="activo" <?php echo ($configs['cookie_banner_estado'] ?? 'inactivo') === 'activo' ? 'selected' : ''; ?>>Banner Activo Inferior</option>
                </select>
            </div>
            <hr style="border-top:1px solid var(--border-color); margin:1.5rem 0;">
            <div class="form-group">
                <label>Documento de Políticas de Privacidad (PDF)</label>
                <div style="background:#f1f5f9; padding: 1rem; border-radius:4px; margin-bottom: 0.5rem; text-align:center;">
                    <?php if(!empty($configs['privacy_policy_url'])): ?>
                        <i class="ri-file-pdf-2-fill" style="font-size:2rem; color: #dc2626; display:block;"></i>
                        <a href="<?php echo htmlspecialchars('<?= base_url('/') ?>' . $configs['privacy_policy_url']); ?>" target="_blank" style="font-size:0.8rem; font-weight:bold; color:var(--primary-color);">Ver Documento Actual</a>
                    <?php else: ?>
                        <i class="ri-file-close-line" style="font-size:2rem; color: #9ca3af; display:block;"></i>
                        <span style="font-size:0.8rem; color: #64748b;">No hay políticas subidas</span>
                    <?php endif; ?>
                </div>
                <input type="file" name="privacy_policy_upload" accept=".pdf" style="width:100%; padding:0.5rem; border:1px dashed #cbd5e1; border-radius:4px; background:#f8fafc;">
                <small style="color:var(--text-muted); display:block; margin-top:5px;">Sube tu archivo .pdf legal. El enlace aparecerá automáticamente de forma estética en todos los pies de página.</small>
            </div>
        </div>

        <!-- NEW: DISEÑO UI -->
        <div class="cfg-panel" style="grid-column: 1 / -1;">
            <h3><i class="ri-layout-masonry-line"></i> Control de Apariencia de Portada (Layout Toggles)</h3>
            <div style="display:flex; flex-wrap:wrap; gap:2rem; margin-top:1rem;">
                <div style="flex:1; min-width:200px; background:#f8fafc; padding:1.5rem; border-radius:8px; border:1px solid #e2e8f0;">
                    <label style="display:flex; justify-content:space-between; font-weight:800; margin-bottom:1rem; align-items:center;">
                        <span>Carrusel Superior</span>
                        <i class="ri-slideshow-3-line" style="font-size:1.5rem; color:var(--primary-color);"></i>
                    </label>
                    <select name="ui_mostrar_carrusel" style="width:100%; padding:0.75rem; border-radius:4px; border:1px solid #cbd5e1;">
                        <option value="activo" <?php echo ($configs['ui_mostrar_carrusel'] ?? 'activo') === 'activo' ? 'selected' : ''; ?>>Mostrar siempre</option>
                        <option value="inactivo" <?php echo ($configs['ui_mostrar_carrusel'] ?? 'activo') === 'inactivo' ? 'selected' : ''; ?>>Ocultar Carrusel</option>
                    </select>
                </div>
                <div style="flex:1; min-width:200px; background:#f8fafc; padding:1.5rem; border-radius:8px; border:1px solid #e2e8f0;">
                    <label style="display:flex; justify-content:space-between; font-weight:800; margin-bottom:1rem; align-items:center;">
                        <span>Bloque de "Historias"</span>
                        <i class="ri-donut-chart-fill" style="font-size:1.5rem; color:var(--primary-color);"></i>
                    </label>
                    <select name="ui_mostrar_stories" style="width:100%; padding:0.75rem; border-radius:4px; border:1px solid #cbd5e1;">
                        <option value="activo" <?php echo ($configs['ui_mostrar_stories'] ?? 'activo') === 'activo' ? 'selected' : ''; ?>>Mostrar siempre</option>
                        <option value="inactivo" <?php echo ($configs['ui_mostrar_stories'] ?? 'activo') === 'inactivo' ? 'selected' : ''; ?>>Ocultar círculos</option>
                    </select>
                </div>
                <div style="flex:1; min-width:200px; background:#f8fafc; padding:1.5rem; border-radius:8px; border:1px solid #e2e8f0;">
                    <label style="display:flex; justify-content:space-between; font-weight:800; margin-bottom:1rem; align-items:center;">
                        <span>Bloque "LO ÚLTIMO"</span>
                        <i class="ri-flashlight-fill" style="font-size:1.5rem; color:var(--primary-color);"></i>
                    </label>
                    <select name="ui_mostrar_urgente" style="width:100%; padding:0.75rem; border-radius:4px; border:1px solid #cbd5e1;">
                        <option value="activo" <?php echo ($configs['ui_mostrar_urgente'] ?? 'activo') === 'activo' ? 'selected' : ''; ?>>Mostrar siempre</option>
                        <option value="inactivo" <?php echo ($configs['ui_mostrar_urgente'] ?? 'activo') === 'inactivo' ? 'selected' : ''; ?>>Ocultar Bloque</option>
                    </select>
                </div>
                <div style="flex:1; min-width:200px; background:#f8fafc; padding:1.5rem; border-radius:8px; border:1px solid #e2e8f0;">
                    <label style="display:flex; justify-content:space-between; font-weight:800; margin-bottom:1rem; align-items:center;">
                        <span>Sección Policial / Local</span>
                        <i class="ri-police-car-fill" style="font-size:1.5rem; color:var(--primary-color);"></i>
                    </label>
                    <select name="ui_mostrar_policial" style="width:100%; padding:0.75rem; border-radius:4px; border:1px solid #cbd5e1;">
                        <option value="activo" <?php echo ($configs['ui_mostrar_policial'] ?? 'activo') === 'activo' ? 'selected' : ''; ?>>Mostrar siempre</option>
                        <option value="inactivo" <?php echo ($configs['ui_mostrar_policial'] ?? 'activo') === 'inactivo' ? 'selected' : ''; ?>>Ocultar Bloque</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- NEW: LOGIN SOCIAL (COMENTARIOS) -->
        <div class="cfg-panel" style="grid-column: 1 / -1; border-top: 4px solid #8b5cf6;">
            <h3><i class="ri-shield-user-fill" style="color:#8b5cf6;"></i> Login Social (Comentarios con Google / Facebook)</h3>
            <p style="font-size:0.85rem; color:var(--text-muted); margin-top:-0.5rem; margin-bottom:1.5rem;">Requiere que crees aplicaciones en Google Cloud Console y Meta for Developers y pegues las credenciales aquí.</p>
            
            <div class="form-group" style="background:#f5f3ff; padding:1.5rem; border-radius:8px; border:1px solid #ddd6fe; margin-bottom:1.5rem;">
                <label style="font-weight:800; color:#4c1d95; font-size:1rem; margin-bottom:0.5rem;"><i class="ri-toggle-fill"></i> Estado del Sistema de Comentarios OAuth</label>
                <select name="social_login_estado" style="width:100%; padding:0.75rem; border-radius:4px; border:1px solid #c4b5fd; font-weight:bold;">
                    <option value="inactivo" <?php echo ($configs['social_login_estado'] ?? 'inactivo') === 'inactivo' ? 'selected' : ''; ?>>DESACTIVADO (Usa el formulario manual básico actual)</option>
                    <option value="activo" <?php echo ($configs['social_login_estado'] ?? 'inactivo') === 'activo' ? 'selected' : ''; ?>>ACTIVO (Forzar inicio de sesión con Google o Facebook)</option>
                </select>
                <p style="font-size:0.85rem; color:#6d28d9; margin-top:0.5rem; margin-bottom:0;"><strong>Atención:</strong> Si lo activas sin configurar las APIs abajo, los botones de login darán error a los usuarios.</p>
            </div>

            <div class="cfg-grid">
                <!-- Panel Google -->
                <div style="border:1px solid #e2e8f0; border-radius:8px; padding:1.5rem;">
                    <h4 style="margin-top:0; display:flex; align-items:center; gap:0.5rem; color:#475569;"><img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" width="20"> Configuración de Google</h4>
                    <p style="font-size:0.8rem; color:var(--text-muted);">URIs de redireccionamiento autorizados:<br><code style="background:#f1f5f9; padding:2px 4px; border-radius:3px; user-select:all;">https://tudominio.com/auth/google/callback</code></p>
                    
                    <div class="form-group">
                        <label>Google Client ID</label>
                        <input type="text" name="google_client_id" value="<?php echo htmlspecialchars($configs['google_client_id'] ?? ''); ?>" placeholder="Ej: 123456789-xxxx.apps.googleusercontent.com">
                    </div>
                    <div class="form-group">
                        <label>Google Client Secret</label>
                        <input type="password" name="google_client_secret" value="<?php echo htmlspecialchars($configs['google_client_secret'] ?? ''); ?>" placeholder="Oculto por seguridad">
                        <small style="color:var(--text-muted); display:block; margin-top:0.25rem;">Déjalo intacto si no lo vas a cambiar.</small>
                    </div>
                </div>

                <!-- Panel Facebook -->
                <div style="border:1px solid #e2e8f0; border-radius:8px; padding:1.5rem;">
                    <h4 style="margin-top:0; display:flex; align-items:center; gap:0.5rem; color:#1877f2;"><i class="ri-facebook-circle-fill" style="font-size:1.5rem;"></i> Configuración de Facebook</h4>
                    <p style="font-size:0.8rem; color:var(--text-muted);">URI de redireccionamiento de OAuth válidos:<br><code style="background:#f1f5f9; padding:2px 4px; border-radius:3px; user-select:all;">https://tudominio.com/auth/facebook/callback</code></p>
                    
                    <div class="form-group">
                        <label>Facebook App ID</label>
                        <input type="text" name="facebook_app_id" value="<?php echo htmlspecialchars($configs['facebook_app_id'] ?? ''); ?>" placeholder="Ej: 987654321098765">
                    </div>
                    <div class="form-group">
                        <label>Facebook App Secret</label>
                        <input type="password" name="facebook_app_secret" value="<?php echo htmlspecialchars($configs['facebook_app_secret'] ?? ''); ?>" placeholder="Oculto por seguridad">
                        <small style="color:var(--text-muted); display:block; margin-top:0.25rem;">Déjalo intacto si no lo vas a cambiar.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- NEW: SEGURIDAD Y MANTENIMIENTO -->
        <div class="cfg-panel" style="grid-column: 1 / -1; border-top: 4px solid #ef4444;">
            <h3><i class="ri-lock-2-line" style="color:#ef4444;"></i> Seguridad y Control Maestro</h3>
            <div style="background:#fee2e2; padding:1.5rem; border-radius:8px; border:1px dashed #f87171;">
                <label style="display:flex; justify-content:space-between; font-weight:800; margin-bottom:1rem; align-items:center;">
                    <span style="color:#991b1b;">Modo Mantenimiento (Offline)</span>
                    <i class="ri-error-warning-fill" style="font-size:1.5rem; color:#ef4444;"></i>
                </label>
                <p style="font-size:0.85rem; color:#991b1b; margin-top:0; margin-bottom:1rem;">Al activar esta opción, ningún visitante público podrá acceder al portal. Solo tú (Administrador) podrás ver la web. Útil para renovaciones grandes.</p>
                <select name="modo_mantenimiento" style="width:100%; padding:0.75rem; border-radius:4px; border:1px solid #f87171; background:white; font-weight:bold; color:#991b1b;">
                    <option value="inactivo" <?php echo ($configs['modo_mantenimiento'] ?? 'inactivo') === 'inactivo' ? 'selected' : ''; ?>>PÚBLICO (Normal)</option>
                    <option value="activo" <?php echo ($configs['modo_mantenimiento'] ?? 'inactivo') === 'activo' ? 'selected' : ''; ?>>EN MANTENIMIENTO (Bloqueado)</option>
                </select>
            </div>
        </div>

        <!-- NEW: PERSONALIZACIÓN AVANZADA CSS/FUENTE -->
        <div class="cfg-panel" style="grid-column: 1 / -1; border-top: 4px solid #10b981;">
            <h3><i class="ri-palette-line" style="color:#10b981;"></i> Personalización de Base (Tipografía y Estilos Dinámicos)</h3>
            <div style="display:flex; gap:2rem; flex-wrap:wrap;">
                <div style="flex:1; min-width:300px;">
                    <label style="font-weight:800; display:block; margin-bottom: 0.15rem;">Familia Tipográfica Global</label>
                    <p style="font-size:0.85rem; color:var(--text-muted); margin-top:0;">Elige la letra predilecta cargada de Google Fonts.</p>
                    <select name="theme_font_family" style="width:100%; padding:0.75rem; border-radius:4px; border:1px solid #cbd5e1; font-family:var(--font-sans);">
                        <?php $current_font = $configs['theme_font_family'] ?? 'Inter'; ?>
                        <option value="Inter" <?php echo $current_font === 'Inter' ? 'selected' : ''; ?>>Inter (Moderna, Limpia) - Recomendado</option>
                        <option value="Roboto" <?php echo $current_font === 'Roboto' ? 'selected' : ''; ?>>Roboto (Periodística, Estándar)</option>
                        <option value="Playfair Display" <?php echo $current_font === 'Playfair Display' ? 'selected' : ''; ?>>Playfair Display (Elegante, Clásica)</option>
                        <option value="Lora" <?php echo $current_font === 'Lora' ? 'selected' : ''; ?>>Lora (Editorial, Literaria)</option>
                    </select>
                </div>
                <div style="flex:2; min-width:300px;">
                    <label style="font-weight:800; display:block; margin-bottom: 0.15rem;">CSS Personalizado (Solo desarrolladores)</label>
                    <p style="font-size:0.85rem; color:var(--text-muted); margin-top:0;">Sobrescribe estilos del portal sin tocar los archivos .css del servidor.</p>
                    <textarea name="theme_custom_css" rows="5" style="width:100%; padding:0.75rem; border-radius:4px; border:1px solid #cbd5e1; font-family:monospace; background:#1e293b; color:#a5b4fc;" placeholder="body { background-color: #000; }"><?php echo htmlspecialchars($configs['theme_custom_css'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

    </div>

    <div style="height: 80px;"></div>
    <div style="position: fixed; bottom: 2rem; right: 2rem; z-index: 9999; background: white; padding: 1rem 1.25rem; border-radius: 50px; box-shadow: 0 10px 30px rgba(37, 99, 235, 0.25); border: 1px solid #bfdbfe; display:flex; align-items:center; gap: 1rem;">
        <button type="submit" class="btn-save" style="margin: 0; padding: 0.75rem 1.5rem; font-size: 1rem; box-shadow: none; border-radius: 30px;"><i class="ri-save-3-line"></i> Guardar Cambios Globales</button>
    </div>
</form>
