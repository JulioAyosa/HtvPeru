<?php
/**
 * html_sanitizer.php — CRIT-01 FIX: Sanitización Extrema Antigravity
 * Sanitizador nativo PHP sin dependencias.
 */

function sanitize_html(string $html): string {
    if (empty($html)) return '';

    // Tags permitidos para contenido editorial
    $allowed_tags = '<p><br><b><i><u><strong><em><a><img><h1><h2><h3><h4><h5><h6>'
                  . '<ul><ol><li><blockquote><iframe><table><tr><td><th><thead><tbody>'
                  . '<figure><figcaption><div><span><video><source><hr><pre><code><sub><sup>';

    $html = strip_tags($html, $allowed_tags);
    $html = preg_replace('/<!--.*?-->/s', '', $html);

    $old_html = '';
    while ($old_html !== $html) {
        $old_html = $html;
        
        $html = preg_replace("/\s+on[a-z]+(\s*=\s*(?:\"[^\"]*\"|'[^']*'|[^>\s]+))?/i", "", $html);

        $html = preg_replace_callback('/(href|src|data)\s*=\s*(["\'])(.*?)\2/i', function($m) {
            $attr = strtolower($m[1]);
            $quote = $m[2];
            $val = $m[3];
            
            $decoded = html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $decoded = preg_replace('/[\s\x00-\x1F\x7F-\x9F]/u', '', $decoded);
            
            if (stripos($decoded, 'javascript:') !== false || stripos($decoded, 'vbscript:') !== false || stripos($decoded, 'data:text/html') !== false) {
                return $attr . '=' . $quote . ($attr === 'href' ? '#' : '') . $quote;
            }
            if ($attr === 'src' && stripos($decoded, 'data:') === 0 && stripos($decoded, 'data:image/') !== 0) {
                return 'src=""';
            }
            return $m[0];
        }, $html);
    }

    $html = preg_replace('/expression\s*\([^)]*\)/i', '', $html);
    $html = preg_replace('/-moz-binding\s*:[^;}"\']*/', '', $html);

    return $html;
}

/**
 * HIGH-NEW-01 FIX: Sanitiza CSS custom para prevenir CSS injection.
 * Filtra expression(), -moz-binding, javascript:, behavior:, @import
 */
function sanitize_css(string $css): string {
    if (empty($css)) return '';
    $css = preg_replace('/expression\s*\([^)]*\)/i', '', $css);
    $css = preg_replace('/-moz-binding\s*:[^;}"\']*/', '', $css);
    $css = preg_replace('/javascript\s*:/i', '', $css);
    $css = preg_replace('/behavior\s*:/i', '', $css);
    $css = preg_replace('/@import/i', '', $css);
    return $css;
}
?>
