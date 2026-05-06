<?php
if (!function_exists('time_elapsed_string')) {
    function time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);
        $weeks = floor($diff->d / 7);
        $diff->d -= $weeks * 7;
        $string = ['y' => $diff->y ? $diff->y . ' año'.($diff->y>1?'s':'') : null, 'm' => $diff->m ? $diff->m . ' mes'.($diff->m>1?'es':'') : null, 'w' => $weeks ? $weeks . ' semana'.($weeks>1?'s':'') : null, 'd' => $diff->d ? $diff->d . ' día'.($diff->d>1?'s':'') : null, 'h' => $diff->h ? $diff->h . ' hora'.($diff->h>1?'s':'') : null, 'i' => $diff->i ? $diff->i . ' minuto'.($diff->i>1?'s':'') : null, 's' => $diff->s ? $diff->s . ' segundo'.($diff->s>1?'s':'') : null];
        $string = array_filter($string);
        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? 'Hace ' . implode(', ', $string) : 'Justo ahora';
    }
}
// Lógica para etiquetas SEO
$meta_title = !empty($articulo['seo_titulo']) ? $articulo['seo_titulo'] : $articulo['titulo'];
$meta_title_full = $meta_title . ' | ' . htmlspecialchars($global_configs['site_title'] ?? 'HTVPERU');
$meta_desc = !empty($articulo['seo_descripcion']) ? $articulo['seo_descripcion'] : $articulo['extracto'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!-- Anti-FOUC: Apply theme instantly before any render -->
    <script>
    (function(){
        var t = localStorage.getItem('theme');
        if(t === 'dark') document.documentElement.setAttribute('data-theme','dark');
    })();
    </script>
    <style>html:not([data-theme]) body, html[data-theme="dark"] body { visibility: visible; }</style>
        <!-- Preconnect to external origins for faster loading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $meta_title_full; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_desc ?: mb_substr(strip_tags($articulo['contenido']), 0, 160)); ?>">
    
    <link rel="icon" type="image/x-icon" href="<?php echo htmlspecialchars($global_configs['favicon_url'] ?? 'img/logo.webp'); ?>">
    <link rel="manifest" href="<?= base_url('/manifest.json') ?>">
    <meta name="theme-color" content="<?php echo htmlspecialchars($global_configs['color_primario'] ?? '#2563eb'); ?>">
    
    <!-- CUSTOM HEAD SCRIPTS -->
    <?php if(!empty($global_configs['script_header'])) echo render_safe_script($global_configs['script_header']); ?>
    
    <!-- OpenGraph & Redes Sociales Gráficas -->
    <?php
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $base_url = $protocol . $_SERVER['HTTP_HOST'] . ($dir === '/' ? '' : $dir) . '/';
    $current_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    ?>
    <meta property="og:title" content="<?php echo htmlspecialchars($articulo['seo_titulo'] ?: $articulo['titulo']); ?>" />
    <meta property="og:description" content="<?php echo htmlspecialchars($articulo['seo_descripcion'] ?: mb_substr(strip_tags($articulo['contenido']), 0, 160)); ?>" />
    <meta property="og:image" content="<?php echo $base_url . ltrim($articulo['imagen_url'], '/'); ?>" />
    <meta property="og:image:secure_url" content="<?php echo $base_url . ltrim($articulo['imagen_url'], '/'); ?>" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:type" content="article" />
    <meta property="og:url" content="<?php echo $current_url; ?>" />
    <meta property="og:site_name" content="HTVPERU" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php echo htmlspecialchars($articulo['seo_titulo'] ?: $articulo['titulo']); ?>" />
    <meta name="twitter:description" content="<?php echo htmlspecialchars($articulo['seo_descripcion'] ?: mb_substr(strip_tags($articulo['contenido']), 0, 160)); ?>" />
    <meta name="twitter:image" content="<?php echo $base_url . ltrim($articulo['imagen_url'], '/'); ?>" />
    <link rel="canonical" href="<?php echo $current_url; ?>" />

    <!-- Schema.org JSON-LD para Google News -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "NewsArticle",
      "headline": "<?php echo htmlspecialchars($articulo['seo_titulo'] ?: $articulo['titulo']); ?>",
      "image": [
        "<?php echo $base_url . ltrim($articulo['imagen_url'], '/'); ?>"
       ],
      "datePublished": "<?php echo date('c', strtotime($articulo['fecha_publicacion'])); ?>",
      "dateModified": "<?php echo date('c', strtotime($articulo['fecha_actualizacion'] ?? $articulo['fecha_publicacion'])); ?>",
      "author": [{
          "@type": "Person",
          "name": "<?php echo htmlspecialchars($articulo['autor']); ?>"
      }],
      "publisher": {
        "@type": "Organization",
        "name": "HTVPERU",
        "logo": {
          "@type": "ImageObject",
          "url": "<?php echo $base_url . 'img/logo.webp'; ?>"
        }
      }
    }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" media="print" onload="this.media='all'">
    <?= \App\Services\AssetManager::css('css/style.css') ?>
    <style>
        :root {
            --primary-color: <?php echo htmlspecialchars($global_configs['color_primario'] ?? '#2563eb'); ?>;
            --primary-hover: <?php echo htmlspecialchars($global_configs['color_secundario'] ?? '#1d4ed8'); ?>;
            --font-sans: '<?php echo htmlspecialchars($global_configs['theme_font_family'] ?? 'Inter'); ?>', sans-serif;
        }
        <?php 
        // MED-03 FIX: Sanitizar CSS custom para prevenir CSS injection
        if(!empty($global_configs['theme_custom_css'])) {
            $safe_css = $global_configs['theme_custom_css'];
            $safe_css = preg_replace('/expression\s*\([^)]*\)/i', '', $safe_css);
            $safe_css = preg_replace('/-moz-binding\s*:[^;}"\']*/', '', $safe_css);
            $safe_css = preg_replace('/javascript\s*:/i', '', $safe_css);
            $safe_css = preg_replace('/behavior\s*:/i', '', $safe_css);
            $safe_css = preg_replace('/@import/i', '', $safe_css);
            echo $safe_css;
        }
        ?>

        .article-header { margin-top: 2rem; margin-bottom: 2rem; text-align: left; position: relative; z-index: 10; }
        .article-category { color: var(--primary-color); font-weight: 800; text-transform: uppercase; letter-spacing: 1px; font-size: 0.875rem; display: block; margin-bottom: 1rem; }
        .article-title { font-family: var(--font-serif); font-size: 3.5rem; line-height: 1.1; margin-bottom: 1.5rem; width: 100%; margin: 0; color: var(--text-main); }
        .article-excerpt { font-size: 1.25rem; color: var(--text-muted); width: 100%; margin: 0 0 2rem 0; line-height: 1.6; }
        .article-meta { display: flex; justify-content: flex-start; flex-wrap: wrap; gap: 2rem; font-size: 0.875rem; color: var(--text-muted); padding: 1rem 0; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); width: 100%; margin: 0; }
        .article-media { width: 100%; height: auto; max-height: 800px; object-fit: contain; border-radius: var(--radius-lg); margin-bottom: 3rem; box-shadow: var(--shadow-md); margin-top: 2rem; background-color: var(--bg-card); display: block; }
        
        .article-body { width: 100%; margin: 0 0 4rem 0; font-size: 1.125rem; line-height: 1.8; color: var(--text-main); font-family: var(--font-sans); }
        .article-body p { margin-bottom: 1.5rem; }
        .article-body h2, .article-body h3, .article-body h4 { font-family: var(--font-sans); color: var(--text-main); margin-top: 2rem; margin-bottom: 1rem; }
        .article-body ul, .article-body ol { margin-bottom: 1.5rem; padding-left: 2rem; }
        .article-body blockquote { border-left: 4px solid var(--primary-color); padding-left: 1rem; color: var(--text-muted); font-style: italic; background: var(--bg-main); padding: 1rem; margin: 1.5rem 0; border-radius: 4px; }
        .article-body img, .article-body video { max-width: 100%; height: auto; border-radius: 8px; margin: 1rem 0; }
        
        .article-tags { width: 100%; margin: 0 0 1.5rem 0; display:flex; flex-wrap:wrap; gap:0.5rem; }
        .article-tags .tag { background: #e5e7eb; color: #4b5563; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .article-source { width: 100%; margin: 0 0 4rem 0; padding: 1rem; background: #eff6ff; border-left: 4px solid var(--primary-color); border-radius: 4px; font-size:0.9rem;}
        .article-source a { font-weight:800; color: var(--primary-color); }

        .related-articles { margin: 4rem 0; padding: 2rem 0; border-top: 1px solid var(--border-color); }
        .related-articles h2 { text-align: left; margin-bottom: 2.5rem; font-family: var(--font-serif); font-size: 2.5rem; color: var(--text-main); }
        .related-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; }
        .related-card { background: var(--bg-card); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-sm); transition: transform 0.3s ease; border: 1px solid var(--border-color); }
        .related-card:hover { transform: translateY(-5px); }
        .related-card img { width: 100%; height: 200px; object-fit: cover; }
        .related-card-content { padding: 1.5rem; }
        .related-card-category { color: var(--primary-color); font-weight: 800; text-transform: uppercase; font-size: 0.75rem; margin-bottom: 0.5rem; display: block; }
        .related-card-title { font-family: var(--font-sans); font-size: 1.25rem; line-height: 1.4; margin-bottom: 0.75rem; color: var(--text-main); }
        .related-card-title a { text-decoration: none; color: inherit; }
        .related-card-title a:hover { color: var(--primary-color); }
        
        body.zen-active .header, body.zen-active .footer, body.zen-active .sidebar-col, body.zen-active .related-articles, body.zen-active .social-share, body.zen-active .article-tags, body.zen-active #fb-root, body.zen-active .fb-comments { display: none !important; }
        body.zen-active { background-color: #fcfaf5 !important; color: #1a1a1a !important; padding-top: 2rem; }
        body.zen-active .article-title { color: #111 !important; font-size: 4rem; }
        body.zen-active .article-body { font-size: 1.35rem; font-family: var(--font-serif); color: #222 !important; max-width: 700px; line-height: 2; margin-top: 3rem; }
        body.zen-active .article-meta, body.zen-active .article-category { justify-content: flex-start; text-align: left; margin-left: auto; margin-right: auto; max-width: 700px; margin-bottom: 2rem; }
        body.zen-active .main-grid { display: block; }
        
        .zen-btn { position: fixed; bottom: 30px; left: 30px; width: 60px; height: 60px; border-radius: 50%; background: var(--primary-color); color: white; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.2); font-size: 1.5rem; cursor: pointer; z-index: 9999; display: flex; align-items: center; justify-content: center; transition: all 0.3s; }
        .zen-btn:hover { transform: scale(1.1); }
        body.zen-active .zen-btn { background: #1a1a1a; color: #fcfaf5; }
    </style>
</head>
<body>
    <?php if(isset($global_configs['modo_mantenimiento']) && $global_configs['modo_mantenimiento'] === 'activo' && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
    <div style="background: #ef4444; color: #ffffff; text-align: center; padding: 0.75rem; font-size: 0.9rem; font-weight: 800; font-family:var(--font-sans); text-transform:uppercase; animation: pulse 2s infinite; z-index:999999; position:relative;">
        <i class="ri-alert-fill"></i> MODO MANTENIMIENTO ACTIVO: El público no puede ver el sitio, solo tú como Administrador.
    </div>
    <style> @keyframes pulse { 0% { background: #b91c1c; } 50% { background: #ef4444; } 100% { background: #b91c1c; } } </style>
    <?php endif; ?>

    <?php if(($global_configs['alert_top_estado'] ?? 'inactivo') === 'activo'): ?>
    <div id="alertTopBar" style="background: linear-gradient(90deg, var(--primary-color) 0%, #1e3a8a 50%, var(--primary-color) 100%); background-size: 200% 100%; animation: alertShimmer 6s ease-in-out infinite; color: #ffffff; text-align: center; padding: 0.6rem 2rem; font-size: 0.85rem; font-weight: 600; position: relative; z-index: 50; display: flex; align-items: center; justify-content: center; gap: 0.75rem;">
        <style>@keyframes alertShimmer { 0%{background-position:200% 0} 50%{background-position:0% 0} 100%{background-position:200% 0} } @keyframes pulseYellow { 0%,100%{box-shadow:0 0 0 0 rgba(251,191,36,0.6)} 50%{box-shadow:0 0 0 6px rgba(251,191,36,0)} }</style>
        <span style="display:inline-flex; align-items:center; width:10px; height:10px; border-radius:50%; background:#fbbf24; animation: pulseYellow 2s infinite; flex-shrink:0;"></span>
        <?php if(!empty($global_configs['alert_top_url'])): ?><a href="<?php echo htmlspecialchars($global_configs['alert_top_url']); ?>" style="color:#fff; text-decoration:none; border-bottom: 1px solid rgba(255,255,255,0.4); padding-bottom:1px;"><?php endif; ?>
        <?php echo htmlspecialchars($global_configs['alert_top_texto'] ?? ''); ?>
        <?php if(!empty($global_configs['alert_top_url'])): ?> <i class="ri-arrow-right-up-line" style="font-size:0.9rem;"></i></a><?php endif; ?>
        <button onclick="this.parentElement.style.display='none'" style="position:absolute; right:1rem; background:rgba(255,255,255,0.15); border:none; color:white; width:24px; height:24px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:0.8rem;" title="Cerrar"><i class="ri-close-line"></i></button>
    </div>
    <?php endif; ?>

    <?php include 'includes/header_global.php'; ?>

    <main class="container main-grid" style="padding: 2rem 1.5rem; display: grid; grid-template-columns: 1fr 340px; gap: 3.5rem; align-items: start;">
        <div class="content-col" style="min-width:0;">
        <div class="article-header">
            <span class="article-category"><?php echo htmlspecialchars($articulo['categoria']); ?></span>
            <h1 class="article-title"><?php echo htmlspecialchars($articulo['titulo']); ?></h1>
            <p class="article-excerpt"><?php echo htmlspecialchars($articulo['extracto']); ?></p>
            <div class="article-meta">
                <span><i class="ri-user-line"></i> Escrito por: <strong><?php echo htmlspecialchars($articulo['autor']); ?></strong></span>
                <span><i class="ri-calendar-line"></i> Fecha: <?php echo date('d/m/Y H:i', strtotime($articulo['fecha_publicacion'])); ?></span>
                <span><i class="ri-eye-line"></i> <strong><?php echo number_format($articulo['vistas']); ?></strong> visitas</span>
                <span style="color:var(--primary-color); font-weight: 800; background: var(--bg-main); padding: 2px 8px; border-radius: 4px; border: 1px solid var(--border-color);"><i class="ri-time-line"></i> Lectura: <?php echo $reading_time; ?> min</span>
                
                <button id="btn-save-bookmark" data-slug="<?php echo htmlspecialchars($articulo['slug']); ?>" data-title="<?php echo htmlspecialchars($articulo['titulo']); ?>" data-img="<?php echo htmlspecialchars($articulo['imagen_url']); ?>" style="background:var(--bg-main); border:1px solid var(--border-color); color:var(--text-main); border-radius:4px; padding:2px 10px; cursor:pointer; font-weight:800; display:flex; align-items:center; gap:5px; transition:all 0.3s ease;">
                    <i class="ri-bookmark-line"></i> Guardar Nota
                </button>
                
                <div style="display:flex; gap:10px; align-items:center; border-left: 1px solid var(--border-color); padding-left: 1rem; margin-left: 1rem;">
                    <button onclick="document.querySelector('.article-body').style.fontSize='1rem'" style="background:var(--bg-main); border:1px solid var(--border-color); color:var(--text-main); border-radius:4px; padding:2px 8px; cursor:pointer;" title="Texto Pequeño">A-</button>
                    <button onclick="document.querySelector('.article-body').style.fontSize='1.125rem'" style="background:var(--bg-main); border:1px solid var(--border-color); color:var(--text-main); border-radius:4px; padding:2px 8px; cursor:pointer;" title="Texto Normal">A</button>
                    <button onclick="document.querySelector('.article-body').style.fontSize='1.3rem'" style="background:var(--bg-main); border:1px solid var(--border-color); color:var(--text-main); border-radius:4px; padding:2px 8px; cursor:pointer;" title="Texto Grande">A+</button>
                </div>
            </div>
            
            <!-- Botones de Compartir (Fase 3) -->
            <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem; flex-wrap: wrap;">
                <?php
                $share_url = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                $share_title = urlencode($articulo['titulo']);
                ?>
                <a href="https://wa.me/?text=<?php echo $share_title; ?>%20-%20<?php echo $share_url; ?>" target="_blank" style="background:#25D366; color:white; padding:0.5rem 1rem; border-radius:50px; font-weight:600; font-size:0.9rem; text-decoration:none; display:flex; align-items:center; gap:0.5rem; transition:transform 0.2s;">
                    <i class="ri-whatsapp-line" style="font-size:1.1rem;"></i> WhatsApp
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url; ?>" target="_blank" style="background:#1877F2; color:white; padding:0.5rem 1rem; border-radius:50px; font-weight:600; font-size:0.9rem; text-decoration:none; display:flex; align-items:center; gap:0.5rem; transition:transform 0.2s;">
                    <i class="ri-facebook-fill" style="font-size:1.1rem;"></i> Facebook
                </a>
                <a href="https://twitter.com/intent/tweet?url=<?php echo $share_url; ?>&text=<?php echo $share_title; ?>" target="_blank" style="background:#000000; color:white; padding:0.5rem 1rem; border-radius:50px; font-weight:600; font-size:0.9rem; text-decoration:none; display:flex; align-items:center; gap:0.5rem; transition:transform 0.2s;">
                    <i class="ri-twitter-x-line" style="font-size:1.1rem;"></i> Twitter
                </a>
                <button onclick="navigator.clipboard.writeText('<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>'); this.innerHTML='<i class=\'ri-check-line\'></i> Copiado!'; setTimeout(() => this.innerHTML='<i class=\'ri-links-line\'></i> Copiar', 2000);" style="background:var(--bg-main); border:1px solid var(--border-color); color:var(--text-main); padding:0.5rem 1rem; border-radius:50px; font-weight:600; font-size:0.9rem; cursor:pointer; display:flex; align-items:center; gap:0.5rem; transition:background 0.2s;">
                    <i class="ri-links-line" style="font-size:1.1rem;"></i> Copiar
                </button>
            </div>
        </div>
        
        <!-- Renderizando la imagen principal o Video con Poster Dinámico (LCP Priority) -->
        <?php echo renderMedia($articulo['imagen_url'], 'article-media', $articulo['video_poster_url'] ?? '', true, '', false); ?>
        
        <!-- Etiquetas / Tags -->
        <?php if (!empty($articulo['tags'])): ?>
        <div class="article-tags">
            <i class="ri-price-tag-3-fill" style="color:var(--text-muted); margin-top:2px;"></i>
            <?php 
                $tags_array = array_map('trim', explode(',', $articulo['tags']));
                foreach ($tags_array as $tag):
                    $tag_slug = strtolower(str_replace(' ', '-', $tag));
            ?>
                <a href="/piura_noticias_php/etiqueta/<?php echo urlencode($tag_slug); ?>" class="tag" style="text-decoration:none; display:inline-block; transition:transform 0.2s;"><?php echo htmlspecialchars($tag); ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php
        $ad_sup = get_ad_from_cache($g_data['publicidad'] ?? [], 'interior_superior');
        if($ad_sup): ?>
            <div style="width:100%; margin: 0 0 2rem 0; text-align:center;">
                <?php if($ad_sup['tipo'] === 'adsense') echo $ad_sup['codigo_script']; else echo '<a href="'.htmlspecialchars($ad_sup['enlace_url']??'#').'" target="_blank"><img src="'.htmlspecialchars($ad_sup['imagen_url']).'" style="max-width:100%; border-radius:4px;"></a>'; ?>
            </div>
        <?php endif; ?>

        <!-- Contenido WYSIWYG -->
        <article class="article-body">
            <!-- Botón Text-to-Speech Web API -->
            <button id="btn-tts" onclick="toggleTTS()" style="display:flex; align-items:center; gap:0.5rem; background:var(--bg-main); color:var(--text-main); border:2px solid var(--border-color); padding:10px 20px; border-radius:50px; cursor:pointer; font-weight:800; margin-bottom: 2rem; transition:transform 0.2s, background 0.3s; box-shadow: var(--shadow-sm);" onmouseover="this.style.background='var(--primary-light)'; this.style.color='var(--primary-color)';" onmouseout="this.style.background='var(--bg-main)'; this.style.color='var(--text-main)';">
                <i class="ri-volume-up-fill" id="tts-icon" style="font-size: 1.2rem; color: var(--primary-color);"></i> <span id="tts-text">Escuchar esta Noticia</span>
            </button>
            <div id="article-text-content">
                <?php echo $contenido_html; ?>
            </div>
        </article>

        <?php
        $ad_inf = get_ad_from_cache($g_data['publicidad'] ?? [], 'interior_inferior');
        if($ad_inf): ?>
            <div style="width:100%; margin: 0 0 3rem 0; text-align:center;">
                <?php if($ad_inf['tipo'] === 'adsense') echo $ad_inf['codigo_script']; else echo '<a href="'.htmlspecialchars($ad_inf['enlace_url']??'#').'" target="_blank"><img src="'.htmlspecialchars($ad_inf['imagen_url']).'" style="max-width:100%; border-radius:4px;"></a>'; ?>
            </div>
        <?php endif; ?>

        <!-- Bloque de Autor (Fase 14) -->
        <div style="width:100%; margin: 0 0 3rem 0; padding: 2rem; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); display:flex; gap: 1.5rem; align-items:center; box-shadow: var(--shadow-sm);">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--primary-light); color: var(--primary-color); display:flex; align-items:center; justify-content:center; font-size: 2.5rem; font-weight: 800; flex-shrink:0;">
                <i class="ri-user-smile-line"></i>
            </div>
            <div>
                <span style="font-size:0.85rem; text-transform:uppercase; font-weight:800; color:var(--text-muted); letter-spacing:1px; display:block; margin-bottom:0.25rem;">Escrito por</span>
                <h4 style="margin:0 0 0.5rem 0; font-size: 1.5rem; color:var(--text-main); font-family:var(--font-sans);"><?php echo htmlspecialchars($articulo['autor']); ?></h4>
                <p style="margin:0; color:var(--text-muted); font-size:0.95rem; line-height:1.5;">Periodista y redactor corporativo. Dedicado a brindar la información más relevante y verificada a la ciudadanía.</p>
            </div>
        </div>

        <!-- Bloque de Fuentes / Source -->
        <?php if (!empty($articulo['fuente_nombre'])): ?>
        <div class="article-source">
            <i class="ri-file-info-line"></i> <strong>Fuente Periodística Original:</strong> 
            <?php if (!empty($articulo['fuente_url'])): ?>
                <a href="<?php echo htmlspecialchars($articulo['fuente_url']); ?>" target="_blank" rel="noopener noreferrer">
                    <?php echo htmlspecialchars($articulo['fuente_nombre']); ?> <i class="ri-external-link-line"></i>
                </a>
            <?php else: ?>
                <span><?php echo htmlspecialchars($articulo['fuente_nombre']); ?></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Botones de Compartir Flotantes (Fase 13) -->
        <div style="margin: 2.5rem 0; padding: 1.5rem 0; border-top: 1px solid var(--border-color); border-bottom: 2px solid var(--border-color); display:flex; gap: 1rem; align-items:center; flex-wrap:wrap;">
            <span style="font-weight: 800; color: var(--text-muted); text-transform:uppercase; font-size: 0.85rem;"><i class="ri-share-line"></i> Viralizar:</span>
            <?php 
            $share_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $share_title = $articulo['titulo'];
            $whatsapp_text = urlencode($share_title . "\n\n👉 Lee la nota completa aquí en el primer comentario 👇Ÿ‘‡\n" . $share_url);
            $twitter_text = urlencode($share_title . "\n\n👉 Lee la nota completa aquí:\n");
            ?>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($share_url); ?>" target="_blank" style="background:#1877F2; color:white; padding: 10px 20px; border-radius:4px; font-weight:bold; text-decoration:none; display:flex; align-items:center; gap:5px; box-shadow:0 3px 6px rgba(24,119,242,0.3); transition:transform 0.3s;"><i class="ri-facebook-circle-fill"></i> Facebook</a>
            <a href="https://api.whatsapp.com/send?text=<?php echo $whatsapp_text; ?>" target="_blank" style="background:#25D366; color:white; padding: 10px 20px; border-radius:4px; font-weight:bold; text-decoration:none; display:flex; align-items:center; gap:5px; box-shadow:0 3px 6px rgba(37,211,102,0.3); transition:transform 0.3s;"><i class="ri-whatsapp-line"></i> WhatsApp</a>
            <a href="https://twitter.com/intent/tweet?text=<?php echo $twitter_text; ?>&url=<?php echo urlencode($share_url); ?>" target="_blank" style="background:#000000; color:white; padding: 10px 20px; border-radius:4px; font-weight:bold; text-decoration:none; display:flex; align-items:center; gap:5px; transition:transform 0.3s;"><i class="ri-twitter-x-line"></i> X</a>
            <button onclick="copiarFormatoRedes()" style="background:#4b5563; color:white; padding: 10px 20px; border:none; border-radius:4px; font-weight:bold; cursor:pointer; display:flex; align-items:center; gap:5px; transition:transform 0.3s; font-family:var(--font-sans);" title="Copia el formato para pegar en el muro de Facebook"><i class="ri-clipboard-line"></i> Formato FB</button>
            <script>
            function copiarFormatoRedes() {
                const texto = `<?php echo addslashes($share_title); ?>\n\n👉 Lee la nota completa aquí en el primer comentario 👇Ÿ‘‡\n<?php echo $share_url; ?>`;
                navigator.clipboard.writeText(texto).then(() => {
                    alert("¡Formato para Facebook copiado al portapapeles!\n\n1. Pega esto al crear una nueva publicación.\n2. Borra el link si quieres poner fondo de color, y pega el link abajo en comentarios como en tu ejemplo.");
                }).catch(err => {
                    alert("Error al copiar al portapapeles: " + err);
                });
            }
            </script>
        </div>

        <!-- Comentarios Nativos -->
        <div id="comentarios" style="width: 100%; margin: 4rem 0; padding-top: 2rem; border-top: 2px solid var(--border-color);">
            <h3 style="font-family: var(--font-sans); margin-bottom: 1.5rem; color: var(--text-main); font-size: 1.75rem;"><i class="ri-discuss-line" style="color:var(--primary-color);"></i> Deja tu Comentario</h3>
            
            <?php echo $comment_msg ?? ''; ?>

            <div style="background:var(--bg-card); padding:2rem; border-radius:var(--radius-lg); border:1px solid var(--border-color); margin-bottom: 3rem;">
                <p style="font-size:0.9rem; color:var(--text-muted); margin-top:0; margin-bottom:1.5rem;"><i class="ri-information-line"></i> Los comentarios están sujetos a moderación para mantener una comunidad respetuosa.</p>
                
                <?php if(($global_configs['social_login_estado'] ?? 'inactivo') === 'activo'): ?>
                    <?php if(!isset($_SESSION['public_user_id'])): ?>
                        <div style="text-align:center; padding: 2rem 0;">
                            <h4 style="margin-top:0; font-family:var(--font-sans); color:var(--text-main);">Inicia sesión para participar</h4>
                            <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:2rem;">Para evitar el spam y mantener una comunidad real, requerimos que inicies sesión.</p>
                            <div style="display:flex; justify-content:center; gap:1rem; flex-wrap:wrap;">
                                <a href="auth/google?return_to=<?php echo urlencode($protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>" style="display:inline-flex; align-items:center; gap:10px; background:white; color:#444; border:1px solid #ccc; padding:10px 20px; border-radius:4px; font-weight:bold; text-decoration:none; box-shadow:0 2px 4px rgba(0,0,0,0.05); transition:transform 0.2s;">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" width="20"> Continuar con Google
                                </a>
                                <a href="auth/facebook?return_to=<?php echo urlencode($protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>" style="display:inline-flex; align-items:center; gap:10px; background:#1877F2; color:white; border:none; padding:10px 20px; border-radius:4px; font-weight:bold; text-decoration:none; box-shadow:0 2px 4px rgba(24,119,242,0.3); transition:transform 0.2s;">
                                    <i class="ri-facebook-circle-fill" style="font-size:1.2rem;"></i> Continuar con Facebook
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem; background:var(--bg-main); padding:1rem; border-radius:8px; border:1px solid var(--border-color);">
                            <?php if(!empty($_SESSION['public_user_avatar'])): ?>
                                <img src="<?php echo htmlspecialchars($_SESSION['public_user_avatar']); ?>" style="width:50px; height:50px; border-radius:50%; object-fit:cover;">
                            <?php else: ?>
                                <div style="width:50px; height:50px; border-radius:50%; background:var(--primary-light); color:var(--primary-color); display:flex; align-items:center; justify-content:center; font-size:1.5rem;"><i class="ri-user-smile-fill"></i></div>
                            <?php endif; ?>
                            <div style="flex:1;">
                                <div style="font-weight:bold; color:var(--text-main);"><?php echo htmlspecialchars($_SESSION['public_user_name']); ?></div>
                                <div style="font-size:0.8rem; color:var(--text-muted);"><i class="ri-shield-check-fill" style="color:#10b981;"></i> Identidad verificada</div>
                            </div>
                            <a href="auth/logout?return_to=<?php echo urlencode($protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>" style="color:var(--text-muted); font-size:0.85rem; text-decoration:none; border:1px solid var(--border-color); padding:5px 10px; border-radius:4px;"><i class="ri-logout-box-r-line"></i> Salir</a>
                        </div>
                        <form method="POST" action="#comentarios">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="noticia_id" value="<?php echo $articulo['id']; ?>">
                            <input type="text" name="website_hp" style="display:none !important; position:absolute; left:-9999px;" tabindex="-1" autocomplete="off">
                            <div style="margin-bottom:1.5rem;">
                                <textarea name="comentario" rows="4" required placeholder="Escribe tu opinión aquí..." style="width:100%; padding:1rem; border:1px solid var(--border-color); border-radius:8px; background:var(--bg-main); color:var(--text-main); resize:vertical; font-family:var(--font-sans);"></textarea>
                            </div>
                            <button type="submit" name="submit_comment" style="background:var(--primary-color); color:white; border:none; padding:10px 24px; border-radius:4px; font-weight:bold; cursor:pointer; font-size:1rem;"><i class="ri-send-plane-fill"></i> Publicar Comentario</button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <form method="POST" action="#comentarios">
                        <?php echo csrf_field(); // AUDIT FIX: CRIT-A4 ?>
                        <input type="hidden" name="noticia_id" value="<?php echo $articulo['id']; ?>">
                        <!-- CRIT-03 FIX: Honeypot anti-spam (invisible para humanos, bots lo llenan) -->
                        <input type="text" name="website_hp" style="display:none !important; position:absolute; left:-9999px;" tabindex="-1" autocomplete="off">
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">
                            <div>
                                <label style="display:block; margin-bottom:0.5rem; font-weight:bold; color:var(--text-main);">Nombre Completo *</label>
                                <input type="text" name="nombre" required style="width:100%; padding:0.75rem; border:1px solid var(--border-color); border-radius:4px; background:var(--bg-main); color:var(--text-main);">
                            </div>
                            <div>
                                <label style="display:block; margin-bottom:0.5rem; font-weight:bold; color:var(--text-main);">Enlace de Perfil de Facebook *</label>
                                <input type="url" name="facebook_url" placeholder="https://facebook.com/tu.perfil" required style="width:100%; padding:0.75rem; border:1px solid var(--border-color); border-radius:4px; background:var(--bg-main); color:var(--text-main);">
                            </div>
                        </div>
                        <div style="margin-bottom:1.5rem;">
                            <label style="display:block; margin-bottom:0.5rem; font-weight:bold; color:var(--text-main);">Comentario *</label>
                            <textarea name="comentario" rows="4" required style="width:100%; padding:0.75rem; border:1px solid var(--border-color); border-radius:4px; background:var(--bg-main); color:var(--text-main); resize:vertical;"></textarea>
                        </div>
                        <button type="submit" name="submit_comment" style="background:var(--primary-color); color:white; border:none; padding:10px 24px; border-radius:4px; font-weight:bold; cursor:pointer; font-size:1rem;"><i class="ri-send-plane-fill"></i> Publicar Comentario</button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Listado de Comentarios Aprobados -->
            <?php
            $com_stmt = $pdo->prepare("SELECT c.*, u.nombre as up_nombre, u.avatar_url, u.proveedor FROM comentarios c LEFT JOIN usuarios_publicos u ON c.usuario_publico_id = u.id WHERE c.noticia_id = ? AND c.estado = 'Aprobado' ORDER BY c.fecha DESC");
            $com_stmt->execute([$articulo['id']]);
            $comentarios_aprobados = $com_stmt->fetchAll();
            ?>
            <div class="comentarios-lista">
                <h4 style="font-family:var(--font-sans); margin-bottom:1.5rem; color:var(--text-main); font-size:1.25rem;">Comentarios (<?php echo count($comentarios_aprobados); ?>)</h4>
                <?php if(count($comentarios_aprobados) > 0): ?>
                    <div style="display:flex; flex-direction:column; gap:1.5rem;">
                    <?php foreach($comentarios_aprobados as $com): ?>
                        <div style="background:var(--bg-main); padding:1.5rem; border-radius:var(--radius-lg); border:1px solid var(--border-color); display:flex; gap:1rem;">
                            <div style="width:48px; height:48px; border-radius:50%; background:var(--primary-light); color:var(--primary-color); display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:1.5rem; overflow:hidden;">
                                <?php if (!empty($com['avatar_url'])): ?>
                                    <img src="<?php echo htmlspecialchars(base_url($com['avatar_url'])); ?>" style="width:100%; height:100%; object-fit:cover;">
                                <?php else: ?>
                                    <i class="ri-user-smile-fill"></i>
                                <?php endif; ?>
                            </div>
                            <div style="flex:1;">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.5rem; flex-wrap:wrap;">
                                    <strong><?php echo htmlspecialchars($com['up_nombre'] ?? $com['nombre'], ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?></strong>
                                    <span style="font-size:0.8rem; color:var(--text-muted);"><i class="ri-time-line"></i> <?php echo date('d/m/Y H:i', strtotime($com['fecha'])); ?></span>
                                </div>
                                <?php if (!empty($com['facebook_url'])): ?>
                                <a href="<?php echo htmlspecialchars($com['facebook_url'], ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?>" target="_blank" style="font-size:0.8rem; color:var(--primary-color); text-decoration:none; display:inline-flex; align-items:center; gap:3px; margin-bottom:10px;"><i class="ri-facebook-circle-fill"></i> Ver Perfil</a>
                                <?php elseif ($com['proveedor'] === 'facebook'): ?>
                                <span style="font-size:0.8rem; color:#1877F2; display:inline-flex; align-items:center; gap:3px; margin-bottom:10px;"><i class="ri-facebook-circle-fill"></i> Verificado por Facebook</span>
                                <?php elseif ($com['proveedor'] === 'google'): ?>
                                <span style="font-size:0.8rem; color:#EA4335; display:inline-flex; align-items:center; gap:3px; margin-bottom:10px;"><i class="ri-google-fill"></i> Verificado por Google</span>
                                <?php endif; ?>
                                <p style="margin:0; font-family:var(--font-sans); color:var(--text-main); line-height:1.6;"><?php echo nl2br(htmlspecialchars($com['comentario'], ENT_QUOTES | ENT_HTML5, 'UTF-8')); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color:var(--text-muted); font-style:italic;">Se el primero en comentar esta noticia.</p>
                <?php endif; ?>
            </div>
        </div>        
        <!-- Noticias Relacionadas -->
        <?php if (count($relacionadas) > 0): ?>
        <section class="related-articles">
            <h2>Noticias Relacionadas</h2>
            <div class="related-grid">
                <?php foreach ($relacionadas as $rel_articulo): ?>
                <div class="related-card">
                    <a href="/piura_noticias_php/<?php echo urlencode($rel_articulo['slug']); ?>">
                        <?php echo renderMedia($rel_articulo['imagen_url'], '', $rel_articulo['video_poster_url'] ?? ''); ?>
                    </a>
                    <div class="related-card-content">
                        <span class="related-card-category"><a href="/piura_noticias_php/categoria/<?php echo urlencode(strtolower($rel_articulo['categoria'])); ?>" style="color:var(--primary-color); text-decoration:none;"><?php echo htmlspecialchars($rel_articulo['categoria']); ?></a></span>
                        <h3 class="related-card-title"><a href="/piura_noticias_php/<?php echo urlencode($rel_articulo['slug']); ?>"><?php echo htmlspecialchars($rel_articulo['titulo']); ?></a></h3>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
        </div> <!-- content-col -->
        
        <aside class="sidebar-col">
            <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:1.5rem; position:sticky; top:2rem;">
                <h3 style="margin-top:0; border-bottom:2px solid var(--primary-color); padding-bottom:0.5rem; font-family:var(--font-sans); color:var(--text-main);"><i class="ri-flashlight-fill" style="color:#fbbf24;"></i> Lo Último</h3>
                <div style="display:flex; flex-direction:column; gap:1.25rem; margin-top:1.5rem;">
                    <?php if(!empty($todas_noticias)): ?>
                    <?php foreach ($todas_noticias as $sn): ?>
                    <a href="/piura_noticias_php/<?php echo urlencode($sn['slug']); ?>" style="display:flex; gap:1rem; text-decoration:none;">
                        <?php echo renderMedia($sn['imagen_url'], '', $sn['video_poster_url'] ?? '', false, 'width: 80px; height: 80px; object-fit: cover; border-radius: 4px; flex-shrink:0;'); ?>
                        <div>
                            <span style="font-size:0.7rem; color:var(--primary-color); font-weight:800; text-transform:uppercase; letter-spacing:0.5px;"><?php echo htmlspecialchars($sn['categoria']); ?></span>
                            <h4 style="margin:0; font-size:0.95rem; color:var(--text-main); font-family:var(--font-sans); line-height:1.3; font-weight:600; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;"><?php echo htmlspecialchars($sn['titulo']); ?></h4>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        
                <?php include 'includes/sidebar_social.php'; ?>
            </aside>
    </main>

    <?php include 'includes/footer_global.php'; ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Dark Mode Toggle (Movido a premium-features.js)
            
            // Reading Progress Bar (header shrink handled by premium-features.js)
            const progressBar = document.getElementById("reading-progress");
            if (progressBar) {
                let isScrolling = false;
                let docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                
                window.addEventListener('resize', () => {
                    docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                }, { passive: true });

                window.addEventListener('load', () => {
                    docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                });
                
                window.addEventListener('scroll', () => {
                    if (!isScrolling) {
                        window.requestAnimationFrame(() => {
                            const winScroll = document.documentElement.scrollTop || document.body.scrollTop;
                            const scrolled = docHeight > 0 ? (winScroll / docHeight) * 100 : 0;
                            progressBar.style.width = scrolled + "%";
                            isScrolling = false;
                        });
                        isScrolling = true;
                    }
                }, { passive: true });
            }
        });
    </script>
    <!-- Script para Text-To-Speech -->
    <script>
    let synthesis = window.speechSynthesis;
    let utterance = null;
    let isPlaying = false;

    function toggleTTS() {
        const textNode = document.getElementById('article-text-content');
        const textToRead = textNode.innerText || textNode.textContent;
        const btnText = document.getElementById('tts-text');
        const btnIcon = document.getElementById('tts-icon');

        if (!utterance) {
            utterance = new SpeechSynthesisUtterance(textToRead);
            utterance.lang = 'es-ES';
            utterance.rate = 1.0;
            
            utterance.onend = function() {
                isPlaying = false;
                btnText.innerText = 'Escuchar esta Noticia';
                btnIcon.className = 'ri-volume-up-fill';
            };
        }

        if (isPlaying) {
            synthesis.pause();
            isPlaying = false;
            btnText.innerText = 'Reanudar Audio';
            btnIcon.className = 'ri-play-circle-fill';
        } else {
            if (synthesis.paused) {
                synthesis.resume();
            } else {
                synthesis.speak(utterance);
            }
            isPlaying = true;
            btnText.innerText = 'Pausar Audio';
            btnIcon.className = 'ri-pause-circle-fill';
        }
    }
    </script>
    
    <?php if(($global_configs['cookie_banner_estado'] ?? 'inactivo') === 'activo'): ?>
    <!-- LEY DE COOKIES GDPR -->
    <div id="gdpr-cookie-banner" style="position:fixed; bottom:0; left:0; width:100%; background:#111827; color:white; padding:1rem; z-index:99999; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; box-shadow:0 -5px 20px rgba(0,0,0,0.5);">
        <div style="flex:1; min-width:300px; font-size:0.85rem; margin-right:2rem; font-family:var(--font-sans);">
            Utilizamos cookies propias y de terceros para fines analíticos y para mostrarte publicidad personalizada en base a un perfil elaborado a partir de tus hábitos de navegación. <a href="/piura_noticias_php/pagina/politica-y-privacidad" style="color:#60a5fa; text-decoration:underline;">Ver política</a>.
        </div>
        <button onclick="document.getElementById('gdpr-cookie-banner').style.display='none'; localStorage.setItem('cookies_accepted', 'true');" style="background:var(--primary-color); color:white; border:none; padding:10px 20px; font-weight:bold; cursor:pointer; font-family:var(--font-sans); border-radius:4px; margin-top:10px;">Aceptar todas</button>
    </div>
    <script>if(localStorage.getItem('cookies_accepted') === 'true') document.getElementById('gdpr-cookie-banner').style.display='none';</script>
    <?php endif; ?>
    
    <!-- CUSTOM BODY SCRIPTS -->
    <?php if(!empty($global_configs['script_footer'])) echo render_safe_script($global_configs['script_footer']); ?>

    <!-- ZEN MODE BUTTON -->
    <button class="zen-btn" id="zen-toggle" title="Modo Lectura (Zen)">
        <i class="ri-eye-line"></i>
    </button>
    <script>
        const zenBtn = document.getElementById('zen-toggle');
        const bodyEle = document.body;
        const iconZen = zenBtn.querySelector('i');
        zenBtn.addEventListener('click', () => {
            bodyEle.classList.toggle('zen-active');
            if(bodyEle.classList.contains('zen-active')) {
                iconZen.className = 'ri-eye-off-line';
            } else {
                iconZen.className = 'ri-eye-line';
            }
        });
    </script>
    <?= \App\Services\AssetManager::js('js/premium-features.js') ?>
<?php include 'includes/floating_social.php'; ?>

<!-- ANTIGRAVITY LITE: Incrementador de vistas asíncrono para liberar la BD principal en carga -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    setTimeout(() => { // Pequeño retraso para dar prioridad a la carga visible
        const viewKey = 'viewed_' + '<?php echo (int)($id ?? 0); ?>';
        if (!localStorage.getItem(viewKey)) {
            fetch('api/view_counter', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ slug: '<?php echo addslashes($slug ?? ""); ?>', id: <?php echo (int)($id ?? 0); ?> })
            })
            .then(res => { if(res.ok) localStorage.setItem(viewKey, '1'); })
            .catch(e => console.error("Async view fail", e));
        }
    }, 1500);
});
</script>

<?php 
$modal_article = 'includes/modal_privacidad.php';
if (file_exists($modal_article)) include_once $modal_article; 
?>
</body>
</html>


