<?php
// includes/header_global.php

// Asegurar dependencias del Global Header si no existen
if(!isset($menu_cats_dynamic)) {
    try {
        $c_stmt = $pdo->query("SELECT nombre, slug FROM categorias WHERE estado='activo' AND mostrar_menu=1 ORDER BY orden ASC");
        $menu_cats_dynamic = $c_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
        $menu_cats_dynamic = [];
    }
}

if(!isset($noticias_por_categoria)) {
    $noticias_por_categoria = [];
    try {
        $mega_menu_stmt = $pdo->query("SELECT id, slug, titulo, categoria, imagen_url, video_poster_url, fecha_publicacion FROM noticias WHERE categoria != 'Publicidad' AND estado_publicacion = 'publicado' ORDER BY fecha_publicacion DESC LIMIT 50");
        $todas_noticias_hdr = $mega_menu_stmt->fetchAll();
        foreach ($todas_noticias_hdr as $n) {
            if (!isset($noticias_por_categoria[$n['categoria']])) {
                $noticias_por_categoria[$n['categoria']] = [];
            }
            if (count($noticias_por_categoria[$n['categoria']]) < 4) {
                $noticias_por_categoria[$n['categoria']][] = $n;
            }
        }
    } catch(Exception $e) {}
}
?>
<?php 
$custom_header_height = isset($global_configs['header_height']) && is_numeric($global_configs['header_height']) ? (int)$global_configs['header_height'] : 100;
$logo_scale = isset($global_configs['header_logo_scale']) && is_numeric($global_configs['header_logo_scale']) ? (float)$global_configs['header_logo_scale'] : 1.0;
$search_width = isset($global_configs['header_search_width']) && is_numeric($global_configs['header_search_width']) ? (int)$global_configs['header_search_width'] : 280;
$actions_gap = isset($global_configs['header_actions_gap']) && is_numeric($global_configs['header_actions_gap']) ? (float)$global_configs['header_actions_gap'] : 1.0;
?>
<style>
.header-inner { height: <?php echo $custom_header_height; ?>px !important; transition: height 0.3s ease; }
.header.shrink .header-inner { height: 70px !important; }
.logo { transform-origin: left center; transition: transform 0.3s ease; }
.header.shrink .logo { transform: scale(<?php echo $logo_scale * 0.85; ?>) !important; }
.search-bar-modern { width: <?php echo $search_width; ?>px !important; transition: width 0.3s ease; }
.header-actions { gap: <?php echo $actions_gap; ?>rem !important; transition: gap 0.3s ease; }

/* HARD MOBILE OVERRIDES (BULLETPROOF) */
@media (max-width: 768px) {
    .header-inner { height: auto !important; padding: 1rem 0 !important; flex-wrap: wrap; justify-content: space-between; }
    .logo img { height: 40px !important; }
    .logo-title { font-size: 1.5rem !important; white-space: nowrap; }
    .slogan-text { display: none !important; }
    .search-bar-modern { display: none !important; }
    #weather-widget { display: none !important; }
    .btn-live { display: none !important; }
    .header-actions { gap: 0.5rem !important; }
    .mobile-menu-btn { display: block !important; width: 100%; text-align: center !important; }
    .nav-list { display: none !important; flex-direction: column !important; width: 100%; background: #0f172a; position: absolute; left: 0; top: 100%; z-index: 1000; box-shadow: 0 10px 20px rgba(0,0,0,0.5); }
    .nav-list.active { display: flex !important; }
    .nav-list li { width: 100%; border-bottom: 1px solid rgba(255,255,255,0.05); }
    .nav-list a { display: flex !important; width: 100%; justify-content: space-between; }
    .mobile-only-nav-item { display: flex !important; }
}
</style>
<?php if(!empty($global_configs['header_bg_url'])): ?>
<style>
/* 1. Parallax y Ajuste de Fondo */
.header { 
    background-image: url('<?php echo htmlspecialchars($global_configs['header_bg_url']); ?>'); 
    background-size: cover; 
    background-position: center center; 
    background-attachment: fixed; /* Parallax effect */
    position: relative; 
}
/* 2. Degradado Elegante (Radial/Vignette) */
.header::before { 
    content: ''; 
    position: absolute; 
    inset: 0; 
    background: radial-gradient(circle at center, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.9) 100%);
    z-index: 0; 
    pointer-events: none; 
}

/* 4. Glassmorphism para los controles */
.header .search-bar-modern,
.header .hover-circle,
.header #weather-widget {
    background: rgba(255, 255, 255, 0.1) !important;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.25) !important;
    color: white !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}
.header #live-search-input { color: white !important; }
.header #live-search-input::placeholder { color: rgba(255,255,255,0.7) !important; }
.header #weather-widget #weather-icon,
.header .hover-circle i { color: white !important; }

