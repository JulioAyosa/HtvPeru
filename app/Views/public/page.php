<!DOCTYPE html>
<html lang="es" data-theme="light">
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
    <title><?php echo $site_title; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" media="print" onload="this.media='all'">
    <?= \App\Services\AssetManager::css('css/style.css') ?>
    <style>
        :root {
            --primary: <?php echo $color_primario; ?>;
            --primary-hover: <?php echo $color_secundario; ?>;
        }
        .page-content {
            background: var(--bg-card);
            padding: 3rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 4rem;
            line-height: 1.8;
            font-size: 1.1rem;
            color: var(--text-main);
        }
        .page-content h1 { font-size: 2.5rem; margin-bottom: 2rem; color: var(--text-main); font-family: 'Playfair Display', serif;}
        .page-content h2, .page-content h3 { margin-top: 2rem; margin-bottom: 1rem; color: var(--text-main); }
        .page-content img { max-width: 100%; height: auto; border-radius: 8px; margin: 1rem 0; }
        .page-content a { color: var(--primary); text-decoration: underline; }
    </style>
    <style>
        :root {
            --primary-color: <?php echo htmlspecialchars($global_configs['color_primario'] ?? '#2563eb'); ?>;
            --primary-hover: <?php echo htmlspecialchars($global_configs['color_secundario'] ?? '#1d4ed8'); ?>;
            --font-sans: '<?php echo htmlspecialchars($global_configs['theme_font_family'] ?? 'Inter'); ?>', sans-serif;
        }
        <?php if(!empty($global_configs['theme_custom_css'])) echo sanitize_css($global_configs['theme_custom_css']); ?>
    </style>
</head>
<body>
    <!-- Topbar -->
    <div style="background: var(--bg-card); border-bottom: 1px solid var(--border-color); padding: 0.5rem 0; font-size: 0.85rem;">
        <div class="container" style="display:flex; justify-content:space-between; color: var(--text-muted);">
            <div><i class="ri-calendar-2-line"></i> <?php echo $fecha_espanol; ?></div>
            <div style="display:flex; gap:1rem; font-size:1rem;">
                <?php if(!empty($global_configs['social_facebook'])): ?><a href="<?php echo htmlspecialchars($global_configs['social_facebook']); ?>" target="_blank" style="color:var(--text-main);"><i class="ri-facebook-circle-fill"></i></a><?php endif; ?>
                <?php if(!empty($global_configs['social_twitter'])): ?><a href="<?php echo htmlspecialchars($global_configs['social_twitter']); ?>" target="_blank" style="color:var(--text-main);"><i class="ri-twitter-x-line"></i></a><?php endif; ?>
                <?php if(!empty($global_configs['social_instagram'])): ?><a href="<?php echo htmlspecialchars($global_configs['social_instagram']); ?>" target="_blank" style="color:var(--text-main);"><i class="ri-instagram-line"></i></a><?php endif; ?>
                <?php if(!empty($global_configs['social_youtube'])): ?><a href="<?php echo htmlspecialchars($global_configs['social_youtube']); ?>" target="_blank" style="color:var(--text-main);"><i class="ri-youtube-fill"></i></a><?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Header -->
    <?php include 'includes/header_global.php'; ?>

    <?php if(($global_configs['tv_envivo_estado'] ?? 'inactivo') === 'activo' && !empty($global_configs['tv_envivo_url'])): ?>
    <div id="pip-player-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); z-index:99999; justify-content:center; align-items:center; padding:1rem; backdrop-filter:blur(5px);">
        <div style="background:#000; width:100%; max-width:800px; border-radius:8px; overflow:hidden; position:relative; box-shadow:0 20px 40px rgba(0,0,0,0.5);">
            <div style="display:flex; justify-content:space-between; align-items:center; background:#111827; padding:1rem; color:white;">
                <h3 style="margin:0; font-family:var(--font-sans); display:flex; align-items:center; gap:0.5rem;"><i class="ri-record-circle-line" style="color:red; animation: pulseRed 2s infinite;"></i> Transmisión en Vivo</h3>
                <i class="ri-close-line" onclick="closeTVModal()" style="font-size:1.5rem; cursor:pointer; transition:color 0.2s;" onmouseover="this.style.color='red'" onmouseout="this.style.color='white'"></i>
            </div>
            <div style="position:relative; padding-bottom:56.25%; height:0;">
                <?php 
                $tv_url = $global_configs['tv_envivo_url'];
                if (strpos($tv_url, 'youtube.com/watch?v=') !== false) {
                    $tv_url = str_replace('watch?v=', 'embed/', $tv_url);
                    $tv_url = explode('&', $tv_url)[0];
                } elseif (strpos($tv_url, 'youtu.be/') !== false) {
                    $tv_url = str_replace('youtu.be/', 'youtube.com/embed/', $tv_url);
                    $tv_url = explode('?', $tv_url)[0];
                }
                ?>
                <iframe src="<?php echo htmlspecialchars($tv_url); ?>" style="position:absolute; top:0; left:0; width:100%; height:100%; border:none;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        </div>
    </div>
    <script>
        function closeTVModal() {
            var modal = document.getElementById('pip-player-modal');
            modal.style.display = 'none';
            var iframe = modal.querySelector('iframe');
            if (iframe) {
                var src = iframe.src;
                iframe.src = src; // Recargar el src detiene la reproducción
            }
        }
    </script>
    <?php endif; ?>

    <!-- Banner -->
    <?php if(!empty($ads_dynamic['cabecera'])): ?>
        <div style="width: 100%; max-width: 970px; margin: 2rem auto; text-align: center;">
            <?php if($ads_dynamic['cabecera']['tipo'] === 'adsense'): ?>
                <?php echo render_safe_script($ads_dynamic['cabecera']['codigo_script']); ?>
            <?php else: ?>
                <a href="<?php echo htmlspecialchars($ads_dynamic['cabecera']['enlace_url'] ?? '#'); ?>" target="_blank">
                    <img src="<?php echo htmlspecialchars($ads_dynamic['cabecera']['imagen_url']); ?>" alt="Ad" style="max-width:100%; height:auto;">
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <main class="container" style="padding-top: 2rem;">
        <div class="page-content">
            <h1><?php echo htmlspecialchars($pagina['titulo']); ?></h1>
            <div class="cuerpo-pagina">
                <?php echo $pagina['contenido']; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'includes/footer_global.php'; ?>

    <?= \App\Services\AssetManager::js('js/premium-features.js') ?>
    
<?php include 'includes/floating_social.php'; ?>

<?php include_once __DIR__ . '/includes/modal_privacidad.php'; ?>

</body>
</html>
