<?php
// AUDIT FIX CRIT-A2: Validador de scripts inyectados
function validate_injected_script($script_val, $key, $pdo, $user_id) {
    if (empty($script_val) || !preg_match('/<script/i', $script_val)) return true;
    $allowed = ['googletagmanager.com','google-analytics.com','analytics.google.com','connect.facebook.net','tawk.to','googleads.g.doubleclick.net','pagead2.googlesyndication.com','www.googleadservices.com'];
    preg_match_all('/src=["\']([^"\']+)["\']/i', $script_val, $matches);
    foreach ($matches[1] ?? [] as $url) {
        $domain = parse_url($url, PHP_URL_HOST);
        $ok = false;
        foreach ($allowed as $d) { if ($domain && str_ends_with($domain, $d)) { $ok = true; break; } }
        if (!$ok && $domain) {
            $pdo->prepare("INSERT INTO registro_actividad (user_id, accion, detalles) VALUES (?,?,?)")->execute([$user_id, 'Alerta Seguridad', "Script no autorizado en $key: $domain"]);
            return false;
        }
    }
    return true;
}

/**
 * HIGH-NEW-02 FIX: Validación ligera de scripts al momento de renderizar.
 * No requiere PDO ni user_id. Solo verifica dominios confiables.
 * @return string El script si es seguro, o string vacío si no lo es.
 */
function render_safe_script(string $script): string {
    if (empty($script)) return '';
    // Si no contiene <script, es probablemente un AdSense snippet HTML puro — permitir
    if (!preg_match('/<script/i', $script)) return $script;
    $allowed = ['googletagmanager.com','google-analytics.com','analytics.google.com','connect.facebook.net','tawk.to','googleads.g.doubleclick.net','pagead2.googlesyndication.com','www.googleadservices.com','cdn.tiny.cloud'];
    preg_match_all('/src=["\']([^"\']+)["\']/i', $script, $matches);
    foreach ($matches[1] ?? [] as $url) {
        $domain = parse_url($url, PHP_URL_HOST);
        $ok = false;
        foreach ($allowed as $d) { if ($domain && str_ends_with($domain, $d)) { $ok = true; break; } }
        if (!$ok && $domain) {
            try {
                \App\Services\LoggerService::security()->warning('Script de publicidad bloqueado por dominio no autorizado', [
                    'domain' => $domain,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ]);
            } catch (\Throwable $e) {
                error_log("HIGH-NEW-02: Script de publicidad bloqueado por dominio no autorizado: $domain");
            }
            return '<!-- script blocked by security policy -->';
        }
    }
    return $script;
}
