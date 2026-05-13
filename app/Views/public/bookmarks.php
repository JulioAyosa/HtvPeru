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
    <title>Mis Guardianes - HTVPERU</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" media="print" onload="this.media='all'">
    <?= \App\Services\AssetManager::css('css/style.css') ?>
    <style>
        .timeline-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; width: 100%; margin: 3rem 0; }
        .timeline-item { display: flex; flex-direction:column; gap: 1rem; background: var(--bg-card); padding: 1.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); transition: transform 0.2s, box-shadow 0.2s; position:relative; }
        .timeline-item:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); border-color: var(--primary-light); }
        .timeline-img-wrap { width: 100%; height: 200px; border-radius: var(--radius-md); overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .timeline-img-wrap img { width: 100%; height: 100%; object-fit:cover; display: block; }
        .timeline-title { font-size: 1.2rem; font-family: var(--font-sans); font-weight: 800; color: var(--text-main); line-height: 1.3; }
        .timeline-title:hover { color: var(--primary-color); }
        .btn-remove-bk { position:absolute; top: 10px; right: 10px; background:var(--danger); color:white; border:none; width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; }
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
            <h1 class="category-title"><i class="ri-bookmark-fill" style="color:var(--danger);"></i> Mis Noticias Guardadas</h1>
            <p class="category-subtitle">Lecturas pendientes guardadas de forma local en tu navegador.</p>
        </div>
    </div>

    <main class="container" style="min-height: 50vh; margin-top: 2rem;">
        <div class="timeline-list" id="bookmarks-container">
            <!-- Cargado vía JS -->
            <div style="grid-column: 1 / -1; text-align:center; padding: 4rem; color:var(--text-muted);">
                <i class="ri-loader-4-line ri-spin" style="font-size:4rem; margin-bottom:1rem; display:block; color:#cbd5e1;"></i>
                Buscando notas guardadas...
            </div>
        </div>
    </main>

    <?php include 'includes/footer_global.php'; ?>
    <!-- Theme handled by Anti-FOUC in head -->
    <?= \App\Services\AssetManager::js('js/premium-features.js') ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('bookmarks-container');
            let bookmarks = JSON.parse(localStorage.getItem('htv_bookmarks')) || [];
            
            function renderBookmarks() {
                if(bookmarks.length === 0) {
                    container.innerHTML = `
                        <div style="grid-column: 1 / -1; text-align:center; padding: 4rem; color:var(--text-muted);">
                            <i class="ri-bookmark-line" style="font-size:4rem; margin-bottom:1rem; display:block; color:#cbd5e1;"></i>
                            Aún no has guardado ninguna noticia para leer más tarde.
                        </div>
                    `;
                    return;
                }
                
                let html = '';
                bookmarks.forEach((b, index) => {
                    const dateObj = new Date(b.date);
                    const dateStr = dateObj.toLocaleDateString();
                    html += `
                    <div class="timeline-item">
                        <button class="btn-remove-bk" data-index="${index}" title="Remover de guardados"><i class="ri-close-line"></i></button>
                        <a href="<?= APP_BASE ?>/${encodeURIComponent(b.slug)}" style="text-decoration:none;">
                            <div class="timeline-img-wrap">
                                <img src="${b.img ? b.img : '<?= APP_BASE ?>/img/placeholder.webp'}" alt="img">
                            </div>
                            <h2 class="timeline-title" style="margin-top:1rem;">${b.title}</h2>
                        </a>
                        <div style="margin-top:auto; font-size:0.8rem; color:var(--text-muted);">
                            Guardado el: ${dateStr}
                        </div>
                    </div>
                    `;
                });
                container.innerHTML = html;
                
                document.querySelectorAll('.btn-remove-bk').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const idx = this.getAttribute('data-index');
                        bookmarks.splice(idx, 1);
                        localStorage.setItem('htv_bookmarks', JSON.stringify(bookmarks));
                        renderBookmarks();
                        const badge = document.getElementById('bookmark-badge');
                        if(badge) {
                            badge.textContent = bookmarks.length;
                            badge.style.display = bookmarks.length > 0 ? 'flex' : 'none';
                        }
                    });
                });
            }
            
            renderBookmarks();
        });
    </script>
<?php include 'includes/floating_social.php'; ?>


<?php include_once __DIR__ . '/includes/modal_privacidad.php'; ?>
</body>
</html>