/* Logo Drop Shadow */
.logo { position: relative; z-index: 10; filter: drop-shadow(0 4px 20px rgba(0,0,0,0.95)) drop-shadow(0 1px 3px rgba(0,0,0,0.9)); }
.logo .htv-text { color: white !important; }
.logo .slogan-text { color: #cbd5e1 !important; text-shadow: 0 2px 5px rgba(0,0,0,0.95); }
</style>
<?php endif; ?>
    <header class="header">
        <div class="container header-inner" style="align-items: center; position: relative; z-index: 25;">
            <div class="logo"><a href="index.php" style="display:flex; align-items:center; gap:0.75rem; text-decoration:none;">
                    <img src="<?php echo htmlspecialchars(!empty($global_configs['logo_url']) ? $global_configs['logo_url'] : 'img/logo.webp'); ?>" alt="Logo" style="height:55px; max-height:100%; width:auto; object-fit:contain; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                    <div style="display: flex; flex-direction: column;">
                        <h1 class="logo-title" style="font-size: 2.2rem; margin:0; line-height: 1; font-family: 'Arial Black', 'Montserrat', Impact, var(--font-sans); font-weight: 900; letter-spacing: -1.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.15);"><span class="htv-text" style="color: var(--text-main, white);">HTV</span><span class="peru-text" style="color:var(--primary-color);">PERU</span></h1>
                        <span class="slogan-text" style="font-size: 0.70rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 2px;"><?php echo htmlspecialchars($global_configs['site_slogan'] ?? 'Una Mirada al Mundo'); ?></span>
                    </div>
                </a></div>

            <div class="header-actions" style="display: flex; gap: 1rem; align-items: center;">
                <div style="position:relative;" id="live-search-container">
                    <form action="search.php" method="GET" class="search-bar-modern"
                        style="display:flex; align-items:center; background:var(--bg-body); border:1px solid var(--border-color); border-radius:50px; padding:0.25rem 0.5rem 0.25rem 1.25rem; transition:all 0.3s ease; width: 280px;">
                        <input type="text" name="q" id="live-search-input" autocomplete="off" placeholder="Buscar noticias..."
                            style="border:none; background:transparent; width:100%; color:var(--text-main); outline:none; font-family:var(--font-sans); font-size: 0.95rem;">
                        <button type="submit"
                            style="background:var(--primary-color); border:none; color:white; width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:transform 0.2s;"><i
                                class="ri-search-line"></i></button>
                    </form>
                    <div id="live-search-results" style="display:none; position:absolute; top:110%; right:0; width:350px; background:var(--bg-card); border:1px solid var(--border-color); border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.1); z-index:9999; overflow:hidden;"></div>
                </div>

                <?php if(($global_configs['tv_envivo_estado'] ?? 'inactivo') === 'activo' && !empty($global_configs['tv_envivo_url'])): ?>
                <a href="#" onclick="document.getElementById('pip-player-modal').style.display='flex'; return false;" class="btn-live"><i class="ri-tv-line"></i> VER TV EN VIVO</a>
                <?php endif; ?>

                <button class="theme-toggle hover-circle" id="theme-toggle" title="Cambiar Tema"
                    style="background:var(--bg-body); color:var(--text-main); border:1px solid var(--border-color); cursor: pointer; display: flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: 50%; transition:all 0.3s ease; flex-shrink:0;"><i
                        class="ri-moon-line" id="theme-icon"></i><i class="ri-sun-fill" id="theme-icon-sun"
                        style="display:none;"></i></button>

                <div id="weather-widget" title="Clima en Piura" style="display:flex; align-items:center; gap:0.5rem; background:var(--bg-body); border:1px solid var(--border-color); padding:0.25rem 0.75rem; border-radius:50px; font-weight:800; font-size:0.85rem; color:var(--text-main); cursor:default; transition:all 0.3s ease; flex-shrink:0;">
                    <i id="weather-icon" class="ri-sun-cloudy-line" style="font-size:1.2rem; color:var(--primary-color);"></i>
                    <span id="weather-temp">...</span>
                </div>
                
                <a href="bookmarks.php" class="hover-circle" title="Mis Noticias Guardadas" style="background:var(--bg-body); color:var(--text-main); border:1px solid var(--border-color); display: flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: 50%; text-decoration:none; transition:all 0.3s ease; position:relative; flex-shrink:0;">
                    <i class="ri-bookmark-line" style="font-size:1.2rem;"></i>
                    <span id="bookmark-badge" style="display:none; position:absolute; top:-5px; right:-5px; background:#ef4444; color:white; font-size:0.65rem; width:18px; height:18px; border-radius:50%; align-items:flex-end; justify-content:center; font-weight:800;">0</span>
                </a>
                
                <a href="login.php" class="hover-circle" title="Ingresar / Panel"
                    style="background:var(--bg-body); color:var(--text-main); border:1px solid var(--border-color); display: flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: 50%; text-decoration:none; transition:all 0.3s ease; flex-shrink:0;"><i
                        class="ri-user-line" style="font-size:1.2rem;"></i></a>
            </div>
        </div>

        <nav class="nav" style="background: var(--primary-color); color: white; position: relative;">
            <div class="container" style="display:flex; flex-direction:column;">
                <button class="mobile-menu-btn" onclick="this.nextElementSibling.classList.toggle('active')"
                    style="display:none; background:transparent; border:none; color:white; font-size:1.2rem; cursor:pointer; padding:1rem; text-align:left; font-weight:800; border-bottom:1px solid rgba(255,255,255,0.1);"><i
                        class="ri-menu-3-line"></i> MENÚ PRINCIPAL</button>
                <ul class="nav-list mega-nav-list"
                    style="margin: 0; padding: 0; display:flex; font-weight:800; font-size:0.85rem; text-transform:uppercase; white-space:nowrap; justify-content:center; align-items:stretch;">
                    <li style="display:flex;"><a href="index.php" style="color:white; padding: 1rem 1.5rem; display:flex; align-items:center;">Inicio</a></li>
                    
                    <?php if(($global_configs['tv_envivo_estado'] ?? 'inactivo') === 'activo' && !empty($global_configs['tv_envivo_url'])): ?>
                    <li style="display:flex;" class="mobile-only-nav-item"><a href="#" onclick="document.getElementById('pip-player-modal').style.display='flex'; return false;" style="color:white; padding: 1rem 1.5rem; background: #ef4444; display:flex; align-items:center; gap:5px; font-weight:800;"><i class="ri-tv-line"></i> TV EN VIVO</a></li>
                    <?php endif; ?>

                    <li style="display:flex;"><a href="ultimas-noticias.php"
                            style="color:white; padding: 1rem 1.5rem; background: #dc2626; display:flex; align-items:center; gap:5px;">Lo Último <i class="ri-flashlight-fill"></i></a></li>

                    <?php
                    foreach ($menu_cats_dynamic as $cat_data):
                        $cat = $cat_data['nombre'];
                        $slug = $cat_data['slug'];
                        ?>
                        <li class="nav-item has-dropdown" style="display:flex;">
                            <a href="category.php?slug=<?php echo urlencode($slug); ?>"
                                style="color:white; opacity:0.9; padding: 1rem 1.5rem; display:flex; align-items:center; gap:0.25rem; white-space:nowrap;">
                                <?php echo htmlspecialchars($cat); ?> <i class="ri-arrow-down-s-line"></i>
                            </a>

                            <?php if (!empty($noticias_por_categoria[$cat])): ?>
                                <!-- Mega Menú Dropdown -->
                                <div class="mega-menu">
                                    <div class="container mega-menu-grid">
                                        <?php foreach ($noticias_por_categoria[$cat] as $mn): ?>
                                            <a href="article.php?slug=<?php echo urlencode($mn['slug'] ?? ''); ?>" class="news-card"
                                                style="height: 180px; border-radius: var(--radius-sm); border: none;">
                                                <div class="card-img-wrap">
                                                    <?php echo renderMedia($mn['imagen_url'], 'card-img', $mn['video_poster_url'] ?? '', false); ?>
                                                </div>
                                                <div class="card-content" style="padding: 1rem;">
                                                    <h3 class="card-title"
                                                        style="font-size: 0.9rem; line-height: 1.2; text-shadow: 0 1px 2px rgba(0,0,0,0.8);">
                                                        <?php echo htmlspecialchars($mn['titulo']); ?></h3>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                    
                    <!-- Botón de Login exclusivo para Móvil -->
                    <li style="display:flex;" class="mobile-only-nav-item">
                        <a href="login.php" style="color:white; padding: 1rem 1.5rem; background: rgba(255,255,255,0.1); display:flex; align-items:center; gap:5px; font-weight:800; border-top: 1px solid rgba(255,255,255,0.2);">
                            <i class="ri-user-settings-line"></i> Ingresar / Panel Admin
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- MOTOR DE BÚSQUEDA MODAL -->
    <div id="searchModal">
        <div class="search-container">
            <a href="#" class="btn-close-search"
                onclick="document.getElementById('searchModal').style.display='none'; return false;"><i
                    class="ri-close-line"></i></a>
            <form action="index.php" method="GET" class="search-box">
                <input type="text" name="q" id="searchInput" placeholder="Escribe tu búsqueda aquí..." required>
                <button type="submit"><i class="ri-search-line"></i></button>
            </form>
        </div>
    </div>

    <!-- MODAL DE TV EN VIVO -->
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
