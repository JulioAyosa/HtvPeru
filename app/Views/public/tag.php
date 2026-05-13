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
    <title>Etiqueta: <?php echo htmlspecialchars(ucwords($tag_name)); ?> - HTVPERU</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" media="print" onload="this.media='all'">
    <?= \App\Services\AssetManager::css('css/style.css') ?>
    <style>
        .category-header { background: var(--bg-card); padding: 4rem 2rem; text-align: center; border-bottom: 1px solid var(--border-color); }
        .category-title { font-family: var(--font-serif); font-size: 2.5rem; color: var(--text-main); margin: 0; }
        .category-subtitle { color: var(--text-muted); font-size: 1.1rem; margin-top: 1rem; }
        
        .timeline-list { display: flex; flex-direction: column; gap: 1.5rem; max-width: 900px; margin: 3rem auto; }
        .timeline-item { display: flex; gap: 2rem; background: var(--bg-card); padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); align-items: flex-start; transition: transform 0.2s, box-shadow 0.2s; text-decoration: none; }
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

        @media (max-width: 768px) {
            .timeline-item { flex-direction: column; }
            .timeline-time { flex-direction: row; border-right: none; border-bottom: 2px solid var(--border-color); padding-right: 0; padding-bottom: 1rem; text-align: left; align-items: center; gap: 1rem; justify-content: flex-start; }
            .timeline-img-wrap { width: 100%; height: auto; }
        }

        .pagination { display: flex; gap: 0.5rem; justify-content: center; margin-top: 2rem; margin-bottom: 4rem; }
        .page-link { padding: 0.5rem 1rem; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 4px; color: var(--text-main); font-weight: 600; text-decoration: none; transition: background 0.2s; }
        .page-link:hover, .page-link.active { background: var(--primary-color); color: white; border-color: var(--primary-color); }
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

    <div class="category-header">
        <div class="container">
            <h1 class="category-title"><i class="ri-hashtag" style="color:var(--primary-color);"></i> <?php echo htmlspecialchars(ucwords($tag_name)); ?></h1>
            <p class="category-subtitle">Explorando publicaciones etiquetadas con "<?php echo htmlspecialchars(ucwords($tag_name)); ?>".</p>
        </div>
    </div>

    <main class="container" style="min-height: 50vh;">
        <div class="timeline-list">
            <?php if (count($noticias) > 0): ?>
                <?php foreach ($noticias as $n): 
                    $date = new DateTime($n['fecha_publicacion']);
                    $time_display = $date->format('H:i');
                    $date_display = $date->format('d/m/Y');
                    $time_ago = time_elapsed_string($n['fecha_publicacion']);
                ?>
                <a href="<?= APP_BASE ?>/<?php echo urlencode($n['slug']); ?>" class="timeline-item">
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
                        <h2 class="timeline-title"><?php echo htmlspecialchars($n['titulo']); ?></h2>
                        <p class="timeline-excerpt"><?php echo htmlspecialchars($n['extracto'] ?? ''); ?></p>
                        
                        <div style="margin-top: auto; display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:0.85rem; color:var(--text-muted);"><i class="ri-user-line"></i> Por: <?php echo htmlspecialchars($n['autor']); ?></span>
                            <span style="color:var(--primary-color); font-weight:800; font-size:0.9rem;">Leer más <i class="ri-arrow-right-line"></i></span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align:center; padding: 4rem; color:var(--text-muted);">
                    <i class="ri-price-tag-3-line" style="font-size:4rem; margin-bottom:1rem; display:block; color:#cbd5e1;"></i>
                    No pudimos encontrar noticias publicadas bajo esta etiqueta todavía.
                </div>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="<?= APP_BASE ?>/etiqueta/<?php echo urlencode($slug); ?>?page=<?php echo $page - 1; ?>" class="page-link"><i class="ri-arrow-left-s-line"></i></a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <a href="<?= APP_BASE ?>/etiqueta/<?php echo urlencode($slug); ?>?page=<?php echo $i; ?>" class="page-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="<?= APP_BASE ?>/etiqueta/<?php echo urlencode($slug); ?>?page=<?php echo $page + 1; ?>" class="page-link"><i class="ri-arrow-right-s-line"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer_global.php'; ?>
    <!-- Theme handled by Anti-FOUC in head -->
    <?= \App\Services\AssetManager::js('js/premium-features.js') ?>
<?php include 'includes/floating_social.php'; ?>


<?php include_once __DIR__ . '/includes/modal_privacidad.php'; ?>
</body>
</html>
