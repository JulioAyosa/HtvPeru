<?php

// PRE-PRODUCCION: Helper para generar URLs con base path dinámico
// Uso en vistas: <?= base_url('/admin/dashboard') ?>
// En desarrollo:  /piura_noticias_php/admin/dashboard
// En producción:  /admin/dashboard
function base_url(string $path = ''): string {
    $base = defined('APP_BASE') ? APP_BASE : '';
    $path = '/' . ltrim($path, '/');
    return rtrim($base, '/') . $path;
}


// MEJ-06: Helper para invalidar cache del homepage
function invalidate_home_cache() {
    $cache_file = __DIR__ . '/../../home_cache.json';
    if (file_exists($cache_file)) @unlink($cache_file);
}

// ANTIGRAVITY v2: Constructor del Caché Global Estático
function build_global_cache($pdo) {
    try {
        $data = [
            'configs' => [],
            'categorias' => [],
            'publicidad' => []
        ];
        
        $c_stmt = $pdo->query("SELECT clave, valor FROM configuracion");
        while($r = $c_stmt->fetch()){ $data['configs'][$r['clave']] = $r['valor']; }
        
        $ca_stmt = $pdo->query("SELECT nombre, slug FROM categorias WHERE estado='activo' AND mostrar_menu=1 ORDER BY orden ASC");
        $data['categorias'] = $ca_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $ad_stmt = $pdo->query("SELECT * FROM publicidad WHERE estado='activo'");
        while($a = $ad_stmt->fetch()){
            if (!isset($data['publicidad'][$a['ubicacion']])) {
                $data['publicidad'][$a['ubicacion']] = [];
            }
            $data['publicidad'][$a['ubicacion']][] = $a;
        }
        
        file_put_contents(__DIR__ . '/../../global_cache.json', json_encode($data));
        return $data;
    } catch(Exception $e) {
        return null;
    }
}

// Helper para sacar un anuncio al azar del pool sin usar RAND() de SQL
function get_ad_from_cache($ads_pool, $ubicacion) {
    if (empty($ads_pool[$ubicacion]) || count($ads_pool[$ubicacion]) === 0) return null;
    $opciones = $ads_pool[$ubicacion];
    $idx = array_rand($opciones);
    return $opciones[$idx];
}

// Renderiza Imagen o Video automáticamente. Permite desactivar autoplay en miniaturas para evitar lag masivo.
function renderMedia($fileUrl, $cssClass = '', $posterUrl = '', $autoplay = true, $inlineStyle = '') {
    if (empty($fileUrl) || ($fileUrl !== '' && strpos($fileUrl, 'http') === false && !file_exists(__DIR__ . '/../../' . $fileUrl))) {
        return '<img loading="lazy" src="https://via.placeholder.com/800x400?text=Sin+Imagen" class="' . htmlspecialchars($cssClass) . '" alt="Placeholder">';
    }

    $extension = strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION));
    $video_extensions = ['mp4', 'webm', 'ogg'];

    if (in_array($extension, $video_extensions)) {
        $posterAttr = !empty($posterUrl) ? ' poster="' . htmlspecialchars($posterUrl) . '"' : '';
        
        if (!$autoplay) {
            $imgSrc = !empty($posterUrl) ? htmlspecialchars($posterUrl) : 'https://via.placeholder.com/800x400/030712/ffffff?text=Video+(Ver+Interior)';
            
            return '<div style="position:relative; width:100%; height:100%; overflow:hidden;">
                        <img loading="lazy" src="' . $imgSrc . '" class="' . htmlspecialchars($cssClass) . '" alt="Video cover" style="object-fit:cover; width:100%; height:100%;">
                        <span style="position:absolute; top:0.75rem; right:0.75rem; background:var(--primary-color); color:white; font-size:0.8rem; font-weight:800; padding:4px 10px; border-radius:4px; z-index:99; text-transform:uppercase; display:flex; align-items:center; gap:0.25rem; font-family:var(--font-sans); box-shadow:0 4px 6px rgba(0,0,0,0.5);">
                            <i class="ri-play-circle-fill" style="font-size:1.1rem;"></i> VIDEO
                        </span>
                    </div>';
        }

        return '<video preload="none" class="' . htmlspecialchars($cssClass) . '" autoplay muted loop playsinline' . $posterAttr . ' style="' . ($inlineStyle ? htmlspecialchars($inlineStyle) : 'object-fit:cover; width:100%; height:100%;') . '">
                    <source src="' . htmlspecialchars($fileUrl) . '" type="video/' . $extension . '">
                    Tu navegador no soporta la reproducción de video.
                </video>';
    } else {
        $styleAttr = $inlineStyle ? ' style="' . htmlspecialchars($inlineStyle) . '"' : '';
        return '<img loading="lazy" src="' . htmlspecialchars($fileUrl) . '" class="' . htmlspecialchars($cssClass) . '" alt="Media"' . $styleAttr . '>';
    }
}
