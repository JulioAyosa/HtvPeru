<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <script>
    (function(){
        var t = localStorage.getItem('theme');
        if(t === 'dark') document.documentElement.setAttribute('data-theme','dark');
    })();
    </script>
    <style>html:not([data-theme]) body, html[data-theme="dark"] body { visibility: visible; }</style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($global_configs['site_title'] ?? 'HTVPERU - Una Mirada al Mundo'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($global_configs['site_slogan'] ?? ''); ?>">
    
    <meta property="og:title" content="<?php echo htmlspecialchars($global_configs['site_title'] ?? 'HTVPERU'); ?>" />
    <meta property="og:description" content="<?php echo htmlspecialchars($global_configs['seo_og_desc'] ?? $global_configs['site_slogan'] ?? ''); ?>" />
    <meta property="og:image" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . (!empty($global_configs['seo_og_image']) ? $global_configs['seo_og_image'] : (!empty($global_configs['logo_url']) ? $global_configs['logo_url'] : 'img/logo.webp')); ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" />
    <meta name="twitter:card" content="summary_large_image" />
    <link rel="icon" type="image/x-icon" href="<?php echo htmlspecialchars(!empty($global_configs['favicon_url']) ? $global_configs['favicon_url'] : 'img/logo.webp'); ?>">
    <link rel="manifest" href="<?= base_url('/') ?>manifest.json">
    <meta name="theme-color" content="<?php echo htmlspecialchars($global_configs['color_primario'] ?? '#2563eb'); ?>">
    
    <?php if(!empty($global_configs['script_header'])) echo render_safe_script($global_configs['script_header']); ?>

    <?php if(!empty($global_configs['google_analytics_id'])): ?>
    <?php $ga_id = htmlspecialchars($global_configs['google_analytics_id'], ENT_QUOTES, 'UTF-8'); ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $ga_id; ?>"></script>
    <script>window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', '<?php echo $ga_id; ?>');</script>
    <?php endif; ?>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" media="print" onload="this.media='all'" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet"></noscript>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" media="print" onload="this.media='all'">
    <?= \App\Services\AssetManager::css('css/style.css') ?>
    <style>
        :root {
            --primary-color: <?php echo htmlspecialchars($global_configs['color_primario'] ?? '#2563eb'); ?>;
            --primary-hover: <?php echo htmlspecialchars($global_configs['color_secundario'] ?? '#1d4ed8'); ?>;
            --font-sans: '<?php echo htmlspecialchars($global_configs['theme_font_family'] ?? 'Inter'); ?>', sans-serif;
        }
        
        <?php if(!empty($global_configs['theme_custom_css'])) echo sanitize_css($global_configs['theme_custom_css']); ?>

        .header { position: sticky; top: 0; z-index: 9999; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        #searchModal { display: none; position: fixed; inset: 0; background: rgba(17,24,39,0.95); z-index: 10000; justify-content: center; align-items: center; backdrop-filter: blur(5px); }
        .search-container { width: 100%; max-width: 800px; padding: 2rem; position: relative; }
        .search-box { display: flex; width: 100%; box-shadow: 0 10px 25px rgba(0,0,0,0.5); border-radius: 8px; overflow: hidden; }
        .search-box input { width: 100%; padding: 1.5rem; font-size: 1.5rem; border: none; outline: none; font-family: var(--font-sans); }
        .search-box button { padding: 1.5rem 2.5rem; background: var(--primary-color); color: white; border: none; cursor: pointer; font-size: 1.5rem; transition: background 0.3s; }
        .search-box button:hover { background: #1d4ed8; }
        .btn-close-search { position: absolute; top: -40px; right: 20px; color: white; font-size: 2.5rem; text-decoration: none; transition: color 0.3s; }
        .btn-close-search:hover { color: var(--primary-color); }
    </style>
</head>

<body>
    <?php if(isset($global_configs['modo_mantenimiento']) && $global_configs['modo_mantenimiento'] === 'activo' && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
    <div style="background: #ef4444; color: #ffffff; text-align: center; padding: 0.75rem; font-size: 0.9rem; font-weight: 800; font-family:var(--font-sans); text-transform:uppercase; animation: pulse 2s infinite; z-index:999999; position:relative;">
        <i class="ri-alert-fill"></i> MODO MANTENIMIENTO ACTIVO: El público no puede ver el sitio, solo tú como Administrador.
    </div>
    <?php endif; ?>

    <?php if(($global_configs['alert_top_estado'] ?? 'inactivo') === 'activo'): ?>
    <div id="alertTopBar" style="background: linear-gradient(90deg, var(--primary-color) 0%, #1e3a8a 50%, var(--primary-color) 100%); background-size: 200% 100%; animation: alertShimmer 6s ease-in-out infinite; color: #ffffff; text-align: center; padding: 0.6rem 2rem; font-size: 0.85rem; font-weight: 600; position: relative; z-index: 50; display: flex; align-items: center; justify-content: center; gap: 0.75rem;">
        <style>@keyframes alertShimmer { 0%{background-position:200% 0} 50%{background-position:0% 0} 100%{background-position:200% 0} }</style>
        <span style="display:inline-flex; align-items:center; gap: 0.5rem; width:10px; height:10px; border-radius:50%; background:#fbbf24; animation: pulseYellow 2s infinite; flex-shrink:0;"></span>
        <style>@keyframes pulseYellow { 0%,100%{box-shadow:0 0 0 0 rgba(251,191,36,0.6)} 50%{box-shadow:0 0 0 6px rgba(251,191,36,0)} }</style>
        <?php if(!empty($global_configs['alert_top_url'])): ?><a href="<?php echo htmlspecialchars($global_configs['alert_top_url']); ?>" style="color:#fff; text-decoration:none; border-bottom: 1px solid rgba(255,255,255,0.4); padding-bottom:1px; transition: border-color 0.2s;" onmouseover="this.style.borderColor='#fff'" onmouseout="this.style.borderColor='rgba(255,255,255,0.4)'"><?php endif; ?>
        <?php echo htmlspecialchars($global_configs['alert_top_texto'] ?? ''); ?>
        <?php if(!empty($global_configs['alert_top_url'])): ?> <i class="ri-arrow-right-up-line" style="font-size:0.9rem;"></i></a><?php endif; ?>
        <button onclick="this.parentElement.style.display='none'" style="position:absolute; right:1rem; background:rgba(255,255,255,0.15); border:none; color:white; width:24px; height:24px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:0.8rem; transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'" title="Cerrar"><i class="ri-close-line"></i></button>
    </div>
    <?php endif; ?>
    <!-- Topbar Institucional -->
    <div class="top-bar-social"
        style="background: var(--bg-main); font-size: 0.8rem; padding: 0.5rem 0; border-bottom: 1px solid var(--border-color); position: relative;">
        <div style="position:absolute; bottom:0; left:0; width:100%; height:2px; background: linear-gradient(90deg, var(--primary-color) 0%, #3b82f6 30%, transparent 100%); opacity: 0.5;"></div>
        <div class="container" style="display:flex; justify-content:space-between; align-items:center; color: var(--text-muted);">
            <div style="display:flex; align-items:center; gap:0.5rem; font-weight:600; letter-spacing:0.3px;">
                <i class="ri-calendar-2-line" style="color:var(--primary-color); font-size:0.95rem;"></i>
                <span><?php echo $fecha_espanol; ?></span>
            </div>
            <div style="display:flex; gap:0.25rem; font-size:1rem;">
                <style>
                    .topbar-social-icon { color: var(--text-muted); width:30px; height:30px; display:flex; align-items:center; justify-content:center; border-radius:50%; transition: all 0.2s ease; }
                    .topbar-social-icon:hover { color: white; transform: translateY(-2px); }
                    .topbar-social-icon.fb:hover { background: #1877f2; }
                    .topbar-social-icon.tw:hover { background: #000; }
                    .topbar-social-icon.ig:hover { background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); }
                    .topbar-social-icon.yt:hover { background: #ff0000; }
                </style>
                <?php if(!empty($global_configs['social_facebook'])): ?><a href="<?php echo htmlspecialchars($global_configs['social_facebook']); ?>" target="_blank" class="topbar-social-icon fb"><i class="ri-facebook-circle-fill"></i></a><?php endif; ?>
                <?php if(!empty($global_configs['social_twitter'])): ?><a href="<?php echo htmlspecialchars($global_configs['social_twitter']); ?>" target="_blank" class="topbar-social-icon tw"><i class="ri-twitter-x-line"></i></a><?php endif; ?>
                <?php if(!empty($global_configs['social_instagram'])): ?><a href="<?php echo htmlspecialchars($global_configs['social_instagram']); ?>" target="_blank" class="topbar-social-icon ig"><i class="ri-instagram-line"></i></a><?php endif; ?>
                <?php if(!empty($global_configs['social_youtube'])): ?><a href="<?php echo htmlspecialchars($global_configs['social_youtube']); ?>" target="_blank" class="topbar-social-icon yt"><i class="ri-youtube-fill"></i></a><?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Ticker de Último Minuto -->
    <?php if(($global_configs['ui_mostrar_urgente'] ?? 'activo') === 'activo'): ?>
    <div class="ticker-wrap">
        <div class="ticker-title">LO ÚLTIMO</div>
        <div class="ticker">
            <div class="ticker__inner">
                <?php if (!empty($ultimas)): ?>
                    <?php foreach ($ultimas as $u): ?>
                        <a href="article.php?slug=<?php echo urlencode($u['slug']); ?>" class="ticker__item"
                            style="margin-right: 2rem;">
                            <i class="ri-flashlight-fill"
                                style="color:#fbbf24; margin-right:8px; font-size: 1.1rem; vertical-align:middle;"></i>
                            <?php echo htmlspecialchars($u['titulo']); ?>
                            <span style="color: rgba(255,255,255,0.3); margin-left: 2rem; font-weight: 400;">///</span>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="ticker__item">Bienvenido a HTVPERU - Mantente informado.</div>
                <?php endif; ?>
            </div>
            <!-- Duplicated ticker for seamless loop -->
            <div class="ticker__inner" aria-hidden="true">
                <?php if (!empty($ultimas)): ?>
                    <?php foreach ($ultimas as $u): ?>
                        <a href="article.php?slug=<?php echo urlencode($u['slug']); ?>" class="ticker__item"
                            style="margin-right: 2rem;">
                            <i class="ri-flashlight-fill"
                                style="color:#fbbf24; margin-right:8px; font-size: 1.1rem; vertical-align:middle;"></i>
                            <?php echo htmlspecialchars($u['titulo']); ?>
                            <span style="color: rgba(255,255,255,0.3); margin-left: 2rem; font-weight: 400;">///</span>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="ticker__item">Bienvenido a HTVPERU - Mantente informado.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php include 'includes/header_global.php'; ?>

    <?php if ($is_search): ?>
        <!-- VISTA DE RESULTADOS DE BÚSQUEDA -->
        <main class="container" style="padding: 4rem 0; min-height: 60vh;">
            <div class="section-title"
                style="border-bottom: 2px solid var(--primary-color); display: inline-block; padding-bottom: 0.5rem; margin-bottom: 3rem;">
                <h2 style="margin: 0; text-transform: uppercase;">Mostrando resultados para: <span
                        style="color:var(--primary-color);">"<?php echo htmlspecialchars($search_query); ?>"</span></h2>
            </div>

            <?php if (count($resultados_busqueda) > 0): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                    <?php foreach ($resultados_busqueda as $r): ?>
                        <a href="article.php?slug=<?php echo urlencode($r['slug'] ?? ''); ?>" class="news-card"
                            style="height: 350px;">
                            <div class="card-img-wrap">
                                <?php echo renderMedia($r['imagen_url'], 'card-img', $r['video_poster_url'] ?? '', false); ?>
                            </div>
                            <div class="card-content">
                                <span class="card-category"><?php echo htmlspecialchars($r['categoria']); ?></span>
                                <h3 class="card-title" style="font-size:1.3rem;"><?php echo htmlspecialchars($r['titulo']); ?></h3>
                                <div style="color:var(--text-muted); font-size:0.85rem; margin-top:0.5rem;"><i
                                        class="ri-calendar-line"></i>
                                    <?php echo date('d/m/Y', strtotime($r['fecha_publicacion'])); ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 5rem 0; color: var(--text-muted);">
                    <i class="ri-search-eye-line"
                        style="font-size: 4rem; color: #cbd5e1; margin-bottom: 1rem; display:block;"></i>
                    <h3>No se encontraron noticias exactas para tu búsqueda.</h3>
                    <p>Intenta usar palabras más generales o revisa la ortografía.</p>
                    <a href="index.php" class="btn-primary"
                        style="display:inline-block; margin-top: 2rem; padding: 1rem 2rem; text-decoration:none; border-radius:4px;"><i
                            class="ri-arrow-left-line"></i> Volver a la Portada</a>
                </div>
            <?php endif; ?>
        </main>
    <?php else: // MODO PORTADA NORMAL ?>

                <main class="container" style="padding: 2rem 0; display: flex; flex-direction: column; gap: 3rem;">

            <!-- STORIES / SHORTS CAROUSEL -->
            <?php if(count($stories) > 0 && ($global_configs['ui_mostrar_stories'] ?? 'activo') === 'activo'): ?>
            <section style="margin-bottom: -1rem;">
                <div class="stories-container" style="display: flex; gap: 1rem; overflow-x: auto; padding-bottom: 1rem; scrollbar-width: none;">
                    <style>.stories-container::-webkit-scrollbar { display: none; }</style>
                    <?php foreach ($stories as $story): ?>
                    <a href="article.php?slug=<?php echo urlencode($story['slug']); ?>" style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem; text-decoration: none; flex-shrink: 0; width: 85px;">
                        <div style="width: 75px; height: 75px; border-radius: 50%; padding: 3px; background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);">
                            <div style="width: 100%; height: 100%; border-radius: 50%; overflow: hidden; border: 3px solid var(--bg-main);">
                                <?php echo renderMedia($story['imagen_url'], 'card-img', $story['video_poster_url'] ?? '', false); ?>
                            </div>
                        </div>
                        <span style="font-size: 0.65rem; color: var(--text-main); font-weight: 600; text-align: center; line-height: 1.2; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; width: 100%;"><?php echo htmlspecialchars($story['titulo']); ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Nueva Mega Hero Section Slider (Autoplay) -->
            <?php if (count($hero_slides) > 0 && ($global_configs['ui_mostrar_carrusel'] ?? 'activo') === 'activo'): ?>
                <style>
                    /* Optimizador de Aceleración de CPU/GPU del Carrusel */
                    .heroSwiper .swiper-wrapper {
                        will-change: transform;
                        transform: translateZ(0);
                    }

                    .heroSwiper .swiper-slide {
                        will-change: transform;
                        transform: translateZ(0);
                        backface-visibility: hidden;
                    }

                    .heroSwiper .news-card {
                        transform: translateZ(0) !important;
                        backface-visibility: hidden !important;
                    }

                    .heroSwiper .card-img {
                        will-change: transform;
                        transform: translateZ(0) !important;
                        backface-visibility: hidden !important;
                    }

                    /* FIX: Flechas de navegación personalizadas más estéticas */
                    .heroSwiper .swiper-button-next::after,
                    .heroSwiper .swiper-button-prev::after {
                        content: none !important; /* Ocultar el texto por defecto feo de swiper */
                    }
                    .heroSwiper .custom-nav-btn {
                        color: white !important;
                        background: rgba(0,0,0,0.7);
                        width: 45px !important;
                        height: 70px !important;
                        margin-top: -35px !important;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        backdrop-filter: blur(4px);
                        transition: background 0.3s, transform 0.2s;
                    }
                    .heroSwiper .custom-nav-btn:hover {
                        background: var(--primary-color);
                        transform: scale(1.05);
                    }
                    .heroSwiper .swiper-button-next.custom-nav-btn {
                        right: 0;
                        border-radius: 8px 0 0 8px;
                    }
                    .heroSwiper .swiper-button-prev.custom-nav-btn {
                        left: 0;
                        border-radius: 0 8px 8px 0;
                    }

                    /* FIX MOBILE PARA MEGA HERO GRID */
                    @media (max-width: 768px) {
                        .heroSwiper { height: auto !important; min-height: 350px !important; margin-bottom: 1rem !important; }
                        .mega-hero-grid { grid-template-columns: 1fr !important; height: auto !important; display: flex !important; flex-direction: column !important; gap: 0.5rem !important; }
                        .mega-hero-grid > a.news-card:first-child { height: 250px !important; }
                        .mega-hero-grid > div { display: none !important; } /* Ocultar stack secundario en movil */
                        .heroSwiper .custom-nav-btn { width: 35px !important; height: 50px !important; }
                        .heroSwiper .custom-nav-btn i { font-size: 1.8rem !important; }
                    }
                </style>
                <div class="swiper heroSwiper" style="width: 100%; height: 530px; margin-bottom: 2rem; position: relative;">
                    <div class="swiper-wrapper" style="padding: 10px 5px; box-sizing: border-box;">
                        <?php foreach ($hero_slides as $slide): ?>
                            <?php if (count($slide) > 0): ?>
                                <div class="swiper-slide">
                                    <section class="mega-hero-grid"
                                        style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; height: 500px;">

                                        <!-- Noticia Principal -->
                                        <a href="article.php?slug=<?php echo urlencode($slide[0]['slug'] ?? ''); ?>" class="news-card"
                                            style="height: 100%;">
                                            <div class="card-img-wrap">
                                                <?php echo renderMedia($slide[0]['imagen_url'], 'card-img', $slide[0]['video_poster_url'] ?? '', false); ?>
                                            </div>
                                            <div class="card-content">
                                                <span
                                                    class="card-category"><?php echo htmlspecialchars($slide[0]['categoria']); ?></span>
                                                <h2 class="card-title" style="font-size: 2rem;">
                                                    <?php echo htmlspecialchars($slide[0]['titulo']); ?></h2>
                                            </div>
                                        </a>

                                        <!-- Stack de Sub-noticias -->
                                        <?php if (count($slide) > 1): ?>
                                            <div style="display: flex; flex-direction: column; gap: 1rem; height: 100%;">
                                                <?php for ($i = 1; $i < count($slide); $i++): ?>
                                                    <a href="article.php?slug=<?php echo urlencode($slide[$i]['slug'] ?? ''); ?>"
                                                        class="news-card" style="height: 100%; flex: 1;">
                                                        <div class="card-img-wrap">
                                                            <?php echo renderMedia($slide[$i]['imagen_url'], 'card-img', $slide[$i]['video_poster_url'] ?? '', false); ?>
                                                        </div>
                                                        <div class="card-content">
                                                            <span
                                                                class="card-category"><?php echo htmlspecialchars($slide[$i]['categoria']); ?></span>
                                                            <h3 class="card-title" style="font-size: 1.25rem;">
                                                                <?php echo htmlspecialchars($slide[$i]['titulo']); ?></h3>
                                                        </div>
                                                    </a>
                                                <?php endfor; ?>
                                            </div>
                                        <?php endif; ?>

                                    </section>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <!-- Controles de Navegación Personalizados y Estéticos -->
                    <div class="swiper-button-next custom-nav-btn">
                        <i class="ri-arrow-right-s-line" style="font-size: 2.5rem;"></i>
                    </div>
                    <div class="swiper-button-prev custom-nav-btn">
                        <i class="ri-arrow-left-s-line" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
            <?php endif; ?>

            <!-- MEGA BANNER SUPERIOR DE MONETIZACIÓN -->
        <?php if(!empty($ads_dynamic['cabecera'])): ?>
            <div style="width: 100%; max-width: 970px; margin: 0 auto 3rem auto; text-align: center;">
                <?php if($ads_dynamic['cabecera']['tipo'] === 'adsense'): ?>
                    <?php echo render_safe_script($ads_dynamic['cabecera']['codigo_script']); ?>
                <?php else: ?>
                    <a href="<?php echo htmlspecialchars($ads_dynamic['cabecera']['enlace_url'] ?? '#'); ?>" target="_blank">
                        <img src="<?php echo htmlspecialchars($ads_dynamic['cabecera']['imagen_url']); ?>" alt="Ad" style="max-width:100%; height:auto;">
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Sección Principal con Sidebar -->
        <div class="layout-wrapper" style="display: flex; gap: 2rem; align-items: flex-start;">
            
            <!-- Área de Noticias Recientes (Diseño Denso y Periodístico) -->
            <!-- Área de Noticias Regionales (Filtro por Pestañas Fase 16) -->
            <div class="content-area" style="flex: 1 1 70%;">
                
                <!-- HEADER DEL BLOQUE: "REGIONAL" + PESTAÑAS -->
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #e5e7eb; margin-bottom: 1.5rem; padding-bottom: 0;">
                    
                    <div style="background: #003db3; padding: 0.5rem 1.5rem; flex-shrink: 0;">
                        <h3 style="margin: 0; color: white; text-transform: uppercase; font-size: 1.25rem; font-weight: 800; letter-spacing: 1px; white-space: nowrap;">REGIONAL</h3>
                    </div>
                    
                    <div style="display: flex; align-items: center; flex: 1; min-width: 250px; justify-content: flex-end; gap: 5px;" class="regional-tabs-wrapper">
                        <button onclick="document.getElementById('regional-tabs').scrollBy({left: -150, behavior: 'smooth'})" style="background:none; border:none; color:var(--primary-color); cursor:pointer; font-size:1.5rem; flex-shrink:0; padding:0; display:flex; align-items:center;">
                            <i class="ri-arrow-left-s-line"></i>
                        </button>
                        
                        <div style="display: flex; gap: 1rem; overflow-x: auto; padding-bottom: 5px; scrollbar-width: none; flex: 1; justify-content: flex-start; scroll-behavior: smooth;" id="regional-tabs">
                            <style>
                                #regional-tabs::-webkit-scrollbar { display: none; }
                                .reg-tab { font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: #6b7280; cursor: pointer; padding: 0.25rem 0.25rem; white-space: nowrap; transition: color 0.2s; border-bottom: 3px solid transparent; margin-bottom: -2px; }
                                .reg-tab:hover { color: #003db3; }
                                .reg-tab.active { color: #003db3; }
                            </style>
                            <span class="reg-tab active" data-dist="Todos" onclick="filterDistrict('Todos', this)">VER TODO</span>
                            <span class="reg-tab" data-dist="Ayabaca" onclick="filterDistrict('Ayabaca', this)">AYABACA</span>
                            <span class="reg-tab" data-dist="Morropón" onclick="filterDistrict('Morropón', this)">MORROPÓN</span>
                            <span class="reg-tab" data-dist="Paita" onclick="filterDistrict('Paita', this)">PAITA</span>
                            <span class="reg-tab" data-dist="Piura" onclick="filterDistrict('Piura', this)">PIURA</span>
                            <span class="reg-tab" data-dist="Sechura" onclick="filterDistrict('Sechura', this)">SECHURA</span>
                            <span class="reg-tab" data-dist="Sullana" onclick="filterDistrict('Sullana', this)">SULLANA</span>
                            <span class="reg-tab" data-dist="Huancabamba" onclick="filterDistrict('Huancabamba', this)">HUANCABAMBA</span>
                            <span class="reg-tab" data-dist="Chulucanas" onclick="filterDistrict('Chulucanas', this)">CHULUCANAS</span>
                        </div>

                        <button onclick="document.getElementById('regional-tabs').scrollBy({left: 150, behavior: 'smooth'})" style="background:none; border:none; color:var(--primary-color); cursor:pointer; font-size:1.5rem; flex-shrink:0; padding:0; display:flex; align-items:center;">
                            <i class="ri-arrow-right-s-line"></i>
                        </button>
                    </div>
                </div>
                
                <!-- GRILLA 2x2 DE TARJETAS REGIONALES -->
                <div id="regional-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                    <?php if(!empty($regionales_todas)): ?>
                        <?php foreach($regionales_todas as $i => $reg): ?>
                            <!-- Cada Tarjeta individual (data-distrito para filtrar con JS) -->
                            <a href="article.php?slug=<?php echo urlencode($reg['slug'] ?? ''); ?>" 
                               class="regional-card" 
                               data-distrito="<?php echo htmlspecialchars($reg['distrito'] ?? 'Varios'); ?>" 
                               style="display: block; position: relative; height: 280px; text-decoration: none; overflow: hidden; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); <?php echo ($i >= 4) ? 'display: none;' : ''; ?>">
                                
                                <!-- Background Image (Llenando toda el area) -->
                                <?php echo renderMedia($reg['imagen_url'], 'card-img', $reg['video_poster_url'] ?? '', false, 'width: 100%; height: 100%; object-fit: cover; position: absolute; top:0; left:0; z-index: 1; transition: transform 0.5s;'); ?>
                                
                                <!-- Gradient Oscuro Inferior -->
                                <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 60%; background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0) 100%); z-index: 2;"></div>
                                
                                <!-- Etiqueta Azul Arriba a la Izquierda (Distrito) -->
                                <div style="position: absolute; top: 0; left: 0; background: #003db3; color: white; padding: 4px 10px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; z-index: 3;">
                                    <?php echo !empty($reg['distrito']) ? htmlspecialchars($reg['distrito']) : 'REGIONAL'; ?>
                                </div>
                                
                                <!-- Textos Inferiores Blancos -->
                                <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 1.5rem 1rem 1rem 1rem; z-index: 3;">
                                    <h3 style="color: white; font-family: var(--font-sans); font-size: 1.1rem; margin: 0 0 0.5rem 0; line-height: 1.3; font-weight: 600; text-shadow: 0 1px 3px rgba(0,0,0,0.8);"><?php echo htmlspecialchars($reg['titulo']); ?></h3>
                                    <div style="color: #d1d5db; font-size: 0.75rem; font-weight: 800; display:flex; align-items:center; gap: 5px;">
                                        <i class="ri-time-line"></i> <?php echo date('d/m/Y', strtotime($reg['fecha_publicacion'])); ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                        
                        <div id="no-regional-msg" style="display:none; grid-column: 1 / -1; padding: 3rem; text-align:center; background:#f8fafc; border:1px dashed #cbd5e1; color:#94a3b8; font-family:var(--font-sans);">
                            <i class="ri-map-pin-line" style="font-size: 2rem;"></i><br>No hay noticias recientes para este distrito.
                        </div>
                    <?php else: ?>
                        <div style="grid-column: 1 / -1; padding: 3rem; text-align:center; background:#f8fafc; border:1px dashed #cbd5e1; color:#94a3b8; font-family:var(--font-sans);">
                            No hay noticias regionales publicadas.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- SCRIPT DE FILTRADO SÚPER RÁPIDO -->
                <script>
                    function filterDistrict(distritoName, tabElement) {
                        // 1. Manejar las clases visuales de los Pestañas (Tabs)
                        document.querySelectorAll('.reg-tab').forEach(t => {
                            t.classList.remove('active');
                            t.style.color = '#6b7280';
                        });
                        tabElement.classList.add('active');
                        tabElement.style.color = '#003db3';
                        
                        // 2. Filtrar las Tarjetas Instantáneamente
                        const cards = document.querySelectorAll('.regional-card');
                        let visibleCount = 0;
                        const maxDisplay = 4; // Solo mostrar las 4 más recientes del distrito
                        
                        cards.forEach(card => {
                            const cardDist = card.getAttribute('data-distrito');
                            
                            // Logica de coincidencia
                            const isMatch = (distritoName === 'Todos') || (cardDist.toLowerCase() === distritoName.toLowerCase());
                            
                            if(isMatch && visibleCount < maxDisplay) {
                                card.style.display = 'block';
                                // Efecto de parpadeo suave
                                card.style.opacity = '0';
                                setTimeout(() => { card.style.opacity = '1'; }, 50);
                                visibleCount++;
                            } else {
                                card.style.display = 'none';
                            }
                        });
                        
                        // 3. Mostrar mensaje si no hay ninguna
                        const msgObj = document.getElementById('no-regional-msg');
                        if (msgObj) {
                            msgObj.style.display = (visibleCount === 0) ? 'block' : 'none';
                        }
                    }
                </script>

                <!-- BOTÓN MÁS NOTICIAS DE ACTUALIDAD GENERAL ABAJO DEL BLOQUE REGIONAL -->
                <div style="margin-top: 4rem;">
                    <div class="section-title" style="border-bottom: 3px solid #111827; display: flex; align-items: center; padding-bottom: 0.75rem; margin-bottom: 2rem;">
                        <div style="width: 14px; height: 14px; background: var(--danger); border-radius: 50%; margin-right: 12px; animation: pulseRed 2s infinite;"></div>
                        <h3 style="margin: 0; text-transform: uppercase; font-size: 1.65rem; letter-spacing: 1px; font-weight: 800; font-family: var(--font-sans); color: var(--text-main);">MÁS NOTICIAS DEL DÍA</h3>
                        <style>@keyframes pulseRed { 0% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(220, 38, 38, 0); } 100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); } }</style>
                    </div>
                    
                    <style>
                        /* Estética Compacta para Móvil en Noticias Generales */
                        @media (max-width: 768px) {
                            #general-news-grid { display: flex !important; flex-direction: column !important; gap: 1rem !important; }
                            .gen-news-card { flex-direction: row !important; height: 130px !important; }
                            .gen-news-img-wrap { width: 130px !important; height: 100% !important; flex-shrink: 0 !important; }
                            .gen-news-content { padding: 0.75rem !important; justify-content: center !important; }
                            .gen-news-title { font-size: 0.95rem !important; line-height: 1.2 !important; margin-bottom: 0.5rem !important; }
                            .gen-news-cat { font-size: 0.55rem !important; padding: 2px 6px !important; top: 6px !important; left: 6px !important; }
                            .gen-news-footer { padding-top: 0.5rem !important; margin-top: 0 !important; }
                        }
                    </style>
                    
                    <div id="general-news-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem;">
                        <?php if(count($recientes) > 0): ?>
                            <?php for($i=0; $i<count($recientes); $i++): $r = $recientes[$i]; ?>
                                <a href="article.php?slug=<?php echo urlencode($r['slug'] ?? ''); ?>" class="gen-news-card" style="display: flex; flex-direction: column; text-decoration: none; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; background: #ffffff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);" onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 12px 20px -5px rgba(0,0,0,0.15)'; this.querySelector('.img-scale').style.transform='scale(1.05)'; this.querySelector('.read-more').style.color='var(--primary-color)'; this.querySelector('.read-more').style.transform='translateX(5px)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.05)'; this.querySelector('.img-scale').style.transform='scale(1)'; this.querySelector('.read-more').style.color='#64748b'; this.querySelector('.read-more').style.transform='translateX(0)';">
                                    
                                    <!-- Contenedor de Imagen -->
                                    <div class="gen-news-img-wrap" style="width: 100%; height: 200px; position: relative; overflow: hidden; background: #f8fafc;">
                                        <div class="img-scale" style="width: 100%; height: 100%; transition: transform 0.6s ease;">
                                            <?php echo renderMedia($r['imagen_url'], 'card-img', $r['video_poster_url'] ?? '', false, 'width: 100%; height: 100%; object-fit: cover;' ); ?>
                                        </div>
                                        <div class="gen-news-cat" style="position: absolute; top: 12px; left: 12px; background: var(--primary-color); color: white; padding: 4px 12px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; border-radius: 4px; z-index: 2; letter-spacing: 0.5px;">
                                            <?php echo htmlspecialchars($r['categoria']); ?>
                                        </div>
                                    </div>
                                    <div class="gen-news-content" style="padding: 1.5rem; display: flex; flex-direction: column; flex-grow: 1;">
                                        <h3 class="gen-news-title" style="margin: 0 0 1rem 0; font-size: 1.2rem; font-family: var(--font-sans); color: var(--text-main); line-height: 1.4; font-weight: 700;"><?php echo htmlspecialchars($r['titulo']); ?></h3>
                                        <div class="gen-news-footer" style="margin-top: auto; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 1rem;">
                                            <span style="font-size: 0.8rem; color: #94a3b8; font-weight: 600; display:flex; align-items:center; gap:5px;"><i class="ri-calendar-event-line"></i> <?php echo date('d/m/Y', strtotime($r['fecha_publicacion'])); ?></span>
                                            <span class="read-more" style="font-size: 0.85rem; color: #64748b; font-weight: 800; text-transform: uppercase; display:flex; align-items:center; gap:4px; transition: all 0.3s ease;">Leer <i class="ri-arrow-right-line"></i></span>
                                        </div>
                                    </div>
                                </a>
                            <?php endfor; ?>
                        <?php endif; ?>
                    </div>
                </div> <!-- Cierra margin-top -->
            </div> <!-- Cierra content-area -->

            <!-- CONTENEDOR SIDEBAR (30%) -->
            <aside class="sidebar-area" style="flex: 1 1 30%; position: sticky; top: 100px;">
                
                <!-- BLOQUE PUBLICITARIO SIDEBAR PRINCIPAL -->
                <?php if(!empty($ads_dynamic['sidebar_top'])): ?>
                    <div style="margin-bottom: 2rem; text-align: center;">
                        <?php if($ads_dynamic['sidebar_top']['tipo'] === 'adsense'): ?>
                            <?php echo render_safe_script($ads_dynamic['sidebar_top']['codigo_script']); ?>
                        <?php else: ?>
                            <a href="<?php echo htmlspecialchars($ads_dynamic['sidebar_top']['enlace_url'] ?? '#'); ?>" target="_blank">
                                <img src="<?php echo htmlspecialchars($ads_dynamic['sidebar_top']['imagen_url']); ?>" alt="Ad" style="max-width:100%; height:auto;">
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Widget Top Titulares -->
                <div class="sidebar-widget" style="margin-bottom: 2rem;">
                    <div style="border-bottom: 2px solid #111827; padding-bottom: 0.25rem; margin-bottom: 1.5rem;">
                        <h3 style="margin: 0; text-transform: uppercase; font-size: 1rem; letter-spacing: 1px;"><i class="ri-flashlight-fill" style="color:var(--danger);"></i> LO ÚLTIMO</h3>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:0;">
                        <?php foreach (array_slice($todas_noticias, 0, 5) as $ul): ?>
                            <a href="article.php?slug=<?php echo urlencode($ul['slug'] ?? ''); ?>" style="display:flex; gap:1rem; border-bottom:1px solid #e2e8f0; padding: 1rem 0; text-decoration: none;">
                                <div style="width: 70px; height: 50px; flex-shrink:0; border-radius:3px; overflow:hidden;">
                                    <?php echo renderMedia($ul['imagen_url'], 'card-img', $ul['video_poster_url'] ?? '', false, 'width: 100%; height: 100%; object-fit: cover;'); ?>
                                </div>
                                <div>
                                    <h4 style="margin:0; font-size:0.85rem; line-height:1.3; font-family:var(--font-sans); color:var(--text-main); font-weight: 600;"><?php echo htmlspecialchars($ul['titulo']); ?></h4>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Widget Lo más leído -->
                <div class="sidebar-widget" style="margin-bottom: 2rem;">
                    <div style="border-bottom: 2px solid #111827; padding-bottom: 0.25rem; margin-bottom: 1.5rem;">
                        <h3 style="margin: 0; text-transform: uppercase; font-size: 1rem; letter-spacing: 1px;">EN TENDENCIA</h3>
                    </div>
                    <ol style="padding: 0; list-style: none; margin: 0;">
                        <?php foreach ($mas_leido as $i => $ml): ?>
                            <li style="margin-bottom: 0; border-bottom: 1px solid #e2e8f0; position: relative;">
                                <a href="article.php?slug=<?php echo urlencode($ml['slug'] ?? ''); ?>" style="display: flex; gap: 1rem; align-items: flex-start; padding: 1rem 0; color: var(--text-main); text-decoration: none;">
                                    <span style="font-size: 1.5rem; font-weight: 800; color: #cbd5e1; font-family: var(--font-serif); line-height: 1;"><?php echo $i+1; ?></span>
                                    <h4 style="margin: 0; font-size: 0.95rem; line-height: 1.3; font-weight: 600; font-family: var(--font-sans);"><?php echo htmlspecialchars($ml['titulo']); ?></h4>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>

                                <!-- Widget Encuesta Activa -->
                <?php
                $encuesta_activa = $pdo->query("SELECT * FROM encuestas WHERE estado = 'activo' AND (fecha_limite IS NULL OR fecha_limite >= NOW()) ORDER BY id DESC LIMIT 1")->fetch();
                if ($encuesta_activa): 
                    $stmt_ops = $pdo->prepare("SELECT * FROM encuestas_opciones WHERE encuesta_id = ?");
                    $stmt_ops->execute([$encuesta_activa['id']]);
                    $opciones = $stmt_ops->fetchAll();
                    
                    $total_v = 0;
                    foreach($opciones as $op) { $total_v += $op['votos']; }
                    
                    $voted = isset($_COOKIE['voted_' . $encuesta_activa['id']]);
                ?>
                <div class="sidebar-widget" style="margin-bottom: 2rem; background: var(--bg-card); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
                    <div style="border-bottom: 2px solid var(--primary-color); padding-bottom: 0.25rem; margin-bottom: 1rem;">
                        <h3 style="margin: 0; text-transform: uppercase; font-size: 1rem; letter-spacing: 1px; color:var(--primary-color);"><i class="ri-bar-chart-box-line"></i> LA ENCUESTA</h3>
                    </div>
                    
                    <h4 style="margin:0 0 1rem 0; font-size:1.1rem; color:var(--text-main); line-height:1.4;"><?php echo htmlspecialchars($encuesta_activa['pregunta']); ?></h4>
                    
                    <div id="poll-container-<?php echo $encuesta_activa['id']; ?>">
                        <?php if ($voted): ?>
                            <!-- Ya votó, mostrar resultados -->
                            <?php foreach($opciones as $op): 
                                $pct = $total_v > 0 ? round(($op['votos'] / $total_v) * 100) : 0;
                            ?>
                            <div style="margin-bottom:0.75rem;">
                                <div style="display:flex; justify-content:space-between; font-size:0.85rem; margin-bottom:0.25rem;">
                                    <span style="color:var(--text-main);"><?php echo htmlspecialchars($op['opcion_texto']); ?></span>
                                    <strong style="color:var(--text-main);"><?php echo $pct; ?>%</strong>
                                </div>
                                <div style="width:100%; height:8px; background:var(--bg-main); border-radius:4px; overflow:hidden; border: 1px solid var(--border-color);">
                                    <div style="width:<?php echo $pct; ?>%; height:100%; background:var(--primary-color);"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <div style="font-size:0.8rem; color:var(--text-muted); margin-top:1rem; text-align:center;">Total votos: <?php echo $total_v; ?></div>
                        <?php else: ?>
                            <!-- Formulario para votar -->
                            <form id="form-poll" onsubmit="submitPoll(event, <?php echo $encuesta_activa['id']; ?>)">
                                <?php foreach($opciones as $op): ?>
                                <label style="display:flex; align-items:flex-start; gap:0.5rem; margin-bottom:0.75rem; cursor:pointer;">
                                    <input type="radio" name="opcion_id" value="<?php echo $op['id']; ?>" style="margin-top:0.25rem;" required>
                                    <span style="font-size:0.95rem; color:var(--text-main);"><?php echo htmlspecialchars($op['opcion_texto']); ?></span>
                                </label>
                                <?php endforeach; ?>
                                <button type="submit" style="width:100%; padding:0.75rem; margin-top:0.5rem; background:var(--primary-color); color:white; border:none; border-radius:4px; font-weight:bold; cursor:pointer;">VOTAR AHORA</button>
                            </form>
                            <div id="poll-error" style="color:var(--danger); font-size:0.85rem; margin-top:0.5rem; display:none;"></div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- JS Script for Poll (will reload page simply on success or handle with JS) -->
                <?php if (!$voted): ?>
                <script>
                async function submitPoll(e, id) {
                    e.preventDefault();
                    const form = e.target;
                    const btn = form.querySelector('button');
                    const err = document.getElementById('poll-error');
                    const fd = new FormData(form);
                    fd.append('encuesta_id', id);
                    
                    btn.disabled = true;
                    btn.textContent = 'Enviando...';
                    err.style.display = 'none';

                    try {
                        const res = await fetch('encuestas_api.php', { method: 'POST', body: fd });
                        const data = await res.json();
                        if (data.error) {
                            err.textContent = data.error;
                            err.style.display = 'block';
                            btn.disabled = false;
                            btn.textContent = 'VOTAR AHORA';
                        } else {
                            // Exito, recargar para ver resultados!
                            window.location.reload();
                        }
                    } catch(e) {
                         err.textContent = 'Error de red. Intenta más tarde.';
                         err.style.display = 'block';
                         btn.disabled = false;
                         btn.textContent = 'VOTAR AHORA';
                    }
                }
                </script>
                <?php endif; ?>
                <?php endif; ?>

                <!-- BLOQUE PUBLICITARIO SIDEBAR SECUNDARIO -->
                <?php if(!empty($ads_dynamic['sidebar_bottom'])): ?>
                    <div style="margin-bottom: 2rem; text-align: center;">
                        <?php if($ads_dynamic['sidebar_bottom']['tipo'] === 'adsense'): ?>
                            <?php echo render_safe_script($ads_dynamic['sidebar_bottom']['codigo_script']); ?>
                        <?php else: ?>
                            <a href="<?php echo htmlspecialchars($ads_dynamic['sidebar_bottom']['enlace_url'] ?? '#'); ?>" target="_blank">
                                <img src="<?php echo htmlspecialchars($ads_dynamic['sidebar_bottom']['imagen_url']); ?>" alt="Ad" style="max-width:100%; height:auto;">
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            
                <?php include 'includes/sidebar_social.php'; ?>
            </aside>
        </div>
    </main>

    <!-- BLOQUES EXTENDIDOS: PARITY HALCON TV -->

        <!-- SECCIÓN POLICIALES -->
        <?php if (count($policiales) > 0 && ($global_configs['ui_mostrar_policial'] ?? 'activo') === 'activo'): ?>
            <section style="background-color: #111827; padding: 4rem 0; margin-bottom: 3rem; border-top: 5px solid #dc2626;">
                <div class="container">
                    <div class="section-title" style="display: inline-block; padding-bottom: 0.5rem; margin-bottom: 2rem;">
                        <h3
                            style="margin: 0; color: white; text-transform: uppercase; font-size: 1.5rem; letter-spacing: -0.5px;">
                            <i class="ri-alarm-warning-fill" style="color: #dc2626;"></i> <?php echo htmlspecialchars($cat_urgente); ?></h3>
                        <div style="width: 50px; height: 4px; background-color: #dc2626; border-radius: 2px; margin-top: 10px;">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                        <?php foreach ($policiales as $p): ?>
                            <a href="article.php?slug=<?php echo urlencode($p['slug'] ?? ''); ?>" class="news-card"
                                style="height: 300px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.5);">
                                <div class="card-img-wrap">
                                    <?php echo renderMedia($p['imagen_url'], 'card-img', $p['video_poster_url'] ?? '', false); ?>
                                </div>
                                <!-- Fondo semi-transparente dramático rojo para Policiales -->
                                <div class="card-content" style="background: rgba(153, 27, 27, 0.7);">
                                    <span class="card-category" style="background:#b91c1c; border-color:#ef4444;">Último
                                        Minuto</span>
                                    <h3 class="card-title" style="font-size: 1.1rem; text-shadow: 0 2px 4px rgba(0,0,0,1);">
                                        <?php echo htmlspecialchars($p['titulo']); ?></h3>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- SECCIÓN POLÍTICA (CARRUSEL IN-SCROLL) -->
        <?php if (count($politica) > 0): ?>
            <section class="container" style="margin-bottom: 4rem;">
                <div class="section-title"
                    style="border-bottom: 2px solid var(--primary-color); display: inline-block; padding-bottom: 0.5rem; margin-bottom: 2rem;">
                    <h3 style="margin: 0; text-transform: uppercase; font-size: 1.5rem;"><i class="ri-government-fill"></i>
                        <?php echo htmlspecialchars($cat_carrusel); ?></h3>
                </div>

                <div class="swiper polSwiper" style="width: 100%; height: 350px; padding: 10px 5px;">
                    <!-- Render Accelerator Styles para Segundo Carrusel -->
                    <style>
                        .polSwiper .swiper-slide {
                            will-change: transform;
                            transform: translateZ(0);
                            backface-visibility: hidden;
                        }
                    </style>

                    <div class="swiper-wrapper">
                        <?php foreach ($politica as $pol): ?>
                            <div class="swiper-slide">
                                <a href="article.php?slug=<?php echo urlencode($pol['slug'] ?? ''); ?>" class="news-card"
                                    style="height: 100%; border-radius: var(--radius-md);">
                                    <div class="card-img-wrap">
                                        <?php echo renderMedia($pol['imagen_url'], 'card-img', $pol['video_poster_url'] ?? '', false); ?>
                                    </div>
                                    <div class="card-content">
                                        <span class="card-category" style="background:var(--primary-color);">Gobierno</span>
                                        <h3 class="card-title" style="font-size: 1.3rem;">
                                            <?php echo htmlspecialchars($pol['titulo']); ?></h3>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </section>
        <?php endif; ?>

        <!-- SECCIÓN ENTRETENIMIENTO / SOCIALES -->
        <?php if (count($entretenimiento) > 0): ?>
            <section class="container" style="margin-bottom: 4rem;">
                <div class="section-title"
                    style="border-bottom: 2px solid #ec4899; display: inline-block; padding-bottom: 0.5rem; margin-bottom: 2rem;">
                    <h3 style="margin: 0; text-transform: uppercase; font-size: 1.5rem;"><i class="ri-vip-crown-fill"
                            style="color: #ec4899;"></i> Sociales y Entretenimiento</h3>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                    <?php foreach ($entretenimiento as $ent): ?>
                        <a href="article.php?slug=<?php echo urlencode($ent['slug'] ?? ''); ?>" class="news-card"
                            style="height: 250px;">
                            <div class="card-img-wrap">
                                <?php echo renderMedia($ent['imagen_url'], 'card-img', $ent['video_poster_url'] ?? '', false); ?>
                            </div>
                            <!-- Espectáculos en tonos Rosado -->
                            <div class="card-content" style="background: rgba(236, 72, 153, 0.7);">
                                <span class="card-category"
                                    style="background:#ec4899; border-color:#f472b6;"><?php echo htmlspecialchars($ent['categoria']); ?></span>
                                <h3 class="card-title" style="font-size: 1.1rem;"><?php echo htmlspecialchars($ent['titulo']); ?>
                                </h3>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- BOLETÍN DE NOTICIAS (Fase Premium Engagement) -->
        <section class="container" style="margin-bottom: 4rem;">
            <div style="background: linear-gradient(135deg, var(--primary-color) 0%, #1e3a8a 100%); border-radius: var(--radius-lg); padding: 3rem 2rem; color: white; display: flex; flex-direction: column; align-items: center; text-align: center; box-shadow: var(--shadow-lg);">
                <i class="ri-mail-send-fill" style="font-size: 3rem; margin-bottom: 1rem; color: #93c5fd;"></i>
                <h2 style="font-size: 2rem; font-family: var(--font-serif); margin: 0 0 1rem 0; color: white !important;">Recibe las noticias más impactantes en tu bandeja</h2>
                <p style="font-size: 1.1rem; color: #e0f2fe; max-width: 600px; margin: 0 auto 2rem auto; font-family: var(--font-sans);">Únete a nuestros +10,000 suscriptores y mantente siempre informado. Sin spam, garantizado.</p>
                <form id="newsletter-form" style="display: flex; gap: 0.5rem; width: 100%; max-width: 600px; position: relative;">
                    <input type="email" id="newsletter-email" placeholder="Tu correo electrónico..." required style="flex: 1; padding: 1rem 1.5rem; font-size: 1.1rem; border: none; border-radius: 50px; outline: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); background-color: #ffffff; color: #111827;">
                    <button type="submit" style="background: var(--danger); color: white; border: none; padding: 1rem 2rem; font-size: 1.1rem; font-weight: 800; border-radius: 50px; cursor: pointer; transition: transform 0.2s, background 0.3s; box-shadow: 0 4px 6px rgba(0,0,0,0.2);">Suscribirme</button>
                    <!-- Spinner and Message -->
                    <div id="newsletter-msg" style="position: absolute; top: calc(100% + 10px); left: 0; width: 100%; text-align: center; font-size: 0.9rem; font-weight: 800;"></div>
                </form>
                <script>
                    document.getElementById('newsletter-form').addEventListener('submit', function(e) {
                        e.preventDefault();
                        const email = document.getElementById('newsletter-email').value;
                        const msgDiv = document.getElementById('newsletter-msg');
                        const btn = this.querySelector('button');
                        btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Procesando...';
                        btn.disabled = true;

                        const formData = new FormData();
                        formData.append('email', email);

                        fetch('suscriptores_api.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            btn.innerHTML = 'Suscribirme';
                            btn.disabled = false;
                            msgDiv.innerText = data.message;
                            if(data.success) {
                                msgDiv.style.color = '#86efac';
                                document.getElementById('newsletter-email').value = '';
                            } else {
                                msgDiv.style.color = '#fca5a5';
                            }
                            setTimeout(() => { msgDiv.innerText = ''; }, 5000);
                        })
                        .catch(err => {
                            btn.innerHTML = 'Suscribirme';
                            btn.disabled = false;
                            msgDiv.innerText = 'Error de red. Intenta de nuevo.';
                        });
                    });
                </script>
            </div>
        </section>

            <!-- FLOATING PiP RADIO / TV LIVE PLAYER -->
    <div id="pip-player" style="position: fixed; bottom: 85px; right: 25px; width: 320px; background: var(--bg-card); border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); border: 1px solid var(--border-color); z-index: 999; overflow: hidden; transform: translateY(0); transition: transform 0.3s; display: none;">
        <div style="background: var(--danger); padding: 8px 15px; display: flex; justify-content: space-between; align-items: center; color: white; font-size: 0.8rem; font-weight: 800; cursor: move;">
            <span style="display:flex; align-items:center; gap:5px;"><i class="ri-broadcast-fill" style="animation: pulse 2s infinite;"></i> HTVPERU EN VIVO</span>
            <button onclick="document.getElementById('pip-player').style.display='none'" style="background: none; border: none; color: white; cursor: pointer;"><i class="ri-close-line" style="font-size:1.2rem;"></i></button>
        </div>
        <div style="padding: 15px; display: flex; align-items: center; gap: 15px;">
            <div style="width: 60px; height: 60px; background: #000; border-radius: 8px; flex-shrink: 0; display:flex; align-items:center; justify-content:center; position:relative; overflow:hidden;">
                <!-- Recreate static broadcast logo -->
                <i class="ri-vidicon-fill" style="color:var(--text-muted); font-size: 2rem; opacity: 0.5;"></i>
                <i class="ri-play-circle-fill" style="color:white; font-size:2rem; position:absolute; z-index:2; cursor:pointer;" onclick="alert('Conexión con CDN Live en curso...');"></i>
            </div>
            <div style="flex: 1;">
                <h4 style="margin:0; font-size:0.95rem; color:var(--text-main); line-height:1.2;">Emisión Principal</h4>
                <p style="margin:0; font-size:0.75rem; color:var(--text-muted); margin-top:3px;">Señal ininterrumpida</p>
            </div>
        </div>
    </div>
    
    <!-- WIDGETS Y FOOTER -->
    <?php endif; // FIN DEL MODO BÚSQUEDA ?>

<?php include 'includes/footer_global.php'; ?>

    <!-- SCRIPTS CUSTOM FOOTER -->
    <?php if(!empty($global_configs['script_footer'])) echo render_safe_script($global_configs['script_footer']); ?>

    <!-- COOKIE BANNER -->
    <?php if(($global_configs['cookie_banner_estado'] ?? 'inactivo') === 'activo'): ?>
    <div id="cookie-banner" style="position:fixed; bottom:0; left:0; width:100%; background:#1e293b; color:#fff; padding:1rem; text-align:center; z-index:99999; display:none; box-shadow:0 -4px 10px rgba(0,0,0,0.1);">
        <p style="margin:0; font-size:0.9rem; display:inline-block;">Utilizamos cookies para mejorar su experiencia en HTV Perú. Al continuar navegando, usted acepta nuestra política de privacidad.</p>
        <button onclick="document.getElementById('cookie-banner').style.display='none'; localStorage.setItem('cookie_accepted','true');" style="margin-left:1rem; background:var(--primary-color); color:#fff; border:none; padding:0.5rem 1rem; border-radius:4px; cursor:pointer;">Aceptar y Cerrar</button>
    </div>
    <script>if(!localStorage.getItem('cookie_accepted')) document.getElementById('cookie-banner').style.display='block';</script>
    <?php endif; ?>

    <!-- Swiper JS Script Inyección -->
    <?= \App\Services\AssetManager::js('js/premium-features.js') ?>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Inicializar Carrusel Principal (Hero)
            if (document.querySelector('.heroSwiper')) {
                new Swiper('.heroSwiper', {
                    loop: true,
                    autoplay: { delay: 5000, disableOnInteraction: false },
                    effect: 'fade',
                    fadeEffect: { crossFade: true },
                    navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' }
                });
            }

            // Inicializar Carrusel Secundario (Policiales/Urgente)
            if (document.querySelector('.polSwiper')) {
                new Swiper('.polSwiper', {
                    slidesPerView: 1,
                    spaceBetween: 10,
                    autoplay: { delay: 4000, disableOnInteraction: false },
                    pagination: { el: '.swiper-pagination', clickable: true },
                    breakpoints: { 640: { slidesPerView: 2, spaceBetween: 20 }, 1024: { slidesPerView: 3, spaceBetween: 30 } }
                });
            }
            // Dark Mode Toggle (Movido a premium-features.js)

            // Reading Progress Bar & Sticky Header Shrink
            const header = document.querySelector('.header');
            const progressBar = document.getElementById("reading-progress");
            let isScrolling = false;
            let docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;

            // Recalcular altura en redimensionamiento y carga para evitar thrashing en cada frame de scroll
            window.addEventListener('resize', () => {
                docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            }, { passive: true });
            
            // También útil tras carga completa de medios
            window.addEventListener('load', () => {
                docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            });

            window.addEventListener('scroll', () => {
                if (!isScrolling) {
                    window.requestAnimationFrame(() => {
                        const winScroll = document.documentElement.scrollTop || document.body.scrollTop;
                        const scrolled = docHeight > 0 ? (winScroll / docHeight) * 100 : 0;
                        if (progressBar) progressBar.style.width = scrolled + "%";

                        // header shrink now handled by premium-features.js
                        isScrolling = false;
                    });
                    isScrolling = true;
                }
            }, { passive: true });

            // Skeleton Reveal Logic
            const skeletons = document.querySelectorAll('.news-card.skeleton');
            skeletons.forEach(card => {
                const img = card.querySelector('img.card-img, video.card-img');
                if (img) {
                    const revealCard = () => {
                        card.classList.remove('skeleton');
                        const content = card.querySelector('.card-content');
                        if (content) content.style.opacity = '1';
                    };
                    if (img.complete || img.readyState === 4) {
                        revealCard();
                    } else {
                        img.addEventListener('load', revealCard);
                        img.addEventListener('loadeddata', revealCard); // for video
                    }
                } else {
                    // Fallback if no media
                    setTimeout(() => {
                        card.classList.remove('skeleton');
                        const content = card.querySelector('.card-content');
                        if (content) content.style.opacity = '1';
                    }, 1000);
                }
            });

            // Load More Logic (AJAX)
            const btnLoadMore = document.getElementById('btn-load-more');
            const loadMoreText = document.getElementById('load-more-text');
            const newsGrid = document.getElementById('news-grid');
            let currentOffset = 19; // 9 hero + 10 recientes iniciales

            if (btnLoadMore) {
                btnLoadMore.addEventListener('click', async () => {
                    const icon = btnLoadMore.querySelector('i');
                    icon.classList.remove('ri-loader-4-line');
                    icon.classList.add('ri-refresh-line', 'ri-spin');
                    loadMoreText.innerText = 'Cargando...';

                    try {
                        const response = await fetch(`load_more_noticias.php?offset=${currentOffset}`);
                        if (response.ok) {
                            const html = await response.text();
                            if (html.trim() === '') {
                                loadMoreText.innerText = 'No hay más noticias';
                                btnLoadMore.style.pointerEvents = 'none';
                                icon.className = 'ri-check-line';
                                return;
                            }

                            // Parse HTML and append
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const cards = doc.querySelectorAll('.news-card');

                            cards.forEach(card => {
                                // Add skeleton state initially
                                card.classList.add('skeleton');
                                const content = card.querySelector('.card-content');
                                if (content) content.style.opacity = '0';
                                content.style.transition = 'opacity 0.3s ease';

                                newsGrid.appendChild(card);

                                // Bind reveal logic to new card
                                const img = card.querySelector('img.card-img, video.card-img');
                                const revealCard = () => {
                                    card.classList.remove('skeleton');
                                    if (content) content.style.opacity = '1';
                                };
                                if (img) {
                                    if (img.complete || img.readyState === 4) { revealCard(); }
                                    else { img.addEventListener('load', revealCard); img.addEventListener('loadeddata', revealCard); }
                                } else {
                                    setTimeout(revealCard, 1000);
                                }
                            });

                            currentOffset += cards.length;
                            loadMoreText.innerText = 'Cargar Más Noticias';
                        }
                    } catch (err) {
                        console.error(err);
                        loadMoreText.innerText = 'Error al cargar';
                        icon.className = 'ri-error-warning-line';
                    }
                });
            }
        });
    </script>
<?php include 'includes/floating_social.php'; ?>

<?php 
$modal_home = __DIR__ . '/../../includes/modal_privacidad.php';
if (file_exists($modal_home)) include_once $modal_home; 
?>

</body>

</html>
