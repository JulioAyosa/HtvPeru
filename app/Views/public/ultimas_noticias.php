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
    <title>Últimas Noticias - HTVPERU</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" media="print" onload="this.media='all'">
    <?= \App\Services\AssetManager::css('css/style.css') ?>
    <style>
        .timeline-list { display: flex; flex-direction: column; gap: 1.5rem; width: 100%; margin: 3rem 0; }
        .timeline-item { display: flex; gap: 2rem; background: var(--bg-card); padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); align-items: flex-start; transition: transform 0.2s, box-shadow 0.2s; }
        .timeline-item:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); border-color: var(--primary-light); }
        .timeline-time { min-width: 120px; display: flex; flex-direction: column; align-items: flex-end; justify-content: flex-start; padding-right: 1.5rem; border-right: 3px solid var(--border-color); color: var(--text-muted); font-weight: 600; font-size: 0.9rem; text-align: right; margin-top: 0.5rem; }
        .timeline-time .hour { font-size: 1.6rem; color: var(--text-main); font-weight: 800; line-height: 1; margin-bottom: 0.2rem; }
        .timeline-content { flex: 1; display: flex; flex-direction: column; justify-content: flex-start; }
        .timeline-cat { color: var(--primary-color); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; margin-bottom: 0.75rem; letter-spacing: 1px; display: inline-block; background: var(--primary-light); padding: 3px 8px; border-radius: 4px; width: fit-content; }
        .timeline-title { font-size: 1.5rem; font-family: var(--font-sans); font-weight: 800; color: var(--text-main); margin-bottom: 0.75rem; line-height: 1.3; }
        .timeline-title:hover { color: var(--primary-color); }
        .timeline-excerpt { color: var(--text-muted); font-size: 1.05rem; line-height: 1.6; margin-bottom: 1rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        .timeline-img-wrap { width: 280px; height: auto; flex-shrink: 0; border-radius: var(--radius-md); overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .timeline-img-wrap img, .timeline-img-wrap video { width: 100%; height: auto; display: block; }
        
        .pagination { display: flex; gap: 0.5rem; justify-content: center; margin-top: 3rem; }
        .page-link { padding: 0.5rem 1rem; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 4px; color: var(--text-main); font-weight: 600; text-decoration: none; transition: background 0.2s; }
        .page-link:hover, .page-link.active { background: var(--primary-color); color: white; border-color: var(--primary-color); }

        @media (max-width: 768px) {
            .timeline-item { flex-direction: column; }
            .timeline-time { flex-direction: row; border-right: none; border-bottom: 2px solid var(--border-color); padding-right: 0; padding-bottom: 1rem; text-align: left; align-items: center; gap: 1rem; justify-content: flex-start; }
            .timeline-img-wrap { width: 100%; height: auto; }
        }
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
    <?php include 'includes/header_global.php'; ?>

    <main class="container main-grid" style="padding: 2rem 1.5rem; display: grid; grid-template-columns: 1fr 340px; gap: 3.5rem; align-items: start; min-height: 60vh;">
        <div class="content-col" style="min-width:0;">
        <div class="section-title" style="border-bottom: 2px solid var(--primary-color); display: flex; justify-content: space-between; align-items: center; padding-bottom: 0.5rem; margin: 0 0 3rem 0; width: 100%;">
            <h2 style="margin: 0; text-transform: uppercase; font-size: 2rem; display:flex; align-items:center; gap:10px;">
                <i class="ri-history-line" style="color:var(--primary-color);"></i> Últimas Noticias
            </h2>
            <span style="color: var(--text-muted); font-weight: 600;">Minuto a Minuto</span>
        </div>
        
        <div class="timeline-list">
            <?php if (count($noticias) > 0): ?>
                <?php foreach ($noticias as $n): 
                    $date = new DateTime($n['fecha_publicacion']);
                    $time_display = $date->format('H:i');
                    $date_display = $date->format('d/m/Y');
                    // Helper logic inside view inline
                    $time_ago = time_elapsed_string($n['fecha_publicacion']);
                ?>
                <a href="<?php echo urlencode($n['slug']); ?>" class="timeline-item">
                    <div class="timeline-time">
                        <span class="hour"><?php echo $time_display; ?></span>
                        <span><?php echo $date_display; ?></span>
                        <span style="font-size:0.75rem; color:var(--primary-color); margin-top:5px; font-weight:800;"><?php echo $time_ago; ?></span>
                    </div>
                    
                    <?php if (!empty($n['imagen_url']) || !empty($n['video_poster_url'])): ?>
                    <div class="timeline-img-wrap">
                        <?php echo renderMedia($n['imagen_url'], 'card-img', $n['video_poster_url'] ?? '', false); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="timeline-content">
                        <span class="timeline-cat"><?php echo htmlspecialchars($n['categoria']); ?></span>
                        <h3 class="timeline-title"><?php echo htmlspecialchars($n['titulo']); ?></h3>
                        <p class="timeline-excerpt"><?php echo htmlspecialchars($n['extracto'] ?? ''); ?></p>
                        <div style="font-size:0.85rem; color:var(--text-muted); font-weight:600; display:flex; justify-content:space-between; align-items:center;">
                            <span><i class="ri-user-line"></i> Por: <?php echo htmlspecialchars($n['autor']); ?></span>
                            <span style="color:var(--primary-color); font-weight:800; display:flex; align-items:center; gap:5px;">Leer más <i class="ri-arrow-right-line"></i></span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align:center; padding: 4rem; color:var(--text-muted);">
                    <i class="ri-inbox-archive-line" style="font-size:4rem; margin-bottom:1rem; display:block; color:#cbd5e1;"></i>
                    No hay noticias publicadas aún.
                </div>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 3rem;">
            <button id="btn-load-more" class="page-link" style="padding: 1rem 3rem; cursor: pointer; border-radius:30px; border:none; background:var(--bg-card); color:var(--text-main); font-weight:800; box-shadow:0 4px 6px rgba(0,0,0,0.05);">
                Cargar Más Noticias <i class="ri-refresh-line"></i>
            </button>
        </div>
        </div> <!-- Fin content-col -->

        <aside class="sidebar-col">
            <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-lg); padding:1.5rem; position:sticky; top:2rem;">
                <h3 style="margin-top:0; border-bottom:2px solid var(--primary-color); padding-bottom:0.5rem; font-family:var(--font-sans); color:var(--text-main);"><i class="ri-fire-fill" style="color:#fbbf24;"></i> Lo Más Leído</h3>
                <div style="display:flex; flex-direction:column; gap:1.25rem; margin-top:1.5rem;">
                    <?php 
                    $mas_leidas = $pdo->query("SELECT id, slug, titulo, categoria, imagen_url, video_poster_url FROM noticias WHERE estado_publicacion = 'publicado' ORDER BY vistas DESC LIMIT 5")->fetchAll();
                    if(!empty($mas_leidas)): 
                        foreach ($mas_leidas as $sn): 
                    ?>
                    <a href="<?php echo urlencode($sn['slug']); ?>" style="display:flex; gap:1rem; text-decoration:none;">
                        <?php echo renderMedia($sn['imagen_url'], '', $sn['video_poster_url'] ?? '', false, 'width: 80px; height: 80px; object-fit: cover; border-radius: 4px; flex-shrink:0;'); ?>
                        <div>
                            <span style="font-size:0.7rem; color:var(--primary-color); font-weight:800; text-transform:uppercase; letter-spacing:0.5px;"><?php echo htmlspecialchars($sn['categoria']); ?></span>
                            <h4 style="margin:0; font-size:0.95rem; color:var(--text-main); font-family:var(--font-sans); line-height:1.3; font-weight:600; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;"><?php echo htmlspecialchars($sn['titulo']); ?></h4>
                        </div>
                    </a>
                    <?php 
                        endforeach; 
                    endif; 
                    ?>
                </div>

                
                <?php include 'includes/sidebar_social.php'; ?>
            
            </div>
        </aside>
        
        
    </main>

    <?php include 'includes/footer_global.php'; ?>
    <script>
        // Theme handled by Anti-FOUC in head
        
        let currentPage = 1;
        const btnLoadMore = document.getElementById('btn-load-more');
        const newsGrid = document.querySelector('.timeline-list');
        
        if (btnLoadMore) {
            btnLoadMore.addEventListener('click', function() {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Cargando...';
                this.disabled = true;
                this.style.opacity = '0.7';
                
                currentPage++;
                
                fetch('load_more_ultimas.php?page=' + currentPage)
                    .then(response => response.text())
                    .then(data => {
                        if (data.trim() === '') {
                            this.innerHTML = 'No hay más noticias locales o antiguas';
                            this.style.opacity = '0.5';
                            this.style.cursor = 'default';
                        } else {
                            newsGrid.insertAdjacentHTML('beforeend', data);
                            this.innerHTML = originalText;
                            this.disabled = false;
                            this.style.opacity = '1';
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        this.innerHTML = 'Error al cargar';
                        this.disabled = false;
                        this.style.opacity = '1';
                    });
            });
        }
    </script>
    <?= \App\Services\AssetManager::js('js/premium-features.js') ?>
<?php include 'includes/floating_social.php'; ?>


<?php include_once 'includes/modal_privacidad.php'; ?>
</body>
</html>


