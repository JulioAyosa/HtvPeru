<?php

// PRE-PRODUCCION: Helper para generar URLs con base path dinámico
// Uso en vistas: base_url('/admin/dashboard')
// En desarrollo:  /piura_noticias_php/admin/dashboard
// En producción:  /admin/dashboard
function base_url(string $path = ''): string {
    $base = defined('APP_BASE') ? APP_BASE : '';
    $path = '/' . ltrim($path, '/');
    return rtrim($base, '/') . $path;
}


// MEJ-06: Helper para invalidar cache del homepage
function invalidate_home_cache() {
    $cache_file = __DIR__ . '/../../storage/cache/home_cache.json';
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
// FASE FINAL: Agregado parámetro $lazy para optimizar LCP (Largest Contentful Paint) en imágenes principales.
function renderMedia($fileUrl, $cssClass = '', $posterUrl = '', $autoplay = true, $inlineStyle = '', $lazy = true) {
    $loadingAttr = $lazy ? ' loading="lazy"' : ' fetchpriority="high"';

    if (empty($fileUrl) || ($fileUrl !== '' && strpos($fileUrl, 'http') === false && !file_exists(PUBLIC_PATH . ltrim($fileUrl, '/')))) {
        $placeholderSvg = 'data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22800%22%20height%3D%22400%22%20viewBox%3D%220%200%20800%20400%22%20preserveAspectRatio%3D%22none%22%3E%3Crect%20width%3D%22800%22%20height%3D%22400%22%20fill%3D%22%23e2e8f0%22%2F%3E%3Ctext%20x%3D%22400%22%20y%3D%22200%22%20fill%3D%22%2394a3b8%22%20font-family%3D%22sans-serif%22%20font-size%3D%2230%22%20font-weight%3D%22bold%22%20text-anchor%3D%22middle%22%20dominant-baseline%3D%22middle%22%3ESin%20Imagen%3C%2Ftext%3E%3C%2Fsvg%3E';
        $styleAttr = $inlineStyle ? ' style="' . htmlspecialchars($inlineStyle) . '"' : '';
        return '<img' . $loadingAttr . ' src="' . $placeholderSvg . '" class="' . htmlspecialchars($cssClass) . '" alt="Placeholder"' . $styleAttr . '>';
    }

    $extension = strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION));
    $video_extensions = ['mp4', 'webm', 'ogg'];

    $finalUrl = (strpos($fileUrl, 'http') === 0) ? $fileUrl : base_url($fileUrl);
    $finalPoster = (!empty($posterUrl) && strpos($posterUrl, 'http') !== 0) ? base_url($posterUrl) : $posterUrl;

    if (in_array($extension, $video_extensions)) {
        $posterAttr = !empty($finalPoster) ? ' poster="' . htmlspecialchars($finalPoster) . '"' : '';
        
        if (!$autoplay) {
            $videoPlaceholderSvg = 'data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22800%22%20height%3D%22400%22%20viewBox%3D%220%200%20800%20400%22%20preserveAspectRatio%3D%22none%22%3E%3Crect%20width%3D%22800%22%20height%3D%22400%22%20fill%3D%22%230f172a%22%2F%3E%3Ctext%20x%3D%22400%22%20y%3D%22200%22%20fill%3D%22%23ffffff%22%20font-family%3D%22sans-serif%22%20font-size%3D%2230%22%20font-weight%3D%22bold%22%20text-anchor%3D%22middle%22%20dominant-baseline%3D%22middle%22%3EVideo%20(Ver%20Interior)%3C%2Ftext%3E%3C%2Fsvg%3E';
            $imgSrc = !empty($finalPoster) ? htmlspecialchars($finalPoster) : $videoPlaceholderSvg;
            
            return '<div style="position:relative; width:100%; height:100%; overflow:hidden;">
                        <img' . $loadingAttr . ' src="' . $imgSrc . '" class="' . htmlspecialchars($cssClass) . '" alt="Video cover" style="object-fit:cover; width:100%; height:100%;">
                        <span style="position:absolute; top:0.75rem; right:0.75rem; background:var(--primary-color); color:white; font-size:0.8rem; font-weight:800; padding:4px 10px; border-radius:4px; z-index:99; text-transform:uppercase; display:flex; align-items:center; gap:0.25rem; font-family:var(--font-sans); box-shadow:0 4px 6px rgba(0,0,0,0.5);">
                            <i class="ri-play-circle-fill" style="font-size:1.1rem;"></i> VIDEO
                        </span>
                    </div>';
        }

        return '<video preload="none" class="' . htmlspecialchars($cssClass) . '" autoplay muted loop playsinline' . $posterAttr . ' style="' . ($inlineStyle ? htmlspecialchars($inlineStyle) : 'object-fit:cover; width:100%; height:100%;') . '">
                    <source src="' . htmlspecialchars($finalUrl) . '" type="video/' . $extension . '">
                    Tu navegador no soporta la reproducción de video.
                </video>';
    } else {
        $styleAttr = $inlineStyle ? ' style="' . htmlspecialchars($inlineStyle) . '"' : '';
        return '<img' . $loadingAttr . ' src="' . htmlspecialchars($finalUrl) . '" class="' . htmlspecialchars($cssClass) . '" alt="Media"' . $styleAttr . '>';
    }
}
